<?php

/**
 *		<locator_cyte>
 *
 *	Include CyTE classes in the class_path
 *
**/
class locator_cyte implements locator  {
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

?>
