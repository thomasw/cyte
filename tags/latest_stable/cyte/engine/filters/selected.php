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


/** selected - compares values and returns `"selected` if they match, `"` if they don't 
	Note: 	Cyte doesn't allow passing arrays to filters. The way to get around this is to
			use a comma delimited string and reconstruct the array in the filter.
**/

class filters_selected implements filters  {
	static function execute($data = '', $parameters = '')  {
		if (strpos($parameters,',') > 0)  {
			$parameters = explode(',',$parameters);
			return (in_array($data, $parameters)) ? $data.'" selected="selected"' : $data.'"';
		} else {
			return ($data == $parameters) ? $data.'" selected="selected"': $data.'"';
		}
	}
}
?>
