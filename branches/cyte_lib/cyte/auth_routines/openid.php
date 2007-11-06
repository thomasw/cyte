<?php

class openid extends authorizer  {
	
	function __construct($auth_params)  {
		// Call the parent constructor which will check for post/session/cookie info
		parent::__construct($auth_params);
	}
	
	// run instead of auth_routine when there isn't a session already going
	function first_auth()  {
		
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
		
		
		// Complete the authentication process using the server's response.
		$response = $consumer->complete($_GET);
		
		if ($response->status == Auth_OpenID_CANCEL)  {
			// This means the authentication was cancelled.
			$this->errors[] = 'Verification cancelled.';
			return false;
		}
		else if ($response->status == Auth_OpenID_FAILURE)  {
			$this->errors[] = "OpenID authentication failed: " . $response->message;
			return false;
		}
		else if ($response->status == Auth_OpenID_SUCCESS)  {
			// This means the authentication succeeded.
			$openid = $response->identity_url;
			$esc_identity = htmlspecialchars($openid, ENT_QUOTES);
			
			$sreg = $response->extensionResponse('sreg');
			
			// if the user doesn't have an account
			if (!$this->fetch_db_data())  {
				// create it
				if (isset($sreg['nickname']))  {
					$this->current_user->user_username = strtolower($sreg['nickname']);
				}
				
				if (is_object($response->endpoint))  {
					if (isset($response->endpoint->server_url))  {
						$this->current_user->user_server = $response->endpoint->server_url;
					}
					
					if (isset($response->endpoint->delegate))  {
						$this->current_user->user_delegation = $response->endpoint->delegate;
					}
				}
				else if (isset($openid) && $openid != '')  {
					$this->current_user->user_delegation = $openid;
				}
				
				$this->current_user->create(array('get_new_id' => true));  // request it to get user_id after create
				
				// if they are logging in with a delegation url, capture both openids for their userid
				if (is_object($response->endpoint) && isset($response->endpoint->delegate) && $response->endpoint->delegate != $openid)  {
					$this->current_user->add_open_id($openid);  // tie the open id to the user id
				}
				$this->current_user->add_open_id($this->current_user->user_delegation);  // tie the open id to the user id
			}
			
			return $this->fetch_db_data();  // will get the user this time
		}

	}
	
	function auth_routine()  {
		// stay logged in as long as session is active
		return $this->fetch_db_data();
	}
	
	function fetch_db_data()  {
		// get user info based on openid
		if (!isset($this->current_user->user_id) || $this->current_user->user_id == '')  {
			// auth stores open id in this->username
			$this->current_user->get_by_open_id($this->username);
			if (!isset($this->current_user->user_id) || $this->current_user->user_id == '')  {
				return false;
			}
			else  {
				return true;
			}
		}
		else  {
			// data be fetched
			return true;
		}
	}
	
	// used after authorization by keys, post/get handlers when seeing if they can see or do
	function check_credentials($requirements = array())  {
		return true;
	}
}
?>
