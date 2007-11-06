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
 * @author:		Greg Allard
 * @version:	1.0		7/3/7
 *
 *		Processes a sample form
 */
 
class sample extends post_handler  {
	//values passed from the form
	public $form_field_one;
	public $form_field_two;
	
	public function authorize()  {
		// if there are special authorization considerations for this form that differ from the page
	}
	
	public function check_attributes()  {
		// check to make sure required attributes were set and have valid data
		if (trim($this->form_field_one) == '')  {
			// missing_field sets failed flag and adds message to errors array
			$this->missing_field('Form Field One');
		}
	}
	
	public function execute()  {
		// all above checks out, do the processing
		
		$success = true;
		
		if (!$success)  {
			$this->post_errors[] = 'There was a problem! If the problem persists, contact <cyte:admin_contact />.';
		}
	}
}
?>
