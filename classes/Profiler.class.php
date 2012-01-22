<?php

/** 
 * Copyright 2011 by Jacob Pedersen <jacob@zafri.dk>
 * All rights reserved.
**/

class Profiler
{
	static $markers = array();
	static $results = array();
	
	static function init()
	{
		
	}
	
	static function start($name)
	{
		self::$markers[$name] = microtime();
	}
	
	static function stop($name)
	{
		self::$results[$name] = microtime() - self::$markers[$name];
	}
	
	static function results()
	{
		echo '<br/>';
		foreach (self::$results as $name => $time)
		{
			echo "{$name}: {$time}<br/>";
		}
	}	
}

?>