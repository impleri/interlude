<?php
/**
 * Admin panels
 *
 * @author Christopher Roussel <christopher@impleri.net>
 * @version $Id$
 * @package Interlude-Example
 * @filesource
 */

if (!defined('PLAY_MUSIC')) {
	die('Play it from the top, Sammie.');
}

/* This extension pack is meant to provide developers a working guide as to how extensions interface with Interlude. Most of the files in this directory are loaded automatically by il when needed. I will provide detailed descriptions of the usage of each file as they relate to integration with the structures of il. They are meant to be self-contained within their directory, as this one is, in order to make upgrades and removals easier. */

/* This file manages the necessary admin panels. Admin panels are divided into four groups: panels, pages, tabs, and boxes. Boxes are the smallest unit, making up one section of a displayed tab and displaying related information (e.g. forums posting preferences, forum reading preferences). Tabs hold the various boxes within a page and may be displayed as a sub-menu item (e.g. forums preferences, forums profile). Pages are discreet HTML pages (and displayed as menu items) that can hold multiple tabs, typically by a javascript function that hides inactive tabs for organisational purposes (e.g. forums options, blog options). Finally, a panel holds multiple pages, always displayed as its own discreet entity on navigation menus (e.g. profile, subscriptions, options). Panels are collected within a single CP (control panel). CPs can be added (e.g. a moderator CP for forums or an editor CP for blogs/CMSs), but should be done so minimally. CPs should be kept to a minimum and used for different roles (which is why interlude comes with only an Admin and a User CP). Panels should also be kept to a minimum, as they are used to organise the CP functionally (which is why there is only one panel in the default UserCP: options (however, extensions like a messaging system or data management create additional panels). Pages are where large extensions should primarily focus their attention, creating a single page for the extension. Tabs and boxes are where smaller extensions should focus their attention, adding onto existing pages (i.e. there is no reason why an extension that adds one or two fields to a user profile should have its own page to handle those two fields). Always remember that a new page means another http request, so be kind to site traffic. */

class exampleAdmin extends ilPanelParent {
	function displayForm() {} // outputs an input form
	function display() {} // outputs unedited view (e.g. what one may see on a profile page)
	function onMenu() {} // runs when creating menu items from parent object
	function register() {} // this is where you want to register sub-items (you could do them all below the registerTab)
}

$acp = new exampleAdmin();

$cp = ilCore::getCP();
$cp->registerTab('admin.options', 'example', 'exampleAdmin');