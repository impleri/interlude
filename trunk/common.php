<?php
/***************************************************************************
 *							 common.php
 *							----------------
 *	package	: core
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

defined( '_IN_IL_' ) or die( 'Direct Access to this location is not allowed.' );

// checks for config.  Loads installation if not found.
 if ( !file_exists( 'config.php' ) || filesize( 'config.php' ) < 10 ) {
	header( 'Location: install/index.php' );
	exit;
	}

include('config.php');

if( !defined("_INSTALLED") ) {
	header("Location: install/index.php");
	exit;
	}
if (file_exists( 'install/index.php' )) {
	message_die(_OFFLINE, 'S_Install_Dir', 'D_Install_Dir');
	exit;
	}

$class_files = array_files('includes', 'class');
foreach($class_files as $file) {
	include_once($root_path . 'includes/' . $file);
}

// Instantiate database
$db = new db( $config->_host, $config->_db, $config->_user, $config->_pw, $config->_prefix );

// Set internal debug level
$db->debug($config->_debug);

// load config
$config = new config();
$config->read();

// Creates class objects
$session = new session();
$my = new user();
$style = new style();

// Die if offline
if( $config->data['disabled'] && !defined("_ADMIN") && !defined("_LOGIN") ) {
	message_die(_OFFLINE, 'S_Offline', 'D_Offline');
	exit;
}

// load SEO if enabled
if($config->data['enable_seo']) {
	include($root_path . 'includes/seo.php' );
}
else {
	$ext = trim($_REQUEST['ext']);
	$opt = trim($_REQUEST['opt']);
	$item = trim($_REQUEST['item']);
}
?>