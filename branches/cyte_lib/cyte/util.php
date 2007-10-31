<?php

/**
 * PHPs default __autload
 * Grabs an instance of ServiceLocator then runs it
 * @package ServiceLocator
 * @author Chris Corbyn
 * @param string class
 */
function __autoload($class)
{
    $locator = new service_locator();
    $locator->load($class);
}

/**
 * Returns input wrapped in a script tag
 */
 function js_script($js) {
	 	$output = '<script type="text/javascript">';
		$output .= $js;
		$output .= "</script>";
		return $output;
 }
/**
 * Returns a javascript alert
 */
 function js_msg($msg) {
		$output = '<script type="text/javascript">'."\n";
		$output .= "\talert('$msg');\n";
		$output .= "</script>\n";
		return $output;
	}


/**
 * Replaces first occurence of $search in $string with $replace
 */
function replace_first_occurence($string, $search, $replace) {
	
	$pos = strpos($string, $search);

	if (is_int($pos)) {
            $len = strlen($search);
            return substr_replace($string, $replace, $pos, $len);
	}
		 
     return $string;
}

/**
 * This function uses regular expressions to determine if an e-mail address is valid.
 */

function checkemail($email)  {
	$pattern = "/^[A-z0-9\._-]+"
		. "@"
		. "[A-z0-9][A-z0-9-]*"
		. "(\.[A-z0-9_-]+)*"
		. "\.([A-z]{2,6})$/";
	return preg_match ($pattern, $email);
}

/**
 * Lists all files in a directory
 * Parameters: (String) Path of directory to list, with_dirs=0 [default], set to 1 to include directories)
 */
function list_files($dir,$with_dirs=FALSE) {
	$cwd = getcwd();			// Current Working Directory
	$files = array();			// Array for storing list of files
	$files_dirs = array();	// Array for storing list of files and sub-dirs
	$count = 0;					// Counter
	
	if (strstr($dir,$_SERVER['DOCUMENT_ROOT']) === FALSE) {
		$dir = $_SERVER['DOCUMENT_ROOT'].$dir;
	}
	@chdir($dir);
	$handle = @opendir($dir);
	while(($file = @readdir($handle)) != FALSE) {
		array_push($files_dirs,$file);
	}
	$handle = @closedir($handle);
	if ($files_dirs != FALSE) {
		if($with_dirs) {
			foreach ($files_dirs as $value) {
				if($value != "." && $value !="..") {
					array_push($files,$value);
				}
				++$count;
			}
		} else {
			foreach ($files_dirs as $value) {
				if(!is_dir($value)) {
					array_push($files,$value);
				}
				++$count;
			}
		}
		chdir($cwd);
		natsort($files);
		return $files;
		
	} else {
		chdir($cwd);
		return FALSE;
	}
}

/**		list_files_as_keys()
 *
 * Lists all files in a directory as the keys of an array with the dir as the value
 *
 * @author		Greg Allard
 * @version		1.0
 * @param		string		path of directory to list
 * @param		bool		set to 1 to include directories. default = 0
 * @return		array		filename => directory
 */
function list_files_as_keys($dir, $with_dirs = FALSE)  {
	$cwd        = getcwd();		// Current Working Directory
	$files      = array();		// Array for storing list of files
	$files_dirs = array();		// Array for storing list of files and sub-dirs
	$count      = 0;			// Counter
	
	if (strstr($dir,$_SERVER['DOCUMENT_ROOT']) === FALSE)  {
		$dir = $_SERVER['DOCUMENT_ROOT'].$dir;
	}
	@chdir($dir);
	$handle = @opendir($dir);
	while (($file = @readdir($handle)) != FALSE)  {
		array_push($files_dirs,$file);
	}
	$handle = @closedir($handle);
	if ($files_dirs != FALSE)  {
		if ($with_dirs)  {
			foreach ($files_dirs as $value)  {
				if ($value != "." && $value !=".." && $value !=".svn")  {
					$files[$value] = $dir;
					//array_push($files,$value);
				}
				++$count;
			}
		} else {
			foreach ($files_dirs as $value)  {
				if (!is_dir($value))  {
					$files[$value] = $dir;
					//array_push($files,$value);
				}
				++$count;
			}
		}
		chdir($cwd);
		natsort($files);
		return $files;
		
	} else {
		chdir($cwd);
		return FALSE;
	}
}

