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
	public static $locators = array();
	
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

# Create the two locators that find CyTE locators and developer locators

/**
 *		<locator_cyte_locator>
 *
 *	Include CyTE's locator files when they are attached
 *
**/
class locator_cyte_locator implements locator  {
	protected $base = '';
	
	public function __construct($directory = '')  {
		global $template_conf;
        $this->base = $template_conf['engine_path'];
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

/**
 *		<locator_locator>
 *
 *	Include developer added locators based on config locator path
 *
**/
class locator_locator implements locator  {
	protected $base;
	
	public function __construct()  {
		global $template_conf;
        $this->base = $template_conf['locator_path'];
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

// attach the two locator locators
service_locator::attach_locator(new locator_cyte_locator(), 'CyTE_locators');
service_locator::attach_locator(new locator_locator(), 'locators');

?>
