<?php
/****
* Copyright 2007 Thomas Welfley and Greg Allard
* 
* Licensed under the Apache License, Version 2.0 (the "License");
* you may not use this file except in compliance with the License.
* You may obtain a copy of the License at
* 
*     http://www.apache.org/licenses/LICENSE-2.0
* 
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
****/

abstract class data_access  {
	# Properties
	protected $table_name;													// Name of the table in the database
	protected $id_field;													// The field in the table that has the primary key
	protected $last_mod_id_field;											// The last modifier's id field
	protected $deleted_field;												// The field in the table that is the deleted flag
	protected $last_mod_field;												// The field in the table that is the lastmod time
	protected $created_field;												// The field in the table that is the time created
	protected $archive_table_name;											// Name of the history table in the database
	public    $field_values;												// All of the fields in the table and their values
	protected $db;                 											// Database connection resource
	private   $errors;														// List of errors encountered
	public    $lang;														// Copy of the current lang array
	protected $get_new_id;													// Flag for getting new record id on creation
	protected $soft_delete;													// Flag for using delete field or deleting from db
	protected $options;														// An array containing options for functions
	
	# Referecnes from page object
	protected $page_current_user;											// Reference to page's authorizor object
	
	# Properties for sets of multiple results
	public $result_set;														// This will contain an array of objects
	public $result_id_set;													// This will contain an array of ids
	public $num_results;													// Number of results for this query
	public $total_avail;													// Number of results total in db before limit
	public $group_counts;													// Values and counts of groupby'd columns
	
	
	function __construct($parameters = array())  {
		global $errors;
		global $lang;
		global $site_conf;
		
		# Set obj properties to default values.
		$this->field_values				= array();
		$this->lang						= $lang;
		$this->result_set				= array();
		$this->result_id_set			= array();
		$this->soft_delete				= TRUE;								// Use soft delete. SET deleted_field = 1
		$this->options					= array();
		$this->num_results				= 0;
		$this->total_avail				= 0;
		
		# Set references
		$this->errors					=& $errors;							// Reference to CyTE's error list.
		
		# Set the object's attributes from the parameters array.
		$this->set_attributes($parameters);
		
		# Set default options for record set functions
		$this->options['start']			= 0;
		$this->options['limit']			= 0;
		$this->options['sort']			= $this->id_field;
		$this->options['direction']		= 'ASC';
		$this->options['get_new_id']	= FALSE;
		$this->options['dsn']			= $site_conf['dsn'];
	}
	
	
	
	# Define in implementation: will check required attributes' validity for functions
	abstract protected function check_create();
	abstract protected function check_get();
	abstract protected function check_edit();
	abstract protected function check_delete();
	abstract protected function check_set();
	
	
	
	/**		<set_attributes>
	 *
	 * Set the attributes of the current instantiated object
	 *
	 * @author		Greg Allard,	Thomas Welfley
	 * @version		1.0.1		11/28/6
	 * @param		mixed		can be either <object> or <array>
	 * @return		none
	 *
	 */
	private function set_attributes($parameter_array) {
		if (is_object($parameter_array))  {  								// if an object is passed
			$parameter_array = get_object_vars($parameter_array);			// convert it to an array
		}
		
        $this_properties = get_object_vars($this);
		
		# Loop through and set all of the object's attributes that exist to the corresponding paremeter values
        foreach ($this_properties as $key => $value)  {
			// make sure that parameters aren't trying to change these attributes
			if ($key != 'field_values' && $key != 'db')  {
				// Check if the property actually exists and if it does, set it equal to its corresponding value.
				if (array_key_exists($key, $parameter_array))  {
					$this->$key = $parameter_array[$key];
				}
			}
			
			# Set created and last_mod attributes to corresponding field names in the implemented class
			if (strpos($key, "_last_mod") !== FALSE)  {
				$this->last_mod_field = $key;
			}
			if (strpos($key, "_created") !== FALSE)  {
				$this->created_field = $key;
			}
        }
	}
	
	
	
	/**		<set_field_values>
	 *
	 * This function updates or populates the $this->field_values array with the 
	 * contents of the array that is passed. It will check to make sure that the 
	 * field_name exists before adding it to the array. Can take either an 
	 * associative array or an object as input. If it is updating the contents
	 * of this object, like someone is editing what is in the database, it will
	 * make a copy of the current object before changing values. This copy may 
	 * be used in a history table to show the changes from version to version.
	 *
	 * Does it really? Show me. probly not needed since using select ... insert
	 *
	 * @author		Greg Allard
	 * @version		3.0.2		4/17/7
	 * @param		mixed		can be either <object> or <array>
	 * @return		none
	 *
	 */
	function set_field_values($row)  {
		if (is_object($row))  {  					// if an object is passed
			$row = get_object_vars($row);			// convert it to an array
		}
		
		/*	This function can't just set $this->field_values equal to $row because 
			we don't want to allow malformed sql queries to be formed from improper 
			arrays passed to this function.
		*/
		
		// get the name of the implementing class
		$this_class = get_class($this);
		
		// get the vars of the implementing class
		$this_class_vars = get_class_vars_noparent($this_class);
		
		// loop through the passed array
		foreach ($row as $key => $value)  {
			// if the key exists as a var in the class
			if (array_key_exists($key, $this_class_vars))  {
				// update this->key to the new value
				$this->$key = $value;
			}
		}
	}
	
	
	
	/**		<parse_options>
	 *
	 * Add the contents of the passed array to <'$this->options'>. Not just setting
	 * $this->options = $options because there may be options previously set that 
	 * shouldn't be overridden.
	 *
	 * @author		Greg Allard
	 * @version		2.0.2		6/26/7
	 * @param		array		contains the options to add
	 * @return		none
	 *
	 */
	function parse_options($options = array())  {
		if (is_array($options) && count($options) > 0)  {
			foreach ($options as $key => $value)  {
				// update existing options and add new ones
				// leave the rest at default
				$this->options[$key] = $value;
			}
		}
	}
	
	
	
	/**		<prepare>
	 *
	 * Check if the database has been connected and connect if needed. Singleton method
	 * insures that there is only one connection made.
	 *
	 * @author		Greg Allard
	 * @version		3.2.1		2/21/8		switched to MDB2
	 * @param		none
	 * @return		mixed		true on success or an error object on failure
	 *
	 */
	protected function prepare()  {
		//$this->db = singleton_db::get_instance($this->options['dsn']);
		$this->db = MDB2::singleton($this->options['dsn']);
		
		if (PEAR::isError($this->db))  {			// if there is a failure
			return $this->db;						// return an error message
		}
		else  {
			return true;
		}
	}
	
	
	
	# CRUD functions for Creating, Retrieving, Updating, and Deleting a single record in the database
	
	
	
