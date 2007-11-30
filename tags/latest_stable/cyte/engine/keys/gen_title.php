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
*	This key uses the title class and is for the title key.
*
*	Author: Thomas Welfley
*	Date: 3 / 4 / 2005
*	Version: 0.6.0
*	Modified: 12/26/2006
*
*	Key Information:
*	<cyte:gen_title />
*	Key has 3 parameters (optional).
*	primary, secondary, separator
* 	primary and secondary are title types. They can be either site, page, or article refering to
*	the site stitle, page title, and article title respectively.
*	separator is an optional string separator. If both primary and secondary are specified, the key will
*	return the specified title types separated by separator. If no separator is specified, the key uses
*	": ". If only primary is specified and no secondary, then the key only returns the primary title type
*	with no separator. If nothing is specified, the key returns the site title which is
*	defined in the configuration file.
*
*	 IF primary has invalid input, it defaults to the site title. If secondarys has invalid input, it defaults to null.
*/

class gen_title extends key {
	public $primary;
	public $secondary;
	public $separator;
	
	function check_attributes() {		
		// Set default values
		if(!isset($this->separator)) {
			$this->separator = ": ";
		}
	
	}
	
	function generate_title() {
		// Get the first part of the title
		switch(strtolower($this->primary)) {
			case 'site':
				$first = $this->page_title->site_title;
			break;
			case 'page':
				$first = $this->page_title->page_title;
			break;
			case 'article':
				$first = $this->page_title->article_title;
			break;
			default:
				$first = $this->page_title->site_title;
			break;
		}
		
		// Get the last part
		switch(strtolower($this->secondary)) {
			case 'site':
				$second = $this->page_title->site_title;
			break;
			case 'page':
				$second = $this->page_title->page_title;
			break;
			case 'article':
				$second = $this->page_title->article_title;
			break;
			default:
				$second = "";
			break;			
		}
		
		// Combine first and second halves if necessary
		if(isset($second) && $second != "") {
			$output = $first . $this->separator . $second;
		} else {
			$output = $first;
		}
		
		return $output;
	}
	
	function display() {
		if(!$this->failed) {	
			return $this->generate_title();
		}
	}
}

?>