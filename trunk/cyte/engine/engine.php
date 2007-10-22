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
	Require CyTE Components
*/

# Data Access class (Abstract database class -- All data access classes must inherit from this class)
require_once('data_access.class.php');

# Visitor class
require_once('visitor.class.php');					// Abstract user class

# Authorizer class
require_once('authorizer.class.php');				// Abstract auhtorizer class

# Post Controller class
require_once('request_controller.class.php');

# Title class
require_once('title.class.php');

# Page class (The heart of the template engine)
require_once('page.class.php');

# Key class (Abstract key class -- inherited by all keys)
require_once('key.class.php');

# Require iterator parser (Used by iterator keys)
require_once('iterator_parser.class.php');

# Require filters class (Used by iterator keys)
require_once('filters.class.php');

# Request handler class (Abstract handler class - Post handler and get handler implement this.)
require_once('request_handler.class.php');

# Post Handler class (Abstract post handler class -- All post handlers must inherit from this class)
require_once('post_handler.class.php');

# Get Handler class (Abstract get handler class -- All get handlers must inherit from this class)
require_once('get_handler.class.php');

# Parser class (Class used to parse blocks of markup for Cyte keys.)
require_once('parser.class.php');


?>