/**		<include_files>
 *
 * Includes all files in a directory
 *
 * @author		Greg Allard
 * @version		1.0		8/2/7
 * @param		string		path of directory
 * @param		bool		set to 1 to include recursively (off by default)
 */
function include_files($dir, $recursive = FALSE)  {
	// get file list
	$files = list_files_as_keys($dir, $recursive);
	
	// if there are files there, include them all
	if (is_array($files) && count($files) > 0)  {
		foreach ($files as $file_name => $path)  {
			// add a slash to the end of path if there isn't one yet
			if (substr($path, -1) != '/')  {
				$path .= '/';
			}
			// if this is a dir and we are recursive
			if ($recursive && is_dir($path.$file_name))  {
				// call this func with the dir path
				include_files($path.$file_name, $recursive);
			}
			else  {
				// include the file
				include_once($path.$file_name);
			}
		}
	}
}

/**
 * A case insensitive in_array function.	
 */

function in_array_cin($strItem, $arItems) {
   $bFound = FALSE;
   foreach ($arItems as $strValue)
   {
       if (strtoupper($strItem) == strtoupper($strValue))
       {
           $bFound = TRUE;
       }
   }
   return $bFound;
}

/**
 * This function determines whether or not an array's keys contains a 
 * specified substring.
 */

function substr_in_array_keys($string, $array) {
	$keys = implode('', array_keys($array));
	
	if(strstr($keys, $string) === FALSE) {
		Return FALSE;
	} else {
		return TRUE;
	}
}

/**
 * Delete a file, or a folder and its contents
 * @author      Aidan Lister <aidan@php.net>
 * @version     1.0.2
 * @param       string   $dirname    Directory to delete
 * @return      bool     Returns TRUE on success, FALSE on failure
 */
function rmdirr($dirname) {
	// Sanity check
	if (!file_exists($dirname)) {
		return false;
	}
	
	// Simple delete for a file
	if (is_file($dirname)) {
		return unlink($dirname);
	}
	
	// Loop through the folder
	$dir = dir($dirname);
	while (false !== $entry = $dir->read()) {
		// Skip pointers
		if ($entry == '.' || $entry == '..') {
			continue;
		}
		
		// Recurse
		rmdirr("$dirname/$entry");
	}
	
	// Clean up
	$dir->close();
	return rmdir($dirname);
}

/**
 * Checks filename for illegal characters. I can't remember where the list comes from.
 * Returns TRUE if ok, FALSE if not ok.
 */
function check_file_name($filename) {
	if (preg_match ("/\\|\.\.|\/|\\\|\\$|\&|\||\?|\*|\@|\#|\%|\^|\*|\(|\)/", $filename)) {
		return FALSE;
	}
	return TRUE;
}

/**
 * Returns the modified date of the script calling the function.
 */
function modified_date() {
	$stats = stat($_SERVER['SCRIPT_FILENAME']);
	$date = date("F j, Y",$stats[9]);
	return $date;
}

/**
 * Connects to the database using the data specified in $db_conf, which is located in the config.
 */
function connect_me()  {
	global $db_conf;
	require_once 'DB.php';
	$db = DB::connect("mysql://".$db_conf['dbuser'].":".$db_conf['dbpass']."@".$db_conf['dbhost']."/".$db_conf['dbname']);
	
	if (DB::isError($db)) {
		die ($db->getMessage());
	}
	
	return $db;
}

/**
 * This function usees a regular expression to determine if a phone number is valid.
 */
