<?php
/***************************************************************************
 * @version $Id: header.php,v 1.4 2005/06/25 03:36:10 impleri Exp $
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
global $config, $my, $style;

$header = $style->read('overall_header');
define('HEADER_IN', 1);
?>