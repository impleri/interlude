<?php
/**
 * Example plugins
 *
 * @author Christopher Roussel <christopher@impleri.net>
 * @version $Id$
 * @package Interlude-Example
 * @filesource
 */

if (!defined('PLAY_MUSIC')) {
	die('Start from the beginning.');
}

/* This extension pack is meant to provide developers a working guide as to how extensions interface with Interlude. Most of the files in this directory are loaded automatically by il when needed. I will provide detailed descriptions of the usage of each file as they relate to integration with the structures of il. They are meant to be self-contained within their directory, as this one is, in order to make upgrades and removals easier. */

/* This file is an actual plugin that will handle the necessary processing for inserting additional data to a user's profile */

class ExampleUserInfo extends ilUserInfo {
	function __construct() {} // this does prep work (not much)
	function display() {} // this will actually output the information in the necessary format
	function edit() {} // this outputs a form field for editing
}