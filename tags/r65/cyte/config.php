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
 * CyTE Configuration File
 */



# Some servers may have doc root set weird or this may be installed in a sub dir
# Try to find out where we are

$config_file_location 				=	__FILE__;

$_SERVER['DOCUMENT_ROOT']			=	str_replace('cyte/config.php', '', $config_file_location);

$version							=	'CyTE 1.1.4';

# Site Configuration

$site_conf = array(
	// Name of installation
	'title'							=>	'CyTE',
	// E-mail address of person responsible for maintaining the system
	'admin_contact'		    		=>	'email@domain.tld',
	// The domain the system is hosted on
	'domain'						=>	$_SERVER["HTTP_HOST"],
	// Base directory
	'base_dir'						=>	'/dir_after_domain', /* No trailing slash */
	// URL of the systems homepage
	'home_link'						=>	'http://'.$_SERVER["HTTP_HOST"].'/dir_after_domain', /* No trailing slash */
	// Path from web root to Administration section
	"admin_path"					=>	"/administrate/", 
	// dsn to connect to the DB
	'dsn'							=>	'mysql://user:password@localhost/dbname',
	// how long the cookie should last if using
	'exp_time'						=>	60*60*24*7*2
);

# Template Engine Configuration

$template_conf = array (
	// This is the path to the templates directory.
	'template_path'		    		=>	$_SERVER['DOCUMENT_ROOT'].'cyte/templates/',
	// This is the path to the keys directory.
	'key_path' 						=>	$_SERVER['DOCUMENT_ROOT'].'cyte/keys/',
	// This is the path to the classes directory.
	'class_path' 					=>	$_SERVER['DOCUMENT_ROOT'].'cyte/classes/',
	// This is the path to the locators directory. Used to autoload files.
	'locator_path' 					=>	$_SERVER['DOCUMENT_ROOT'].'cyte/locators/',
	// This is the path to the filters directory. Used in iterators.
	'filter_path' 					=>	$_SERVER['DOCUMENT_ROOT'].'cyte/filters/',
	// This is the path to the main CyTE files.
	'engine_path' 					=>	$_SERVER['DOCUMENT_ROOT'].'cyte/engine/',
	// This is the name of the default template.
	'def_template' 		    		=>	'default.html',
	// Load default template if CyTE fails to load the template passed to the page constructor.
	'load_def'						=>	TRUE,
	// This is the name of the error page template.
	'error'							=>	'error.html',
	// Fatal error message string
	// This fatal error message is displayed if CyTE fails to load the error template.
	'fatal_error'					=>	'This page could not be rendered by the template engine. Please notify this site\'s <a href="mailto:'.$site_conf['admin_contact'].'" > administrator</a>.',
	// Output verbose error data (the contents of errors array) on fatal errors
	'verbose_error'		    		=>	TRUE,
	// Debug mode - Output cyte_errors array in HTML comments on every page load.
	'debug_mode'					=>	TRUE,
	// This is the path to the authorization routines directory.
	'auth_routines' 				=>	$_SERVER['DOCUMENT_ROOT'].'cyte/auth_routines/',
	// This is the path to the post handlers directory.
	'post_handlers' 				=>	$_SERVER['DOCUMENT_ROOT'].'cyte/post_handlers/',
	// This is the path to the get handlers directory.
	'get_handlers' 					=>	$_SERVER['DOCUMENT_ROOT'].'cyte/get_handlers/',
	// Name of language pack.
	'language'						=>	'english-us.php',
	// Default authorization form
	'def_auth_form'		    		=>	'login.html',
	// Default authorization routine
	'def_auth_routine'	    		=>	'',
	// Default authorization parameters (to be sent to the authorizer and auth routine)
	'def_auth_params'	    		=>  array(),
	// Default authorization level
	'def_auth_req'					=>	1,
	// Output processing time in html comment at the bottom of all pages
	'show_processing_time'			=>	TRUE,
	// Default User class to be used with authorization. The class that extends visitor
	'def_user'						=> 'visitor'
);


# Default Safe Keys
$safe_keys = array(
	'link'
);

// include the cyte core
require_once('engine/core.php');

// Add locations to the service locator

?>