function isphone($element)  {
	$element = preg_split('//', $element, -1, PREG_SPLIT_NO_EMPTY);
	$element = preg_grep("/\d/", $element);
	$element = implode('', $element);
	if (strlen ($element) != 10)  {
		return false;
	}
	else  {
		return $element;
	}
}

/**
  * Clean an array or string for printing nicely. Will remove slashes and 
  * take care of html
  *
  * @author       Greg Allard
  * @version      1.0
  * @param        mixed   $value   This can be an array or a string
  * @return       mixed   $value   This will return the same type as passed
*/
function clean_for_print($value)  {
	if (is_array($value))  {  // recursive if it needs to go deeper
		return array_map('clean_for_print', $value);
	}
	else  {
		return htmlspecialchars(stripslashes($value));
	}
}

/**
  * Clean an array or string for insertion into a database. Will add slashes if needed and 
  * take care of html
  *
  * @author       Greg Allard
  * @version      1.0
  * @param        mixed   $value   This can be an array or a string
  * @return       mixed   $value   This will return the same type as passed
*/
function clean_for_db($value)  {
	if (is_array($value))  {  // recursive if it needs to go deeper
		return array_map('clean_for_db', $value);
	}
	else  {
		return htmlspecialchars($value);
	}
}

/**
  * Clean an array or string for re-insertion into a database.
  * When something is edited and resubmitted you might need to reslash
  *
  * @author       Greg Allard
  * @version      1.0
  * @param        mixed   $value   This can be an array or a string
  * @return       mixed   $value   This will return the same type as passed
*/
function addslashes_deep($value)  {
	if (is_array($value))  {  // recursive if it needs to go deeper
		return array_map('addslashes_deep', $value);
	}
	else  {
		return addslashes($value);
	}
}

/**
  * This will print out <pre> and </pre> around $value which is printed with print_r()
  *
  * @author       Greg Allard
  * @version      1.0
  * @param        mixed   $value   This can be an array or a string
  * @param        bool    $return  Set to true if you need string return instead of printed
  * @return       outputs text or returns a string
*/
function printer($value, $return = false)  {
	if ($return)  {
		$ret_val  = '<pre>';
		$ret_val .= print_r($value, $return);
		$ret_val .= '</pre>';
		return $ret_val;
	}
	else  {
		echo '<pre>';
		print_r($value, $return);
		echo '</pre>';
	}
}

// -----------------------------------------
/* *****************************************
		-- Clean Functions --
********************************************/
// -----------------------------------------

/**			form2db()
 *
 * Used for cleaning text from a form before sending it to the DB
 *
 * @author       Greg Allard
 * @version      1.1.2		8/22/6
 * @param        mixed   $value   This can be an array, object, or a string
 * @return       mixed   $value   This will return the same type as passed
 */
function form2db($value)  {
	if (is_array($value))  {  // recursive if it needs to go deeper
		return array_map('form2db', $value);
	}
	else if (is_object($value))  {
		if (strtolower(get_class($value)) != 'db_mysql')  {  // some classes have connected to db, don't want that part
			$array = get_object_vars($value);  // get the vars in an object and make an associative array with it
			$value->fill(form2db($array));       // call this func on the array and fill the passed object
		}
		return $value;
	}
	else  {
		if (get_magic_quotes_gpc() == 0)  {  // if slashes aren't automatically added by php
			$value = addslashes($value);     // add some slashes
		}
		return $value;
	}
}

/**			form2form()
 *
 * Used for cleaning text from a form that needs to be put back into a form
 * Useful when there are errors that need to be fixed before submitting to the DB
 *
 * @author       Greg Allard
 * @version      1.1.1		8/1/6
 * @param        mixed   $value   This can be an array, object, or a string
 * @return       mixed   $value   This will return the same type as passed
 */
