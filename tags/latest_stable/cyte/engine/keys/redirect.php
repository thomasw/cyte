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
 * @author:		Greg Allard
 * @version:	1.0				6/12/7
 * @param:		location		where to redirect
 *				auth			set to 1 to only redirect if logged in
 *
 * @type:		non-container
 */
	
class redirect extends key  {
	public $location;
	public $auth;					// [optional] set to 1 to only redirect if logged in
	
	function check_attributes()  {
		# see if auth is required
		if ($this->auth == 1)  {
			if (!$this->current_user->authorized)  {
				$this->failed = true;
			}
		}
	}
	
	function display()  {
		// Get the global conf so we know the base directory
		global $site_conf;
		
		header('Location: '.$site_conf['base_dir'].$this->location);
	}
}
?>
