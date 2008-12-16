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

class parser {
	
	private $block;
	private $split_block;
	
	private $errors;
	
	/** A note about the two following variables:
	 * The following variables are used to form the beggining of keys, ie:
	 * <[delimeter][delimeter_separater][keylist][attribs] />
	 * <cyte:username||"Guest" type="23" />
	 * You can change these to suit your needs, but beware...
	 * This will make your installation of cyte incompatible with any
	 * modules that do not use your key scheme.
	 * 
	 * Also, note that setting these to empty strings will cause havoc. 
	 * I've never tried it myself because I'm not brave enough.
	 * Good luck.
	 *
	 * These variables were specifically omitted from the configuration
	 * to discourage people to changing them: Compatibility is a good thing.
	 */

	# Delimeters	
	private $delimeter = "cyte";													// Key delimeter. <cyte:key>Yay!</cyte>
	private $delimeter_separator = ":";												// Inserted in between delimeter and keys for opening of containers and shorthand open/cclose tags
	
	
	function __construct($block) {
		global $errors;
		
		$this->block = $block;
		$this->split_block = array();
		$this->errors =& $errors;
		
		$this->split_block = $this->parse($this->block);
	}
	
	public function parse($block, $safe_keys = NULL) {
		
		# Regexpressions for matching and replacing keys	
		$open_pattern	= '<'.$this->delimeter.$this->delimeter_separator.'\s*([^=>\s/]+)\s*([^>]*)\s*(/)?>';			// Regular expression for matching keys of the form: <cyte:test att="tears" />
		$close_pattern = '< */ *'.$this->delimeter.' *>';
		
		# Split the document along key positionis and get key data.
		$content = preg_split('#('.$open_pattern.')|('.$close_pattern.')#', $block);
		$key_count = preg_match_all('#('.$open_pattern.')|('.$close_pattern.')#', $block, $keys);
		
		# Form key data into a coherent structure.
		$keys = $this->gen_key_list($keys, $safe_keys);
		
		# Verif all open tags have close tags - if no close tag is detected, modify the invalid tag so that it executes as a closed tag.
		$keys = $this->validate_keys($keys);
		
		# Merge content array and key arrays into split_block
		return $this->form_split_block($content, $keys);
	}
	
	private function gen_key_list($keys, $safe_keys=NULL) {
		global $lang;
		$output = array();
		
		// Loop thorugh all of the keys and build their array of data
		// If it is a close tag, simply push NULL.
		for($i = 0; $i<count($keys[0]); ++$i) {
			if(str_replace(' ', '', $keys[0][$i]) == '</'.$this->delimeter.'>') {
				$output[] = FALSE;
			} else {
				
				// Add some wonderful whitespace so that strstr doesn't die if this was empty (dirty hack, sorry...)
				if($keys[3][$i] == '') {
					$keys[3][$i] = ' ';
				}
				
				// Parse the parameters string - If parse_parameters returns false, the parameter string is malformed.
				if(($parameters = $this->parse_parameters($keys[3][$i])) === FALSE) {
					$this->errors[] = $lang['err_013'].$keys[1][$i];
					$parameters = array();
				}
				
				# Generate the array representation of the key
				$output[] = array(
									'keys' => $this->parse_keys($keys[2][$i]),
									'parameters'=>$parameters,
									'instance' => $keys[1][$i],
									'closed' => (substr_compare($keys[1][$i], '/', strlen($keys[1][$i])-2,1)===0),
									'safe_keys' => $safe_keys,
									'content' => NULL,
									'uid'	=> time()+$i
								 );
			}
		}
		
		return $output;
	}
	
	private function form_split_block($content, $keys) {
		global $lang;		
		$output = array();
		$key_count = count($keys);
		
		foreach($content as $i=>$value) {
			if($i > 0 && (($i-1) < $key_count)) {
				$output[]= $keys[$i-1];
			}
			$output[] = $content[$i];
		}
		
		return $output;
		
	}
	
	private function validate_keys($keys) {
		return $keys;
	}
	
	public function getNext() {		
		$add_content = FALSE;				# Flag to determine whether or not to accumulate content for use in a container tag
		$content = '';						# Variable for storing accumulated content
		$span = 0;							# Number of split_block elements returned content will replace
		$key = null;						# Holder for start of a container key.
		global $lang;						# Global language data.
		
		# Find the next shortkey and return it
		foreach($this->split_block as $possible_key) {
			if(is_array($possible_key) && $possible_key['closed'] == TRUE) {
				return $possible_key;
			}
		}
		
		# If there are no short keys, find the next container tag and return it
		foreach($this->split_block as $i=>$possible_key) {
			if($possible_key === FALSE ) {
				if($key === NULL) {		// If $key is null, then we found a close tag that doesn't have an open tag. Push an error, remove the tag, but continue processing.
					$this->replace(NULL,' ', NULL, $i);
					$this->errors[] = $lang['err_010'];
				} else {
					// Push an error to errors and clear out that tag so that it doesn't cause trouble later.
					$key['content'] = $content;
					$key['span'] = $span+1;
					return $key;
				}
			}
			
			if(is_array($possible_key)) {
				$add_content = true;
				$key = $possible_key;
				$key['original'] = $possible_key;
				$content = "";
				$span = 0;
			} else if($add_content) {
				$content .= $possible_key;
				$span++;
			}
		}
		
		# If $key is set at this point, we found an open tag with no close tag. Push an error and try to execute it as a closed tag.
		if($key !== NULL) {
			$this->errors[] = $lang['err_009'].$key['instance'];
			$key['closed'] = TRUE;
			$key['span'] = 0;
			return $key;
		}
		
		return FALSE;
	}
	
