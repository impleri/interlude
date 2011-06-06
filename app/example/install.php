<?php
/**
 * Install file
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

/* This file intergrates with the installer, giving interlude the necessary information to tie the extension into the system */

class ExampleInstaller extends ilInstallerParent {
	function __construct() {} // need to load files to help the installation process, load them here (e.g. the extension info object from index.php)
	function check() {} // installer will check for previous versions of extensions with this one's name before loading this. Use this if you need to check for renamed extensions (i.e. you've renamed the extension, so you need to check for a different name to get the version)
	function createDB () {} // add structural information (e.g. tables, modifications, etc) to the DB. Be sure to rename old tables if the schema changes (so the old data isn't lost). Be sure to save time in writing by using the XML schema for simplified cross-DB support.
	function upgrade ($version) {} // only runs if a previous version was found. Upgrade/migrate extension data to the new schema
	function populateDB ($prefab=false) {} // adds data to the DB. If your extension is creating a new reference table for existing data, this would be a good location to generate that data. Use $prefab to insert 'stock' data (i.e. example posts/products/images/etc).
	function cleanDB () {} // Use this to remove old, unnecessary data from the DB (such as old, unused tables).
	// additional steps will insert the extension, rebuild the panels, attempt to activate the plugin and resolve dependancies, and (if successful) rebuild the related caches (e.g. plugins, templates).
}