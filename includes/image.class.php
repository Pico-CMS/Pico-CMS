<?php

class Image {
	var $src;
	var $error;
	var $imgWidth;
	var $imgHeight;
	var $bits;
	var $channels;

	// construct, open a file and load its details to the class
	function Image($source) {
		if (!is_file($source)) { 
			$this->error = 'Unable to find source image';
			return false; 
		}
		$this->src = $source;

		$imageInfo = getimagesize($source);

		$width           = $imageInfo[0];
		$height          = $imageInfo[1];
		$this->bits      = $imageInfo['bits'];
		$this->channels  = $imageInfo['channels'];
		$this->imgWidth  = $width;
		$this->imgHeight = $height;
	}

	// renders a resized jpg, cropping out of bounds areas
	function GetJPG($w, $h, $path = null) {
		$source_im = $this->LoadImage();

		if ($source_im == FALSE) {
			$this->error = 'Unable to load source image';
			return FALSE;
		}

		$final_width  = $w;
		$final_height = $h;

		$original_width  = $this->imgWidth;
		$original_height = $this->imgHeight;
		
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

		$new_im = $this->NewImage($new_width, $new_height);
		if ($new_im == FALSE) {
			$this->error = 'Unable to process image';
			return FALSE;
		}

		imagecopyresampled($new_im, $source_im, 0, 0, 0, 0, $new_width, $new_height, $original_width, $original_height);
		imagedestroy($source_im); // free up some memory!

		$final_im = $this->NewImage($final_width, $final_height);
		if ($final_im == FALSE) {
			$this->error = 'Unable to process image';
			return FALSE;
		}

		imagecopyresampled($final_im, $new_im, 0, 0, $x_trim, $y_trim, $final_width, $final_height, $final_width, $final_height);
		imagedestroy($new_im); // free up some memory!

		@imagejpeg($final_im, $path, $quality);
		imagedestroy($final_im);

		if ($path != null) {
			if (!is_file($path)) { 
				$this->error = 'Unable to create image';
				return FALSE;
			}
			else {
				@chmod($path, 0666);
				return TRUE;
			}
		}
	}

	// renders a PNG, padding the image and adding transparency where needed
	function GetPNG($w, $h, $path) {
		$source_im = $this->LoadImage();

		if ($source_im == FALSE) {
			$this->error = 'Unable to load source image';
			return FALSE;
		}

		$desired_width  = $w;
		$desired_height = $h;
	
		$width  = $this->imgWidth;
		$height = $this->imgHeight;
		
		$original_width  = $this->imgWidth;
		$original_height = $this->imgHeight;
		
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

		$new_im = $this->NewImage($final_width, $final_height);

		if ($new_im == FALSE) {
			$this->error = 'Unable to process image';
			return FALSE;
		}

		// make it transparent bg
		$trans_colour = imagecolorallocatealpha($new_im, 255, 255, 255, 127);
		imagefill($new_im, 0, 0, $trans_colour);

		// resize the image down
		imagecopyresampled($new_im, $source_im, 0, 0, 0, 0, $final_width, $final_height, $original_width, $original_height);
		imagedestroy($source_im); // free up some memory!

		$final_im = $this->NewImage($desired_width, $desired_height);
		if ($final_im == FALSE) {
			$this->error = 'Unable to process image';
			return FALSE;
		}

		$trans_colour = imagecolorallocatealpha($final_im, 255, 255, 255, 127);
		imagesavealpha($final_im, true);
		imagefill($final_im, 0, 0, $trans_colour);
		imagecopyresampled($final_im, $new_im, $x_trim, $y_trim, 0, 0, $final_width, $final_height, $final_width, $final_height);
		imagedestroy($new_im); // free up some memory!

		@imagepng($final_im, $path);
		imagedestroy($final_im);

		if ($path != null) {
			if (!is_file($path)) { 
				$this->error = 'Unable to create image';
				return FALSE;
			}
			else {
				@chmod($path, 0666);
				return TRUE;
			}
		}
	}

	// gets whatever error we have encountered, probably due to out of memory
	function GetError() {
		return $this->error;
	}

	// loads $src image, if we have the memory, and returns an image handler
	private function LoadImage() {
		$haveMemory = $this->HaveMemory($this->imgWidth, $this->imgHeight);
		if ($haveMemory) {
			$i = imagecreatefromstring(file_get_contents($this->src));
			return $i;
		}
		else {
			return FALSE;
		}
	}

	// makes a new $image, if we have the memory, and returns the image handler
	private function NewImage($width, $height) {
		$haveMemory = $this->HaveMemory($width, $height);
		if ($haveMemory) {
			$i = imagecreatetruecolor($width, $height);
			return $i;
		}
		else {
			return FALSE;
		}
	}

	// checks to see if we have enough memory to process an image or allocate to a new image
	private function HaveMemory($width, $height) {
		$memoryAllocated = ini_get('memory_limit');
		$letter = substr($memoryAllocated, -1);
		if ($letter == 'K') { 
			$factor = 1024;
		}
		elseif ($letter == 'M') { 
			$factor = 1024*1024;
		}
		elseif ($letter == 'G') { 
			$factor = 1024*1024*1024;
		}
		else {
			$factor = 1;
		}

		$totalMemory = $memoryAllocated * $factor;
		$memoryAvailable = $totalMemory - memory_get_usage();

		$bits = (is_numeric($this->bits)) ? $this->bits : 4; // this might be totally wrong but checks out alright
		$memoryNeeded = $width * $height * $bits;

		if ($memoryNeeded > $memoryAvailable) { 
			return false; 
		}
		else {
			return true;
		}
	}
}