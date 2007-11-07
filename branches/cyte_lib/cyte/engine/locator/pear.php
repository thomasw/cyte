<?php

/**
 *		<locator_pear>
 *
 *	Include PEAR files installed on the system
 *
**/
class locator_pear implements locator  {
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

?>