function form2form($value)  {
	if (is_array($value))  {  // recursive if it needs to go deeper
		return array_map('form2form', $value);
	}
	else if (is_object($value))  {
		if (strtolower(get_class($value)) != 'db_mysql')  {  // some classes have connected to db, don't want that part
			$array = get_object_vars($value);  // get the vars in an object and make an associative array with it
			$value->fill(form2form($array));       // call this func on the array and fill the passed object
		}
		return $value;
	}
	else  {  // strip the slashes that are put there from post before displaying to form
		return smart2entities(htmlspecialchars(stripslashes($value)));
	}
}

/**			db2text()
 *
 * Used for cleaning text from a the DB that needs to be printed in plain text
 *
 * @author       Greg Allard
 * @version      1.1.1		8/1/6
 * @param        mixed   $value   This can be an array, object, or a string
 * @return       mixed   $value   This will return the same type as passed
 */
function db2text($value)  {
	if (is_array($value))  {  // recursive if it needs to go deeper
		return array_map('db2text', $value);
	}
	else if (is_object($value))  {
		if (strtolower(get_class($value)) != 'db_mysql')  {  // some classes have connected to db, don't want that part
			$array = get_object_vars($value);  // get the vars in an object and make an associative array with it
			$value->fill(db2text($array));       // call this func on the array and fill the passed object
		}
		return $value;
	}
	else  {  // convert html, bbcode, and new lines
		return smart2entities(nl2br(bbcode2html(htmlspecialchars(html2bbcode($value)))));
	}
}

/**			db2form()
 *
 * Used for cleaning text from a the DB that needs to be put in a form to be edited
 *
 * @author       Greg Allard
 * @version      1.1.1		8/1/6
 * @param        mixed   $value   This can be an array, object, or a string
 * @return       mixed   $value   This will return the same type as passed
 */
function db2form($value)  {
	if (is_array($value))  {  // recursive if it needs to go deeper
		return array_map('db2form', $value);
	}
	else if (is_object($value))  {
		if (strtolower(get_class($value)) != 'db_mysql')  {  // some classes have connected to db, don't want that part
			$array = get_object_vars($value);  // get the vars in an object and make an associative array with it
			$value->fill(db2form($array));       // call this func on the array and fill the passed object
		}
		return $value;
	}
	else  {  // convert html, bbcode
		return smart2entities(htmlspecialchars(html2bbcode($value)));
	}
}

/**			db2db()
 *
 * Used for cleaning text from a the DB that needs to be put in a form to be edited
 *
 * @author       Greg Allard
 * @version      1.1.1		8/1/6
 * @param        mixed   $value   This can be an array, object, or a string
 * @return       mixed   $value   This will return the same type as passed
 */
function db2db($value)  {
	if (is_array($value))  {  // recursive if it needs to go deeper
		return array_map('db2db', $value);
	}
	else if (is_object($value))  {
		if (strtolower(get_class($value)) != 'db_mysql')  {  // some classes have connected to db, don't want that part
			$array = get_object_vars($value);  // get the vars in an object and make an associative array with it
			$value->fill(db2db($array));       // call this func on the array and fill the passed object
		}
		return $value;
	}
	else  {  // add slashes since they weren't added from a post
		return addslashes($value);
	}
}

/**			form2text()
 *
 * Used for cleaning text from a form to be diplayed
 *
 * @author       Greg Allard
 * @version      1.1.1		8/1/6
 * @param        mixed   $value   This can be an array, object, or a string
 * @return       mixed   $value   This will return the same type as passed
 */
function form2text($value)  {
	if (is_array($value))  {  // recursive if it needs to go deeper
		return array_map('form2text', $value);
	}
	else if (is_object($value))  {
		if (strtolower(get_class($value)) != 'db_mysql')  {  // some classes have connected to db, don't want that part
			$array = get_object_vars($value);  // get the vars in an object and make an associative array with it
			$value->fill(form2text($array));   // call this func on the array and fill the passed object
		}
		return $value;
	}
	else  {  // strip slashes since they were added from a post
		return smart2entities(stripslashes($value));
	}
}

