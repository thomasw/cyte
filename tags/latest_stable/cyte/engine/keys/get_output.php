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
*	This file is for outputting get handler output.
*
*	Author: Thomas Welfley
*	Date: 10 / 17 / 2005
*	Updated to new key actionat: 2/25/07
*	Version: 0.0.3
*
*	Parameters:	action - specifies which get handler to display output for. By default, the key will output all get_handler's output.
*				wraptag - specifies what html tag to wrap the output in. (default is p)
*				id - used to specify what you want the id of the printed list to be
*				class - used to specify what you want the class of the printed list to be.
*	container tag: no
*	Special notes: The or operator, "||", can be used inside the value for "action" to allow a developer to specify multiple actions.
*	Example 1:
*	<cyte:get_output action="login" /> 
*	Example 2:
* 	<cyte:get_errors action="login||search||contact_action" />
*/
	
class get_output extends key {
	
	public $action;					// List of actions to display output for.
	public $wraptag;				// tag to wrap output message in.
	public $class;					// html class attribute
	public $id;						// html id attribute
		
	private $output;				// Output message from get_handler
	private $action_list;				// Array of actions to output output for.
	private $executed_get_handler;	// The get handler the just executed
	
	function check_attributes() {
		
		# Check if action was specified, and if it was, split it apart by  ||
		if(trim($this->action) =='') {
			$this->action_list = array();
		} else {
			$this->action_list = explode('||', $this->action);
		}
		
		# Check wraptag
		if(trim($this->wraptag) == '') {
			$this->wraptag = 'p';
		}		
		
		# Setup class	
		if(trim($this->class) != '') {
			$this->class = ' class="'.$this->class.'"';
		}
		
		# Setup id	
		if(trim($this->id) != '') {
			$this->id = ' id="'.$this->id.'"';
		}
		
		# Get output and check its validity
		$this->output = $this->page_request_controller->get_get_handler_output();
		if($this->output === FALSE || trim($this->output) == '') {
			$this->failed = TRUE;
			return;
		}

		# Get the get handler that executed - if none, fail.
		if(isset($_GET['action'])) {
			$this->executed_get_handler = $_GET['action'];
		} else {
			$this->failed = TRUE;
			return;
		}
	}
	
	private function generate_output() {
		return '<'.$this->wraptag.$this->id.$this->class.'>'.$this->output.'</'.$this->wraptag.'>';
	}
	
	function display() {
		if(!$this->failed) {			
			if(in_array($this->executed_get_handler, $this->action_list) || count($this->action_list) == 0) {
				return $this->generate_output();
			}
			
			return '';
		}
	}
	
}
?>