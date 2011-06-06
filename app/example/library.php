<?php
/**
 * Example libraries
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

/* This file provides library information. It will always be loaded after installation. This is meant to be the place where the extension registers its libraries for use when needed. */

$lib = ilCore::getLibrary();
$lib->register('libExampleBlog', array('example', 'lib', 'example'));
/* the first item is the library name being registered (same as the library package provided as listed in index.php)
The second item is a pathway finder for where the file is. The array is a walkdown from the extensions directory (i.e. we will be looking for the file SCRIPT-ROOT/extensions/example/lib/example.php) */
$lib->register('libFoo', array('example', 'lib', 'foo.lib'));
// just a second example. The pathway here will be SCRIPT-ROOT/extensions/example/lib/foo.lib.php)