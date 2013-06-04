<?php
/* layout.class.php
 *
 * the purpose of this class is to standardize use configurable input, and process it for display
 */
 
$request = basename($_SERVER['REQUEST_URI']);
if ($request == 'layout.class.php') { echo 'You cannot access this file directly'; exit(); }
 
class Layout
{
	var $layout;
	var $parseVars = array();

	function Layout($layout)
	{
		$this->layout = $layout;
	}
	
	function AddVar($varName, $varType)
	{
		$this->parseVars[strtoupper($varName)] = $varType;
	}
	
	function PrepOutput($layoutData, $layout, $parseVars)
	{
		$output = '';
		
		$layers = array();
		$level  = 0;
		
		//$layout = $this->layout;
		
		for ($i = 0; $i < strlen($layout); $i++)
		{
			$char = substr($layout, $i, 1);
			if ($char == '{')
			{
				if (substr($layout, $i, 3) == '{if')
				{
					$level++;
				}
			}
			
			$layers[$level] .= $char;
			
			if ($char == '}')
			{
				if (substr($layout, ($i-4), 5) == '{/if}')
				{
					$level--;
				}
			}
		}

		if ($level != 0) {
			return 'Error processing layout: Unterminated if condition';
		}
		
		if (sizeof($layers) > 0)
		{
			$layer = array_pop($layers);
			$num_matches = preg_match_all('/\{if:(.*?)\}(.*?)\{\/if\}/s', $layer, $matches);
			
			for ($y = 0; $y < $num_matches; $y++)
			{
				$matched_text = $matches[0][$y];
				$flag         = $matches[1][$y];
				$op           = $matches[2][$y];
				
				// see if we have an operand
				$operands = array(
					' == ' => 'equal', 
					' != ' => 'notEqual', 
					' <= ' => 'lessThanEqual', 
					' >= ' => 'greaterThanEqual', 
					' < ' => 'lessThan', 
					' > ' => 'greaterThan'
				);
				
				$found_operand = false;
				
				foreach ($operands as $operand => $callback)
				{
					// flag = {if:(XXXXXXXXXX==BLAH)}
					if (strstr($flag, $operand))
					{
						$found_operand = true;
						list($flag, $compare) = explode($operand, $flag);
						
						$val = $layoutData[$flag];
						
						// special keywords, TODAY represents a timestamp dated today, for comparisons
						if ($compare == 'TODAY') { $compare = mktime(0,0,0, date('m'), date('d'), date('y')); }
						
						if (isset($parseVars[$flag]))
						{
							$type = $parseVars[$flag];
							if ($type == 'date') { $type = 'num'; }
							$show = call_user_func(array($this, $callback), $type, $val, $compare);
						}
						else
						{
							$show = false;
						}
					}
				}
				
				if (!$found_operand)
				{
					if (isset($parseVars[$flag]))
					{
						$val = $layoutData[$flag];
						$type = $parseVars[$flag];
						if ($type == 'date') { $type = 'num'; }
						$show = $this->noOperand($type, $val);
					}
					else
					{
						$show = false;
					}
				}
				
				if (isset($layoutData[$flag]))
				{
					if ($show)
					{
						$replace = $op;
					}
					else
					{
						$replace = '';
					}
				}
				else
				{
					$replace = '';
				}
				
				$layout = str_replace($matched_text, $replace, $layout);
			}
			
			if (sizeof($layers) > 0)
			{
				$layout = $this->PrepOutput($layoutData, $layout, $parseVars);
			}
		}
		
		return $layout;
	}
	
