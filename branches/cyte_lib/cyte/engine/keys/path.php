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
 * This file is for the path key.
 * This key outputs the value of $site_conf['base_dir']
 *
 * Author: Thomas Welfley
 * Date: 8 / 10 / 2007
 * Version: 0.0.1
 *
 * Parameters: None
 * Container tag: No
 * Example:
 * <cyte:path />
 *
 */

class path extends key {
	
	public $path;
	
	function check_attributes() {
		global $site_conf;
		
		$this->path = $site_conf['base_dir'];
	}
		
	function display() {
		return $this->path;
	}
}

?>