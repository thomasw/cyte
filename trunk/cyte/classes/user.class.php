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
class user extends visitor  {
	#create a public var for each field in the table and initialize to NULL
	
	public $user_id 				= NULL;
	public $user_username			= NULL;
	public $user_password			= NULL;
	public $user_server		 		= NULL;
	public $user_delegation			= NULL;
	public $user_xrds				= NULL;
	public $user_deleted			= NULL;
	public $user_last_mod			= NULL;
	public $user_created			= NULL;
	
	function __construct($auth_routine = '', $auth_requirement = '', $auth_params = array(), $parameters = array())  {
		// Set the table specific vars in the parameters array to pass to parent constructor
		$parameters['table_name']					= 'users';				// Name of the table in the database
		$parameters['id_field']						= 'user_id';			// The field in the table that has the primary key
		$parameters['last_mod_id_field']			= NULL;					// The last modifier's id field
		$parameters['deleted_field']				= 'user_deleted';		// The field in the table that is the deleted flag
		$parameters['last_mod_field']				= 'user_last_mod';		// The field in the table that is the lastmod time
		$parameters['created_field']				= 'user_created';		// The field in the table that is the time created
		$parameters['archive_table_name']			= NULL;					// Name of the history table in the database
		
		
		// Call the parent constructor
		parent::__construct($auth_routine, $auth_requirement, $auth_params, $parameters);
		
		
		if ($auth_requirement > 0)  {
			$this->authorized 		= $this->authorize($auth_params);
		}
		
	}
	
	
	/**		<get_by_open_id>
	 *
	 * This function will retrieve a record from the database by matching 
	 *
	 *
	 * @author		Greg Allard
	 * @version		1.0.0		8/2/7
	 * @param		array		options containing an open id
	 * @return		boolean		true on success and false on failure
	 *
	 */
	function get_by_open_id($options = array())  {
		// allow just the open id to be passed
		if (is_string($options) && $options != '')  {
			$options = array('open_id' => $options);
		}
		
		$this->parse_options($options);
		
		// open_id is required
		if ($this->options['open_id'] != '')  {
			
			if ($this->prepare() !== true)  {
				$this->errors[] = $this->lang['db_101'];  // unable to connect
				return FALSE;
			}
			
			
			// Start the SQL statement
			$sql  = ' SELECT * FROM `users`, `user_openids` WHERE users.user_id = user_openids.user_id AND 
						open_id = "'.$this->options['open_id'].'" ';
			
			if (isset($this->options['deleted']) && ($this->options['deleted'] == 0 || $this->options['deleted'] == 1))  {  // 2 makes it optional. ie skip this
				$sql .= ' AND user_deleted = '.$this->options['deleted'].' ';
			}
			
			
			$result = $this->db->query($sql);
			// check if there was an error executing the query
			if (DB::isError($result))  {
				// report error and fail
				$this->errors[] = $this->lang['db_102'].' $data_access->get_record() '.$result->getMessage().' SQL: '.$sql;  // unable to execute query
				return FALSE;
			}
			else  {  // if no errors
				// fill the field_values with the result from the database
				while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))  {
					$this->set_field_values($row);
				}
				return TRUE;
			}
		}
	}
	
	
	
	/**
	 *
	 *		This function joins this user with an open id
	 *
	 *	@author		Greg Allard
	 *	@version	0.0.1	8/3/7
	 *	@param		open id
	 *	@return		bool	true or false depending on success
	 *
	 */
	function add_open_id($open_id)  {
		// make sure both ids are set and the user doesn't already have this interest
		if ($this->user_id > 0 && $open_id != '' && !$this->has_open_id($open_id))  {
			if ($this->prepare() !== true)  {
				$this->errors[] = $this->lang['db_101'];  // unable to connect
				return FALSE;
			}
			
			$sql = "INSERT INTO `user_openids` ".
				   "(`user_id`,        `open_id`) VALUES ".
				   "('$this->user_id', '$open_id') ";
			$result = $this->db->query($sql);
			if (DB::isError($result))  {
				$this->errors[] = $this->lang['db_102'].' $user->add_open_id() '.$result->getMessage().' SQL: '.$sql;  // unable to execute
				return false;
			}
			else  {
				// no errors. added successfully
				return true;
			}
		}
		else  {
			return false;
		}
	}
	
	
	/**
	 *
	 *		This function checks to see if this user has passed open id
	 *
	 *	@author		Greg Allard
	 *	@version	0.0.1	8/3/7
	 *	@param		open id
	 *	@return		bool	true or false depending on whether the use has it
	 *
	 */
	function has_open_id($open_id)  {
		if ($open_id != '' && $this->user_id > 0)  {
			if ($this->prepare() !== true)  {
				$this->errors[] = $this->lang['db_101'];  // unable to connect
				return FALSE;
			}
			$sql = "SELECT * FROM user_openids WHERE user_id = '$this->user_id' AND open_id = '$open_id' ";
			$result = $this->db->query($sql);
			if (DB::isError($result))  {
				$this->errors[] = $this->lang['db_102'].' $user->has_open_id() '.$result->getMessage().' SQL: '.$sql;  // unable to execute
				return false;
			}
			else  {
				// no errors
				while ($row = $result->fetchrow(DB_FETCHMODE_OBJECT))  {
					if (isset($row->user_id) && $row->user_id != '')  {
						return true;
					}
					else  {
						return false;
					}
				}
			}
		}
		else  {
			return false;
		}
	}
	
	function authorize()  {
		return $this->authorizer->start($this);
	}
	
	function deauthorize()  {
		$this->authorizer->logout();
		$this->authorized = FALSE;
	}
	
	function meets_credentials()  {
	}
	
	function check_create()  {
		return TRUE;
	}
	
	function check_get()  {
		return TRUE;
	}
	
	function check_edit()  {
		return TRUE;
	}
	
	function check_delete()  {
		return TRUE;
	}
	
	function check_set()  {
		return TRUE;
	}
}
