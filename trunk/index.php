<?php
//
//	file: index
//	begin: 01/01/2006
//	$Author$
//	$Revision$
//	$Date$
//
//	description: main file

// first things are first
define('PLAY_MUSIC', true);
$il_ext = substr(strrchr(__FILE__, '.'), 1);
$il_root = dirname(__FILE__) . '/';

// simple startup
include($il_root . 'includes/core.' . $il_ext);

// start session
$session->load();

// let the extension take over the system
include($config->extension_by_name($extension));

// sorry that there isn't any more!
?>