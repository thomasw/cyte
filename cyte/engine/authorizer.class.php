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
 *		A heavily modified version of PEAR's AUTH script
 *
 *		@author		PEAR Team People, Greg Allard
 *
 *		See http://www.php.net/license/2_02.txt for AUTH Liscense
 */

define('AUTH_IDLED',       -1);
define('AUTH_EXPIRED',     -2);
define('AUTH_WRONG_LOGIN', -3);

abstract class authorizer  {
	
	/**
	 * Auth lifetime in seconds
	 *
	 * If this variable is set to 0, auth never expires
	 *
	 * @var  integer
	 * @see  set_expire(), check_auth()
	 */
	var $expire = 0;
	
	/**
	 * Has the auth session expired?
	 *
	 * @var   bool
	 * @see   check_auth()
	 */
	var $expired = false;
	
	/**
	 * Maximum time of idleness in seconds
	 *
	 * The difference to $expire is, that the idletime gets
	 * refreshed each time, check_auth() is called. If this
	 * variable is set to 0, idle time is never checked.
	 *
	 * @var integer
	 * @see set_idle(), check_auth()
	 */
	var $idle = 0;
	
	/**
	 * Is the maximum idletime over?
	 *
	 * @var boolean
	 * @see check_auth()
	 */
	var $idled = false;
	
	/**
	 * Current authentication status
	 *
	 * @var boolean
	 */
	var $authorized = false;
	
	/**
	 * Username
	 *
	 * @var string
	 */
	var $username = '';
	
	/**
	 * Password
	 *
	 * @var string
	 */
	var $password = '';
	
	/**
	 * Login callback function name
	 *
	 * @var string
	 */
	var $login_callback = '';
	
	/**
	 * Failed Login callback function name
	 *
	 * @var string
	 */
	var $login_failed_callback = '';
	
	/**
	 * Logout callback function name
	 *
	 * @var string
	 */
	var $logout_callback = '';
	
    /**
     * Auth session-array name
     *
     * @var string
     */
    var $session_name = '_authsession';
    
	/**
	 * How many times has checkAuth been called
	 * @var int
	 */
	var $auth_checks = 0;
	
	/**
	 * The site configuration array. Provides cookie info
	 * @var array
	 */
	var $site_conf;
	
	/**
	 * The site's language array. Contains authorization message localization.
	 * @var array
	 */
	var $lang;
	
	/**
	 * The user object for the current user. Reference passed to start()
	 * @var user object
	 */
	var $current_user;
	
	/**
	 * Options for the class
	 * @var array
	 */
	var $options = array();
	
	/**
	 * Constructor
	 *
	 * @param	mixed	options
	 *
	 * @return	void
	**/
	function __construct($options)  {
		global $site_conf, $lang;
		
		// copy the site configuration
		if (is_array($site_conf))  {
			$this->site_conf = $site_conf;
		}
		
		// copy the language array
		if (is_array($lang))  {
			$this->lang = $lang;
		}
		
		if (!empty($options['sessionName'])) {
			$this->session_name = $options['sessionName'];
			unset($options['sessionName']);
		}
		
		if (isset($options['idle']) && $options['idle'] > 0)  {
			$this->idle = $options['idle'];
		}
		
		if (isset($options['expire']) && $options['expire'] > 0)  {
			$this->expire = $options['expire'];
		}
		
		// set defaults
		$this->options['cryptType']			= 'md5';
		$this->options['session_id_name']	= 'PHPSESSID';
		$this->options['use_cookies']		= true;
		
		// set options based on passed values
		$this->parse_options($options);
	}
	
	// used to query a database or other auth server by the extending auth routine
	abstract function auth_routine();
	// run instead of auth_routine when there isn't a session already going. call auth_routine from it if no diff
	abstract function first_auth();
	// used after authorization by keys, post/get handlers when seeing if they can see or do something
	abstract function check_credentials($requirements = array());
	
