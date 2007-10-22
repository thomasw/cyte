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
class logout extends get_handler {
	
	public $logout;
	
	public function authorize() {
		# No authorization necessary. Form is public.
	}
	
	public function check_attributes() {
		# Make sure required fields are set.
	}
	
	public function execute() {
		global $site_conf;
		$this->current_user->deauthorize();
		header('Location: '.$site_conf['base_dir'].'/index.php');
	}
}

?>
