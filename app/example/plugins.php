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
	die('Play it from the top, Sammie.');
}

/* This extension pack is meant to provide developers a working guide as to how extensions interface with Interlude. Most of the files in this directory are loaded automatically by il when needed. I will provide detailed descriptions of the usage of each file as they relate to integration with the structures of il. They are meant to be self-contained within their directory, as this one is, in order to make upgrades and removals easier. */

/* This file provides plugin information. It will be loaded during regular usage (i.e. outside of maintenance mode and installation mode). This is meant to be the place where the extension connects into the rest of the il system. Be sure to be minimal here, simply providing connection points (see exampleUserPlugins::extendUserInfo), as we should not be loading the entire extension when it's not needed! */

class exampleContentPlugins extends ilPlugin {
	// this is how to register a plugin
	function __construct () {
		$this->register('content', $this);
	}

	// one of the many plugin points. This is roughly equivalent to a 'filter' in WordPress
	function beforeDisplay (&$content) {
		$content = 'Hello World!<br />' . $content;
	}
}

class exampleUserPlugins extends ilPlugin {
	function __construct () {
		$this->register('user', $this);
	}

	// a plugin that adds extra information to the user object, which is used e.g. when displaying a user profile.
	function extendUserInfo (&$user) {
		ilFunctions::import('ext.example.plugs.user_info'); // includes the source file once if not already done (SCRIPT_ROOT/extensions/example/plugs/user_info.php)
		$user->profile->exampleBlog = new ExampleUserInfo(); // creates the user info field exampleBlog
	}
}