<?php
/***************************************************************************
 * @version $Id: index.php,v 1.6 2005/06/25 03:36:08 impleri Exp $
 * @package pluscms
 * @copyright (C) 2005 impleri.net
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 *
 * begin		: Saturday, 23 April 2005
 * email		: christopher@impleri.net
 * Version		: 0.0.1 - 2005/05/20
 *
 * Plus CMS is Open Source Software
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