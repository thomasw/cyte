<?php

/**
 *		<locator_key>
 *
 *	Include CyTE keys in the key folder
 *
**/
class locator_key implements locator  {
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

?>
