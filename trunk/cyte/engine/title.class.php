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
* Title Object
*
* This object is designed to allow for easier access to title data.
* 
* Author: Thomas Welfley
* Date: 2/10/2006
*
* Constructor accepts the page title (the site title is loaded from site_conf)
*
* gen_joint_title accepts a separator string, and two strings to identify whichs
* of the title vars you want to combine. Valid input is, site, page, or article.
* Default: Site title: Page title
*
*/

class title {
	public $site_title;
	public $page_title;
	public $article_title;
	
	function title($page_title = '') {
		global $site_conf;
		
		$this->page_title = $page_title;
		$this->site_title = $site_conf['title'];
	}
	
	function gen_joint_title($separate_string = ': ', $first='site', $second='page') {
		
		$titles = array (
			'site'		=> $this->site_title,
			'page'		=> $this->page_title,
			'article'	=> $this->article_title	
		);
		
		// Ensure that first and second vars are valid and merge, else just return the site title.
		if(($first == 'site' || $first == 'page' || $first == 'article') &&
		   ($second == 'site' || $second == 'page' || $second == 'article')) {
				if($titles["$first"] == '' ||  $titles["$second"] == ''){
					return FALSE;
				} else {
					return $titles["$first"].$separate_string.$titles["$second"];
				}
			} else {
				return FALSE;
			}
	}

}
?>