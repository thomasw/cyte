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

abstract class get_handler extends request_handler {
	protected $get_errors;								// Alias of handler errors array.
	
	public function get_attributes() {
		return array_remove_key('action',$_GET);
	}

	public function define_errors_alias() {
		$this->get_errors =& $this->handler_errors;
	}

	public function get_handler_name() {
		return $_GET['action'];
	}
	
}


?>