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
 * @version:	1.0				5/7/7
 * @param:		h				heading level. i.e. 2 for <h2>
 *				contentbox		whether or not to show contentbox
 *
 * @type:		non-container
 */
	
class login_form extends key  {
	public $username;
	
	function check_attributes()  {
		// if something was posted, keep the field infos
		if (is_array($_POST))  {
			// check if there were errors
			if (count($this->current_user->errors) > 0)  {
				// update the form so the poster can fix errors
				if (isset($_POST['username']))  {
					$this->username = form2form($_POST['username']);
				}
			}
		}
	}
	
	function display() {
		$output = '';
		
		//login form
		$output .= '
					
					<form action="'.$_SERVER['PHP_SELF'].'" method="post" id="login_form">
						<h2>Login</h2>	
						<p>
							<label for="username">Email:</label><br />
							<input type="text" class="text" id="username" name="username" value="'.$this->username.'"/>
						</p>
						<p>
							<label for="password">Password:</label><br />
							<input type="password" class="text" id="password" name="password" />
						</p>
						<p>
							<cyte:link href="/register.php" accesskey="R">Create an Account</cyte>
						</p>
						<p class="rt">
							<input type="submit" id="submit" name="Submit" value="Login" />
						</p>	
					</form>';
					
		return $output;
	}
}
?>
