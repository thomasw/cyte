/**
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
*/
CREATE TABLE `user_openids` (
  `open_id` varchar(255) NOT NULL default '',
  `user_id` mediumint(8) NOT NULL default '0',
  PRIMARY KEY  (`open_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `users` (
  `user_id` mediumint(8) NOT NULL auto_increment,
  `user_username` varchar(64) NOT NULL default '',
  `user_deleted` tinyint(1) NOT NULL default '0',
  `user_last_mod` int(11) NOT NULL default '0',
  `user_created` int(11) NOT NULL default '0',
  PRIMARY KEY  (`user_id`),
  KEY `user_username` (`user_username`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;