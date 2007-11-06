<?php

class defauth extends authorizer  {
	
	
	function __construct($auth_params)  {
		// Call the parent constructor which will check for post/session/cookie info
		parent::__construct($auth_params);
	}
	
	// run instead of auth_routine when there isn't a session already going
	function first_auth()  {
		return $this->auth_routine();
	}
	
	function auth_routine()  {
		if (!isset($this->current_user->user_id) || $this->current_user->user_id == '')  {
			if (!$this->current_user->get_record(array('requirements' => array('user_username' => $this->username))))  {
				$this->current_user->errors[] = $this->lang['login_system_error'];
				return false;
			}
		}
		if (isset($this->current_user->user_password) && $this->current_user->user_password != '')  {
			if ($this->verify_password($this->password, $this->current_user->user_password, $this->options['cryptType']))  {
				return true;
			}
			else  {
				$this->current_user->errors[] = $this->lang['login_password'];
				return false;
			}
		}
		else  {
			$this->current_user->errors[] = $this->lang['login_username'];
			return false;
		}
	}
	
	
	// used after authorization by keys, post/get handlers when seeing if they can see or do
	function check_credentials($requirements = array())  {
		if (isset($requirements['user_level']))  {
			if ($this->current_user->user_level >= $requirements['user_level'])  {
				return true;
			}
			else  {
				return false;
			}
		}
	}
	
	
}
?>
