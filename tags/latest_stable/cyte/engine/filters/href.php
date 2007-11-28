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

/** href - Generates a link - accepts a path and a display string. */
class filters_href implements filters  {
	static function execute($path, $display="") {
		$output = '<a href="'.$path.'">';
		
		// Output
		if($display !== null && $display !== "") {
			$output .= $display;
		} else {
			$output .= $path;
		}
		
		return $output.'</a>';
	}
}
