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
*	This key returns the admin contact link.
*
*	Author: Thomas Welfley
*	Date: 11 / 17 / 2006
*	Version: 0.0.2
*
*	Parameters: None
*	Container tag: No
*/

class admin_contact extends key {
	
	private $email;						// Email from the site_conf array.
	
	public  $display;					// Call defined display property.
	
	function check_attributes() {
		// Get site_conf array so we know admin contact information
		global $site_conf;		
		$this->email = $site_conf['admin_contact'];
		
		// Check to make sure the dispaly var was set
		if(!isset($this->display) || $this->display == "") {
			$this->display = $this->email;
		}
	}
	
	function generate_ahref() {
		return '<a href="mailto:'.$this->email.'">'.$this->display.'</a>';		
	}
	
	function display() {
		return $this->generate_ahref();
	}
}

?>