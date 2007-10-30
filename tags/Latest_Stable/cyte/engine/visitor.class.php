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

abstract class visitor extends data_access  {
	public $authorized;														# Boolean value that indicates whether the user is authorized to view a page
	
	public $authorizer;
	public $errors;
	
	function __construct($auth_routine, $auth_requirement, $auth_params, $db_params) {
		global $errors;
		
		# Set default values
		$this->authorized		= false;
		$this->errors			=&$errors;
		
		// Call the parent constructor
		parent::__construct($db_params);
			
		# Check to see if auth_requirement is not 0 before calling auth_routine
		if ($auth_requirement > 0)  {
			$this->authorizer		= $this->instantiate_authorizer($auth_routine, $auth_params);
			//$this->authorized 		= $this->authorize($auth_params);
		}
	}
	
	abstract function authorize();
	abstract function deauthorize();
	
	private function instantiate_authorizer($auth_routine, $auth_params) {
		global $template_conf, $lang;
		
		# REQUIRE the authorizer
		if($template_conf['verbose_error'])  {
			require_once($template_conf['auth_routines'].$auth_routine.'.php');
		} else {
			// This will cause CyTE to die with no error message if verbose error reporting is off.
			@require_once($template_conf['auth_routines'].$auth_routine.'.php');
		}
		
		if(!class_exists($auth_routine)) {
			// Class does not exist, abort key processing.
			$this->errors[] = $lang['err_017'].$auth_routine;
			$this->fatal_error();
		}
		
		# Instantiate new authorizer
		$output = new $auth_routine($auth_params);
		
		// give reference back to the current user
		$output->current_user =& $this;
		
		# Check if authorizer extends authorizer
		if(!in_array('authorizer',class_parents($output))) {
			// Object does not inherit from authorizer, Abort processing
			$this->errors[] = $lang['err_016'].$auth_routine;
			$this->fatal_error();
		}
		
		return $output;
	}
	
	private function fatal_error() {
	   global $template_conf;
	   
	   $output = "<br />".$template_conf['fatal_error']."\n";
	   
	   if ($template_conf['verbose_error']) {
	      $output .= "<ul>\n";
	      foreach($this->errors as $error) {
	         $output .= "<li>$error</li>\n";
	      }
	      $output .= "</ul>\n";
	   }
	   
	   echo $output;
	   
	   exit;
	}
}

?>