	/**		<parse_options>
	 *
	 * Add the contents of the passed array to <'$this->options'>. Not just setting
	 * $this->options = $options because there may be options previously set that 
	 * shouldn't be overridden.
	 *
	 * @author		Greg Allard
	 * @version		2.0.2		6/26/7
	 * @param		array		contains the options to add
	 * @return		none
	 *
	 */
	private function parse_options($options = array())  {
		if (is_array($options) && count($options) > 0)  {
			foreach ($options as $key => $value)  {
				// update existing options and add new ones
				// leave the rest at default
				$this->options[$key] = $value;
			}
		}
	}
	
	
	/**
	 * Assign data from login form to internal values
	 *
	 * This function takes the values for username and password
	 * from $HTTP_POST_VARS/$_POST and assigns them to internal variables.
	 * If you wish to use another source apart from $HTTP_POST_VARS/$_POST,
	 * you have to derive this function.
	 *
	 * Added conditions to check if username and password exist in cookies
	 *
	 * @global $HTTP_POST_VARS, $_POST, $HTTPCOOKIE_VARS, $_COOKIE
	 * @see    Auth
	 * @return void
	 * @access private
	 */
	private function assign_data()  {
		if (isset($_POST['username']) && $_POST['username'] != '')  {
			$this->username = (get_magic_quotes_gpc() == 1 ? stripslashes($_POST['username']) : $_POST['username']);
		}
		else if (isset($_GET['openid_identity']) && $_GET['openid_identity'] != '')  {
			$this->username = (get_magic_quotes_gpc() == 1 ? stripslashes($_GET['openid_identity']) : $_GET['openid_identity']);
		}
		else if (isset($_COOKIE['username']) && $_COOKIE['username'] != '' && $this->options['use_cookies'])  {
			$this->username = $_COOKIE['username'];
		}
		else if (isset($_SESSION[$this->session_name]['username']) && $_SESSION[$this->session_name]['username'] != '')  {
			$this->username = $_SESSION[$this->session_name]['username'];
		}
		
		if (isset($_POST['password']) && $_POST['password'] != '')  {
			$this->password = (get_magic_quotes_gpc() == 1 ? stripslashes($_POST['password']) : $_POST['password']);
		}
		else if (isset($_COOKIE['password']) && $_COOKIE['password'] != '' && $this->options['use_cookies'])  {
			$this->password = $_COOKIE['password'];  // cookie already encrypted
			$this->options['cryptType'] = 'none';
		}
	}
	
	/**
	 * Start new auth session
	 *
	 * @access public
	 * @return void
	 */
	public function start(&$user)  {
		// make this->current_user a reference to the actual current user
		$this->current_user = $user;
		
		// setup and start the cookies and sessions
		@session_name($this->options['session_id_name']);
		@session_set_cookie_params($this->site_conf['exp_time'], $this->site_conf['base_dir']."/", $this->site_conf['domain']);
		@session_start();
		
		// copy the data from post/cookie
		$this->assign_data();
		
		
		// set the flag for checking user data
		$login_ok = false;
		
        // check username and password
        if (!empty($this->username))  {
			// if the session info isn't set yet
			if (!$this->get_auth())  {
				// only called when there isn't a session set
				$login_ok = $this->first_auth();
				if ($login_ok)  {
					// set session and cookie info
					$this->set_auth($this->username);
					// do login callback function if set
					if (is_callable($this->login_callback))  {
						call_user_func($this->login_callback, $this->username, $this);
					}
				}
				else  {
					// data doesn't check out, pass error and call login failed function if set
					// do in routine! $this->current_user->errors[] = $this->lang['login_error'];
					if (is_callable($this->login_failed_callback))  {
						call_user_func($this->login_failed_callback, $this->username, $this);
					}
				}
			}
			else  {
				// called every pageload while session is active
				$login_ok = $this->auth_routine();
			}
			
			// if all above checks out
            if ($login_ok)  {
				// check idle and expiration times
				$login_ok = $this->check_times();
				// if that's cool
				if ($login_ok)  {
					// update idle time
					$this->update_idle();
					// check for man in the middle attack
					$login_ok = $this->spoof_check();
				}
            }
        }
		return $login_ok;
	}
	
