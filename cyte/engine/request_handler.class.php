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

abstract class request_handler {
	
	# Properties
	protected $params;														// Parameters from the attribute array ($_GET, $_POST, et cetera)
	protected $handler;														// The request handler being called (aslo the key's class name)
	protected $failed;														// Request handler failure flag.
	protected $output;														// The request handler's output: usually a success message.
	
	# Referecnes from page object
	protected $current_user;												// Reference to the current user object
	protected $errors;														// Reference to page's title object.
	protected $handler_errors;												// Reference to page's request_controller error array.
	
	function __construct(&$handler_errors, &$current_user) {
		global $errors;
		
		# Set obj properties to default values.
		$this->failed 					= FALSE;							// Handler Failure flag
		$this->handler					= $this->get_handler_name();		// Name of handler being executed.

		# Set references
		$this->errors							=& $errors;							// Reference to CyTE's error list.
		$this->handler_errors					=& $handler_errors;					// Reference to the handler's errors array.
		$this->current_user	 					=& $current_user;					// Reference to the current user object
		
		# Define errors alias
		$this->define_errors_alias();
		
		# Get list of attributes
		$this->params = $this->get_attributes();
		
		# Set the object's attributes from the specified params array.
		$this->set_attributes($this->params);
		
		# Authorize the user to execute this handler
		$this->authorize();
		
		# Check to make sure the appropriate attributes are set
		if(!$this->failed) {
			$this->check_attributes();
		}
		
		# Execute the handler if it hasn't failed by now
		if(!$this->failed) {
			$this->execute();
		}
	}
	
	private function set_attributes($parameter_array) {
		global $lang;																								// Get lang data.
        $this_properties = get_object_vars($this);																	// Get class properties, including those that are private
		$this_public_properties = array_diff_key(get_class_vars(get_class($this)), get_class_vars('post_handler'));	// Get class properties, excluding those that are private or defined in key abstract class
		
		# Loop through and set all of the object's attributes that exist to the corresponding parameter values.
        foreach ($parameter_array as $key => $value)  {
            if (array_key_exists($key, $this_properties))  {
				// If the property exists, we can only modify it if it is public. If it isn't public, don't modify and push an error.
				if(array_key_exists($key, $this_public_properties)) {
					$this->$key = $value;
				} else {
						$this->errors[] = $lang['handler_err_004']." ".$this->handler;
				}
			} else {
					// Attempting to set a property that does not exist. Push an error.
					$this->errors[] = $lang['handler_err_003']." ".$this->handler;
			}
        }
	}
	
	public function get_output() {
		return $this->output;
	}	
		
	protected function missing_field($field_name) {
		global $lang;
		
		if(isset($field_name)) {
			$this->post_errors[] = $lang['handler_missingfield1'].$field_name.$lang['handler_missingfield2'].$field_name.$lang['handler_missingfield3'];
			$this->failed = TRUE;
		}
	}

	# Define these at the next abstract level:
	abstract public function get_attributes();
	abstract public function define_errors_alias();
	abstract public function get_handler_name();
	
	# Define these at the handler level:
	abstract public function authorize();
	abstract public function check_attributes();
	abstract public function execute();
	
}


?>