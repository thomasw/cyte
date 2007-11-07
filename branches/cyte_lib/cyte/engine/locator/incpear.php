<?php

/**
 *		<locator_incpear>
 *
 *	PEAR files may be included in the classes folder and not installed on the system
 *
**/
class locator_incpear implements locator  {
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

?>
