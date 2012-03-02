<?php

/**
 * Make sure site, framework and app paths are defined before this bootloader
 * script is executed.
 */
if (!defined('SITE') || !defined('FRAMEWORK') || !defined('APP'))
{
	die('SITE, FRAMEWORK, and APP path constants should be defined');	
}

// The application should follow these conventions
define('CLASSES', 		FRAMEWORK . 'classes' . DS);
define('TEMPLATES', 	APP . 'templates' . DS);
define('MODELS', 		APP . 'models' . DS);
define('CONTROLLERS', 	APP . 'controllers' . DS);
define('TEMPORARY', 	APP . 'tmp' . DS );
define('MAIL_TEMPLATES', TEMPLATES . 'emails' . DS);
define('MIGRATIONS', APP . 'migrations' . DS);

require(CLASSES . 'ClassLoader.class.php');

$load_paths = array(CLASSES, MODELS, CONTROLLERS, CLASSES . 'smarty' . DS);
ClassLoader::initialize($load_paths);

/**
 * If not URL is defined, make a guess.
 */
if (!defined('URL'))
{
	define('URL', Util::getRootDirectory());
}

/**
 * If no resource base is configured (e.g. a url to a CDN), 
 * the resource folder is the the "app/static" folder by convention.
 */
if (!defined('RESOURCE_BASE'))
{
	define('RESOURCE_BASE', Link::base(''));
}

/**
 * Start a session with the name of the URL.
 * Just to make sure we don't collide with other stuff.
 */
session_name(URL);	
session_start();

if (!defined('SKIP_GZIP'))
{
	/**
	 * Use output compression using the Zlib library - if supported 
	 * by the browser.
	 */
	if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip'))
	{
		ini_set('zlib.output_compression', 1);
		header('Content-Encoding: gzip');
	}
}

if (!defined('SKIP_BOOTLOAD'))
{
	/** 
	 * Turn on output buffering.
	 */
	ob_start();

	//Profiler::start('request');


	$bootloader = new Bootloader();
	$bootloader->handleRequest();

	//Profiler::stop('request');
	//var_dump(Profiler::$results);

	//if (Util::isDebug())
	//{
	//	DbObject::dumpStats();
	//}

	/**
	 * End and flush output buffer.
	 */
	ob_end_flush();
}

?>