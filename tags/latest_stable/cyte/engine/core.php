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

# Instantiate global errors array
$errors = array();

# Utility functions
require_once('util.php');

# Language file
require_once('languages/'.$template_conf['language']);

# Template engine
require_once('engine.php');

# The locator locates class files for inclusion
require_once('locator.php');

service_locator::attach_locator(new locator_cyte(),				'CyTE');
service_locator::attach_locator(new locator_incpear(),			'inc_PEAR');
service_locator::attach_locator(new locator_engineincpear(),	'engine_inc_PEAR');
service_locator::attach_locator(new locator_subcyte(),			'sub_CyTE');
service_locator::attach_locator(new locator_key(),				'cyte_keys');
service_locator::attach_locator(new locator_subkey(),			'cyte_sub_key');
service_locator::attach_locator(new locator_filters(),			'cyte_filters');
service_locator::attach_locator(new locator_pear(),				'PEAR');

?>
