<?php
/***************************************************************************
 * @version $Id: blogs.php,v 1.5 2005/06/25 03:36:10 impleri Exp $
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
 * To do : Too much to list!
 *
 ***************************************************************************/

defined( '_IN_PLUS' ) or die( 'Direct Access to this location is not allowed.' );

switch($opt)
{
	case 'view':
		$session->init(PAGE_VIEWBLOG);
		view_blog($item);
		return;
	case 'viewpost':
		$session->init(PAGE_VIEWPOST);
		view_post($item);
		return;
	case 'friends':
		$session->init(PAGE_VIEWBLOG);
		view_friends($item);
		return;
	case 'post':
		$session->init(PAGE_POST);
		post($item);
		return;
	case 'front':
	default:
		$session->init(PAGE_BLOGS);
		view_front();
		return;
}

function view_front()
{
	global $style, $my;
	include( $root_path . 'extensions/header.php');
	//$sql = "SELECT * FROM `%__users`";
	//$file = "memberlist_body";
	//$style->read($sql, $file, $config->data['cache_tpl']);
	echo "<div><br /> $opt </div>";
	include( $root_path . 'extensions/footer.php');
	return;
}

function view_blog( $item )
{
	global $style, $my, $db;
	$sql = "SELECT * FROM `%__blogs` WHERE `b_id` = '$item'";
	$blog = null;
	$db->setQuery($sql);
	if (!($db->loadObject($blog))) {
		return false;
	}
	$style->load('', $blog->b_tpl, $blog->b_style, $blog->b_imgset);
	// $access = implode(', ', $my->auths['view_blogs']);
	$access = '2,3,4';
	$sql = "SELECT * FROM `%__blogs` b, `%__blog_posts` bp, `%__users` u
		WHERE b.b_id = '$item'
		AND bp.bp_blog = b.b_id
		AND bp.bp_author = u.u_id";
	$file = "blog_body";
	$body = array(
		'style' => 'blog',
		'content' => $style->read($file, $sql, $item, $config->data['cache_tpl']),
		'frame' => 'blog_frame',
	);
	$style->loadPage($body);
	return;
}

function view_friends( $item )
{
global $style, $my;
include( $root_path . 'extensions/header.php');
$sql = "SELECT * FROM `%__users`";
$file = "memberlist_body";
// $style->read($file, $sql, '', $config->data['cache_tpl']);
echo "<div><br /> Friends of $item </div>";
include( $root_path . 'extensions/footer.php');
return;
}

function view_post( $item )
{
global $style, $my;
//$sql = "SELECT * FROM `%__users`";
//$file = "memberlist_body";
//$style->read($sql, $file, $config->data['cache_tpl']);
echo "<div><br /> Post # $item </div>";
return;
}

function post( $item )
{
global $style, $my;
include( $root_path . 'extensions/header.php');
//$sql = "SELECT * FROM `%__users`";
//$file = "memberlist_body";
//$style->read($sql, $file, $config->data['cache_tpl']);
echo "<div><br /> Posting to Blog $item </div>";
include( $root_path . 'extensions/footer.php');
return;
}

?>