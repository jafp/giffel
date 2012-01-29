<?php

define('DS', DIRECTORY_SEPARATOR);

define('SITE', dirname(__FILE__) . DS );
define('FRAMEWORK', SITE . '..' . DS);
define('APP', SITE);

require SITE . 'config.php';
require FRAMEWORK . 'bootloader.php';

?>