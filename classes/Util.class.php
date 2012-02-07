<?php

/** 
 * Copyright 2011 by Jacob Pedersen <jacob@zafri.dk>
 * All rights reserved.
**/

class Util
{
	private static $db = null;
	
	public static function isDebug()
	{
		return ( defined('DEBUG') && DEBUG == true);
	}

	public static function useCache()
	{
		return ( defined('USE_CACHE') && USE_CACHE == true );
	}

	public static function getDb()
	{
		if (self::$db == null)
		{
			self::connectDb();
		}
		return self::$db;
	}

	private static function connectDb()
	{
		$dsn = "mysql:dbname=".DBNAME.";host=".DBHOST;
		try 
		{
			self::$db = new PDO($dsn, DBUSER, DBPASS);
			self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		catch (PDOException $e)
		{
			die($e->getMessage());
		}
	}
	
	// Finds the root directory
	public static function getRootDirectory() {
		// Find the root of the webserver
		$url = substr(dirname($_SERVER['SCRIPT_FILENAME']), 
			strlen($_SERVER['DOCUMENT_ROOT']));

		if ($url[0] != '/') {
			$url = '/' . $url;
		}
		if ($url == '/') {
			$url = '';
		}

		return $url;
	}

	// Checks if mod_rewrite is enabled
	public static function isRewriteEnabled() {
		if (function_exists('apache_get_modules')) {
			$modules = apache_get_modules();
			if (in_array('rewrite', $modules) || in_array('mod_rewrite', $modules)) {
				return true;
			}
		}
		return false;
	}
	
	public function setFlash($message) {
		$_SESSION['flash'] = $message;
	}
	public function hasFlash() {
		return isset($_SESSION['flash']);
	}
	public function getFlashMessage() {
		return $_SESSION['flash'];
	}
	public function getFlash() {
		$message = Util::getFlashMessage();
		unset($_SESSION['flash']);
		return $message;
	}
  
  /**
   * Random password function.
   * Thanks to Neal on Stackoverflow.com
   * http://stackoverflow.com/a/6101969
   */
  public function randomPassword() {
    $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
    $pass = array();
    for ($i = 0; $i < 8; $i++) {
        $n = rand(0, strlen($alphabet)-1);
        $pass[$i] = $alphabet[$n];
    }
    return implode($pass);
  }

    public static function find_closest(&$arr, $num)
    {
        $prev = 0;
        $last = 0;
    
        $length = count($arr);
        $winners = array();
        $winners_len = 0;
    
        sort($arr, SORT_NUMERIC);   
        
        foreach ($arr as $i => $next)
        {
            if ($i > 0 && $i <= $length)
                $prev = $arr[$i - 1];
    
            if ($num >= $prev && $num <= $next)
                $winners[] = $i;
        }    
    
        $winners_len = count($winners);
        if ($winners_len == 0)
        {
            if ($num < $arr[0])
                array_push($winners, 0, 1);
        
        if ($num > $arr[$length - 1])
                array_push($winners, $length - 1, $length - 2);
            
        }
        else
        {
            $last = $winners[count($winners) - 1];
        
            if (count($winners) != 3) 
            {
                if ($winners[0] > 0)
                    $winners[] = $winners[0] - 1;
            
                if ($last < $length)
                    $winners[] = $last + 1;
            }
        }
    
        $res = array();
        foreach ($winners as $w)
        {
        	$res[] = $arr[$w];
        }

        return $res;
    }	

    public static function arrayOfModelFields($marr)
    {
    	$r = array();
    	foreach ($marr as $m)
    		$r[] = $m->toArray();
    	return $r;
    }

}

?>