	function Output($layoutData = array(), $layout = null, $parseVars = null)
	{
		if ($parseVars == null)
		{
			$parseVars = $this->parseVars;
		}
		
		if ($layout == null)
		{
			$layout = $this->layout;
		}
		
		foreach ($layoutData as $key=>$val)
		{
			// make all parseVars uppercase please
			$layoutData[strtoupper($key)] = $val;
		}
		
		//echo '<pre><xmp>'.print_r($parseVars, true).'</xmp></pre>';
		
		$layout = $this->PrepOutput($layoutData, $layout, $parseVars);
		
		// now we need to output some data
		//$layout = $this->layout;
		
		foreach ($parseVars as $varName => $varType)
		{
			// find the var
			
			$num_matches = preg_match_all('/\{'.$varName.'(.*?)\}/s', $layout, $matches);
			
			for ($x = 0; $x < $num_matches; $x++)
			{
				$full_text    = $matches[0][$x];
				$matched_text = $matches[1][$x];
				
				if (strlen($matched_text) > 0)
				{
					$parameters = explode(',', $matched_text);
					array_shift($parameters); // gets rid of empty parameter
				}
				else
				{
					$parameters = array();
				}
				
				$val = $layoutData[$varName];
				
				if ($varType == 'layout')
				{
					$replace = $this->Output($val['data'], $val['layout'], $val['vars']);
				}
				elseif ($varType == 'text')
				{
					if (sizeof($parameters) > 0)
					{
						$num_words = $parameters[0];
						$words = explode(' ', trim(strip_tags($val)));
						$keep = array_slice($words, 0, $num_words);
						$replace = implode(' ', $keep);
					}
					else
					{
						$replace = $val;
					}
				}
				elseif ($varType == 'date')
				{
					$format  = (sizeof($parameters) > 0) ? implode(',', $parameters) : 'h:ia F j, Y';
					$replace = date($format, $val);
				}
				elseif ($varType == 'link')
				{
					$link_text = (sizeof($parameters) > 0) ? implode(',', $parameters) : $val;
					$line = '<a href="'.$val.'">'.$link_text.'</a>';
					//$line = $this->Output($layoutData, $line, $parseVars);
					$replace = $line;
				}
				elseif ($varType == 'num')
				{
					$replace = $val;
				}
				elseif (($varType == 'image') or ($varType == 'image_list'))
				{
					if ($varType == 'image')
					{
						$sources = array($val);
						$before = '';
						$after  = '';
					}
					else
					{
						$sources = $val['images'];
						$before  = $val['before'];
						$after   = $val['after'];
					}
					
					$replace = '';
					$counter = 0;
					foreach ($sources as $source)
					{
						if (is_file($source))
						{
							if (sizeof($parameters) >= 2)
							{
								$width    = $parameters[0];
								$height   = $parameters[1];
								$pad_mode = (isset($parameters[2])) ? $parameters[2] : 'crop';
							}
							else
							{
								list($width, $height) = getimagesize($source); // keep it original
								$pad_mode = 'crop';
							}
							
							if ( ($width == 0) and ($height == 0) )
							{
								list($width, $height) = getimagesize($source); // keep it original
								$pad_mode = 'crop';
							}
						
							$extension = ($pad_mode == 'crop') ? 'jpg' : 'png';
							$filename  = md5(basename($source) . '_' . $width . '_' . $height . '_' . $pad_mode) . '.' . $extension;
							$disp      = 'includes/tmp/' . $filename;
							
							if (is_file($disp))
							{
								if (filemtime($source) > filemtime($disp))
								{
									@Pico_IsWritable($disp);
									@unlink($disp);
								}
							}
							
							if (!is_file($disp))
							{
								if (($width == 0) or ($height == 0))
								{
									$this->make_resized_image($source, $disp, $width, $height);
								}
								elseif ($pad_mode == 'crop')
								{
									$this->make_new_image($source, $disp, $width, $height);
								}
								else
								{
									$this->make_new_image_ws($source, $disp, $width, $height);
								}
							}
							
							if (is_file($disp))
							{
								$line = $before . '<img src="'.PICO_BASEPATH . $disp.'" />' . $after;
								$line = $this->Output(array('NUM'=>$counter), $line, array('NUM'=>'num'));
								
								$replace .= $line;
								$counter++;
							}
						}
					}
				}
				
				$layout = str_replace($full_text, $replace, $layout);
			}
		}
		
		return $layout;
	}
	
