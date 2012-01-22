<?php

/** 
 * Copyright 2011 by Jacob Pedersen <jacob@zafri.dk>
 * All rights reserved.
**/

class ClassLoader
{
	/** Is autoloader initialized */
	private static $initialized = false;
	
	/**
	* Initializes the autoloader
	*/
	public static function initialize($load_paths)
	{
		if (!self::$initialized)
		{
			require(CLASSES . 'Autoloader.class.php');
			if (!defined('DEBUG') || DEBUG == false)
			{
				Autoloader::setCacheFilePath(APP . 'tmp' . DS . 'class_cache.txt');
			}
			Autoloader::setClassPaths($load_paths);
			spl_autoload_register(array('Autoloader', 'loadClass'));
			
			self::$initialized = true;
		}
	}
}

?>