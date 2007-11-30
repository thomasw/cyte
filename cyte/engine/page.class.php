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
*   This object is used to render pages.
*
*   Author: Thomas Welfley, Greg Allard
*   Date: 3 / 3 / 2005
*	Modified: 8 / 1 / 2007
*   Version: 1.4.0
*
*   Parameter Information:
*   Constructor has 1 parameters - an arguments array which should contain the following data.
*   
*   template
*      Specifies the template to be used to render the page. The default template will be used in the event of 
*		an error or, if this parameter is not specified.
*   title:
*      Specify a title for the document. The TITLE key determines how this title is managed. See the title key
*      documentation for more help.
*   auth_requirement
*      Specify an authorization requirement for a document:
*      0 - Authorization is NOT required for this document and an authorization routine will not be run.
*          The Authorized variable will be set to TRUE.
*      1 - Authorization IS required. If $auth_requirement is set to 1, the page must have a valid login form and
*          a valid authorization routine or the page rendering will fail and an error message will be displayed.
*      2 - Authorization is optional. If no authorization routine is provided, authorized will be set to false.
*          No Authorization form is required. This level will not try to authorized unauthorized users.
*		If none is set, the default authorization level from the configuration file will be used.
*   auth_form
*      This is a template for a form that will set the conditions for the authorization routine to be succesful
*      provided you input a valid username and password. If not set, default authorization form from the config
*		will be used.
*   auth_routine 
*      This is a php file that handles how the template engine checks to see if the user is authorized and process
*      auth_form input.
*	auth_params
*		auth_routines may require some parameters to use to authorize
*
*/
class page {
	# Page properties
	
   	var $avail_templates;													// Array of available templates
	var $avail_keys;														// Array of available keys
	var $avail_modules;														// Array of installed modules
	
	var $title;																// Title object
	var $template_file;														// The specified template
	var $page;																// Stores the unparsed document
	var $auth_requirement;														// 0 Authorization not required, 1 Authorization is required
	var $auth_form;															// The authorization form. - Used to validate users who are not authorized
	
	var	$errors;															// Listing of errors encountered
	var $start_time;														// Time that page is instantiated at
	
	var $request_controller;												// Request Controller
	var $current_user;														// User Object
   

	var $parsed_page;														// Stores the parsed page
	var $segmented_doc;														// Stores the segmented document
	
	function __construct($args=FALSE) {
		global $template_conf;
		global $site_conf;
		global $lang;
		global $errors;
		
		# Prepare base vars
		$this->errors		        =& $errors;
		
		# Get time
		$start_time                 = microtime();
		$start_time                 = explode(" ",$start_time);
		$this->start_time           = $start_time[1] + $start_time[0];
		
		# Get default values
		$template_file              = $template_conf['def_template'];
		$title                 	 	= new title();
		$auth_requirement           = $template_conf['def_auth_req'];
		$auth_form                  = $template_conf['def_auth_form'];
		$auth_routine               = $template_conf['def_auth_routine'];
		$auth_params                = $template_conf['def_auth_params'];
		
		# Overwrite defaults with values in $arg
		if(isset($args['template'])) {
		   $template_file = $args['template'];
		}
		if(isset($args['title'])) {
		   $title->page_title = $args['title'];
		}
		if(isset($args['auth_requirement'])) {
		   $auth_requirement = $args['auth_requirement'];
		}
		if(isset($args['auth_form'])) {
		   $auth_form = $args['auth_form'];
		}
		if(isset($args['auth_routine'])) {
		   $auth_routine = $args['auth_routine'];
		}
		if(isset($args['auth_params'])) {
		   $auth_params = $args['auth_params'];
		}
		
		$this->title				= $title;
		$this->auth_form			= $auth_form;
		$this->auth_requirement		= $auth_requirement;
		$this->template_file		= $template_file;
		$this->current_user			= new $template_conf['def_user']($auth_routine, $auth_requirement, $auth_params);
		$this->avail_templates		= list_files($template_conf['template_path']);
		$this->page					= FALSE;
		$this->avail_keys			= $this->get_keys($template_conf['engine_path'].'keys/');	// add cyte's basic keys
		$this->avail_keys			= array_merge($this->avail_keys, $this->get_keys());		// add developer keys from specified directory
		
		# Check that we have a list of available templates, otherwise die.
		if($this->avail_templates === FALSE) {
		   $this->errors[] = $lang['err_006'];
		   $this->fatal_error();
		}
		
		# Check that we have a list of available keys, otherwise die.
		if($this->avail_keys === FALSE) {
		   $this->errors[] = $lang['err_007'];
		   $this->fatal_error();
		}
		
		# Instantiate the request_controller - do not pass authorization level here, it is irrelevant - post handler execution is independent of authorization level required to view a page!!!
		$this->request_controller = new request_controller($this->current_user);
		
		# Determine which template to load and load it.
		$this->load_template($this->select_template());
   		
		# FIN
	}
	