/**			url2link()
 *
 * php.net comments contained a function to convert urls to links with html
 * with or without http:// and also grabs emails.
 *
 * @author		Sune Rievers, modified by Greg Allard
 * @version		1.0.3		7/30/6
 * @param        mixed   $value   This can be an array, object, or a string
 * @return       mixed   $value   This will return the same type as passed
 */
function url2link($value)  {
	if (is_array($value))  {  // recursive if it needs to go deeper
		return array_map('url2link', $value);
	}
	else if (is_object($value))  {
		if (strtolower(get_class($value)) != 'db_mysql')  {  // some classes have connected to db, don't want that part
			$array = get_object_vars($value);  // get the vars in an object and make an associative array with it
			$value->fill(url2link($array));   // call this func on the array and fill the passed object
		}
		return $value;
	}
	else  {  // the preg_replace part found on php.net
		return  preg_replace(
			array(
				'/(?(?=<a[^>]*>.+<\/a>)
					(?:<a[^>]*>.+<\/a>)
					|
					([^="\']?)((?:https?|ftp|bf2|):\/\/[^<> \n\r]+)
				  )/iex',
				'/<a([^>]*)target="?[^"\']+"?/i',
				'/<a([^>]+)>/i',
				'/(^|\s)(www.[^<> \n\r]+)/iex',
				'/(([_A-Za-z0-9-]+)(\\.[_A-Za-z0-9-]+)*@([A-Za-z0-9-]+)
				(\\.[A-Za-z0-9-]+)*)/iex'
			),
			array(
				"stripslashes((strlen('\\2')>0?'\\1<a href=\"\\2\">\\2</a>\\3':'\\0'))",
				'<a\\1',
				'<a\\1 target="_blank">',
				"stripslashes((strlen('\\2')>0?'\\1<a href=\"http://\\2\">\\2</a>\\3':'\\0'))",
				"stripslashes((strlen('\\2')>0?'<a href=\"mailto:\\0\">\\0</a>':'\\0'))"
			),
			$value
		);
	}
}

/**			bbcode2html()
 *
 * Converts bbcode to html
 *
 * @author       Greg Allard
 * @version      1.0.1		8/2/6
 * @param        mixed   $value   This can be an array, object, or a string
 * @return       mixed   $value   This will return the same type as passed
 */
function bbcode2html($value)  {
	if (is_array($value))  {  // recursive if it needs to go deeper
		return array_map('bbcode2html', $value);
	}
	else if (is_object($value))  {
		if (strtolower(get_class($value)) != 'db_mysql')  {  // some classes have connected to db, don't want that part
			$array = get_object_vars($value);  // get the vars in an object and make an associative array with it
			$value->fill(bbcode2html($array));       // call this func on the array and fill the passed object
		}
		return $value;
	}
	else  {  // convert the bbcode to html
		
		// [b] to <strong> and [/b] to </strong>
		$value = preg_replace('/\[(b)\]/i', '<strong>', $value);
		$value = preg_replace('/\[\/(b)\]/i', '</strong>', $value);
		
		// [em] to <em> and [/em] to </em>
		$value = preg_replace('/\[(em)\]/i', '<em>', $value);
		$value = preg_replace('/\[\/(em)\]/i', '</em>', $value);
		
		// [i] to <input ... />
		$i=0;
		while(substr_count($value, "[i]") > 0)  {  // loop and count to give each a different name
			$start = strpos($value, "[i]");
			$value = substr_replace($value, '<input type="text" name="in'.$i.'" />', $start, 3);
			$i++;
		}
		
		// [t] to <textarea ...></textarea>
		$i=0;
		while(substr_count($value, "[t]") > 0)  {  // loop and count to give each a different name
			$start = strpos($value, "[t]");
			$value = substr_replace($value, '<textarea name="tx'.$i.'" id="tx'.$i.'" class="prefill_textarea" rows="1" cols="60" onkeydown="expandtextarea(this);"></textarea>', $start, 3);
			$i++;
		}
		
		return $value;
	}
}

