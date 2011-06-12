<?php
/**
 * index file
 *
 * since everything is handled internally, this is primarily to set the first
 * few definitions and load the system.
 *
 * @package interlude
 * @subpackage web
 * @copyright Christopher Roussel <christopher@impleri.net>
 */

/**
 * path to the system startup file
 * this can be either an absolute or a relative path anywhere accessible
 * including outside of this web-accessible directory
 */
$loadPath = '/path/to/app/startup.php';

/**
 * really bad error message
 * used in the off-chance that this script cannot find the system startup
 */
define('IL_FILE_ERROR', 'I cannot find the required file %s!  Please contact the system
administrator immediately.');

/* DO NOT EDIT BELOW THIS LINE! */

/**
 * starting time (for debug and performance)
 */
define('IL_STARTTIME', microtime(true));

// check for existence of startup file
if (!file_exists($loadPath)) {
    die(sprintf(IL_FILE_ERROR, $loadPath));
    exit;
}

/**
 * system security constant
 * if this is not declared, nothing will load
 */
define('PLAY_MUSIC', true);

/**
 * script file extension
 * uses the extension of this file (generally, this is php)
 */
define('IL_EXT', substr(strrchr(__FILE__,'.'),1));

/**
 * web root path
 * this is used later on for routing includes (like images)
 */
define('IL_WEBROOT', dirname(__FILE__));

// load core in autoload.php
require_once $loadPath;
unset($loadPath);

// output display
$app = ilFactory::getApplication();
$app->display();
