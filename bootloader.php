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

require(CLASSES . 'ClassLoader.class.php');

$load_paths = array(CLASSES, MODELS, CONTROLLERS, CLASSES . 'smarty' . DS);
ClassLoader::initialize($load_paths);

// The base url
define('URL', Util::getRootDirectory());

session_name(URL);
session_start();

/**
 * Use output compression using the Zlib library.
 */
ini_set('zlib.output_compression', 1);

/** 
 * Turn on output buffering.
 */
ob_start();

$bootloader = new Bootloader();
$bootloader->handleRequest();

/**
 * End and flush output buffer.
 */
ob_end_flush();

?>