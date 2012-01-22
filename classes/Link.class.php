<?php

/** 
 * Copyright 2011 by Jacob Pedersen <jacob@zafri.dk>
 * All rights reserved.
**/

class Link 
{
	public static function to($label, $page, $class) 
	{
		return '<a href="' . Link::url($page) . '" id="link-' . $page .'" class="' . $class . '">' . $label . '</a>';
	}
	
	public static function url($page) 
	{
		$url = (defined('USE_REWRITE') && USE_REWRITE) ? URL . '/' . $page : URL .'/?q=/' . $page;
		$url = 'http://' . $_SERVER['SERVER_NAME'] . $url;
		return $url;
	}
	
	public static function base($url) 
	{
		return URL . '/app/static' . $url;
	}
}

?>