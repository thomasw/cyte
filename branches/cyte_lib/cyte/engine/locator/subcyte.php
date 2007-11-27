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
