<?php
/***************************************************************************
 * @version $Id: login.php,v 1.1 2005/06/23 14:57:22 impleri Exp $
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
$page = PAGE_LOGIN;
switch($opt)
{
	case 'login':
		$session->init($page);
		$session->login('admin', md5('nothing'));
		echo "<script>window.history.go(-1); </script>\n";
		return;
	case 'logout':
		$session->init($page);
		$session->logout();
		echo "<script>window.history.go(-1); </script>\n";
		return;
	case 'screen':
	default:
		$session->init($page);
		view_front();
		return;
}
$style->load();
include( $root_path . 'extensions/header.php');
include( $root_path . 'extensions/footer.php');


?>