	/**		<get_keys>
	 *
	 * Gets all the keys in the keys directory as well as keys in sub directories.
	 *
	 * @author		Greg Allard
	 * @version		1.1.1		11/30/7
	 * @param		string		path of directory
	 * @return		array		filename => directory
	 */
	private function get_keys($dir = '')  {
		global $template_conf;
		
		if ($dir == '')  {
			$dir = $template_conf['key_path'];
		}
		
		# Get a list of available keys
		$avail_keys = list_files_as_keys($dir, 1);
		
		$more_keys  = array();  // start the array to add to if we find more
		
		// see if there were any directories containing more keys
		if (is_array($avail_keys) && count($avail_keys) > 0)  {
			foreach ($avail_keys as $key_file => $key_path)  {
				// if there isn't a ., then its probly a folder
				if (strpos($key_file, '.') === FALSE)  {
					// double check anyways
					if (is_dir($key_path.$key_file))  {
						// get its keys
						$more_keys = array_merge ($more_keys, $this->get_keys($key_path.$key_file.'/', 1));
						// remove from list since its a directory and not a key file
						unset($avail_keys[$key_path]);
					}
				}
			}
		}
		
		return array_merge($avail_keys, $more_keys);
		
	}
	
	/**
	 * select_template()
	 * No parameters
	 * Selects a template based on whether or not the current user is authorized, the page's authorziation level, and the specified authorization form.
	 */
	private function select_template() {
		global $template_conf;												// Get the site's template configuration data
		
		// Read the specified template if user is authorized, or authorization is not required. Otherwise, get the auth form -- fail if no auth form is specified.
		if ($this->current_user->authorized || $this->auth_requirement == 2 || $this->auth_requirement == 0)  {
			$template_file = $this->template_file;
		} else {
			if(isset($this->auth_form) && $this->auth_form != "") {
				$template_file = $this->auth_form;
			} else {
				$template_file = $template_conf['error'];
			}
		}
		return $template_file;
	}
	
	/**
	 * load_template($template)
	 * 1 parameter
	 * template - The name of a template file to load - path to templates folder is automatically appended
	 * The function load's the template data into the object's page property. The function is delcared private because it is essentially an extension of the constructor.
	 */
	private function load_template($template) {
		global $template_conf;
		global $site_conf;
		global $lang;
  
	   /**
	      Check to see if the specified template exists and loads it.
	      If the template does not exist, load the default
	      if load_def is true in the config and the template passed to 
	      the function was not the default. If the default does not
	      exist or load_def is false, try to load the error template,
	      and if the error template does not exist, output error
	      message array and kill the script.
	   */

	   if(in_array($template, $this->avail_templates)) {
	      $template_resource = $template_conf['template_path'].$template;
	   } elseif($template_conf['load_def'] && in_array($template_conf['def_template'], $this->avail_templates)  && $template != $template_conf['default']) {
	      $this->errors[] = $lang['err_002'];
	      $template_resource = $template_conf['template_path'].$template_conf['def_template'];
	   } elseif(in_array($template_conf['error'], $this->avail_templates)) {
	      if($template_conf['load_def'] || $template == $template_conf['default']) {
	         $this->errors[] = $lang['err_003'];
	      } else {
	         $this->errors[] = $lang['err_004'];
	      }
	      $template_resource = $template_conf['template_path'].$template_conf['error'];
	   } else {
	      $this->errors[] = $lang['err_005'];
	      $this->fatal_error();
	   }

	   if($template_conf['verbose_error']) {     
	       $this->page = file_get_contents($template_resource);
	   } else {
	        $this->page = @file_get_contents($template_resource);
	   }

	   if($this->page === FALSE) {
	      $this->errors[] = $lang['err_008'];
	      $this->fatal_error();
	   } else {
	       return TRUE;
	   }         
            

	}
   
	private function fatal_error() {
	   global $template_conf;
   
	   $output = "<br />".$template_conf['fatal_error']."\n";
   
	   if ($template_conf['verbose_error']) {
	      $output .= "<ul>\n";
	      foreach($this->errors as $error) {
	         $output .= "<li>$error</li>\n";
	      }
	      $output .= "</ul>\n";
	   }
   
	   echo $output;
   
	   exit;
	}
	