/**			html2bbcode()
 *
 * Converts allowed html to bbcode tags to avoid htmlspecialchars
 *
 * @author       Greg Allard
 * @version      1.0.1		8/2/6
 * @param        mixed   $value   This can be an array, object, or a string
 * @return       mixed   $value   This will return the same type as passed
 */
function html2bbcode($value)  {
	if (is_array($value))  {  // recursive if it needs to go deeper
		return array_map('html2bbcode', $value);
	}
	else if (is_object($value))  {
		if (strtolower(get_class($value)) != 'db_mysql')  {  // some classes have connected to db, don't want that part
			$array = get_object_vars($value);  // get the vars in an object and make an associative array with it
			$value->fill(html2bbcode($array));       // call this func on the array and fill the passed object
		}
		return $value;
	}
	else  {  // convert the html to bbcode
		
		// <strong> to [b] and </strong> to [/b]
		$value = preg_replace('/\<(strong)\>/i', '[b]', $value);
		$value = preg_replace('/\<\/(strong)\>/i', '[/b]', $value);
		
		// <em> to [em] and </em> to [/em]
		$value = preg_replace('/\<(em)\>/i', '[em]', $value);
		$value = preg_replace('/\<\/(em)\>/i', '[/em]', $value);
		
		// <input .../> to [i]
		$value = preg_replace('/\<(input type=\"text\" name=\"in[0-9]+\" )\/\>/i', '[i]', $value);
		
		// <textarea ...></textarea> to [t]
		$value = preg_replace('/\<(textarea name=\"tx[0-9]+\")\>\<\/textarea\>/i', '[t]', $value);
		
		return $value;
	}
}

/**			smart2entities()
 *
 * Converts smart quotes to the entity equivalent
 *
 * @author       Greg Allard
 * @version      1.0.1		8/9/6
 * @param        mixed   $value   This can be an array, object, or a string
 * @return       mixed   $value   This will return the same type as passed
 */
function smart2entities($value)  {
	if (is_array($value))  {  // recursive if it needs to go deeper
		return array_map('smart2entities', $value);
	}
	else if (is_object($value))  {
		if (strtolower(get_class($value)) != 'db_mysql')  {  // some classes have connected to db, don't want that part
			$array = get_object_vars($value);  // get the vars in an object and make an associative array with it
			$value->fill(smart2entities($array));       // call this func on the array and fill the passed object
		}
		return $value;
	}
	else  {  // convert the smart quotes to entities
		
		$value = str_replace("“", "&ldquo;", $value);  //left smart quote
		$value = str_replace("”", "&rdquo;", $value);  //right smart quote
		$value = str_replace("’", "&rsquo;", $value);  //right single smart (can also use &#146;)
		$value = str_replace("‘", "&lsquo;", $value);  //left single smart (can also use &#145;)
		
		return $value;
	}
}

/**			form2email()
 *
 * Converts posted material for email readiness.
 *
 * @author       Greg Allard
 * @version      1.0.1		8/9/6
 * @param        mixed   $value   This can be an array, object, or a string
 * @return       mixed   $value   This will return the same type as passed
 */
function form2email($value)  {
	if (is_array($value))  {  // recursive if it needs to go deeper
		return array_map('form2email', $value);
	}
	else if (is_object($value))  {
		if (strtolower(get_class($value)) != 'db_mysql')  {  // some classes have connected to db, don't want that part
			$array = get_object_vars($value);  // get the vars in an object and make an associative array with it
			$value->fill(form2email($array));       // call this func on the array and fill the passed object
		}
		return $value;
	}
	else  {  // strip the slashes
		return stripslashes($value);
	}
}

/**			preg_replace_sucks()
 *
 * Replaces all instances of \" with " because preg_replace only seems to escape " for some retarded reason.
 *
 * @author       Thomas Welfley
 * @version      0.0.1		12/14/2006
 * @param        mixed   $value   String
 * @return       mixed   $value   String
 */
