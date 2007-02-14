<?php
/***************************************************************************
 * @version $Id: common.php,v 1.9 2005/06/25 03:36:08 impleri Exp $
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

defined( '_IN_PLUS' ) or die( 'Direct Access to this location is not allowed.' );

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