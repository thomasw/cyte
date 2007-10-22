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
*	This key displays outputs $_SERVER['PHP_SELF'].
*
*
*	Author: Thomas Welfley
*	Date: 11/17/2006
*	Version: 0.0.3
*	
*	Key Information:
*	PHP_SELF
*	Parameters: None
*	Container tag: No
*
*/

class php_self extends key {
	
	private $php_self;
	
	function check_attributes() {
		$this->php_self = $_SERVER['PHP_SELF'];
	}
	
	function display() {
		return $this->php_self;
	}
}

?>