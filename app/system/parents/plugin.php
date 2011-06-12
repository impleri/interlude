<?php
/**
 * generic plugin object
 *
 * @package interlude
 * @subpackage framework
 * @copyright Christopher Roussel <christopher@impleri.net>
 */

if (!defined('PLAY_MUSIC')) {
	die('Play it from the top, Sammie.');
}

class ilParentPlugin extends ilParentObject {
	var $name = '';

	function __construct() {} // runs before final dependancy resolution

	function __destruct() {} // updates extension config

	function init() {} // runs after dependancy resolution

	function register ($type, $class) {}
}
