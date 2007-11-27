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


/** pluralize
 * Outputs a plural or singualr suffix depending on the value of $count;.
 *
 * The default singular suffix is an empty string. The default plural 
 * suffix is "s" - Example: Cow and Cows
 *
 * To change the plural suffix, pass it to the filter as a paramter
 * Ex: pluralize(es)
 *
 * To change the singular suffix and the plural suffix, pass a comma 
 * separated list to the filter with the first element the singular suffix 
 * and the second element the plural suffix
 * EX: cherr{cherry_count | pluralize(y,ies)}
 * Any additional items in the list will be ignored.
 */

class filters_pluralize extends filters  {
	static function execute($count, $suffixlist="s") {
		
		# Get the suffixes from the suffixlist
		$suffixlist = filters::comma_to_array($suffixlist);
		if(count($suffixlist) > 1) {
			$plural_suffix = $suffixlist[1];
			$singular_suffix = $suffixlist[0];
		} else {		
			$singular_suffix = "";
			$plural_suffix = $suffixlist[0];
		}
		
		# Return the singular suffix if the count is equal to 1, plural otherwise
		if($count == 1) {
			return $singular_suffix;
		} else {
			return $plural_suffix;
		}
	}
}
?>