function preg_replace_sucks($block)  {
	return str_replace('\\"','"',$block);
}

// -----------------------------------------
/* *****************************************
		-- End Clean Functions --
********************************************/
// -----------------------------------------

/**
 * This will return the last element in $array. This is a php5 bypass since 
 * it doesn't require pass by reference.
 *
 * @author       Greg Allard
 * @version      1.0
 * @param        array   $array
 * @return       mixed   will be of type of last element
 */
function last($array)  {
	$ret_val = end($array);
	return $ret_val;
}


if (!function_exists('array_intersect_key'))  {
   function array_intersect_key()  {
       $arrs   = func_get_args();
       $result = array_shift($arrs);
       foreach ($arrs as $array)  {
           foreach ($result as $key => $v)  {
               if (!array_key_exists($key, $array))  {
                   unset($result[$key]);
               }
           }
       }
       return $result;
   }
}

/**
 * This function will remove a specified key and its associated value from an array, returning the result of the removal.
 *
 * @author      Thomas Welfley
 * @version     0.0.1
 * @param       string	$key	The key to be removed
 * @param		array	$array	The array to remove the key from
 * @param		int		$limit	How many to remove (-1 for all occurrences -- this is the default behavior)
 * @return      array   will be resulting array after $limit removals are made
 */
function array_remove_key($remove_key, $array, $limit=-1) {
	$output = array();
	
	foreach($array as $key=>$value) {
		if($key != $remove_key || $limit == 0)	{		// It is okay to copy.		
			$output[$key] = $value;
		} else {										// Matching element found, not copying it.
			--$limit;
		}
	}
	
	return $output;
}


/**			<random_string>
 *
 *		PHP Versions >= 4.3.0
 *
 *	Creates a random string of numbers, letters, or both of specified length based on input
 *
 *	@author		Greg Allard
 *	@version	1.0			6/27/7
 *	@param		boolean		with_numbers
 *	@param		boolean		with_letters
 *	@param		int			length
**/
function random_string($with_numbers = TRUE, $with_letters = TRUE, $length = 10)  {
	$content = '';
	if ($with_numbers)  {
		$content .= '0123456789';
	}
	if ($with_letters)  {
		$content .= 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	}
	return substr(str_shuffle($content), 0, $length);
}


/**			<get_class_vars_noparent>
 *
 * Gets a class's vars but omits the parent class's vars. Used for extending data classes'
 * database fields.
 *
 * @author		Chris Havreberg, Greg Allard
 * @version		1.0.1		4/17/7
 * @param		string		$class_name		the class name to get the vars from
 * @return		array		$class_vars		an array of all of the vars of the class
 */
function get_class_vars_noparent($class_name)  {
	// get two copies of the vars from the class (one for loop, one for modifying)
	$class_vars = $copy = get_class_vars($class_name);
	
	// get the vars in the parent class
	$parent_vars = get_class_vars(get_parent_class($class_name));
	
	// loop through the vars in the class
	foreach ($copy as $varname => $varval)  {
		// if the var is in the parent
		if (array_key_exists($varname, $parent_vars))  {
			// remove it
			unset($class_vars[$varname]);
		}
	}
	return $class_vars;
}


/**			<get_object_vars_noparent>
 *
 * Gets an object's vars but omits the parent class's vars
 *
 * @author		Greg Allard
 * @version		1.0		6/21/7
 * @param		object		$object			the object
 * @return		array		$object_vars	an array of all of the vars of the object
 */
function get_object_vars_noparent($object)  {
	// get two copies of the vars from the object (one for loop, one for modifying)
	$object_vars = $copy = get_object_vars($object);
	
	// get the vars in the parent object
	$parent_vars = get_class_vars(get_parent_class($object));
	
	// loop through the vars in the object
	foreach ($copy as $varname => $varval)  {
		// if the var is in the parent
		if (array_key_exists($varname, $parent_vars))  {
			// remove it
			unset($object_vars[$varname]);
		}
	}
	return $object_vars;
}
?>