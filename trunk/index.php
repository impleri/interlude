<?php
/***************************************************************************
 *							 index.php
 *							----------------
 *	begin		: 1 January 2006
 *	copyright	: impleri
 *	email		: impleri@impleri.net
 *
 *	version	: 0.0.1 - 01/01/2006
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

// main initialisation
$starttime = microtime();
define( '_IN_PLUS', true );
$root_path = ((!@function_exists('realpath') || !@realpath('./common.php')) ? './' : @realpath('./')) . '/';
$root_path = str_replace('\\', '/', $root_path);
include($root_path . 'includes/constants.php');
include($root_path . 'includes/functions.php');
include( $root_path . 'common.php' );

// Load Extension
if($ext) {
	if(file_exists($root_path . 'extensions/' . $ext . '.php')) {
		include($root_path . 'extensions/' . $ext . '.php');
	} else {
		// 404
		include($root_path . 'extensions/cover.php');
	}
} 
else {
	// 404
	include($root_path . 'extensions/cover.php');
}

?>