	function greaterThan($type, $val1, $val2)
	{
		if ($type == 'bool')
		{
			$return = FALSE;
		}
		elseif ($type == 'num')
		{
			$return = ($val1 > $val2) ? TRUE : FALSE;
		}
		else
		{
			$return = FALSE;
		}
		
		return $return;
	}
	
	function lessThan($type, $val1, $val2)
	{
		if ($type == 'bool')
		{
			$return = FALSE;
		}
		elseif ($type == 'num')
		{
			$return = ($val1 < $val2) ? TRUE : FALSE;
		}
		else
		{
			$return = FALSE;
		}
		
		return $return;
	}
	
	function greaterThanEqual($type, $val1, $val2)
	{
		if ($type == 'bool')
		{
			$return = FALSE;
		}
		elseif ($type == 'num')
		{
			$return = ($val1 >= $val2) ? TRUE : FALSE;
		}
		else
		{
			$return = FALSE;
		}
		
		return $return;
	}
	
	function lessThanEqual($type, $val1, $val2)
	{
		if ($type == 'bool')
		{
			$return = FALSE;
		}
		elseif ($type == 'num')
		{
			$return = ($val1 <= $val2) ? TRUE : FALSE;
		}
		else
		{
			$return = FALSE;
		}
		
		return $return;
	}
	
	function notEqual($type, $val1, $val2)
	{
		if ($type == 'bool')
		{
			$return = ($val1 != $val2) ? TRUE : FALSE;
		}
		elseif ($type == 'num')
		{
			$return = ($val1 != $val2) ? TRUE : FALSE;
		}
		else
		{
			// string
			$val1 = strtolower($val1);
			$val2 = strtolower($val2);
			$return = ($val1 != $val2) ? TRUE : FALSE;
		}
		
		return $return;
	}
	
	function equal($type, $val1, $val2)
	{
		if ($type == 'bool')
		{
			$return = ($val1 == $val2) ? TRUE : FALSE;
		}
		elseif ($type == 'num')
		{
			$return = ($val1 == $val2) ? TRUE : FALSE;
		}
		else
		{
			// string
			$val1 = strtolower($val1);
			$val2 = strtolower($val2);
			$return = ($val1 == $val2) ? TRUE : FALSE;
		}
		
		return $return;
	}
	
	function noOperand($type, $val)
	{
		if ($type == 'bool')
		{
			$return = ($val == TRUE) ? TRUE : FALSE;
		}
		elseif ($type == 'num')
		{
			$return = ($val != 0) ? TRUE : FALSE;
		}
		elseif ($type == 'image')
		{
			$return = (is_file($val)) ? TRUE : FALSE;
		}
		else
		{
			$return = (strlen($val) > 0) ? TRUE : FALSE;
		}
		
		return $return;
	}
	
	// image creation functions (taken from media functions)
	
	function make_new_image($full_path, $dest, $final_width = 320, $final_height = 140)
	{
		$i = imagecreatefromstring(file_get_contents($full_path));
		
		$original_width  = imagesx($i);
		$original_height = imagesy($i);
		
		$x_mod = $final_width / $original_width;
		$new_width  = $final_width;
		$new_height = $original_height * $x_mod;
		
		if ($new_height > $final_height)
		{
			$y_trim = ($new_height - $final_height) / 2;
			$x_trim = 0;
		}
		else
		{
			$y_trim = 0;
			if ($new_height < $final_height)
			{
				$y_mod = $final_height / $new_height;
				$new_width = $y_mod * $new_width;
				$x_trim = ($new_width - $final_width) / 2;
				$new_height = $final_height;
			}
		}
		
		$new_width = round($new_width);
		$new_height = round($new_height);
		
		$new_im = imagecreatetruecolor($new_width, $new_height);
		imagecopyresampled($new_im, $i, 0, 0, 0, 0, $new_width, $new_height, $original_width, $original_height);
		
		$final_im = imagecreatetruecolor($final_width, $final_height);
		imagecopyresampled($final_im, $new_im, 0, 0, $x_trim, $y_trim, $final_width, $final_height, $final_width, $final_height);
		
		//imagejpeg($final_im, $dest, 100);
		
		$this->gallery_output_image($final_im, $dest, 'jpg');
	}

