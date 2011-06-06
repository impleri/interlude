<?php
/**
 * Plugin classes
 *
 * @author Christopher Roussel <christopher@impleri.net>
 * @version $Id$
 * @package Interlude
 * @filesource
 */

if (!defined('PLAY_MUSIC')) {
	die('Play it from the top, Sammie.');
}

class ilPlugin extends ilCacheParent {
	var $name = '';

	function __construct() {} // runs before final dependancy resolution

	function __destruct() {} // updates extension config

	function init() {} // runs after dependancy resolution

	function register ($type, $class) {}
}