	/**		<create>
	 *
	 * This function will push the contents of this object into a new record.
	 * It uses the table_name and id_field variables to automatically generate a proper
	 * INSERT statement.
	 *
	 * @author		Greg Allard,	Kevin Hallmark
	 * @version		3.0.3		4/17/7
	 * @param		none
	 * @return		boolean		true on success and false on failure
	 *
	 */
	function create($options = array())  {
		$this->parse_options($options);
		if ($this->check_create())  {
			if ($this->prepare() !== true)  {
				$this->errors[] = $this->lang['db_101'];  // unable to connect
				return FALSE;
			}
			
			// Start the SQL statement
			$sql = "INSERT INTO ".$this->table_name." SET ";
			
			// Set the created time to now
			if (isset($this->created_field) && $this->created_field != '' && (!isset($this->{$this->created_field}) || $this->{$this->created_field} == 0))  {
				$this->{$this->created_field} = time();
			}
			
			// Set the last mod time
			if (isset($this->last_mod_field) && $this->last_mod_field != '' && isset($this->options['last_mod_time']) && $this->options['last_mod_time'] != '')  {
				$this->{$this->last_mod_field} = $this->options['last_mod_time'];
			}
			else if (isset($this->last_mod_field) && $this->last_mod_field != '')  {
				$this->{$this->last_mod_field} = time();
			}
			
			// Get the field names
			$field_values = get_class_vars_noparent(get_class($this));
			
			$i = 0;
			// Loop through each of the names and write some sql
			foreach ($field_values as $field_name => $value)  {
				// if not the primary key
				if ($field_name != $this->id_field || (isset($this->$field_name) && $this->$field_name != ''))  {
					// if this isn't the first statement
					if ($i != 0)  {
						// separate statements with commas
						$sql .= " , ";
					}
					// field_name = 'new value'
					$sql .= ' '.$field_name." = '".$this->$field_name."' ";
					$i++;
				}
			}
			
			$result = $this->db->query($sql);
			// check if there was an error executing the query
			if (PEAR::isError($result))  {
				// report error and fail
				$this->errors[] = $this->lang['db_102'].' $data_access->create() '.$result->getMessage().' SQL: '.$sql;  // unable to execute query
				return FALSE;
			}
			else  {  // if no errors
				// Get the newly created ID from the database
				// This can be enabled by setting get_new_id to TRUE in the options passed to the constructor
				if ($this->options['get_new_id'])  {
					// make it find the one just created
					$options = array('requirements' => get_object_vars_noparent($this));
					// remove the empty id from the array
					unset($options['requirements'][$this->id_field]);
					// get it
					$this->get_record($options);
				}
				return TRUE;
			}
		}
		else  {
			return FALSE;
		}
	}
	
	
	
