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
/**
*	key
*   This is the abstract key class. All CyTE keys need to extend this object.
*
*   Author: Thomas Welfley
*   Modified: Jared Lang
*   Revision Date: 9 / 26 / 2007
*   Version: 0.1.0
*/
abstract class key {
	
	# Properties
	public $param;															// Parameter list from the key
	public $content;														// If this is a container tag, then content holds what the tag is wrapped around
	public $make_safe;														// Make Safe flag: Set to true if output needs to have 'bad' keys removed
	public $safe_keys;														// List of safe keys that shouldn't be removed by make_safe; Default constructor sets it to the default safe_keys in the config file
	public $iteration_list;													// Array of objects to iterate through
	public $failed;															// Failure flag. Set to true if key fails.
	public $key;															// The key being called (aslo the key's class name)
	public $data;															// contains data from post or db. see get_data()
	
	# Referecnes from page object
	protected $errors;														// Reference to global errors array.
	protected $page_title;													// Reference to page's title object.
	protected $page_post_handler;											// Reference to page'st post_handler object
	protected $current_user;												// Reference to page's authorizor object
	
	function __construct($key, $parameters=array(), $content, $instance, &$title=NULL, &$request_controller=NULL, &$current_user=NULL) {
		global $errors;
		
		# Set obj properties to default values.
		$this->params	 				= $parameters;						// The key's parameters
		$this->make_safe 				= FALSE;							// Don't call make_safe
		$this->failed 					= FALSE;							// Failure flag								
		$this->safe_keys 				= $this->set_safe_keys();			// Set object's safe keys to the default
		$this->key						= $key;
		$this->content					= $content;
		$this->instance					= $instance;
		
		# Set references                                                         
		$this->errors					=& $errors;
		$this->page_title				=& $title;
		$this->page_request_controller	=& $request_controller;
		$this->current_user	 			=& $current_user;
			
		# Set the object's attributes from the parameters array.
		$this->set_attributes($parameters);
		
		# Check to make sure all of the required attributes are set.
		$this->check_attributes();
		
		# Set the object's iteration list (default is NULL making the key a non-iterator - override set_iteration_list to make it an iterator)
		$this->set_iteration_list();
		
		# If the iteration list isn't NULL, check it to make sure it is an array and non-empty.
		$this->validate_iteration_list();
	}
	
	private function set_attributes($parameter_array) {
		global $lang;														// Get lang data.
        $this_properties = get_object_vars($this);															// Get class properties, including those that are private
		$this_public_properties = array_diff_key(get_class_vars(get_class($this)), get_class_vars('key'));	// Get class properties, excluding those that are private or defined in key abstract class
		
		# Loop through and set all of the object's attributes that exist to the corresponding parameter values (those with scope will be ignored.)
		foreach ($parameter_array as $key => $value)  {
            if (array_key_exists($key, $this_properties))  {
				// If the property exists, we can only modify it if it is public. If it isn't public, don't modify and push an error.
				if(array_key_exists($key, $this_public_properties)) {
					$this->$key = $value;
				} else {
					$this->errors[] = $lang['key_err_007']." ".$this->instance;
				}
			} else {
				// Attempting to set a property that does not exist. Push an error if that parameter has scope
				if(strstr($key, '.') === FALSE) {
					$this->errors[] = $lang['key_err_009']." ".$this->instance;
				}
			}
        }
		
		# Loop through the parameters array again only considering those with scope. Overwrite attributes that have already been set.
		foreach ($parameter_array as $key => $value) {
			// Check to make sure parameter has scope.
			if(strstr($key, '.') !== FALSE) {
				# Split by the scope operator -- 0 == scope, 1 === attribute name
				$keyandscope = explode('.', $key, 2);
				// Check if the property actually exists and if it does, set it equal to its corresponding value.
				if($keyandscope[0] == $this->key && (array_key_exists($keyandscope[1], $this_properties) || array_key_exists($keyandscope[1].":protected", $this_properties)) ) {
					// If the property exists, we can only modify it if it is public. If it isn't public, don't modify and push an error.
					if(array_key_exists($keyandscope[1], $this_public_properties)) {
						$this->$keyandscope[1] = $value;
					} else {
						$this->errors[] = $lang['key_err_007']." ".$this->instance;
					}
				} else if ($keyandscope[0] == $this->key) {					
						$this->errors[] = $lang['key_err_009']." ".$this->instance;
				}
			}
		}
		
	}
	
	/*
	 * set_safe_keys()
	 * This function is used by the constructor to define $this->safe_keys; By default, it will set it to the default safe keys defined in the config file.
	 * If you are writing a key and you want to use a different set of safe keys, override this function and have it return an array with your set of safe keys as elements.
	 */
	protected function set_safe_keys() {
		global $safe_keys;													// Default safe keys from the configuration file.
		return $safe_keys;
	}
	
	/*
	 * set_iteration_list()
	 * This function is used to define an object's iteration list. By default, it sets the list to null making the key a non-iterator.
	 * Override this function in your key and return any array if you want to create an iterator.
	 * return $this->iterate() in $this->display() to genetate the iterator output.
	 */
	protected function set_iteration_list() {
		return NULL;
	}
	
	protected function validate_iteration_list() {
		global $lang;														// Get global language data
		
		# Check to make sure iteration list is a non-empty array
		if(($this->iteration_list != NULL) && (!is_array($this->iteration_list) || (count($this->iteration_list) <= 0))) {
			$this->errors[] = $lang['key_err_010'].' in '.$this->instance;
			$this->failed = TRUE;
		}	
	}	
	
	protected function undefined_att_err($att) {
		global $lang;														// get global language data.
		$this->errors[] = $lang['key_err_002'].$att.' in '.$this->instance;
	}
	
	/*
	 * iterate()
	 * This function itreates through iteration_list applying each iteration to the template in $this->content.
	 * Call this function in the display function of iterator keys.
	 * Override it at your own peril.
	 */
	
	protected function iterate() {
		$parser = new iterator_parser($this->content);						// Instatiate new iterator parser
		$output = "";														// Instatiate output variable
				
		# ITERATE
		for($i = 0; $i < count($this->iteration_list); $i++) {
			// Apply the template in $this->content at each iteration. Add it to $output
			$output .= $parser->apply($this->iteration_list[$i], $i);
		}
		
		return $output;
	}
	
	/**
	 *		<get_data>
	 *
	 *		This function checks the post array for data and will fill a specified 
	 *		class with the posted information. If nothing was posted, it will used
	 *		an id if passed to search the database for data to use to fill the class.
	 *
	 *	@author		Greg Allard
	 *	@version	0.0.1	8/10/7
	 *	@param		array		containing <class> name to instantiate 
	 							and [optional] the <id> to find in the db if no post
	 *	@return		none
	**/
	function get_data($options = array())  {
		// check if required info is provided
		if (is_array($options) && isset($options['class']) && $options['class'] != '')  {
			// instantiate the class
			$this->data = new $options['class'];
			
			// check to see if anything was posted
			if (is_array($_POST) && count($_POST) > 0)  {
				// if it was, fill the object
				$this->data->set_field_values($_POST);
				$this->data->form2form();
			}
			// else, check if an id was provided
			else if (isset($options['id']) && $options['id'] != '')  {
				// get that record from the database
				$this->data->get_record($options['id']);
				$this->data->db2form();
			}
			
		}
	}
	
	# Abstract functions
	
	abstract public function display();
	abstract public function check_attributes();	
}

?>