<?php
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

require('cyte/core.php');



$page = new page(array(
						'title'			=>		'Welcome',
						'auth_requirement' => 		2
					));
$page->insert('
			 <h2>Welcome</h2>
			 <p>
				Lorem and Ipsum!
			</p>
			<p>
				Files to check to get started:
				<ul>
					<li>config.php</li>
					<li>user.php</li>
					<li>defauth.php</li>
				</ul>
			</p>
			 ');
$page->render();
?>