	// creates an image with a finite size, sections out what it can to produce the most logical image for given size, adds transparency where needed
	function make_new_image_ws($full_path, $dest, $desired_width = 320, $desired_height = 140)
	{
		//error_reporting(E_ALL);
		$i = imagecreatefromstring(file_get_contents($full_path));
		
		$width  = imagesx($i);
		$height = imagesy($i);
		
		$original_width  = $width;
		$original_height = $height;
		
		if ($width > $desired_width)
		{
			$mod    = $desired_width / $width;
			$width  = $desired_width;
			$height = $height * $mod;
		}
		
		if ($height > $desired_height)
		{
			$mod    = $desired_height / $height;
			$height = $desired_height;
			$width  = $width * $mod;
		}
		
		$final_width  = round($width);
		$final_height = round($height);
		
		// get the trim
		$x_trim = ($desired_width - $final_width) / 2;
		$y_trim = ($desired_height - $final_height) / 2;
		
		$new_im = imagecreatetruecolor($final_width, $final_height);
		$trans_colour = imagecolorallocatealpha($new_im, 255, 255, 255, 127);
		imagefill($new_im, 0, 0, $trans_colour);
		
		imagecopyresampled($new_im, $i, 0, 0, 0, 0, $final_width, $final_height, $original_width, $original_height);
		//imagepng($new_im, $dest, 0, PNG_NO_FILTER); return;
		
		
		$final_im = imagecreatetruecolor($desired_width, $desired_height);
		// make it transparent bg
		$trans_colour = imagecolorallocatealpha($final_im, 255, 255, 255, 127);
		imagesavealpha($final_im, true);
		imagefill($final_im, 0, 0, $trans_colour);
		
		imagecopyresampled($final_im, $new_im, $x_trim, $y_trim, 0, 0, $final_width, $final_height, $final_width, $final_height);
		
		$this->gallery_output_image($final_im, $dest, 'png');
	}
	
	function make_resized_image($source, $dest, $desired_width = 0, $desired_height = 0)
	{
		$i = imagecreatefromstring(file_get_contents($source));
		
		$original_width  = imagesx($i);
		$original_height = imagesy($i);
		
		if ($desired_width != 0)
		{
			// scale by width
			$mod = $desired_width / $original_width;
			$final_width = $desired_width;
			$final_height = round($original_height * $mod);
		}
		else
		{
			// scale by height
			$mod = $desired_height / $original_height;
			$final_height = $desired_height;
			$final_width = round($original_width * $mod);
		}
		
		$new_im = imagecreatetruecolor($final_width, $final_height);
		imagecopyresampled($new_im, $i, 0, 0, 0, 0, $final_width, $final_height, $original_width, $original_height);
		//imagejpeg($new_im, $dest, 100);
		
		$this->gallery_output_image($new_im, $dest, 'jpg');
	}
	
	function gallery_output_image($im, $dest, $mode)
	{
		if ($dest != null)
		{
			$writable = Pico_IsWritable($dest, true);
			if ($writable)
			{
				if ($mode == 'png')
				{
					imagepng($im, $dest, 5, PNG_NO_FILTER);
				}
				else
				{
					imagejpeg($im, $dest, 100);
				}
				
				return TRUE;
			}
			else
			{
				return FALSE;
			}
		}
		else
		{
			if ($mode == 'png')
			{
				imagepng($im, $dest, 5, PNG_NO_FILTER);
			}
			else
			{
				imagejpeg($im, $dest, 100);
			}
		}
	}
}


?>