	/**		<get_record>
	 *
	 * This function will retrieve a record from the database based on the 
	 * options passed. The function allows the options to be formatted in 
	 * three ways. One way is 'field_name' => 'value'. This will get a result 
	 * where key = value. Another option is to have the value as an array 
	 * that contains possible values of 'field_name'. The last option is to 
	 * have that second array have an operator as the first value in the array. 
	 * Operators allowed are =,  >,  < ,  >=,  <=, !=, LIKE, IN
	 *
	 * Example array:
	 *
	 * array ( 
	 *			"requirements" => array (
	 * 										"dbfieldname" => array (
	 *																	"option1", "option2"
	 *																), 
	 * 										"dbfieldname2" => array (
	 *																	">", "option"
	 *																), 
	 * 										"user_level" => array (
	 *																	">=", $auth_lev
	 *																), 
	 * 										"deleted" => 0
	 *									)
	 * 		)
	 *
	 *
	 *		This function can also be passed an id of the record to get.
	 *
	 *
	 * @author		Greg Allard
	 * @version		3.0.4		1/17/8
	 * @param		array		an array containing requirements
	 * @return		boolean		true on success and false on failure
	 *
	 * @see			<parse_query_requirements>		get_record passes "requirements" to parse_query_requirements
	 *
	 */
	function get_record($options = array())  {
		// allow to pass an id to this function to get the record with that id
		if (!is_array($options) && is_numeric($options))  {
			// set the options to look for the record with that id
			$options = array(
				'requirements'		=>		array($this->id_field => $options)
			);
		}
		
		$this->parse_options($options);
		
		// show only non deleted records by default
		if (!isset($this->options['requirements'][$this->deleted_field]) || $this->options['requirements'][$this->deleted_field] == '')  {
			// if there is a deleted field
			if ($this->deleted_field != '')  {
				$this->options['requirements'][$this->deleted_field] = 0;
			}
		}
		
		if ($this->check_get())  {
			if ($this->prepare() !== true)  {
				$this->errors[] = $this->lang['db_101'];  // unable to connect
				return FALSE;
			}
			
			
			// Start the SQL statement
			$sql  = ' SELECT * FROM '.$this->table_name.' ';
			
			
			
			# parse the requirements to build an sql query
			// Check to make sure the requirements are set
			if (isset($this->options['requirements']) && is_array($this->options['requirements']) && count($this->options['requirements']) > 0)  {
				$sql .= ' WHERE '.data_access::parse_query_requirements($this->options['requirements']);
			}
			
			
			$result = $this->db->query($sql);
			// check if there was an error executing the query
			if (PEAR::isError($result))  {
				// report error and fail
				$this->errors[] = $this->lang['db_102'].' $data_access->get_record() '.$result->getMessage().' SQL: '.$sql;  // unable to execute query
				return FALSE;
			}
			else  {  // if no errors
				// fill the field_values with the result from the database
				while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC))  {
					$this->set_field_values($row);
				}
				return TRUE;
			}
		}
		else  {
			return FALSE;
		}
	}
	
	
	
	/**		<edit>
	 *
	 * This function UPDATEs a row in the database using <id_field>, <field_values> and <table_name>.
	 *
	 * @author		Greg Allard,	Kevin Hallmark
	 * @version		3.0.4		4/17/7
	 * @param		none
	 * @return		boolean		true on success and false on failure
	 *
	 */
	function edit($options = array())  {
		$this->parse_options($options);
		if ($this->check_edit())  {
			if ($this->prepare() !== true)  {
				$this->errors[] = $this->lang['db_101'];  // unable to connect
				return FALSE;
			}
			
			// if transactions are supported, start one
			if ($this->db->supports('transactions'))  {
				$this->db->beginTransaction();
			}
			
			// Start the SQL statement
			$sql = ' UPDATE '.$this->table_name.' SET ';
			
			// Set the last mod time
			if (isset($this->options['last_mod_time']) && $this->options['last_mod_time'] != '')  {
				$this->{$this->last_mod_field} = $this->options['last_mod_time'];
			}
			else  {
				$this->{$this->last_mod_field} = time();
			}
			
			// Get the field names
			$field_values = get_class_vars_noparent(get_class($this));
			
			$i = 0;
			// Loop through each of the names and write some sql
			foreach ($field_values as $field_name => $value)  {
				// if not the primary key
				if ($field_name != $this->id_field)  {
					// if this isn't the first statement
					if ($i != 0)  {
						// separate statements with commas
						$sql .= " , ";
					}
					// field_name = 'new value'
					$sql .= ' '.$field_name." = '".$this->$field_name."' ";
					$i++;
				}
			}
			
			// WHERE primary_key_field = id_to_update
			$sql .= ' WHERE '.$this->id_field." = '".$this->{$this->id_field}."' ";
			
			// before executing the update query, call the archive function to copy old contents
			if ($this->archive())  {
				$result = $this->db->query($sql);
				// check if there was an error executing the query
				if (PEAR::isError($result))  {
					// rollback the transaction, report error, and fail
					if ($this->db->inTransaction())  {
						$this->db->rollback();  // end the transaction
					}
					$this->errors[] = $this->lang['db_102'].' $data_access->edit() '.$result->getMessage().' SQL: '.$sql;  // unable to execute query
					return FALSE;
				}
				else  {  // if no errors
					// only commit the changes if everything checks out
					if ($this->db->inTransaction())  {
						$this->db->commit();  // commit the changes to finish the transaction
					}  // if transactions aren't supported, query would already have committed
					return TRUE;
				}
			}
			else  {
				if ($this->db->inTransaction())  {
					$this->db->rollback();  // end the transaction
				}
				// archive failed, don't update without it
				return FALSE;
			}
		}
		else  {
			return FALSE;
		}
	}
	
	
	
	/**		<delete>
	 *
	 * This function either sets deleted_field = 1 or deletes a row from the table
	 * depending on <'$this->soft_delete'>
	 *
	 * @author		Greg Allard
	 * @version		3.0.4		4/17/7
	 * @param		none
	 * @return		boolean		true on success and false on failure
	 *
	 */
	function delete($options = array())  {                                    
		$this->parse_options($options);
		if ($this->check_delete())  {
			if ($this->prepare() !== true)  {
				$this->errors[] = $this->lang['db_101'];  // unable to connect
				return FALSE;
			}
			
			// if transactions are supported, start one
			if ($this->db->supports('transactions'))  {
				$this->db->beginTransaction();
			}
			
			// if the id of the row to delete is set
			if (isset($this->{$this->id_field}) && $this->{$this->id_field} != '')  {
				// if we want to set deleted = 1 instead of removing from the db
				if ($this->soft_delete)  {
					$sql  = ' UPDATE '.$this->table_name.' SET ';
					$sql .= ' '.$this->deleted_field.'     = 1 ';
					
					if (isset($this->last_mod_id_field) && $this->last_mod_id_field != '' && 
						isset($this->page_current_user) && $this->page_current_user->user_id != '')  {
							$sql .= ' , '.$this->last_mod_id_field." = '".$this->page_current_user->user_id."' ";
					}
					
					$sql .= ' WHERE '.$this->id_field.' = '.$this->{$this->id_field}.' ';
					
					// before executing the update query, call the archive function to copy old contents
					if ($this->archive())  {
						$result = $this->db->query($sql);
						// check if there was an error executing the query
						if (PEAR::isError($result))  {
							// rollback the transaction, report error, and fail
							if ($this->db->inTransaction())  {
								$this->db->rollback();  // end the transaction
							}
							$this->errors[] = $this->lang['db_102'].' $data_access->delete() '.$result->getMessage().' SQL: '.$sql;  // unable to execute query
							return FALSE;
						}
						else  {  // if no errors
							// only commit the changes if everything checks out
							if ($this->db->inTransaction())  {
								$this->db->commit();  // commit the changes to finish the transaction
							}  // if transactions aren't supported, query would already have committed
							return TRUE;
						}
					}
					else  {
						if ($this->db->inTransaction())  {
							$this->db->rollback();  // end the transaction
						}
						// archive failed. don't delete without it
						return FALSE;
					}
				}
				else  {  // else remove from db
					$sql  = ' DELETE FROM '.$this->table_name.' WHERE ';
					$sql .= ' '.$this->id_field.' = '.$this->{$this->id_field}.' ';
					
					// before executing the delete query, call the archive function to copy old contents
					if ($this->archive())  {
						$result = $this->db->query($sql);
						// check if there was an error executing the query
						if (PEAR::isError($result))  {
							// rollback the transaction, report error, and fail
							if ($this->db->inTransaction())  {
								$this->db->rollback();  // end the transaction
							}
							$this->errors[] = $this->lang['db_102'].' $data_access->delete() '.$result->getMessage().' SQL: '.$sql;  // unable to execute query
							return FALSE;
						}
						else  {  // if no errors
							if ($this->db->inTransaction())  {
								$this->db->commit();  // commit the changes to finish the transaction
							}  // if transactions aren't supported, query would already have committed
							return TRUE;
						}
					}
					else  {
						if ($this->db->inTransaction())  {
							$this->db->rollback();  // end the transaction
						}
						// archive failed. don't delete without it
						return FALSE;
					}
				}
			}
		}
		else  {
			return FALSE;
		}
	}
	
	
	
	# Set retrieval functions
	
	
	
	/**		<get_set>
	 *
	 * This function will retrieve a set of records from the database based on the 
	 * options passed. The function allows the options to be formatted in 
	 * three ways. One way is 'field_name' => 'value'. This will get a result 
	 * where key = value. Another option is to have the value as an array 
	 * that contains possible values of 'field_name'. The last option is to 
	 * have that second array have an operator as the first value in the array. 
	 * Operators allowed are =,  >,  < ,  >=,  <=, !=, LIKE, IN.
	 *
	 * There is also the possibility to get records connected through a join
	 * table. In the parameter array, pass 'join' and set it to an array with
	 * the values 'join_table', 'join_with_id_field', and 'id' set to the
	 * respective values. 'alt_id_field' can also be set if the join table
	 * uses a different field name than this class does for its id.
	 *
	 * Example array:
	 *
	 * array ( 
	 *			"requirements" => array (
	 * 										"dbfieldname" => array (
	 *																	"option1", "option2"
	 *																), 
	 * 										"dbfieldname2" => array (
	 *																	">", "option"
	 *																), 
	 * 										"user_level" => array (
	 *																	">=", $auth_lev
	 *																), 
	 * 										"deleted" => 0
	 *									),
	 *			"join" => array (
	 *										"join_table"			=>		"join_that_and_this",
	 *										"join_with_id_field"	=>		"foreign_id",
	 *										"id"					=>		23,
	 *										"alt_id_field"			=>		"diff_id"
	 *							)
	 * 		)
	 *
	 * @author		Greg Allard
	 * @version		1.1.1		1/17/8
	 * @param		array		an array containing requirements
	 * @return		boolean		true on success and false on failure
	 *
	 * @see			<parse_query_requirements>		get_set passes "requirements" to parse_query_requirements
	 *
	 */
	function get_set($options = array())  {
		$this->parse_options($options);
		// show only non deleted records by default
		if (!isset($this->options['requirements'][$this->deleted_field]) || $this->options['requirements'][$this->deleted_field] == '')  {
			// if there is a deleted field
			if ($this->deleted_field != '')  {
				$this->options['requirements'][$this->deleted_field] = 0;
			}
		}
		if ($this->check_set())  {
			if ($this->prepare() !== true)  {
				$this->errors[] = $this->lang['db_101'];  // unable to connect
				return FALSE;
			}
			
			// make sure to reset the limit in case it was used with another query
			$this->db->setLimit(0,0);
			
			/*	If limiting the results, then two queries are needed since one is needed for
				the limited set and another will be used to count the total available.
			*/ 
			$sql_select1 = "SELECT * ";
			$sql_select2 = "SELECT count(*) ";
			
			$field_prefix = '';  // used on field names when joins are used
			
			// look for joining options
			if (isset($this->options['join']) && is_array($this->options['join']) && count($this->options['join']) > 0)  {
				if (isset($this->options['join']['join_table']) && $this->options['join']['join_table'] != ''  &&
					isset($this->options['join']['join_with_id_field']) && $this->options['join']['join_with_id_field'] != ''  &&
					isset($this->options['join']['id']) && $this->options['join']['id'] > 0
				)  {
					// make sure it's a real integer
					$this->options['join']['id'] = intval($this->options['join']['id']);
					
					
					// check if alt_id_field should be used
					if (isset($this->options['join']['alt_id_field']) && $this->options['join']['alt_id_field'] != '')  {
						$id_field = $this->options['join']['alt_id_field'];
					}
					else  {
						$id_field = $this->id_field;
					}
					
					// FROM this_table, join_table WHERE this.id = join.id AND join.foreign_id = #
					$sql_from  = 'FROM '.$this->table_name.', '.$this->options['join']['join_table'].' '.
						'WHERE '.$this->table_name.'.'.$this->id_field.' = '.$this->options['join']['join_table'].'.'.$id_field.' '.
						'AND '.$this->options['join']['join_table'].'.'.$this->options['join']['join_with_id_field'].' = '.$this->options['join']['id'].' ';
					
					// set the prefix so the requirements won't be ambiguous
					$field_prefix = $this->table_name.'.';
				}
				else  {
					$this->errors[] = $this->lang['db_missing_param'];
					return false;
				}
			}
			else  {
				$sql_from    = 'FROM '.$this->table_name.' ';
			}
			
			
			
			
			
			# parse the requirements to build an sql query
			// Check to make sure the requirements are set
			if (isset($this->options['requirements']) && is_array($this->options['requirements']) && count($this->options['requirements']) > 0)  {
				if ($field_prefix == '')  {
					$sql_from .= ' WHERE ';  // add it on here. can't add above since there might not be a WHERE
				}
				else  {
					$sql_from .= ' AND ';  // add it on here. can't add above since there might not be an AND
				}
				$sql_from .= data_access::parse_query_requirements($this->options['requirements'], $field_prefix);
			}
			
			
			
			// set the way to sort the results
			if ($this->options['sort'] != '' && $this->options['direction'] != '')  {
				$sql_order   = 'ORDER BY '.$field_prefix.$this->options['sort'].' '.$this->options['direction'].' ';
			}
			else  {
				$sql_order = '';
			}
			
			// if the limit isn't set to zero
			if ($this->options['limit'] != 0)  {
				# create two queries
				// the limited query
				//$sql_limit   = 'LIMIT '.$this->options['start'].', '.$this->options['limit'].' ';
				$sql1        = $sql_select1.$sql_from.$sql_order;//.$sql_limit;
				
				// and the query to get the count
				$sql2              = $sql_select2.$sql_from;
				$this->total_avail = $this->db->queryOne($sql2);  // get the count
				
				// Set the limit with MDB2's set limiit function after the count is retrieved
				$this->db->setLimit($this->options['limit'], $this->options['start']);
			}
			// else the limit wasn't changed
			else  {
				// get them all. only need one db query
				$sql1        = $sql_select1.$sql_from.$sql_order;
			}
			$result = $this->db->query($sql1);
			
			// make sure to reset the limit for every other query
			$this->db->setLimit(0,0);
			
			if (PEAR::isError($result))  {
				$this->errors[] = $this->lang['db_102'].' $data_access->get_set() '.$result->getMessage().' SQL: '.$sql1;  // unable to execute query
				return FALSE;
			}
			else  {
				
				//unset the result class to prevent results from being appended to result_set instead of replacing
				unset($this->result_set);
				$this->result_set = Array();
				while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC))  {
					// create a new object of this class or class passed
					if (isset($this->options['class']) && $this->options['class'] != '' && class_exists($this->options['class'], true))  {
						$result_object = new $this->options['class'];
					}
					else  {
						$this_class = get_class($this);
						$result_object = new $this_class;
					}
					
					// fill it with the results
					$result_object->set_field_values($row);
					
					// store the results in this object
					
					$this->result_set[]    = $result_object;		// this will put the object in to this->objects
					if ($this->id_field != '')  {
						$this->result_id_set[] = $row[$this->id_field];	// an array of ids is used in some cases
					}
				}
				
				$this->num_results = count($this->result_set);
				
				// if total_avail wasn't set yet
				if ($this->total_avail == 0)  {
					// set it to num_results
					$this->total_avail =$this->num_results;
				}
				
				return TRUE;
			}
		}
		else  {
			return FALSE;
		}
	}
	
	
	/**		<parse_query_requirements>
	 *
	 * This function will retrieve a set of records from the database based on the 
	 * options passed. The function allows the options to be formatted in 
	 * three ways. One way is 'field_name' => 'value'. This will get a result 
	 * where key = value. Another option is to have the value as an array 
	 * that contains possible values of 'field_name'. The last option is to 
	 * have that second array have an operator as the first value in the array. 
	 * Operators allowed are =,  >,  < ,  >=,  <=, !=, LIKE, IN.
	 *
	 * There is also the possibility to get records connected through a join
	 * table. In the parameter array, pass 'join' and set it to an array with
	 * the values 'join_table', 'join_with_id_field', and 'id' set to the
	 * respective values. 'alt_id_field' can also be set if the join table
	 * uses a different field name than this class does for its id.
	 *
	 * Example array:
	 *
	 * 
	 *								array (
	 * 										"dbfieldname" => array (
	 *																	"option1", "option2"
	 *																), 
	 * 										"dbfieldname2" => array (
	 *																	">", "option"
	 *																), 
	 * 										"user_level" => array (
	 *																	">=", $auth_lev
	 *																), 
	 * 										"deleted" => 0
	 *									)
	 * 
	 *
	 * @author		Greg Allard
	 * @version		1.0.0		2/12/8
	 * @param		array		an array containing requirements
	 * @param		string		the prefix of the field name. used when joining tables
	 * @return		string		the sql text
	**/
	static function parse_query_requirements($requirements = array(), $field_prefix = '')  {
		$sql_from = '';
		
		// check if something was passed
		if (isset($requirements) && is_array($requirements) && count($requirements) > 0)  {
			// initialize the allowed operators array
			$operators = array ("=", ">", "<", ">=", "<=", "!=", "LIKE", "IN");
			$i = 0;  // initialize the loop counter
			// loop through the requirements array
			foreach ($requirements as $field_name => $options)  {
				// if $options is an array
				if (is_array($options))  {
					// if we are not in the first loop
					if ($i > 0)  {
						$sql_from .= ' AND ';  // add AND between statements
					}
					
					$open_paren = false;  // flag for if there was an open parenthesis
					$j = 0;  // loop counter since $key may not start at zero or be integers
					// need to loop through the options
					foreach ($options as $key => $option)  {
						// if this option is an operator
						if (in_array ($option, $operators,true))  {
							// temporarily store this which will be used in the next loop
							$temp = ' '.$field_prefix.''.$field_name.' '.$option.' ';
						}
						// if we have created a temporary sql statement
						elseif (isset($temp) && $temp != '')  {
							// append the value to the end of temp and append that to the end of sql
							$sql_from .= ' '.$temp." '".$option."' ";
							unset($temp);
						}
						// else we have multiple values to use
						else  {
							// if this is the first in the loop
							if ($j == 0)  {
								// set open_paren to true
								$open_paren = TRUE;
								$sql_from .= ' ( '.$field_prefix.''.$field_name." = '".$option."' ";
							}
							else  {
								$sql_from .= ' OR '.$field_prefix.$field_name." = '".$option."' ";
							}
						}
						$j++;
					}
					// if there was an opening ( used
					if ($open_paren)  {
						$sql_from .= ' ) ';
						unset($open_paren);
					}
					$i++; // update the count
				}
				// else we know that the = operator is the one to use and only one value to use
				else if (!is_object($options))   {
					// if we are not in the first loop
					if ($i > 0)  {
						$sql_from .= ' AND ';  // add AND between statements
					}
					
					$sql_from .= ' '.$field_name." = '".$options."' ";
					
					$i++; // update the count
				}
			}
		}
		
		return $sql_from;
	}
	
	
	
	# History functions
	
	
	
	/**		<archive>
	 *
	 * This function copies the data in a table for a record that is about to be changed.
	 * It stores the data in the history table.
	 *
	 * @author		Greg Allard
	 * @version		1.0.2		4/17/7
	 * @param		none
	 * @return		boolean		true on success and false on failure
	 *
	 */
	function archive($options = array())  {
		if (isset($this->archive_table_name) && trim($this->archive_table_name) != '')  {
			$this->parse_options($options);
			if ($this->prepare() !== true)  {
				$this->errors[] = $this->lang['db_101'];  // unable to connect
				return FALSE;
			}
			
			// Start the SQL statement. Using an INSERT ... SELECT query to avoid old data
			$sql = "INSERT INTO ".$this->archive_table_name." (";
			
			// Get the field names
			$keys = array_keys(get_class_vars_noparent(get_class($this)));
			
			for ($i = 0; $i < count($keys); $i++ )  {
				// if this isn't the first statement
				if ($i != 0)  {
					// separate statements with commas
					$sql .= " , ";
				}
				// field_name
				$sql .= ' '.$keys[$i]."  ";
			}
			$sql .= ' ) SELECT * FROM '.$this->table_name.' ';
			$sql .= ' WHERE '.$this->id_field." = '".$this->{$this->id_field}."' ";
			
			$result = $this->db->query($sql);
			// check if there was an error executing the query
			if (PEAR::isError($result))  {
				// rollback the transaction, report error, and fail
				if ($this->db->inTransaction())  {
					$this->db->rollback();  // end the transaction
				}
				$this->errors[] = $this->lang['db_102'].' $data_access->archive() '.$result->getMessage().' SQL: '.$sql;  // unable to execute query
				return FALSE;
			}
			else  {  // if no errors
				return TRUE;
			}
		}
		else  {  // since its not set up, just allow things to go through normally
			return TRUE;
		}
	}
	
	
	
	/**
	 *
	 *		This function joins this object's id_field with a foreign_id in a join table
	 *
	 *	@author		Greg Allard
	 *	@version	1.0.1	9/5/7
	 *	@param		array 	containing 
	 *				"id"			The foreign_id to join
	 *				"id_field"		The name of the foreign_id field
	 *				"table"			The table to join in
	 *				"alt_id_field"	[optional] The alternate name of this table's id field
	 *	@return		bool	true or false depending on success
	 *
	 */
	function add_join($options = array())  {
		$this->parse_options($options);
		// check for required parameters
		if (is_array($options)			&&
			isset($options['id'])		&&
			isset($options['id_field'])	&&
			isset($options['table'])	&&
			isset($this->{$this->id_field})
		)  {
			// turn the ids into ints
			$this->{$this->id_field} = intval($this->{$this->id_field});
			$options['id']           = intval($options['id']);
			
			// then make sure they are valid values for ids
			if ($options['id'] > 0 && $this->{$this->id_field} > 0)  {
				// then check to make sure there isn't already a join for this
				if (!$this->has_join($options))  {
					// prep for query time
					if ($this->prepare() !== true)  {
						$this->errors[] = $this->lang['db_101'];  // unable to connect
						return FALSE;
					}
					
					// check if alt_id_field should be used
					if (isset($options['alt_id_field']) && $options['alt_id_field'] != '')  {
						$id_field = $options['alt_id_field'];
					}
					else  {
						$id_field = $this->id_field;
					}
					
					//		INSERT INTO table_name (id_1, id_2) VALUES (#, #)
					$sql = 'INSERT INTO '.$options['table'].' '.
						   '('.$id_field.', '.$options['id_field'].') VALUES '.
						   '('.$this->{$this->id_field}.', '.$options['id'].') ';
					$result = $this->db->query($sql);
					if (PEAR::isError($result))  {
						$this->errors[] = $this->lang['db_102'].' $data_access->add_join() '.$result->getMessage().' SQL: '.$sql;  // unable to execute
						return false;
					}
					else  {
						// no errors. added successfully
						return true;
					}
				}
				else  {
					$this->errors[] = $this->lang['db_has_join'];
					return false;
				}
			}
			else  {
				$this->errors[] = $this->lang['db_missing_param'];
				return false;
			}
			
		}
		else  {
			$this->errors[] = $this->lang['db_missing_param'];
			return false;
		}
	}
	
	/**
	 *
	 *		This function removes a join from table where this object's id_field is joined with a foreign_id
	 *
	 *	@author		Greg Allard
	 *	@version	1.0.1	9/5/7
	 *	@param		array 	containing 
	 *				"id"			The foreign_id
	 *				"id_field"		The name of the foreign_id field
	 *				"table"			The table where they are joined
	 *				"alt_id_field"	[optional] The alternate name of this table's id field
	 *	@return		bool	true or false depending on success
	 *
	 */
	function remove_join($options = array())  {
		$this->parse_options($options);
		// check for required parameters
		if (is_array($options)			&&
			isset($options['id'])		&&
			isset($options['id_field'])	&&
			isset($options['table'])	&&
			isset($this->{$this->id_field})
		)  {
			// turn the ids into ints
			$this->{$this->id_field} = intval($this->{$this->id_field});
			$options['id']           = intval($options['id']);
			
			// then make sure they are valid values for ids
			if ($options['id'] > 0 && $this->{$this->id_field} > 0)  {
				// then check to make sure there is a join to remove
				if ($this->has_join($options))  {
					// prep for query time
					if ($this->prepare() !== true)  {
						$this->errors[] = $this->lang['db_101'];  // unable to connect
						return FALSE;
					}
					
					// check if alt_id_field should be used
					if (isset($options['alt_id_field']) && $options['alt_id_field'] != '')  {
						$id_field = $options['alt_id_field'];
					}
					else  {
						$id_field = $this->id_field;
					}
					
					
					//		DELETE FROM table WHERE id_1 = # AND id_2 = #
					$sql = 'DELETE FROM '.$options['table'].' WHERE 
							'.$id_field.' = '.$this->{$this->id_field}.' AND 
							'.$options['id_field'].' = '.$options['id'].' ';
					$result = $this->db->query($sql);
					if (PEAR::isError($result))  {
						$this->errors[] = $this->lang['db_102'].' $data_access->remove_join() '.$result->getMessage().' SQL: '.$sql;  // unable to execute
						return false;
					}
					else  {
						// no errors. removed successfully
						return true;
					}
				}
				else  {
					$this->errors[] = $this->lang['not_has_join'];
					return false;
				}
			}
			else  {
				$this->errors[] = $this->lang['db_missing_param'];
				return false;
			}
			
		}
		else  {
			$this->errors[] = $this->lang['db_missing_param'];
			return false;
		}
	}
	
	/**
	 *
	 *		This function checks to see if a join exists between ids in a join table
	 *
	 *	@author		Greg Allard
	 *	@version	1.0.1	9/5/7
	 *	@param		array 	containing 
	 *				"id"			The foreign_id
	 *				"id_field"		The name of the foreign_id field
	 *				"table"			The table where they are joined
	 *				"alt_id_field"	[optional] The alternate name of this table's id field
	 *	@return		bool	true or false depending on success
	 *
	 */
	function has_join($options = array())  {
		$this->parse_options($options);
		// check for required parameters
		if (is_array($options)			&&
			isset($options['id'])		&&
			isset($options['id_field'])	&&
			isset($options['table'])	&&
			isset($this->{$this->id_field})
		)  {
			// turn the ids into ints
			$this->{$this->id_field} = intval($this->{$this->id_field});
			$options['id']           = intval($options['id']);
			
			// then make sure they are valid values for ids
			if ($options['id'] > 0 && $this->{$this->id_field} > 0)  {
				// prep for query time
				if ($this->prepare() !== true)  {
					$this->errors[] = $this->lang['db_101'];  // unable to connect
					return FALSE;
				}
				
				// check if alt_id_field should be used
				if (isset($options['alt_id_field']) && $options['alt_id_field'] != '')  {
					$id_field = $options['alt_id_field'];
				}
				else  {
					$id_field = $this->id_field;
				}
				
				//		SELECT * FROM table WHERE id_1 = # AND id_2 = #
				$sql = 'SELECT * FROM '.$options['table'].' WHERE 
						'.$id_field.' = '.$this->{$this->id_field}.' AND 
						'.$options['id_field'].' = '.$options['id'].' ';
				
				$result = $this->db->query($sql);
				if (PEAR::isError($result))  {
					$this->errors[] = $this->lang['db_102'].' $data_access->has_join() '.$result->getMessage().' SQL: '.$sql;  // unable to execute
					return false;
				}
				else  {
					// no errors
					while ($row = $result->fetchRow(MDB2_FETCHMODE_OBJECT))  {
						if (isset($row->$id_field) && $row->$id_field != '')  {
							return true;
						}
						else  {
							return false;
						}
					}
				}
			}
			else  {
				$this->errors[] = $this->lang['db_missing_param'];
				return false;
			}
			
		}
		else  {
			$this->errors[] = $this->lang['db_missing_param'];
			return false;
		}
	}
	
	
	
	/**
	 *
	 *		This function groups rows by a column and returns col value and count for each
	 *
	 *	@author		Greg Allard
	 *	@version	1.0.1	10/3/7
	 *	@param		array 	containing 
	 *				"group_col"		The column to group
	 *	@return		bool	true or false depending on success
	 *
	 */
	function group_count($options = array())  {
		$this->parse_options($options);
		if (isset($this->options['group_col']) && $this->options['group_col'] != '')  {
			if ($this->prepare() !== true)  {
				$this->errors[] = $this->lang['db_101'];  // unable to connect
				return FALSE;
			}
			
			// what to get
			$sql_select1 = 'SELECT '.$this->options['group_col'].', count(*) as count ';
			
			// where to get it
			$sql_from    = 'FROM `'.$this->table_name.'` ';
			
			// do the grouping
			$sql_group   = 'GROUP BY '.$this->options['group_col'].' ';
			
			// set the way to sort the results
			$sql_order   = 'ORDER BY '.$this->options['group_col'].' '.$this->options['direction'].' ';
			
			// concatenate em
			$sql1        = $sql_select1.$sql_from.$sql_group.$sql_order;
			
			$result = $this->db->query($sql1);
			if (DB::isError($result))  {
				$this->errors[] = $this->lang['db_102'].' $data_access->group_count() '.$result->getMessage().' SQL: '.$sql1;  // unable to execute query
				return FALSE;
			}
			else  {
				$this->group_counts = array();
				
				while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))  {
					$key = $row[$this->options['group_col']];
					$this->group_counts[$key] = $row['count'];
				}
				
				return TRUE;
			}
		}
		else  {
			return FALSE;
		}
	}
	
	
	// ---------------------------------------------------------------------
	/* *****--------------*********************--------------***************
			---------------- Clean Functions ----------------
	********--------------*********************--------------***************/
	// ---------------------------------------------------------------------
	
	/**			form2db()
	 *
	 * Used for cleaning text from a form before sending it to the DB
	 *
	 * @author       Greg Allard
	 * @version      1.1.3		5/4/7
	 * @param        mixed   $value   This can be an array, object, or a string
	 * @return       mixed   $value   This will return the same type as passed
	 */
	function form2db($value = NULL)  {
		if ($value == NULL)  {
			// get the name of the implementing class
			$this_class = get_class($this);
			
			// get the vars of the implementing class
			$this_class_vars = get_class_vars_noparent($this_class);
			
			// loop through the class vars, cleaning each
			foreach ($this_class_vars as $field_name => $field_value)  {
				if (isset($this->$field_name) && $this->$field_name != '')  {
					$this->$field_name = $this->form2db($this->$field_name);
				}
			}
			
			// if there are child objects
			if ($this->num_results > 0)  {
				// clean them all
				foreach ($this->result_set as $key => $result)  {
					$result->form2db();
				}
			}
		}
		else  {
			if (get_magic_quotes_gpc() == 0)  {  // if slashes aren't automatically added by php
				$value = addslashes($value);     // add some slashes
			}
			return $value;
		}
	}
	
	/**			form2form()
	 *
	 * Used for cleaning text from a form that needs to be put back into a form
	 * Useful when there are errors that need to be fixed before submitting to the DB
	 *
	 * @author       Greg Allard
	 * @version      1.1.2		5/4/7
	 * @param        mixed   $value   This can be an array, object, or a string
	 * @return       mixed   $value   This will return the same type as passed
	 */
	function form2form($value = NULL)  {
		if ($value == NULL)  {
			// get the name of the implementing class
			$this_class = get_class($this);
			
			// get the vars of the implementing class
			$this_class_vars = get_class_vars_noparent($this_class);
			
			// loop through the class vars, cleaning each
			foreach ($this_class_vars as $field_name => $field_value)  {
				if (isset($this->$field_name) && $this->$field_name != '')  {
					$this->$field_name = $this->form2form($this->$field_name);
				}
			}
			
			// if there are child objects
			if ($this->num_results > 0)  {
				// clean them all
				foreach ($this->result_set as $key => $result)  {
					$result->form2form();
				}
			}
		}
		else  {
			if (get_magic_quotes_gpc() != 0)  {  // if slashes are automatically added by php
				$value = stripslashes($value);
			}
			return $this->smart2entities(htmlspecialchars($value));
		}
	}
	
	/**			db2text()
	 *
	 * Used for cleaning text from a the DB that needs to be printed in plain text
	 *
	 * @author       Greg Allard
	 * @version      1.1.2		5/4/7
	 * @param        mixed   $value   This can be an array, object, or a string
	 * @return       mixed   $value   This will return the same type as passed
	 */
	function db2text($value = NULL)  {
		if ($value == NULL)  {
			// get the name of the implementing class
			$this_class = get_class($this);
			
			// get the vars of the implementing class
			$this_class_vars = get_class_vars_noparent($this_class);
			
			// loop through the class vars, cleaning each
			foreach ($this_class_vars as $field_name => $field_value)  {
				if (isset($this->$field_name) && $this->$field_name != '')  {
					$this->$field_name = $this->db2text($this->$field_name);
				}
			}
			
			// if there are child objects
			if ($this->num_results > 0)  {
				// clean them all
				foreach ($this->result_set as $key => $result)  {
					$result->db2text();
				}
			}
		}
		else  {  // convert html, bbcode, and new lines
			return $this->smart2entities(nl2br($this->url2link($this->bbcode2html(htmlspecialchars($this->html2bbcode($value))))));
		}
	}
	
	/**			db2form()
	 *
	 * Used for cleaning text from a the DB that needs to be put in a form to be edited
	 *
	 * @author       Greg Allard
	 * @version      1.1.2		5/4/7
	 * @param        mixed   $value   This can be an array, object, or a string
	 * @return       mixed   $value   This will return the same type as passed
	 */
	function db2form($value = NULL)  {
		if ($value == NULL)  {
			// get the name of the implementing class
			$this_class = get_class($this);
			
			// get the vars of the implementing class
			$this_class_vars = get_class_vars_noparent($this_class);
			
			// loop through the class vars, cleaning each
			foreach ($this_class_vars as $field_name => $field_value)  {
				if (isset($this->$field_name) && $this->$field_name != '')  {
					$this->$field_name = $this->db2form($this->$field_name);
				}
			}
			
			// if there are child objects
			if ($this->num_results > 0)  {
				// clean them all
				foreach ($this->result_set as $key => $result)  {
					$result->db2form();
				}
			}
		}
		else  {  // convert html, bbcode
			return $this->smart2entities(htmlspecialchars($this->html2bbcode($value)));
		}
	}
	
	/**			db2db()
	 *
	 * Used for cleaning text from a the DB that needs to be put in a form to be edited
	 *
	 * @author       Greg Allard
	 * @version      1.1.2		5/4/7
	 * @param        mixed   $value   This can be an array, object, or a string
	 * @return       mixed   $value   This will return the same type as passed
	 */
	function db2db($value = NULL)  {
		if ($value == NULL)  {
			// get the name of the implementing class
			$this_class = get_class($this);
			
			// get the vars of the implementing class
			$this_class_vars = get_class_vars_noparent($this_class);
			
			// loop through the class vars, cleaning each
			foreach ($this_class_vars as $field_name => $field_value)  {
				if (isset($this->$field_name) && $this->$field_name != '')  {
					$this->$field_name = $this->db2db($this->$field_name);
				}
			}
			
			// if there are child objects
			if ($this->num_results > 0)  {
				// clean them all
				foreach ($this->result_set as $key => $result)  {
					$result->db2db();
				}
			}
		}
		else  {  // add slashes since they weren't added from a post
			return addslashes($value);
		}
	}
	
	/**			form2text()
	 *
	 * Used for cleaning text from a form to be diplayed
	 *
	 * @author       Greg Allard
	 * @version      1.1.2		5/4/7
	 * @param        mixed   $value   This can be an array, object, or a string
	 * @return       mixed   $value   This will return the same type as passed
	 */
	function form2text($value = NULL)  {
		if ($value == NULL)  {
			// get the name of the implementing class
			$this_class = get_class($this);
			
			// get the vars of the implementing class
			$this_class_vars = get_class_vars_noparent($this_class);
			
			// loop through the class vars, cleaning each
			foreach ($this_class_vars as $field_name => $field_value)  {
				if (isset($this->$field_name) && $this->$field_name != '')  {
					$this->$field_name = $this->form2text($this->$field_name);
				}
			}
			
			// if there are child objects
			if ($this->num_results > 0)  {
				// clean them all
				foreach ($this->result_set as $key => $result)  {
					$result->form2text();
				}
			}
		}
		else  {  // strip slashes since they were added from a post
			if (get_magic_quotes_gpc() != 0)  {  // if slashes are automatically added by php
				$value = stripslashes($value);
			}
			return $this->smart2entities(nl2br($this->url2link($this->bbcode2html(htmlspecialchars($this->html2bbcode($value))))));
		}
	}
	
	/**			url2link()
	 *
	 * php.net comments contained a function to convert urls to links with html
	 * with or without http:// and also grabs emails.
	 *
	 * @author		Sune Rievers, modified by Greg Allard
	 * @version		1.0.4		5/4/7
	 * @param        mixed   $value   This can be an array, object, or a string
	 * @return       mixed   $value   This will return the same type as passed
	 */
	function url2link($value = NULL)  {
		if ($value == NULL)  {
			// get the name of the implementing class
			$this_class = get_class($this);
			
			// get the vars of the implementing class
			$this_class_vars = get_class_vars_noparent($this_class);
			
			// loop through the class vars, cleaning each
			foreach ($this_class_vars as $field_name => $field_value)  {
				if (isset($this->$field_name) && $this->$field_name != '')  {
					$this->$field_name = $this->url2link($this->$field_name);
				}
			}
			
			// if there are child objects
			if ($this->num_results > 0)  {
				// clean them all
				foreach ($this->result_set as $key => $result)  {
					$result->url2link();
				}
			}
		}
		else  {  // the preg_replace part found on php.net
			return  preg_replace(
				array(
					'/(?(?=<a[^>]*>.+<\/a>)
						(?:<a[^>]*>.+<\/a>)
						|
						([^="\']?)((?:https?|ftp|bf2|):\/\/[^<> \n\r]+)
					  )/iex',
					'/<a([^>]*)target="?[^"\']+"?/i',
					'/<a([^>]+)>/i',
					'/(^|\s)(www.[^<> \n\r]+)/iex',
					'/(([_A-Za-z0-9-]+)(\\.[_A-Za-z0-9-]+)*@([A-Za-z0-9-]+)
					(\\.[A-Za-z0-9-]+)*)/iex'
				),
				array(
					"stripslashes((strlen('\\2')>0?'\\1<a href=\"\\2\">\\2</a>\\3':'\\0'))",
					'<a\\1',
					'<a\\1 target="_blank">',
					"stripslashes((strlen('\\2')>0?'\\1<a href=\"http://\\2\">\\2</a>\\3':'\\0'))",
					"stripslashes((strlen('\\2')>0?'<a href=\"mailto:\\0\">\\0</a>':'\\0'))"
				),
				$value
			);
		}
	}
	
	/**			bbcode2html()
	 *
	 * Converts bbcode to html
	 *
	 * @author       Greg Allard
	 * @version      1.0.2		5/4/7
	 * @param        mixed   $value   This can be an array, object, or a string
	 * @return       mixed   $value   This will return the same type as passed
	 */
	function bbcode2html($value = NULL)  {
		if ($value == NULL)  {
			// get the name of the implementing class
			$this_class = get_class($this);
			
			// get the vars of the implementing class
			$this_class_vars = get_class_vars_noparent($this_class);
			
			// loop through the class vars, cleaning each
			foreach ($this_class_vars as $field_name => $field_value)  {
				if (isset($this->$field_name) && $this->$field_name != '')  {
					$this->$field_name = $this->bbcode2html($this->$field_name);
				}
			}
			
			// if there are child objects
			if ($this->num_results > 0)  {
				// clean them all
				foreach ($this->result_set as $key => $result)  {
					$result->bbcode2html();
				}
			}
		}
		else  {  // convert the bbcode to html
			
			// [b] to <strong> and [/b] to </strong>
			$value = preg_replace('/\[(b)\]/i', '<strong>', $value);
			$value = preg_replace('/\[\/(b)\]/i', '</strong>', $value);
			
			// [strong] to <strong> and [/strong] to </strong>
			$value = preg_replace('/\[(strong)\]/i', '<strong>', $value);
			$value = preg_replace('/\[\/(strong)\]/i', '</strong>', $value);
			
			// [em] to <em> and [/em] to </em>
			$value = preg_replace('/\[(em)\]/i', '<em>', $value);
			$value = preg_replace('/\[\/(em)\]/i', '</em>', $value);
			
			// [i] to <em> and [/i] to </em>
			$value = preg_replace('/\[(i)\]/i', '<em>', $value);
			$value = preg_replace('/\[\/(i)\]/i', '</em>', $value);
			
			return $value;
		}
	}
	
	/**			html2bbcode()
	 *
	 * Converts allowed html to bbcode tags to avoid htmlspecialchars
	 *
	 * @author       Greg Allard
	 * @version      1.0.2		5/4/7
	 * @param        mixed   $value   This can be an array, object, or a string
	 * @return       mixed   $value   This will return the same type as passed
	 */
	function html2bbcode($value = NULL)  {
		if ($value == NULL)  {
			// get the name of the implementing class
			$this_class = get_class($this);
			
			// get the vars of the implementing class
			$this_class_vars = get_class_vars_noparent($this_class);
			
			// loop through the class vars, cleaning each
			foreach ($this_class_vars as $field_name => $field_value)  {
				if (isset($this->$field_name) && $this->$field_name != '')  {
					$this->$field_name = $this->html2bbcode($this->$field_name);
				}
			}
			
			// if there are child objects
			if ($this->num_results > 0)  {
				// clean them all
				foreach ($this->result_set as $key => $result)  {
					$result->html2bbcode();
				}
			}
		}
		else  {  // convert the html to bbcode
			
			// <strong> to [b] and </strong> to [/b]
			$value = preg_replace('/\<(strong)\>/i', '[b]', $value);
			$value = preg_replace('/\<\/(strong)\>/i', '[/b]', $value);
			
			// <b> to [b] and </b> to [/b]
			$value = preg_replace('/\<(b)\>/i', '[b]', $value);
			$value = preg_replace('/\<\/(b)\>/i', '[/b]', $value);
			
			// <em> to [em] and </em> to [/em]
			$value = preg_replace('/\<(em)\>/i', '[em]', $value);
			$value = preg_replace('/\<\/(em)\>/i', '[/em]', $value);
			
			// <i> to [i] and </i> to [/i]
			$value = preg_replace('/\<(i)\>/i', '[i]', $value);
			$value = preg_replace('/\<\/(i)\>/i', '[/i]', $value);
			
			return $value;
		}
	}
	
	/**			smart2entities()
	 *
	 * Converts smart quotes to the entity equivalent
	 *
	 * @author       Greg Allard
	 * @version      1.0.2		5/4/7
	 * @param        mixed   $value   This can be an array, object, or a string
	 * @return       mixed   $value   This will return the same type as passed
	 */
	function smart2entities($value = NULL)  {
		if ($value == NULL)  {
			// get the name of the implementing class
			$this_class = get_class($this);
			
			// get the vars of the implementing class
			$this_class_vars = get_class_vars_noparent($this_class);
			
			// loop through the class vars, cleaning each
			foreach ($this_class_vars as $field_name => $field_value)  {
				if (isset($this->$field_name) && $this->$field_name != '')  {
					$this->$field_name = $this->smart2entities($this->$field_name);
				}
			}
			
			// if there are child objects
			if ($this->num_results > 0)  {
				// clean them all
				foreach ($this->result_set as $key => $result)  {
					$result->smart2entities();
				}
			}
		}
		else  {  // convert the smart quotes to entities
			
			$value = str_replace("", "&ldquo;", $value);  //left smart quote
			$value = str_replace("", "&rdquo;", $value);  //right smart quote
			$value = str_replace("", "&rsquo;", $value);  //right single smart (can also use &#146;)
			$value = str_replace("", "&lsquo;", $value);  //left single smart (can also use &#145;)
			
			return $value;
		}
	}
	
	/**			form2email()
	 *
	 * Converts posted material for email readiness.
	 *
	 * @author       Greg Allard
	 * @version      1.0.2		5/4/7
	 * @param        mixed   $value   This can be an array, object, or a string
	 * @return       mixed   $value   This will return the same type as passed
	 */
	function form2email($value = NULL)  {
		if ($value == NULL)  {
			// get the name of the implementing class
			$this_class = get_class($this);
			
			// get the vars of the implementing class
			$this_class_vars = get_class_vars_noparent($this_class);
			
			// loop through the class vars, cleaning each
			foreach ($this_class_vars as $field_name => $field_value)  {
				if (isset($this->$field_name) && $this->$field_name != '')  {
					$this->$field_name = $this->form2email($this->$field_name);
				}
			}
			
			// if there are child objects
			if ($this->num_results > 0)  {
				// clean them all
				foreach ($this->result_set as $key => $result)  {
					$result->form2email();
				}
			}
		}
		else  {  // strip the slashes
			return stripslashes($value);
		}
	}
	
	// ---------------------------------------------------------------------
	/* *****--------------*************************--------------***********
			---------------- End Clean Functions ----------------
	********--------------*************************--------------***********/
	// ---------------------------------------------------------------------
}
?>
