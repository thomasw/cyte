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
$lang = array(
	// Template Engine Errors Messages
	"err_001"		=> "[001] No authorization form was specified.",
	"err_002"		=> "[002] The template passed to the constructor of the page object does not exist. CyTE will now attempt to load the default template.",
	"err_003"		=> "[003] The default template could not be loaded. CyTE will now attempt to load the error template.",
	"err_004"		=> "[004] The template passed to the constructor of the page object does not exist. CyTE will now attempt to load the error template.",
	"err_005"		=> "[005] No templates could be loaded.",
	"err_006"		=> "[006] CyTE could not get a list of files in the templates directory. Check the value of 'template_path' in your config, make sure the directory exists, and make sure that it has appropriate permissions.",
	"err_007"		=> "[007] CyTE could not get a list of files in the keys directory. Check the value of 'key_path' in your config, make sure the directory exists, and make sure that it has appropriate permissions.",
	"err_008"		=> "[008] There is a problem with the template file. Make sure this file exists and that the permissions are appropriate.",
	"err_009"		=> "[009] A CyTE tag is missing its terminating '/'. Tags without a terminating / are usually the opening of a container tag. However, this tag has no corresponding close tag. Attempting to execute the following as a closed tag: ",
	"err_010"		=> "[010] A close container tag was found, but no corresponding open tag was detected.",
	"err_011"		=> "[011] You are not authorized to view this page.",
	"err_012"		=> "[012] The following container key does not have a corresponding close tag: ",
	"err_013"		=> "[013] Malformed attributes detected. Attempting to execute the following key with no parameters set: ",
	"err_014"		=> "[014] A null alternate key was detected. Alternate key will fail and continue to next if one exists. Instance: ",
	"err_015"		=> "[015] Content-Key Mismatch: Ensure that your markup does not contain unclosed or malformed CyTE tags. Error occurred while executing: ",
	"err_016"		=> "[016] An authorizor does not extend the abstract authorizor class. Authorizor: ",
	"err_017"		=> "[017] An appropriately defined class does not exist in a authorizor file. Please verify that a class with the same name as the file resides in authorizor file for: ",	
	// Key Error messages
	"key_err_001"	=> "[KEYERR-001] Key processing resulted in no output.",
	"key_err_002"	=> "[KEYERR-002] A required parameter was not specified: ",
	"key_err_003"	=> "[KEYERR-003] Unable to include a key. Key processing aborted.",
	"key_err_004"	=> "[KEYERR-004] Attempt to use a key that does exist detected. Key processing aborted.",
	"key_err_005"	=> "[KEYERR-005] A key's class does not inherit the abstract key class. Key processing aborted.",
	"key_err_006"	=> "[KEYERR-006] A key file does not contain an appropriately named class. Every key file should contain a class with the same name as the file (excluding the extension).",
	"key_err_007"	=> "[KEYERR-007] Attempt to modify a non-public or primitive key attribute detected in the following key call:",
	"key_err_008"	=> "[KEYERR-008] Attempt to execute prohibited key detected. Aborting. Key: ",
	"key_err_009"	=> "[KEYERR-009] Attempt to set attribute that is not defined detected in: ",
	"key_err_010"	=> "[KEYERR-010] Attempt to iterate through an invalid iterator list in: ",
	"key_err_011"	=> "[KEYERR-011] Attempt to access an inaccessible / non-existent property in an iterator key:  ",
	"key_err_012"	=> "[KEYERR-012] Call to non-existent filter or private function detected in an iterator key: ",
	// Handler / Controller Error Messages
	"handler_err_001"	=> "[HANDERR-001] An appropriately defined class does not exist in a handler file. Please verify that a class with the same name as the file resides in handler file for: ",
	"handler_err_002"	=> "[HANDERR-002] A post handler does not inherit from the post handler abstract class in the handler file for ",
	"handler_err_003"	=> "[HANDERR-003] The specified parameters array contains a value for a parameter that is not defined in: ",
	"handler_err_004"	=> "[HANDERR-004] The specified parameters array contains a value for a private handler attribute in: ",
	"handler_err_005"	=> "[HANDERR-005] Aborting handler processing because a specified handler does not exist: ",
	"handler_err_006"	=> "[HANDERR-006] A get handler does not inherit from the get handler abstract class in the handler file for ",
	"handler_missingfield1"	=> "The ",
	"handler_missingfield2"	=> " field is required. Please submit a valid ",
	"handler_missingfield3"	=> ".",
	// Database Error Messages
	"db_101"		=> "[DBERR-101] Could not connect to the database.",
	"db_102"		=> "[DBERR-102] Unable to execute the query.",
	"db_103"		=> "[DBERR-103] There is a unique field conflict.",
	// Errors with mods
	"mod_101"		=> "[MODERR-101] This class was previously declared:",  		// when using, add class name to error string
	"mod_102"		=> "[MODERR-102] This key was previously declared: ",  			// when using, add key name to error string
	"mod_103"		=> ". This key was declared by the mod: ",  					// used in addition to mod_102
	"mod_104"		=> "[MODERR-103] This post handler was previously declared:", 	// when using, add class name to error string
	// Login errors
	"login_error"			=> "The username and/or password you entered was incorrect.",
	"login_username"		=> "The username you entered was not found.",
	"login_password"		=> "The password you entered was invalid.",
	"login_unauth"			=> "You are not authorized to view this page.",
	"login_idle"			=> "You have been logged out due to being idle for too long.",
	"login_expire"			=> "Your authorization session has expired.",
	"login_breach"			=> "There has been a security breach, authorization has been terminated to prevent unauthorized access. If the problem persists, please contact a system administrator.",
	"login_system_error"	=> "The system was unable to connect to the server to check your credentials. If the problem persists, please contact a system administrator.",
	// Output
	"render_time"	=> "This page was rendered in: ", 
	// register form
	"register_passwords_not_match"	=> "Please enter the same password twice.",
	// question form
	"answer_all_questions" => "You must answer all of the questions on this form."
);
?>