	/**
	 * Register variable in a session telling that the user
	 * has logged in successfully
	 *
	 * @param  string Username
	 * @return void
	 * @access private
	 */
	private function set_auth($username)  {
		if (!isset($_SESSION[$this->session_name]) || !is_array($_SESSION[$this->session_name]))  {
			$_SESSION[$this->session_name] = array();
		}
		
		if (!isset($_SESSION[$this->session_name]['data']))  {
			$_SESSION[$this->session_name]['data'] = array();
		}
		
		$_SESSION[$this->session_name]['sessionip']        = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
		$_SESSION[$this->session_name]['sessionuseragent'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
		
		// This should be set by the container to something more safe
		// Like md5(passwd.microtime)
		if (empty($_SESSION[$this->session_name]['challengekey']))  {
			$_SESSION[$this->session_name]['challengekey'] = md5($username.microtime());
		}
		
		$_SESSION[$this->session_name]['challengecookie'] = md5($_SESSION[$this->session_name]['challengekey'].microtime());
		setcookie('authchallenge', $_SESSION[$this->session_name]['challengecookie']);
		
		$_SESSION[$this->session_name]['registered']   = true;
		$_SESSION[$this->session_name]['username']     = $username;
		$_SESSION[$this->session_name]['timestamp']    = time();
		$_SESSION[$this->session_name]['idle']         = time();
		//$_SESSION[$this->session_name]['current_user'] = $this->current_user;
		
		
		// check if cookies should be used
		if ($this->options['use_cookies'])  {
			setcookie('username', $this->username, time() + $this->site_conf['exp_time'], $this->site_conf['base_dir']."/", $this->site_conf['domain']);
			
			if ($this->options['cryptType'] == 'none')  {
				setcookie('password', $this->password, time() + $this->site_conf['exp_time'], $this->site_conf['base_dir']."/", $this->site_conf['domain']);
			}
			else  {
				setcookie('password', md5($this->password), time() + $this->site_conf['exp_time'], $this->site_conf['base_dir']."/", $this->site_conf['domain']);
			}
		}
	}
	
    /**
     * Has the user been authenticated?
     *
     * @access public
     * @return bool  True if the user is logged in, otherwise false.
     */
    public function get_auth()  {
		if	(!empty($_SESSION) && isset($_SESSION[$this->session_name]) && isset($_SESSION[$this->session_name]['registered']) && $_SESSION[$this->session_name]['registered'] === true)  {
            return true;
        }
		else  {
            return false;
        }
    }
	
	/**
	 * Logout function
	 *
	 * This function clears any auth tokens in the currently
	 * active session and executes the logout callback function,
	 * if any
	 *
	 * @access public
	 * @return void
	 */
	public function logout()  {
		if (is_callable($this->logout_callback))  {
			call_user_func_array($this->logout_callback, array($_SESSION[$this->session_name]['username'], &$this) );
		}
		
		$this->username = '';
		$this->password = '';
		
		$_SESSION[$this->session_name] = null;
		
		setcookie('username', false, time() - $this->site_conf['exp_time'], $this->site_conf['base_dir']."/", $this->site_conf['domain']);
		setcookie('password', true, time() - $this->site_conf['exp_time'], $this->site_conf['base_dir']."/", $this->site_conf['domain']);
	}
	
    /**
     * Update the idletime
     *
     * @access private
     * @return void
     */
    private function update_idle()  {
       $_SESSION[$this->session_name]['idle'] = time();
    }
	
    /**
     * Returns the time up to the session is valid
     *
     * @access public
     * @return integer
     */
    public function session_valid_thru()  {
        if (!isset($_SESSION[$this->session_name]['idle']))  {
            return 0;
        }
        return ($_SESSION[$this->session_name]['idle'] + $this->idle);
    }
	
	/**
	 * Checks if user idles too long or if the session expires
	 *
	 * @access private
	 * @return boolean  Whether or not the user is authenticated.
	 */
	private function check_times()  {
		if (isset($_SESSION[$this->session_name]))  {
			// Check if authentication session is expired
			if ($this->expire > 0 && isset($_SESSION[$this->session_name]['timestamp']) && 
			   ($_SESSION[$this->session_name]['timestamp'] + $this->expire) < time())  {
				$this->expired = true;
				$this->current_user->errors[] = $this->lang['login_expire'];
				$this->logout();
				return false;
			}
			
			// Check if maximum idle time is reached
			if ($this->idle > 0 && isset($_SESSION[$this->session_name]['idle']) &&	
			   ($_SESSION[$this->session_name]['idle'] + $this->idle) < time())  {
				$this->idled = true;
				$this->current_user->errors[] = $this->lang['login_idle'];
				$this->logout();
				return false;
			}
		}
		return true;
	}
	
	
	/**
	 * Checks for a man-in-the-middle attack by comparing ip, browser info, and challenge cookie
	 *
	 * @access private
	 * @return boolean  Whether or not the user is authenticated.
	 */
	private function spoof_check()  {
		return true;
		/*if (isset($this->current_user->user_id) && $this->current_user->user_id != '')  {
			// Check for ip change
			if ( isset($this->server['REMOTE_ADDR']) && $this->current_user->user_remote_addr != $this->server['REMOTE_ADDR'])  {
				// Check if the IP of the user has changed, if so we assume a man in the middle attack and log him out
				$this->expired = true;
				$this->current_user->errors[] = $this->lang['login_breach'];
				$this->logout();
				return false;
			}
			
			// Check for useragent change
			if ( isset($this->server['HTTP_USER_AGENT']) && $this->current_user->user_http_user_agent != $this->server['HTTP_USER_AGENT'])  {
				// Check if the User-Agent of the user has changed, if so we assume a man in the middle attack and log him out
				$this->expired = true;
				$this->current_user->errors[] = $this->lang['login_breach'];
				$this->logout();
				return false;
			}
		}*/
	}
	
	/**
	 * Register additional information that is to be stored
	 * in the session.
	 *
	 * @access public
	 * @param  string  Name of the data field
	 * @param  mixed   Value of the data field
	 * @param  boolean Should existing data be overwritten? (default
	 *                 is true)
	 * @return void
	 */
	public function set_auth_data($name, $value, $overwrite = true)  {
		if (!empty($_SESSION[$this->session_name]['data'][$name]) && $overwrite == false)  {
			return;
		}
		
		$_SESSION[$this->session_name]['data'][$name] = $value;
	}
	
    /**
     * Get additional information that is stored in the session.
     *
     * If no value for the first parameter is passed, the method will
     * return all data that is currently stored.
     *
     * @access public
     * @param  string Name of the data field
     * @return mixed  Value of the data field.
     */
    public function get_auth_data($name = null)  {
        if (!isset($_SESSION[$this->session_name]['data']))  {
            return(null);
        }
		
        if (is_null($name))  {
            if (isset($_SESSION[$this->session_name]['data'])) {
                return $_SESSION[$this->session_name]['data'];
            }
			else  {
                return null;
            }
        }
        if (isset($_SESSION[$this->session_name]['data'][$name]))  {
            return $_SESSION[$this->session_name]['data'][$name];
        }
		else  {
            return null;
        }
    }
	
    /**
     * Crypt and verfiy the entered password
     *
     * @param  string Entered password (salt this if used)
     * @param  string Password from the data container (usually this password
     *                is already encrypted.
     * @param  string Type of algorithm with which the password from
     *                the container has been crypted. (md5, crypt etc.)
     *                Defaults to "md5".
     * @return bool   True, if the passwords match
     */
    protected function verify_password($password1, $password2, $cryptType = "md5")  {
        switch ($cryptType)  {
        case "crypt" :
            return (($password2 == "**" . $password1) ||
                    (crypt($password1, $password2) == $password2)
                    );
            break;
		
        case "none" :
            return ($password1 == $password2);
            break;
		
        case "md5" :
            return (md5($password1) == $password2);
            break;
		
        default :
            if (function_exists($cryptType))  {
                return ($cryptType($password1) == $password2);
            }
            else if (method_exists($this,$cryptType))  { 
                return ($this->$cryptType($password1) == $password2);
            }
			else  {
                return false;
            }
            break;
        }
    }
}
?>