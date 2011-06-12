<?php
/**
 * system installer
 *
 * @package interlude
 * @subpackage app
 * @copyright Christopher Roussel <christopher@impleri.net>
 */

if (!defined('PLAY_MUSIC')) {
	die('Play it from the top, Sammie.');
}

class ilInstaller extends ilParentInstall {}

// additional steps will insert the extension, rebuild the panels, attempt to activate the plugin and resolve dependancies, and (if successful) rebuild the related caches (e.g. plugins, templates).