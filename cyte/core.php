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

// Turn on when testing
//error_reporting(E_ALL);

# Instantiate global errors array
$errors = array();

# Configuartion File
require_once('config.php');

# Utility functions
require_once('util.php');

# Language file
require_once('engine/languages/'.$template_conf['language']);

# Template engine
require_once('engine/engine.php');

# Require data objects
require_once('locator.php');


?>
