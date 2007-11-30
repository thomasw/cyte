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

abstract class post_handler extends request_handler {
	protected $post_errors;								// Alisa of handler_errors array.
	
	public function get_attributes() {
		return array_remove_key('post_handler', $_POST);
	}
	
	public function define_errors_alias() {
		$this->post_errors =& $this->handler_errors;
	}

	public function get_handler_name() {
		return $_POST['post_handler'];
	}
	
}


?>