<?php
/**
 * Library class
 *
 * @author Christopher Roussel <christopher@impleri.net>
 * @version $Id$
 * @package Interlude-Example
 * @filesource
 */

if (!defined('PLAY_MUSIC')) {
	die('Play it from the top, Sammie.');
}

class ilLibrary extends ilCacheParent {
	$data = array();
	function register ($library, $pathArray) {
	/* the first item is the library name being registered (same as the library package provided as listed in index.php)
	The second item is a pathway finder for where the file is. The array is a walkdown from the extensions directory (i.e. we will be looking for the file SCRIPT-ROOT/extensions/example/lib/example.php)
	$lib->register('libExampleBlog', array('example', 'lib', 'example'));
	$lib->register('libFoo', array('example', 'lib', 'foo.lib'));
	just a second example. The pathway here will be SCRIPT-ROOT/extensions/example/lib/foo.lib.php)} */
	}

	function load ($library) {
		if (isset($this->data[$library])) {
			return ilCore::includeFile($this->data[$library]);
		}
		else {
			$this->setError('LIB_NOT_FOUND', $library);
		}
	}
}