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
*	This file is for outputting post handler output.
*
*	Author: Thomas Welfley
*	Date: 10 / 17 / 2005
*	Updated to new key format: 2/25/07
*	Version: 0.0.3
*
*	Parameters:	form - specifies which post handler to display output for. By default, the key will output all post_handler's output.
*				wraptag - specifies what html tag to wrap the output in. (default is p)
*				id - used to specify what you want the id of the printed list to be
*				class - used to specify what you want the class of the printed list to be.
*	container tag: no
*	Special notes: The or operator, "||", can be used inside the value for "form" to allow a developer to specify multiple forms.
*	Example 1:
*	<cyte:post_output form="login" /> 
*	Example 2:
* 	<cyte:post_errors form="login||search||contact_form" />
*/
	
class post_output extends key {
	
	public $form;					// List of forms to display output for.
	public $wraptag;				// tag to wrap output message in.
	public $class;					// html class attribute
	public $id;						// html id attribute
		
	private $output;				// Output message from post_handler
	private $form_list;				// Array of forms to output output for.
	private $executed_post_handler;	// The post handler the just executed
	
	function check_attributes() {
		
		# Check if form was specified, and if it was, split it apart by  ||
		if(trim($this->form) =='') {
			$this->form_list = array();
		} else {
			$this->form_list = explode('||', $this->form);
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
		$this->output = $this->page_request_controller->get_post_handler_output();
		if($this->output === FALSE || trim($this->output) == '') {
			$this->failed = TRUE;
			return;
		}

		# Get the post handler that executed - if none, fail.
		if(isset($_POST['post_handler'])) {
			$this->executed_post_handler = $_POST['post_handler'];
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
			if(in_array($this->executed_post_handler, $this->form_list) || count($this->form_list) == 0) {
				return $this->generate_output();
			}
			
			return '';
		}
	}
	
}
?>