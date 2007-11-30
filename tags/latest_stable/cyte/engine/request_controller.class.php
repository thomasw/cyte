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

class request_controller {
	
	public $post_errors;												// Post errors array.
	public $get_errors;													// Get errors array.
	private $post_handler;												// Post handler object
	private $get_handler;												// Get handler object
	
	# References
	private $errors;													// Reference to Page's errors array.
	private $current_user;												// Reference to current user
	
	function __construct(&$current_user) {	
		global $errors;	
		
		# Set default values
		$this->errors				=&	$errors;
		$this->post_errors 			=	array();
		$this->current_user 		=&	$current_user;
		
		# Instantiate get handler
		$this->get_handler = $this->instantiate_get_handler();
		
		# Instantiate post handler
		$this->post_handler = $this->instantiate_post_handler();
	}
	
	private function instantiate_post_handler() {
		global $template_conf;
		global $lang;
		
		# Check to see if a post handler was specified
		if(!isset($_POST['post_handler']) || ($post_handler = $_POST['post_handler']) == '') {
			return NULL;
		}
		
		# Include the post handler
		if($template_conf['verbose_error']) {
			$include = include_once($template_conf['post_handlers'].$post_handler.'.php');
		} else {			
			$include = @include_once($template_conf['post_handlers'].$post_handler.'.php');
		}
		
		# Fail if including the post hanlder was unsuccessful.
		if($include === FALSE) {
			$this->post_handler = NULL;
			$this->errors[] = $lang['handler_err_005'].$post_handler;
			return FALSE;
		}
		
		if(!class_exists($post_handler)) {
			// Class does not exist, abort key processing.
			$this->errors[] = $lang['handler_err_001'].$post_handler;
			return NULL;
		}
		
		# Instantiate new post handler
		$output = new $post_handler($this->post_errors, $this->current_user);
		
		# Check if the post handler extends post_handler
		if(!in_array('post_handler',class_parents($output))) {
			// Object does not inherit from post handler
			$this->errors[] = $lang['handler_err_002'].$post_handler;
			return null;
		}
		
		return $output;
	}
	
	private function instantiate_get_handler() {
		global $template_conf;
		global $lang;
		
		# Check to see if a get handler was specified
		if(!isset($_GET['action']) || ($get_handler = $_GET['action']) == '') {
			return NULL;
		}
		
		# Include the get handler
		if($template_conf['verbose_error']) {
			$include = include_once($template_conf['get_handlers'].$get_handler.'.php');
		} else {			
			$include = @include_once($template_conf['get_handlers'].$get_handler.'.php');
		}
		
		# Fail if including the get hanlder was unsuccessful.
		if($include === FALSE) {
			$this->get_handler = NULL;
			$this->errors[] = $lang['handler_err_005'].$get_handler;
			return FALSE;
		}
		
		if(!class_exists($get_handler)) {
			// Class does not exist, abort key processing.
			$this->errors[] = $lang['handler_err_001'].$get_handler;
			return NULL;
		}
		
		# Instantiate new get handler
		$output = new $get_handler($this->get_errors, $this->current_user);
		
		# Check if the get handler extends get_handler
		if(!in_array('get_handler',class_parents($output))) {
			// Object does not inherit from get handler
			$this->errors[] = $lang['handler_err_006'].$get_handler;
			return null;
		}
		
		return $output;
	}
	
	public function get_post_handler_output() {
		if($this->post_handler == NULL) {
			return FALSE;
		} else {
			return $this->post_handler->get_output();
		}
	}
		
	
	public function get_get_handler_output() {
		if($this->get_handler == NULL) {
			return FALSE;
		} else {
			return $this->get_handler->get_output();
		}
	}
	
}

?>