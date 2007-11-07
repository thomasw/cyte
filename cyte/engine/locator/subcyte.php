<?php

/**
 *		<locator_subcyte>
 *
 *	Include CyTE classes in sub directories of the class_path
 *
**/
class locator_subcyte implements locator  {
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

?>