	private function instantiate_key($keyalt, $parameters, $container_contents, $instance) {
		# Get configuration vars for use in keys.
		global $lang;
		global $template_conf;
		
		$key								= strtolower($keyalt).".php";	// The file that contains the class
		$class 								= $keyalt;						// The name of the class we need to instantiate
		$output								= FALSE;						// Output holder
		$output								= FALSE;						// Default output
		
		if (isset($this->avail_keys[$key]))  {
			$key = $this->avail_keys[$key].$key;  							// $template_conf['key_path'].$key;
			if($template_conf['verbose_error']) {							// Get the class if we haven't already
				include_once($key);	
			} else {
				@include_once($key);
			}
				
			// Make sure that the key file defines an appropriately named class
			if(!class_exists($class)) {
				// Class does not exist, abort key processing.
				$this->errors[] = $lang['key_err_006']." Key: $key";
				return FALSE;
			}
			
			// Instantiate the class.
			$key_obj = new $class($keyalt, $parameters, $container_contents, $instance, $this->title, $this->request_controller, $this->current_user);
			
			// Test to make sure the class in the key file inherits the abstract key class, otherwise abort
			if(!in_array("key",class_parents($key_obj))) {
				// Object does not inherit from key, no (easy) way to make sure it implements display and that it has the appropriate parameters. Abort processing
				$this->errors[] = $lang['key_err_005']." Key: $key";
				return FALSE;
			}
			
			$output = $key_obj;										// Get the output
		} else {
			// The key does not exist. Abort key processing.
			$this->errors[] = $lang['key_err_004']." Key: $key";
			return FALSE;
		}
		
		return $output;
	}
	
	private function execute_key($keys, $parameters, $container_contents, $instance, $safe_keys=NULL) {
		global $lang;														// Get global language data
		
		foreach($keys as $key) {
			# If the key is a string, remove its quotation marks and return it.
			if($key[0] =='"') {
				$key = substr_replace($key, '', 0, 1);
				$key = substr_replace($key, '', strlen($key), 1);
				return array('content'=>$key, 'safe_keys'=>$safe_keys);
			} else if($safe_keys!==NULL && !in_array($key, $safe_keys)){		# Test if key is prohibited by $safe_keys
				$this->errors[] = $lang['key_err_008'].$instance;
				$output = FALSE;
			} else 	if(trim($key) == "") {										# Test if key is valid
				// A null key exists. Push appropriate error
				$this->errors[] = $lang['err_014'].$instance;
				$output = FALSE;
			} else {															# Execute the key if we reach this point.
				$key_instance = $this->instantiate_key($key, $parameters, $container_contents, $instance);
				
				# If key execution failed, we want to set output to false, otherwise we want to return the output.
				if(!is_object($key_instance)) {
					$output = FALSE;
				} else if ($key_instance->failed) {
					$output = FALSE;
				} else {
					if($key_instance->make_safe)  {
						if($safe_keys === NULL) {
							$safe_keys = $key_instance->safe_keys;
						}
						return array('content'=>$key_instance->display(), 'safe_keys'=>array_intersect($safe_keys,$key_instance->safe_keys));
					} else {
						return array('content'=>$key_instance->display(), 'safe_keys'=>$safe_keys);
					}
				}
			}
		}
			
		return $output;
	}
	
	
	public function parse($block) {
		$parser = new parser($block);
		$i = 0;
		
		while($next = $parser->getNext()) {
			$content = $this->execute_key($next['keys'], $next['parameters'], $next['content'], $next['instance'], $next['safe_keys']);
			
			# If the key failed, replace it with an empty string.
			if($content === FALSE) {
				$content = array('content'=>'', 'safe_keys'=>NULL);
			}
			
			$parser->replace($next, $content['content'], $content['safe_keys']);
			++$i;
		}
		
		return $parser->merge();
	}
	
	public function render() {		
		# Parse the page
		$this->page = $this->parse($this->page);
		
		# Remove any remaining unused content slots and output the page.
		$this->page = str_replace("<content-slot />", "", $this->page);
		echo $this->page;
		
		# Output processing time if enabled in config
		$this->print_render_time();
		
		# Output errors if debug mode is enabled in config
		$this->print_errors();
	}
   	
	private function print_render_time($override_spt=FALSE) {
		global $template_conf;												// Template engine configuration data
		global $lang;														// Get language data
		
		if($template_conf['show_processing_time'] || $override_spt) {
		     $render_time = microtime();
		     $render_time = explode(" ",$render_time);
		     $render_time = $render_time[1] + $render_time[0];
		     $execution_time = $render_time - $this->start_time;
		     echo "\n<!-- ".$lang['render_time'].$execution_time.' -->';
		}
	}
	
	private function print_errors($override_debug=FALSE) {
		global $template_conf;												// Template engine configuration data
		global $lang;														// Get language data
		
		if($template_conf['debug_mode'] || $override_debug) {
			echo "\n\n<!-- There were ".count($this->errors)." error(s) encountered.";
			
			if(count($this->errors) <= 0) {
				echo ' -->';
			} else {
				echo "\n\n";
			}
			
			foreach($this->errors as $error) {
				echo "\t".$error."\n";
			}
			
			if(count($this->errors) > 0) {
				echo "\n-->";
			}
		}		
	}

	public function insert($input) {
    
	   $this->page = replace_first_occurence($this->page, '<content-slot />', $input."\n");
   
	}
}

?>