	public function replace($key, $content, $safe_keys = NULL, $spec_dex=NULL) {
		global $lang;
		$index = array_search($this->strip_container_atts($key), $this->split_block);
		
		# Check to see if an index was passed instead of a key
		if($key === NULL and $spec_dex !== NULL) {
			$index = $spec_dex;
		}
		
		# Key / Content mismatch. Push error.
		if($index === FALSE) {	
			$this->errors[] = $lang['err_015'].$key['instance'];
			return FALSE;
		}
		
		if($content == '') {			# Remove keys that return no data.
			$this->split_block = $this->insert_block($index, array());
		} else if($key['closed']) {		# If it is a closed, block, we don't need to pass it the span value
			$this->split_block = $this->insert_block($index, $this->parse($content, $safe_keys));
		} else {						# A container tag's contents can span multiple elements of split_block, so we need to pass it how many it spans.
			$this->split_block = $this->insert_block($index, $this->parse($content, $safe_keys), $key['span']);
		}
		
		return TRUE;
	}
	
	public function merge() {
		$merge = "";
				
		// Merge each string in split_block
		foreach($this->split_block as $substr) {
			if(!is_array($substr)) {
				$merge .= $substr;
			}
		}
		
		return $merge;
	}
	
	private function insert_block($index, $content, $span=0) {
		$output = array();
		
		foreach($this->split_block as $i => $value) {
			if($i == $index) {
				foreach($content as $newelement) {
					$output[] = $newelement;
				}
			} else if($i >= $index+1 && $i < $index+$span+1) {
				// Do nothing. Old content is already included in $content because it is in a container tag
			} else {
				$output[] = $value;
			}
		}
		
		return $output;
	}
	
	private function strip_container_atts($key) {
		if(!isset($key['original'])) {		# Don't bother removing the extraneous attributes if it is a closed tag. It shouldn't have any.
			return $key;
		}
		
		return $key['original'];
	}
	
	private function parse_keys($key_string) {
		return explode("||", trim($key_string));
	}
	
	private function parse_parameters($unparsed_param) {
		# Regular expressions
		$match_attribute_names		=	'#[A-z0-9]*(.[A-z0-9]+)?\s*=#';				// Matches attribute names with the = sign included
		//$match_attribute_values	=	'#"(([^"\\\\]*)|([^"]*\\\\?"[^"]*))"#';		// Matches attribue values, including their ""  ([^"]*(\\\\")?)*[^"\\\\]* This allows escaping quotes but doesn't work correctly.
		$match_attribute_values 	= '#"([^"]*)"#'; 								// Dumb attribute matching - This doesn't allow escaping quotes.
		
		# Counters
		$num_values					=	0;											// The number of values found in the unparsed_params string
		$num_attributes				=	0;											// The number of attributes found in the unparsed_params string
		
		# Get the values.
		$num_attributes = preg_match_all($match_attribute_values, $unparsed_param, $values);

		# Remove the values from the string
		foreach($values[0] as $val) {;
			$unparsed_param = str_replace($val, "", $unparsed_param);
		}
		
		# Get the attributes.
		$num_values	= preg_match_all($match_attribute_names, $unparsed_param, $attributes);
		
		# If we don't have the same number of attributes and values, the key is malformed. Return false.
		if($num_values != $num_attributes) {
			return FALSE;
		}
		
		# Test if there are any attributes ore values to deal with, if there are combine them!
		if($num_values == 0 && $num_attributes == 0) {
			return array();
		} else {
			return array_combine($this->clean_attribute_name($attributes[0]), $this->clean_attribute_value($values[0]));			
		}
		
		return false;
	}
	
	private function clean_attribute_value($value) {
		# If passed an array of attribute values, call clean on every element and return.
		if(is_array($value)) {
			foreach($value as &$v) {
				$v = $this->clean_attribute_value($v);
			}
		
			return $value;
		}
		
		# Clean the string
		
		// Strip whitespace
		$value = trim($value);
		
		// Get rid of leading and trailing quotation marks
		$value = substr_replace($value, '', 0, 1);
		$value = substr_replace($value, '', strlen($value)-1, 1);
		
		// Return the result
		return $value;
	}
	
	private function clean_attribute_name($name) {
		# If passed an array of attribute names, call clean on every element and return.
		if(is_array($name)) {
			foreach($name as &$n) {
				$n = $this->clean_attribute_name($n);
			}
		
			return $name;
		}
		
		# Clean the string
		
		// Remove extra whitespace from around an attribute stripped from a key and get rid of the =
		$name = trim(str_replace("=", "", $name));
		
		
		// Return the cleaned string
		return $name;
	}
}



?>