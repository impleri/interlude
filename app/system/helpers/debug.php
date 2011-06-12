<?php
/**
 * system debugger
 *
 * @package interlude
 * @subpackage app
 * @copyright Christopher Roussel <christopher@impleri.net>
 */

if (!defined('PLAY_MUSIC')) {
	die('Play it from the top, Sammie.');
}

class ilHelperDebug {
	static function &getDebug() {}

	static function top() {
		if (defined('LIVE_SITE')) {
			error_reporting(0);
		}
		else {
			error_reporting(E_ALL);
		}
	}
}
