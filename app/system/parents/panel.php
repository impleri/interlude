<?php
/**
 * generic panel object
 *
 * @package interlude
 * @subpackage framework
 * @copyright Christopher Roussel <christopher@impleri.net>
 */

if (!defined('PLAY_MUSIC')) {
	die('Play it from the top, Sammie.');
}

/* Control panels are divided into four groups: panels, pages, tabs, and boxes. Boxes are the smallest unit, making up one section of a displayed tab and displaying related information (e.g. forums posting preferences, forum reading preferences). Tabs hold the various boxes within a page and may be displayed as a sub-menu item (e.g. forums preferences, forums profile). Pages are discreet HTML pages (and displayed as menu items) that can hold multiple tabs, typically by a javascript function that hides inactive tabs for organisational purposes (e.g. forums options, blog options). Finally, a panel holds multiple pages, always displayed as its own discreet entity on navigation menus (e.g. profile, subscriptions, options). Panels are collected within a single CP (control panel). CPs can be added (e.g. a moderator CP for forums or an editor CP for blogs/CMSs), but should be done so minimally. CPs should be kept to a minimum and used for different roles (which is why interlude comes with only an Admin and a User CP). Panels should also be kept to a minimum, as they are used to organise the CP functionally (which is why there is only one panel in the default UserCP: options (however, extensions like a messaging system or data management create additional panels). Pages are where large extensions should primarily focus their attention, creating a single page for the extension. Tabs and boxes are where smaller extensions should focus their attention, adding onto existing pages (i.e. there is no reason why an extension that adds one or two fields to a user profile should have its own page to handle those two fields). Always remember that a new page means another http request, so be kind to site traffic. */

class ilParentPanel extends ilDataCacheParent {
	var $type;
	var $data = array();

	function __construct() {
		$this->loadTable();
	}

	function loadTable() {
		$sql = $this->_cache->buildSelect($this->_tbl, array('type' => $this->type), array('GROUP' => 'parent'));
		$this->_cache->query($sql);
	}

	function reload() {
		$ext = ilCore::getExt();
		foreach ($ext->loaded as $extension) {
			$path = 'ext.' . $extension->name . '.' . $this->type;
			$name = $extension->name . ucfirst($this->type);
			if (ilFunctions::import($path)) {
				$this->plugs[$name] = new $name();
			}
		}
	}
}
