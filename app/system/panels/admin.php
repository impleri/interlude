<?php
/**
 * admin panels
 *
 * @package interlude
 * @subpackage framework
 * @copyright Christopher Roussel <christopher@impleri.net>
 */

if (!defined('PLAY_MUSIC')) {
	die('Play it from the top, Sammie.');
}

class ilPanelAdmin extends ilParentPanel {
	function __construct() { // creates a window environment
		$this->type = 'admin';
		return parent::__construct();
	}
	function onWindowMenu() { // runs when creating menu item for windows

	}
}
