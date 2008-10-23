<?php
/***************************************************************************
 * @version $Id$
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

/* Page Definitions */
define('PAGE_COVER', -10);
define('PAGE_MAIN', -11);
define('PAGE_SEARCH', -12);
define('PAGE_LOGIN', -13);
define('PAGE_VIEWONLINE', -14);
define('PAGE_INBOX', -15);
define('PAGE_POST', -16);

define('PAGE_ALBUMS', -20);
define('PAGE_VIEWALBUM', -21);
define('PAGE_VIEWPHOTO', -22);

define('PAGE_BLOGS', -30);
define('PAGE_VIEWBLOG', -31);
define('PAGE_VIEWPOST', -32);

define('PAGE_FORUMS', -40);
define('PAGE_VIEWFORUM', -41);
define('PAGE_VIEWTOPIC', -42);

define('PAGE_WIKI', -50);
define('PAGE_VIEWCAT', -51);
define('PAGE_VIEWENTRY', -52);

define('PAGE_REPOS', -60);
define('PAGE_VIEWDIR', -61);
define('PAGE_VIEWFILE', -62);
define('PAGE_DOWNLOAD', -63);

define('PAGE_PROFILE', -70);
define('PAGE_VIEWPROF', -71);
define('PAGE_MEMBERLIST', -72);

// pages are stored in database
while ($row = $db->get_row($result))
{
	define(strtoupper('page_' . $row['page_name']), $row['page_id']);
}

/* Error Messages */
define('_MESSAGE', 400);
define('_GENERAL', 401);
define('_OFFLINE', 402);
define('_CRITICAL', 403);
define('_FOUND', 404);

?>