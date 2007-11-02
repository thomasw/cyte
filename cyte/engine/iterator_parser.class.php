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
*	iterator_parser
*   This object is used parse iterator templates passed to iterator keys
*
*   Author: Thomas Welfley
*   Modified: Jared Lang
*   Date: 9 / 8 / 2007
*   Version: 0.1.0
*/

class iterator_parser {
	var $template;
	var $segmented_template;
	var $var_map;
	var $errors;
	var $filters;
	
	function __construct($template="") {
		global $errors;
		$this->errors		        =& $errors;
		$this->template				= $template;
		$this->segmented_template 	= array();
		$this->var_map 				= array();
		$this->filters				= new filters();
				
		if($template != "") {
			# Segment the template and generate the variable map
			$this->segment($template);
		
			# Verify that all filter calls are only calls to valid filters
			$this->validate_filters();
		}
	}
	
	public function apply($object=NULL, $count) {
		if(count($this->var_map) <= 0) {
			return implode('', $this->segmented_template);
		}
		
		foreach($this->var_map as $mapping) {
			global $lang;
			$data = "";
			
			# Reset previous insert.
			$mapping['reference'] = "";
			
			# Get var name
			$var = $mapping['var'];
			
			# Pull the data from the object.
			if($var == "this") {											// User wants to print the 'object' meaning that $object probably isn't an object at all.
				$data = $object;
			} else if($var == "this.count"){
				$data = $count;
			} else if(property_exists($object, $var)) {						// User wants a property of the object - let's check to make sure that the property actually exists.
				$data = $object->$var;
			} else {														// Attempting to acces a non-existant property - throwing an error.
				if(get_class($object)) {
					$this->errors[] = $lang['key_err_011'].get_class($object).'->'.$var;
				} else {
					$this->errors[] = $lang['key_err_011'].$var;
				}
				$data = "";
			}
			
			# Insert the data into the segmented template, and if appropriate, apply a filter.
			switch(count($mapping)) {
				case 5: 													// Mapping has a filter and attributes
					$mapping['reference'] = $this->filters->$mapping['filter']($data, $mapping['filter_parameters']);
				break;
				case 4:														// Mapping has just a filter
					$mapping['reference'] = $this->filters->$mapping['filter']($data);
				break;
				default:													// Mapping has just a var.
					$mapping['reference'] = $data;
				break;
			}
			
		}
		
		return implode('',$this->segmented_template);
	}
	
	# Private functions
	
	private function segment($template) {
		# Valid PHP variable names (excluding the $)
		$var = "[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*"; // Courtesy of http://devzone.zend.com/manual/language.variables.html

		# Valid template patterns
		$match_pattern = "#({ *($var|this.count)( *\| *($var) *(\(([^\)}]*)\))?)? *})#";
		
		# Split document by valid patterns
		$segmented = preg_split($match_pattern, $template, -1, PREG_SPLIT_DELIM_CAPTURE);
				
		# Form mappings
		$segmented_count = count($segmented);
		for($i=0; $i<$segmented_count; $i++) {
			if($i == 0 || $i==($segmented_count-1)) {						// The first and last elements are always template syntax free.
				$this->segmented_template[] = $segmented[$i];
			} else if($this->full_filter($segmented[$i])) {					// Next 5 array elements belong to this filter
				# Get data from next $segmented elemetns
				$instance			= $segmented[$i];
				$var_name 			= $segmented[$i+1];
				$filter_name 		= $segmented[$i+3];
				$filter_parameters	= $segmented[$i+5];
				$next_string		= $segmented[$i+6];
				$jump				= 6;
				
				# Build new var mapping and extend the segmented_template
				$this->segmented_template[] = "";							// New var slot
				$this->var_map[] = array(									// New var mapping
										'instance' 			=> $instance,
										'var' 				=> $var_name,
										'filter' 			=> $filter_name,
										'filter_parameters'	=> $filter_parameters
										);				
				$this->var_map[count($this->var_map)-1]['reference'] =& $this->segmented_template[count($this->segmented_template)-1];				
				$this->segmented_template[] = $next_string;
				
				# Move counter forward
				$i = $i + $jump;
			} else if($this->no_params_filter($segmented[$i])) {			// Next 4 array elements belong to this filter
				# Get data from next $segmented elemetns
				$instance			= $segmented[$i];
				$var_name 			= $segmented[$i+1];
				$filter_name 		= $segmented[$i+3];
				$next_string		= $segmented[$i+4];
				$jump				= 4;
				
				# Build new var mapping and extend the segmented_template
				$this->segmented_template[] = "";							// New var slot
				$this->var_map[] = array(									// New var mapping
										'instance' 			=> $instance,
										'var' 				=> $var_name,
										'filter' 			=> $filter_name
										);				
				$this->var_map[count($this->var_map)-1]['reference'] =& $this->segmented_template[count($this->segmented_template)-1];				
				$this->segmented_template[] = $next_string;
				
				# Move counter forward
				$i = $i + $jump;
			} else if($this->small_filter($segmented[$i])) {				// Next 2 array elements belong to this filter
				# Get data from next $segmented elemetns
				$instance			= $segmented[$i];
				$var_name 			= $segmented[$i+1];
				$next_string		= $segmented[$i+2];
				$jump				= 2;
				
				# Build new var mapping and extend the segmented_template
				$this->segmented_template[] = "";							// New var slot
				$this->var_map[] = array(									// New var mapping
										'instance' 			=> $instance,
										'var' 				=> $var_name
										);				
				$this->var_map[count($this->var_map)-1]['reference'] =& $this->segmented_template[count($this->segmented_template)-1];				
				$this->segmented_template[] = $next_string;
				
				# Move counter forward
				$i = $i + $jump;
			} else {														// Plain text? - Something is wrong if you ever get here.
				
			}
		}	
		
		#echo "Segmented Template:<br />\n";
		#printer($this->segmented_template);
		#echo "Var map<br />\n";
		#printer($this->var_map);
	}
	
	private function validate_filters() {
		global $lang;														// Global lang data
		
		foreach($this->var_map as &$mapping) {
			# Check if there is a filter set
			if(isset($mapping['filter'])) {
				# Remove the filter call and push an error message if the filter doesn't exist.
				if(!method_exists($this->filters, $mapping['filter']) || !is_callable(array($this->filters,$mapping['filter']))){
					$this->errors[]= $lang['key_err_012']." ".$mapping['filter'];
					unset($mapping['filter']);
					unset($mapping['filter_parameters']);
				}
			}
		}
		
	}
	
	# Filter type test functions - used in $this->segment()
	
	// {var | filter(params)}
	private function full_filter($filter) {
			return strpos($filter,'(') !== FALSE;
	}
	
	//{var}
	private function small_filter($filter) {
			return strpos($filter,'(') === FALSE && strpos($filter,'|')===FALSE;
	}
	
	// {var | filter}
	private function no_params_filter($filter) {
			return strpos($filter,'|') !== FALSE && strstr($filter,'(') === FALSE;
	}
}
?>