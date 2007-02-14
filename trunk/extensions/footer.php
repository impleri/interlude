<?php
/***************************************************************************
 * @version $Id: footer.php,v 1.3 2005/06/25 03:36:10 impleri Exp $
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
global $config, $style, $starttime;

$endtime = microtime();
$style->_totaltime = $endtime - $starttime;
$footer = $style->read('overall_footer');

?>