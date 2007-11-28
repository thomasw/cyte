<?php

/**
 *		A heavily modified version of PEAR's LDAP AUTH script
 *		Fetches login data from specified LDAP server, checks db for user,
 *		creates user if necessary, gets user info into current user object from db
 *		
 *		@author		PEAR Team People, Greg Allard
 *		
 *		See http://www.php.net/license/2_02.txt for AUTH Liscense
 *		
 *		
**/
class ldap extends authorizer  {
	/**
	 * Connection ID of LDAP Link
	 * @var string
	 */
	var $conn_id = false;
	
	/**
	 * LDAP search function to use		// somehow this is too important to be in $this->options?
	 * @var string
	 */
	var $ldap_search_func;
	
	/**
	 * Constructor sets the defaults and calls
	 *
	 * @param		$auth_params	associative array with host, port, basedn, etc
	 */
	function __construct($auth_params)  {
		// Call the parent constructor which will check for post/session/cookie info
		parent::__construct($auth_params);
		
		
		# set default values for the options						Examples:
		$this->options['host']			= 'localhost';				# ldap.netsols.de or 127.0.0.1
		$this->options['port']			= '389';					# 636 or whereever your server runs
		// url overrides host/port combo. useful for ldaps://
		$this->options['url']			= '';						# ldaps://ldap.netsols.de
		// will bind as this instead of anonymous if set
		$this->options['binddn']		= '';						# 'cn=Jan Wagner,ou=Users,dc=netsols,dc=de'
		$this->options['bindpw']		= '';						# The password to use for binding with binddn
		$this->options['scope']			= 'sub';					# one, sub (default), or base
		// the base distinguished name of your server
		$this->options['basedn']		= '';						# 'o=netsols,c=de' or 'cn=admin,o=netsols,c=de'
		// gets prepended to basedn when searching for user
		$this->options['userdn']		= '';						# 'ou=People', 'ou=Users'
		// the user attribute to search for
		$this->options['userattr']		= "uid";					# 'samAccountName'
		// array of attributes to return from the search (userattr) will always be retrieved
		$this->options['attributes']	= array();					# array('whencreated', 'objectguid', 'sn', 'givenname')
		// objectclass of user (for the search filter)
		$this->options['useroc']		= 'posixAccount';			# 'user'
		// gets prepended to basedn when searching for group
		$this->options['groupdn']		= '';						# 'ou=Groups'
		// the group attribute to search for (default: cn)
		$this->options['groupattr']		= 'cn';						
		// objectclass of group (for the search filter)
		$this->options['groupoc']		= 'groupOfUniqueNames';		# 'posixGroup'
		// the attribute of the group object where the user dn may be found
		$this->options['memberattr']	= 'uniqueMember';			# 'memberUid'
		// whether the memberattr is the dn of the user (default) or the value of userattr (usually uid)
		$this->options['memberisdn']	= true;
		// the name of group to search for
		$this->options['group']			= '';						# admin
		// Enable/Disable debugging output
		$this->options['debug']			= false;
		
		
		if (is_array($auth_params)) {
			$this->parse_options($auth_params);
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
	
	// run instead of auth_routine when there isn't a session already going
	function first_auth()  {
		return $this->bind_ldap();
	}
	
	function auth_routine()  {
		return true;
	}
	
	
	/**
	 * Fetch data from LDAP server
	 *
	 * Searches the LDAP server
	 * combination.
	 *
	 * @return boolean
	 */
	function bind_ldap()  {
		$this->connect();
		$this->get_base_dn();
		
		// make search filter. ie: '(&(objectClass=user)(uid=gallard))'  gallard being the passed username
		$filter = '(&(objectClass='.$this->options['useroc'].')('.$this->options['userattr'].'='.$this->username.'))';
		
		// make search base dn. prepend userdn if it is set to something
		$search_basedn = $this->options['userdn'];
		if ($search_basedn != '' && substr($search_basedn, -1) != ',')  {
			$search_basedn .= ',';
		}
		$search_basedn .= $this->options['basedn'];
		
		#  set the parameters to pass to the ldap search function
		// if options['attributes'] is set, return only those attributes, else = all
		if (count($this->options['attributes']) > 0)  {
			// make sure that the user attribute is in the array
			if (!in_array($this->options['userattr'], $this->options['attributes']))  {
				// add it if it isn't
				$this->options['attributes'][] = $this->options['userattr'];
			}
			$func_params = array($this->conn_id, $search_basedn, $filter, $this->options['attributes']);
		}
		else  {
			// will be much slower since it is retrieving all, so using attributes is highly recommended
			$func_params = array($this->conn_id, $search_basedn, $filter);
		}
		
		$this->debug("Searching with $filter in $search_basedn", __LINE__);
		
		#  do the search
		$result_id = @call_user_func_array($this->ldap_search_func, $func_params);
		// check if the user was found
		if ($result_id !== false)  {
			if (ldap_count_entries($this->conn_id, $result_id) == 1)  { // did we get just one entry?
				$this->debug('User was found', __LINE__);
				
				// found the username, now get the distinguished name so we can try to bind as them
				$entry_id = ldap_first_entry($this->conn_id, $result_id);
				$user_dn  = ldap_get_dn($this->conn_id, $entry_id);
				
				// get all the entries retrieved from the database
				$entries = ldap_get_entries($this->conn_id, $result_id);
				// if results were found
				if (is_array($entries) && $entries['count'] > 0)  {
					// assign each of the attributes to the current user
					$this->current_user->ldap_attributes = (object) $entries[0];
					//printer($this->current_user);
				}
				
				ldap_free_result($result_id);
				
				// need to catch an empty password as openldap seems to return TRUE
				// if anonymous binding is allowed
				if ($this->password != "")  {
					$this->debug("Bind as $user_dn", __LINE__);
					
					// try binding as this user with the supplied password
					if (@ldap_bind($this->conn_id, $user_dn, $this->password))  {
						$this->debug('Bind successful', __LINE__);
						
						// check group if appropiate
						if (isset($this->options['group']) && $this->options['group'] != '')  {
							// decide whether memberattr value is a dn or the username
							$this->debug('Checking group membership', __LINE__);
							return $this->check_group(($this->options['memberisdn']) ? $user_dn : $this->username);
						}
						else  {
							$this->debug('Authenticated', __LINE__);
							$this->disconnect();
							return true; // user authenticated
						} // checkGroup
					} // bind
					else  {
						$this->current_user->errors[] = $this->lang['login_password'];
					}
				} // non-empty password
				else  {
					$this->current_user->errors[] = $this->lang['login_password'];
				}
			} // no entries
			else  {
				$this->current_user->errors[] = $this->lang['login_username'];
			}
		}
		else  {  // user wasn't found
			$this->current_user->errors[] = $this->lang['login_username'];
			$this->debug('User not found', __LINE__);
		}
		$this->debug('NOT authenticated!', __LINE__);
		$this->disconnect();
		return false;
	}
	
	/**
	 * Connect to the LDAP server using the global options
	 *
	 * @access private
	 * @return object  Returns a PEAR error object if an error occurs.
	 */
	private function connect()  {
		// connect
		if (isset($this->options['url']) && $this->options['url'] != '')  {
			$this->debug('Connecting with URL', __LINE__);
			$conn_params = array($this->options['url']);
		}
		else  {
			$this->debug('Connecting with host:port', __LINE__);
			$conn_params = array($this->options['host'], $this->options['port']);
		}
		
		if (($this->conn_id = @call_user_func_array('ldap_connect', $conn_params)) === false)  {
			return PEAR::raiseError('Auth_Container_LDAP: Could not connect to server.', 41, PEAR_ERROR_DIE);
		}
		$this->debug('Successfully connected to server', __LINE__);
		
		// try switchig to LDAPv3
		$ver = 0;
		if (@ldap_get_option($this->conn_id, LDAP_OPT_PROTOCOL_VERSION, $ver) && $ver >= 2)  {
			$this->debug('Switching to LDAPv3', __LINE__);
			@ldap_set_option($this->conn_id, LDAP_OPT_PROTOCOL_VERSION, 3);
		}
		
		// bind with credentials or anonymously
		if ($this->options['binddn'] && $this->options['bindpw'])  {
			$this->debug('Binding with credentials', __LINE__);
			$bind_params = array($this->conn_id, $this->options['binddn'], $this->options['bindpw']);
		}
		else  {
			$this->debug('Binding anonymously', __LINE__);
			$bind_params = array($this->conn_id);
		}
		
		// bind for searching
		if ((@call_user_func_array('ldap_bind', $bind_params)) == false)  {
			$this->debug();
			$this->disconnect();
			return PEAR::raiseError("Auth_Container_LDAP: Could not bind to LDAP server.", 41, PEAR_ERROR_DIE);
		}
		$this->debug('Binding was successful', __LINE__);
	}
	
	/**
	 * Disconnects (unbinds) from ldap server
	 *
	 * @access private
	 */
	private function disconnect()  {
		if ($this->is_valid_link())  {
			$this->debug('disconnecting from server');
			@ldap_unbind($this->conn_id);
		}
	}
	
	/**
	 * Tries to find Basedn via namingContext Attribute
	 *
	 * @access private
	 */
	private function get_base_dn()  {
		if ($this->options['basedn'] == "" && $this->is_valid_link())  {
			$this->debug("basedn not set, searching via namingContexts.", __LINE__);
			
			$result_id = @ldap_read($this->conn_id, "", "(objectclass=*)", array("namingContexts"));
			
			if (ldap_count_entries($this->conn_id, $result_id) == 1)  {
				
				$this->debug("got result for namingContexts", __LINE__);
				
				$entry_id = ldap_first_entry($this->conn_id, $result_id);
				$attrs = ldap_get_attributes($this->conn_id, $entry_id);
				$basedn = $attrs['namingContexts'][0];
				
				if ($basedn != "")  {
					$this->debug("result for namingContexts was $basedn", __LINE__);
					$this->options['basedn'] = $basedn;
				}
			}
			ldap_free_result($result_id);
		}
		
		// if base ist still not set, raise error
		if ($this->options['basedn'] == "") {
			return PEAR::raiseError("Auth_Container_LDAP: LDAP search base not specified!", 41, PEAR_ERROR_DIE);
		}
		return true;
	}
	
	/**
	 * determines whether there is a valid ldap conenction or not
	 *
	 * @access private
	 * @return boolean
	 */
	private function is_valid_link()  {
		if (is_resource($this->conn_id))  {
			if (get_resource_type($this->conn_id) == 'ldap link')  {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Parse options passed to the container class
	 *
	 * @access private
	 * @param  array
	 */
	private function parse_options($array)  {
		foreach ($array as $key => $value)  {
			$this->options[$key] = $value;
		}
		
		// get the according search function for selected scope
		switch ($this->options['scope'])  {
		case 'one':
			$this->ldap_search_func = 'ldap_list';
			break;
		case 'base':
			$this->ldap_search_func = 'ldap_read';
			break;
		default:
			$this->ldap_search_func = 'ldap_search';
			break;
		}
		$this->debug("LDAP search function will be: {$this->ldap_search_func}", __LINE__);
	}
	
	/**
	 * Validate group membership
	 *
	 * Searches the LDAP server for group membership of the
	 * authenticated user
	 *
	 * @param  string Distinguished Name of the authenticated User
	 * @return boolean
	 */
	function check_group($user)  {
		// make filter
		$filter = sprintf('(&(%s=%s)(objectClass=%s)(%s=%s))',
						  $this->options['groupattr'],
						  $this->options['group'],
						  $this->options['groupoc'],
						  $this->options['memberattr'],
						  $user
						  );
		
		// make search base dn
		$search_basedn = $this->options['groupdn'];
		if ($search_basedn != '' && substr($search_basedn, -1) != ',')  {
			$search_basedn .= ',';
		}
		$search_basedn .= $this->options['basedn'];
		
		$func_params = array($this->conn_id, $search_basedn, $filter, array($this->options['memberattr']));
		
		$this->debug("Searching with $filter in $search_basedn", __LINE__);
		
		// search
		if (($result_id = @call_user_func_array($this->ldap_search_func, $func_params)) != false)  {
			if (ldap_count_entries($this->conn_id, $result_id) == 1)  {
				ldap_free_result($result_id);
				$this->debug('User is member of group', __LINE__);
				$this->disconnect();
				return true;
			}
		}
		
		// default
		$this->debug('User is NOT member of group', __LINE__);
		$this->disconnect();
		return false;
	}
	
	/**
	 * Outputs debugging messages
	 *
	 * @access private
	 * @param string Debugging Message
	 * @param integer Line number
	 */
	private function debug($msg = '', $line = 0)  {
		if ($this->options['debug'] === true)  {
			if ($msg == '' && $this->is_valid_link())  {
				$msg = 'LDAP_Error: ' . @ldap_err2str(@ldap_errno($this->_conn_id));
			}
			print("$line: $msg <br />");
		}
	}
}

?>
