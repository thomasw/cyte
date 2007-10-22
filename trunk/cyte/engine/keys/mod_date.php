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
*	This file is for the mod_date key.
*
*	Author: Thomas Welfley
*	Date: 11 / 17 / 2006
*	Version: 0.0.3
*
*	Parameters: None
*	Container tag: No
*/

class mod_date extends key {
	
	private $date;
	
	function gen_date() {		
		// Set date
		$stats = stat($_SERVER['SCRIPT_FILENAME']);
		$this->date = date("F j, Y", $stats[9]);
	}	
	
	function check_attributes() {
		$this->gen_date();
	}
	
	function display() {
		return $this->date;
	}
	
}

?>
