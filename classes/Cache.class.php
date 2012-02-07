<?php

/**
 * Simple text file based cache solution.
 * Copyright (C) 2012 ITBuster.dk 
 */

/**
 * Directory to store cached versions of pages.
 */
define('PAGE_CACHE', TEMPORARY . 'cache' . DS);

/**
 * Simple file based mplementation
 */
class Cache
{
	/**
	 * Clears the whole page cache.
	 */
	public static function clearAll()
	{
		$files = glob(PAGE_CACHE . '*.html');
		foreach($files as $file)
		{
			unlink($file); 
		}
	}

	public static function hasCached($q)
	{
		return file_exists(self::getPath($q));
	}

	public static function outputCached($q)
	{
		echo file_get_contents(self::getPath($q));
	}

	public static function cacheOutput($q, $contents)
	{
		file_put_contents(self::getPath($q), $contents);
	}

	private static function getPath($q)
	{
		if ($q == '') { $q = 'INDEX'; }
		$q = str_replace('/', '_', $q);
		return PAGE_CACHE . $q . '.html'; 
	}
}

?>