<?php
/***************************************************************************
 * @version $Id: seo.php,v 1.3 2005/06/23 14:57:22 impleri Exp $
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
 * To do : Expand SEO
 *
 ***************************************************************************/

defined( '_IN_PLUS' ) or die( 'Direct Access to this location is not allowed.' );

$url_array = explode("/", $_SERVER['REQUEST_URI']);
for($n=0; $n < count($url_array); $n++) {
	$chunk = $url_array[$n];
	if($chunk == 'blogs') {
		$ext = 'blogs';
		if($url_array[$n+1] == 'view') {
			$opt='view';
			$item=$url_array[$n+2];
		}
		elseif($url_array[$n+1] == 'viewpost') {
			$opt='viewpost';
			$item=$url_array[$n+2];
		}
		elseif($url_array[$n+1] == 'friends') {
			$opt='friends';
			$item=$url_array[$n+2];
		}
		elseif($url_array[$n+1] == 'post') {
			$opt='post';
			$item=$url_array[$n+2];
		}
		else {
			$opt='front';
		}
	}
	if($chunk == 'forums') {
		$ext = 'blogs';
		if($url_array[$n+1] == 'view') {
			$opt='view';
			$item=$url_array[$n+2];
		}
		elseif($url_array[$n+1] == 'viewpost') {
			$opt='viewpost';
			$item=$url_array[$n+2];
		}
		elseif($url_array[$n+1] == 'friends') {
			$opt='friends';
			$item=$url_array[$n+2];
		}
		elseif($url_array[$n+1] == 'post') {
			$opt='post';
			$item=$url_array[$n+2];
		}
		else {
			$opt='front';
		}
	}
	if($chunk == 'wiki') {
		$ext = 'blogs';
		if($url_array[$n+1] == 'view') {
			$opt='view';
			$item=$url_array[$n+2];
		}
		elseif($url_array[$n+1] == 'viewpost') {
			$opt='viewpost';
			$item=$url_array[$n+2];
		}
		elseif($url_array[$n+1] == 'friends') {
			$opt='friends';
			$item=$url_array[$n+2];
		}
		elseif($url_array[$n+1] == 'post') {
			$opt='post';
			$item=$url_array[$n+2];
		}
		else {
			$opt='front';
		}
	}
	if($chunk == 'albums') {
		$ext = 'blogs';
		if($url_array[$n+1] == 'view') {
			$opt='view';
			$item=$url_array[$n+2];
		}
		elseif($url_array[$n+1] == 'viewpost') {
			$opt='viewpost';
			$item=$url_array[$n+2];
		}
		elseif($url_array[$n+1] == 'friends') {
			$opt='friends';
			$item=$url_array[$n+2];
		}
		elseif($url_array[$n+1] == 'post') {
			$opt='post';
			$item=$url_array[$n+2];
		}
		else {
			$opt='front';
		}
	}
	if($chunk == 'main') {
		$ext = 'blogs';
		if($url_array[$n+1] == 'view') {
			$opt='view';
			$item=$url_array[$n+2];
		}
		elseif($url_array[$n+1] == 'viewpost') {
			$opt='viewpost';
			$item=$url_array[$n+2];
		}
		elseif($url_array[$n+1] == 'friends') {
			$opt='friends';
			$item=$url_array[$n+2];
		}
		elseif($url_array[$n+1] == 'post') {
			$opt='post';
			$item=$url_array[$n+2];
		}
		else {
			$opt='front';
		}
	}
	if($chunk == 'login') {
		$ext = 'login';
	}
	else {
		$ext = 'cover';
	}
}
?>