<?php
/**
 * @author:		Greg Allard
 * @version:	1.0.1		7/16/7
 *
 *	displays pagination links
 */

class pagination extends key  {
	public $type				=	'count';					// count, alpha, time, group
	
	public $class_prefix;
	public $get_prefix;
	public $start_value;
	public $limit_value;
	public $total_avail;
	
	public $group_col			=	'';
	public $data_class			=	'';
	public $requirements		=	'';							// would be for group count. # not displayed atm
	public $heading				=	'';
	
	public $field_alpha			=	'';
	
	function check_attributes()  {
	}
	
	function gen_link($for)  {
		$parameters = array();
		
		// if we want prev link and there is a prev page
		if ($for == 'Previous' && $this->start_value > 0)  {
			$start = $this->start_value - $this->limit_value;
			if ($start < 0)  {
				$start = 0;
			}
			$parameters[$this->get_prefix.'start'] = $start;
			
			$url = htmlentities(get_url($parameters));
			return '<p class="'.$this->class_prefix.$for.'"><a href="'.$url.'">'.$for.'</a></p>';
		}
		// or we want next page and there is more to show
		else if ($for == 'Next' && $this->total_avail > $this->start_value + $this->limit_value)  {
			$start = $this->start_value + $this->limit_value;
			if ($start < 0)  {
				$start = 0;
			}
			$parameters[$this->get_prefix.'start'] = $start;
			
			$url = htmlentities(get_url($parameters));
			return '<p class="'.$this->class_prefix.$for.'"><a href="'.$url.'">'.$for.'</a></p>';
		}
		
		// no page to link to
		return '';
	}
	
	function get_end()  {
		if ($this->total_avail < $this->start_value + $this->limit_value)  {
			return $this->total_avail;
		}
		else  {
			return $this->start_value + $this->limit_value;
		}
	}
	
	function display()  {
		$output = '';
		
		switch ($this->type)  {
			case 'count':
				if ($this->limit_value > 0)  {
					$output .= '
						<div class="pagination">
							'.$this->gen_link('Previous').$this->gen_link('Next').'
							<p class="'.$this->class_prefix.'disp">Displaying '.($this->start_value + 1).' - '.$this->get_end().' of '.$this->total_avail.'</p>
						</div>
						';
				}
				break;
			case 'group':
				if ($this->group_col != '' && $this->data_class != '')  {
					$data = new $this->data_class;
					$data->group_count(array('group_col' => $this->group_col));
					if (is_array($data->group_counts) && count($data->group_counts) > 0)  {
						$selected = false;
						$output .= '<div class="group_pagination"><p>'.$this->heading.'</p><ul>';
						foreach($data->group_counts as $group => $count)  {
							// check if it is selected
							if (isset($_GET[$this->get_prefix.'group_col']) && isset($_GET[$this->get_prefix.'group_val']) && 
									$_GET[$this->get_prefix.'group_col'] == $this->group_col && $_GET[$this->get_prefix.'group_val'] == $group
							)  {
								$output .= '<li><strong>'.$group.'</strong></li>'; // ('.$count.')
								$selected = true;
							}
							else  {
								$parameters[$this->get_prefix.'group_col'] = $this->group_col;
								$parameters[$this->get_prefix.'group_val'] = $group;
								$parameters[$this->get_prefix.'start'] = 0;  // reset incase there wouldn't be any after this offset
								
								$url = htmlentities(get_url($parameters));
								$output .= '<li><a href="'.$url.'">'.$group.'</a></li>'; // ('.$count.')
							}
						}
						// if one was selected
						if ($selected)  {
							// display ALL as a link
							$parameters[$this->get_prefix.'group_col'] = '';
							$parameters[$this->get_prefix.'group_val'] = '';
							$parameters[$this->get_prefix.'start'] = 0;  // reset incase there wouldn't be any after this offset
							
							$url = htmlentities(get_url($parameters));
							$output .= '<li><a href="'.$url.'">All</a></li>';
						}
						else  {
							// all is selected
							$output .= '<li><strong>All</strong></li>';
						}
						$output .= '</ul></div>';
					}
				}
				break;
			case 'alpha':
				if ($this->field_alpha != '')  {
					$letters = array('A','B','C','D','E','F','G','H','I','J','K','L','M',
									 'N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
					$selected = false;
					$output .= '<div class="alpha_pagination"><p>'.$this->heading.'</p><ul>';
					foreach($letters as $letter)  {
						// check if it is selected
						if (isset($_GET[$this->get_prefix.'field_alpha']) && isset($_GET[$this->get_prefix.'letter']) && 
								$_GET[$this->get_prefix.'field_alpha'] == $this->field_alpha && $_GET[$this->get_prefix.'letter'] == $letter
						)  {
							$output .= '<li><strong>'.$letter.'</strong></li>'; // ('.$count.')
							$selected = true;
						}
						else  {
							$parameters[$this->get_prefix.'field_alpha'] = $this->field_alpha;
							$parameters[$this->get_prefix.'letter'] = $letter;
							$parameters[$this->get_prefix.'start'] = 0;  // reset incase there wouldn't be any after this offset
							
							$url = htmlentities(get_url($parameters));
							$output .= '<li><a href="'.$url.'">'.$letter.'</a></li>'; // ('.$count.')
						}
					}
					// if one was selected
					if ($selected)  {
						// display ALL as a link
						$parameters[$this->get_prefix.'field_alpha'] = '';
						$parameters[$this->get_prefix.'letter'] = '';
						$parameters[$this->get_prefix.'start'] = 0;  // reset incase there wouldn't be any after this offset
						
						$url = htmlentities(get_url($parameters));
						$output .= '<li><a href="'.$url.'">All</a></li>';
					}
					else  {
						// all is selected
						$output .= '<li><strong>All</strong></li>';
					}
					$output .= '</ul></div>';
				}
				break;
		}
		
		
		
		return $output;
	}
}
?>
