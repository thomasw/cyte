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
 * This file is for the link key.
 * This key is only for linking INTIERNAL documents! No need to use it for external links or anchors.
 *
 * Author: Thomas Welfley & Greg Allard
 * Date: 11 / 17 / 2006
 * Version: 0.0.6
 *
 * Parameters: url, class, accesskey
 * Container tag: Yes
 * Example:
 * To link the gallery section, you would pass the following to link.
 * <cyte:link href="/gallery/" class="yay" accesskey="G" />Gallery Section</cyte>
 * This yields:
 * <a href="/base/dir/gallery/" class="yay" accesskey="g" /><em class="acc">G</em>allery</a>
 */
	
class link extends key {
	
	public $href;					// URL for the link
	public $class;					// [Optional] Class to be applied to the a tag
	public $accesskey;				// [Optional] Access key for the link. First math in $title will be wrapped in an em tag with class="acc"
	
	private $title;					// [Optional] Link text to be wrapped in the a tag (this is what the tag wraps around, doesn't need to be set with a parameter)
	private $base_dir;
	private $ahref;
	
	function check_attributes() {
		// Get the global conf so we know the base directory
		global $site_conf;
		$this->base_dir = $site_conf['base_dir'];
		
		// href is a required attribute
		if(!isset($this->href) || $this->href == "") {
			$this->failed = TRUE;
			$this->undefined_att_err('href');
		} else {
			$this->href = $this->base_dir.$this->href;
		}
		
		// Set title to the contents of the container or href if it isn't set
		if(isset($this->content) && $this->content != "") {
			$this->title = $this->content;
		} else {
			$this->title = $this->href;
		}
		
		// Generate ahref
		$this->ahref = $this->generate_ahref();
	}
	
	/*
	 * generate_ahref(): Generate a link based on the object properties.
	 */ 
	function generate_ahref() {		
		// Start building the link
		$link = '<a href="'.$this->href.'"';
		
		// Append class if it is set
		if(isset($this->class) && $this->class != "") {
			$link .=' class="'.$this->class.'"';
		}
		
		// Append accesskey if it is set
		if(isset($this->accesskey) && $this->accesskey != "" && strlen($this->accesskey) == 1) {
			$link .=' accesskey="'.$this->accesskey.'"';
			
			// Insert em into the title
			$title = replace_first_occurence($this->title, $this->accesskey,'<em class="acc">'.$this->accesskey.'</em>');
		} else {
			// Since there was no access key, just go ahead and set title to the value of the object property
			$title = $this->title;
		}
		
		$link .= '>';
		
		// Append title if it is set, otherwise use the URL
		$link .= $title;
		
		$link .='</a>';
		
		return $link;
	}
	
	function display() {
		if(!$this->failed) {
			return $this->ahref;
		}
	}
	
}
?>
