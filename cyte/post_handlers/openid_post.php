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
 * @version:	1.0		8/2/7
 *
 *		Processes openid form data
 */

class openid_post extends post_handler  {
	public $openid;
	
	public function authorize()  {
		# No authorization necessary. Form is public.
	}
	
	public function check_attributes()  {
		# Make sure required fields are set.
		if (trim($this->openid) === '')  {
			$this->missing_field('OpenID');
		}
	}
	
	public function execute()  {
		
		
		$path_extra = dirname(dirname(dirname(__FILE__)));
		$path = ini_get('include_path');
		$path = $path_extra . PATH_SEPARATOR . $path;
		ini_set('include_path', $path);
		
		/**
		 * This is where the example will store its OpenID information.  You
		 * should change this path if you want the example store to be created
		 * elsewhere.  After you're done playing with the example script,
		 * you'll have to remove this directory manually.
		 */
		$store_path = "/tmp/_php_consumer_test";
		
		if (!file_exists($store_path) &&
			!mkdir($store_path)) {
			print "Could not create the FileStore directory '$store_path'. ".
				" Please check the effective permissions.";
			exit(0);
		}
		
		$store = new Auth_OpenID_FileStore($store_path);
		
		/**
		 * Create a consumer object using the store object created earlier.
		 */
		$consumer = new Auth_OpenID_Consumer($store);
		
		//session_start();
		
		
		$scheme = 'http';
		if (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on') {
			$scheme .= 's';
		}
		
		
		$process_url = sprintf("$scheme://%s/user.php",
							   $_SERVER['SERVER_NAME']);
		
		$trust_root = sprintf("$scheme://%s%s",
							  $_SERVER['SERVER_NAME'],
							  dirname($_SERVER['PHP_SELF']));
		
		// Begin the OpenID authentication process.
		$auth_request = $consumer->begin($this->openid);
		
		// Handle failure status return values.
		if (!$auth_request) {
			$error = "Authentication error.";
			include 'index.php';
			exit(0);
		}
		
		
		$auth_request->addExtensionArg('sreg', 'optional', 'nickname');
		
		// Redirect the user to the OpenID server for authentication.  Store
		// the token for this authentication so we can verify the response.
		
		$redirect_url = $auth_request->redirectURL($trust_root,
												   $process_url);
		
		header("Location: ".$redirect_url);
	}
}

?>
