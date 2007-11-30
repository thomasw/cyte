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
 * This file is for the navigation key.
 *
 * Author: Greg Allard
 * Date: 5/4/7
 * Version: 0.0.1
 *
 */

class navigation extends key  {
	
	function check_attributes()  {
	}
	
	function display()  {
		$output = '
			<ul>
				<li><cyte:link href="/" accesskey="H">Home</cyte></li>
			</ul>
			';
		return $output;
	}
}
?>
