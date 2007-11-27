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
*	filters
*   This object contains filters and their helper functions for CyTE's iterator parser.
*
*   Author: Thomas Welfley
*   Date: 9 / 26 / 2007
*   Version: 0.0.1
*/

abstract class filters {
	
	var $errors;
	
	function __construct() {
		global $errors;
		$this->errors		        =& $errors;
	}
	
	# Implement this
	static abstract function execute();  // where the action's at
	
	# Define filter helper functions here
	
	/** comma_to_array - Splits a comma seperated list of values into an array */
	static function comma_to_array($string) {
		return explode(',', $string);
	}
}
?>