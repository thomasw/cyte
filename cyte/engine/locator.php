<?php
/**

Chris Corbyn said

"I'm sure this is needed by more than me.

"My objective was to allow __autoload() to be easily extended in complex 
systems/frameworks where specific libraries etc may need loading 
differently but you don't want to hard-code little adjustments into your 
working __autoload() to allow this to happen.

"Using a ServiceLocator object with some static methods and properties to 
allow loosely coupled locators to be attached to it you can swap/change 
and add to the functionality of your __autoload() at runtime.

"The core stuff:"
*/


/**
 * Defines the methods any actual locators must implement
 * @package ServiceLocator
 * @author Chris Corbyn
**/
interface locator  {
	/**
	 * Inform of whether or not the given class can be found
	 * @param string class
	 * @return bool
	**/
	public function can_locate($class);
	
	/**
	 * Get the path to the class
	 * @param string class
	 * @return string
	**/
	public function get_path($class);
}

/**
 * The main service locator.
 * Uses loosely coupled locators in order to operate
 * @package ServiceLocator
 * @author Chris Corbyn
**/
class service_locator  {
	/**
	 * Contains any attached service locators
	 * @var array Locator
	**/
	protected static $locators = array();
	
	/**
	 * Attach a new type of locator
	 * @param object Locator
	 * @param string key
	**/
	public static function attach_locator(locator $locator, $key)  {
		self::$locators[$key] = $locator;
	}
	
	/**
	 * Remove a locator that's been added
	 * @param string key
	 * @return bool
	**/
	public static function drop_locator($key)  {
		if (self::is_active_locator($key))  {
			unset(self::$locators[$key]);
			return true;
		}
		else return false;
	}
	
	/**
	 * Check if a locator is currently loaded
	 * @param string key
	 * @return bool
	**/
	public static function is_active_locator($key)  {
		return array_key_exists($key, self::$locators);
	}
	
	/**
	 * Load in the required service by asking all service locators
	 * @param string class
	**/
	public function load($class)  {
		foreach (self::$locators as $key => $obj)  {
			if ($obj->can_locate($class))  {
				require_once $obj->get_path($class);
				if (class_exists($class)) return;
			}
		}
	}
}

/**
 *		<cyte_locator>
 *
 *	Include CyTE classes in the class_path
 *
**/
class cyte_locator implements locator  {
	protected $base;
	
	public function __construct()  {
		global $template_conf;
        $this->base = $template_conf['class_path'];
    }
   
    public function can_locate($class)  {
        $path = $this->get_path($class);
        if (file_exists($path))  {
			return true;
		}
        else  {
			return false;
		}
    }
   
    public function get_path($class)  {
        return $this->base . '/' . $class . '.class.php';
    }
}

/**
 *		<inc_pear_locator>
 *
 *	PEAR files may be included in the classes folder and not installed on the system
 *
**/
class inc_pear_locator implements locator  {
	protected $base;
	
	public function __construct()  {
		global $template_conf;
        $this->base = $template_conf['class_path'];
    }
   
    public function can_locate($class)  {
        $path = $this->get_path($class);
        if (file_exists($path))  {
			return true;
		}
        else  {
			return false;
		}
    }
   
    public function get_path($class)  {
		// if classes are in directories, see if they use pear's naming scheme
        return $this->base.str_replace('_', '/', $class).'.php';
    }
}

/**
 *		<sub_cyte_locator>
 *
 *	Include CyTE classes in sub directories of the class_path
 *
**/
class sub_cyte_locator implements locator  {
	protected $base;
	
	public function __construct()  {
		global $template_conf;
        $this->base = $template_conf['class_path'];
    }
   
    public function can_locate($class)  {
        $path = $this->get_path($class);
        if (file_exists($path))  {
			return true;
		}
        else  {
			return false;
		}
    }
   
    public function get_path($class)  {
		// if classes are in directories, see if they use pear's naming scheme
        return $this->base.str_replace('_', '/', $class).'.class.php';
    }
}

#	CyTE Key should be included by the parser, but we can check just in case
#	One could extend another

/**
 *		<sub_cyte_locator>
 *
 *	Include CyTE keys in the key folder
 *
**/
class key_locator implements locator  {
	protected $base;
	
	public function __construct()  {
		global $template_conf;
        $this->base = $template_conf['key_path'];
    }
   
    public function can_locate($class)  {
        $path = $this->get_path($class);
        if (file_exists($path))  {
			return true;
		}
        else  {
			return false;
		}
    }
   
    public function get_path($class)  {
		// key path and naming scheme
        return $this->base.$class.'.php';
    }
}

/**
 *		<sub_cyte_locator>
 *
 *	Include CyTE keys in sub key folders
 *
**/
class sub_key_locator implements locator  {
	protected $base;
	
	public function __construct()  {
		global $template_conf;
        $this->base = $template_conf['key_path'];
    }
   
    public function can_locate($class)  {
        $path = $this->get_path($class);
        if (file_exists($path))  {
			return true;
		}
        else  {
			return false;
		}
    }
   
    public function get_path($class)  {
		// key path and naming scheme
        return $this->base.str_replace('_', '/', $class).'.php';
    }
}

/**
 *		<pear_locator>
 *
 *	Include PEAR files installed on the system
 *
**/
class pear_locator implements locator  {
	protected $base = '';
	
	public function __construct($directory = '')  {
		$this->base = (string) $directory;
	}
	
	public function can_locate($class)  {
		$path = $this->get_path($class);
		if (file_exists($path))  {
			return true;
		}
		else  {
			return false;
		}
	}
	
	public function get_path($class)  {
		return $this->base.str_replace('_', '/', $class).'.php';
	}
}



// attach the locators
service_locator::attach_locator(new cyte_locator(), 'CyTE');
service_locator::attach_locator(new inc_pear_locator(), 'inc_PEAR');
service_locator::attach_locator(new sub_cyte_locator(), 'sub_CyTE');
service_locator::attach_locator(new key_locator(), 'key');
service_locator::attach_locator(new sub_key_locator(), 'sub_key');
service_locator::attach_locator(new pear_locator(), 'PEAR');

?>
