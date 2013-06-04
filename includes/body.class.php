<?php
/* body.class.php
 *
 * class that helps build the page output
 * 
 */

$request = basename($_SERVER['REQUEST_URI']);
if ($request == 'body.class.php') { echo 'You cannot access this file directly'; exit(); }
 
class Body
{
	var $base_url; // variable to prefix all URLs with so it is directory independant
	var $head; // variable to add stuff to the <head>
	var $classes = array(); // classes for <body>
	var $thumbnail_url = ''; // URL for social media networks to pull a thumbnail image
	var $title_pieces = array(); // array to help build <title>
	var $social_title = ''; // variables for posting to social media
	var $social_desc  = '';
	
	public function url($url)
	{
		$full_url = $this->base_url . $url;
		$full_url = str_replace('//', '/', $full_url);
		return $full_url;
	}
	
	public function add_head($text)
	{
		$this->head .= "\t" . $text . "\n";
	}
	
	public function get_head()
	{
		return $this->head;
	}

	public function add_class($class)
	{
		$this->classes[] = $class;
	}

	public function get_classes()
	{
		return $this->classes;
	}

	public function set_thumbnail($file)
	{
		if (is_file($file))
		{
			if (strlen($this->thumbnail_url) == 0)
			{
				$this->thumbnail_url = $file;
			}
		}
	}

	public function get_thumbnail()
	{
		$return = '';
		$s = ($_SERVER['REMOTE_PORT'] == 443) ? 's' : '';
		if (is_file($this->thumbnail_url))
		{
			$full_url = 'http:'.$s.'//' . $_SERVER['SERVER_NAME'] . $this->url($this->thumbnail_url);
			$return = $full_url;
		}
		else
		{
			if (is_file('site/images/site_thumbnail.png')) {
				$full_url = 'http:'.$s.'//' . $_SERVER['SERVER_NAME'] . $this->url('site/images/site_thumbnail.png');
			} 
			elseif (is_file('site/images/site_thumbnail.jpg')) {
				$full_url = 'http:'.$s.'//' . $_SERVER['SERVER_NAME'] . $this->url('site/images/site_thumbnail.jpg');
			}
			$return = $full_url;
		}
		return $return;
	}

	public function set_title($piece, $text)
	{
		// 0 = the page name
		// 1 = component title (from blog or directory or some such thing)
		// ? = TBD
		if (is_numeric($piece))
		{
			$this->title_pieces[$piece] = $text;
		}
	}

	public function get_title()
	{
		return $this->title_pieces;
	}

	public function set_social($title, $desc)
	{
		$this->social_title = $title;
		$this->social_desc = $desc;
	}
}
