<?php
/***************************************************************************
 * @version $Id: functions.php,v 1.6 2005/06/23 14:57:22 impleri Exp $
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

/*
 * function get_ip
 * ---------------
 * Gets the user's IP address and encodes it.
 */

function get_ip() 
{
	$client_ip = ( !empty($_SERVER['REMOTE_ADDR']) ) ? $_SERVER['REMOTE_ADDR'] : ( ( !empty($_ENV['REMOTE_ADDR']) ) ? $_ENV['REMOTE_ADDR'] : $REMOTE_ADDR );
	$explod_ip = explode('.', $client_ip);
	return sprintf('%02x%02x%02x%02x', $explod_ip[0], $explod_ip[1], $explod_ip[2], $explod_ip[3]);
}

/*
 * function decode_ip
 * ------------------
 * Decodes IP address.
 */

function decode_ip($int_ip)
{
	$hexipbang = explode('.', chunk_split($int_ip, 2, '.'));
	return hexdec($hexipbang[0]). '.' . hexdec($hexipbang[1]) . '.' . hexdec($hexipbang[2]) . '.' . hexdec($hexipbang[3]);
}

/*
 * function array_files
 * --------------------
 * Opens $dir and sets an array of all php files in $dir that contain
 * the $pref prefix.
 */
 
function array_files($dir, $pref) {
	global $config;

	$set_dir = opendir($this->_root . $dir);
	$a_files = array();
	while ( $file = readdir($set_dir) )
	{
		if ( preg_match('/^' . $pref . '.*?\.php$/', $file))
		{
			$filename = trim($file);
			$a_files[$filename] = $filename;
		}
	}
	closedir($set_dir);
	@asort($a_files);

	return $a_files;
}

/*
 * function db_serial
 * ------------------
 * Serializes $array into a string.  More efficient than serialize().
 * Only works down to arrays within arrays.
 */

function db_serial($array)
{
	$test = array();
	while(list($k, $v) = @each($array)) {
		$temp = array();
		while(list($key, $val) = @each($v)) {
			$temp[] = $key . '?' . $val;
		}
		$test[] = $k . '@' . implode('.', $temp);
	}
	return implode('|', $test);
}

/*
 * function db_unserial
 * --------------------
 * Unserializes string into an array.  More efficient than unserialize().
 * Only works down to arrays within arrays.
 */
 
function db_unserial($string)
{
	$array = array();
	$test2 = explode('|', $string);
	foreach($test2 as $work) {
		$temp = explode('@', $work);
		$test = array();
		foreach(explode('.', $temp[1]) as $part) {
			$part2 = explode('?', $part);
			$test[$part2[0]] = $part2[1];
		}
		$array[$temp[0]] = $test;
	}
	return $array;
}

/*
 * function message_die
 * --------------------
 * Stops processing and returns error message
 */
 
function message_die($type, $err = '', $title = '', $errlog='')
{
	global $db, $style, $config, $my, $starttime, $root_path, $session;
	@include($root_path . 'language/'.$style->_lang.'/lang_main.php');
	if(empty($data)) {
		include_once($root_path . 'language/en_US/lang_main.php');
	}

	if ( $db->_debug <> 0 && ( $msg_code != _GENERAL || $msg_code == _CRITICAL ) ) {
		$debug_text = '';
		if (!empty($errlog)) {
			$debug_text .= "<br /><br />SQL Error : " . $errlog['errno'] . " " . $errlog['errmsg'];
			$debug_text .= "<br /><br />". $errlog['sql'];
			$debug_text .= "<br /><br />Line : " . $errlog['line'] . "<br />File : " . $errlog['file'];
		}
	}

	switch($type)
	{
		case _MESSAGE:
			if ( $title == '' ) {
				$title = 'D_Information';
			}
			if ( $err == '' ) {
				$err = 'S_An_error_occured';
			}
			break;

		case _GENERAL:
			if ( $err == '' ) {
				$err = 'S_An_error_occured';
			}
			if ( $title == '' ) {
				$title = 'D_General_Error';
			}
			break;

		case _CRITICAL:
			$custom_error_message = sprintf($data['S_Custom_Error'], '<a href="mailto:' . $config->data['site_email'] . '">', '</a>');
			if ( $err == '' ) {
				$err = 'D_Critical_Error';
			}
			if ( $title == '' ) {
				$title = 'S_Critical_Error';
			}
			if ( !empty($data[$err]) ) {
				$err = $data[$err];
			}
			if ( !empty($data[$title]) ) {
				$title = $data[$title];
			}
			if ( $db->_debug ) {
				if ( $errlog != '' ) {
					$err = $err . '<br /><br /><b><u>DEBUG MODE</u></b>' . $debug_text;
				}
			}
			break;
	}

	if ( $type != _CRITICAL ) {
		$custom_error_message = sprintf($data['S_Custom_Error'], '<a href="mailto:' . $config->data['site_email'] . '">', '</a>');
		if ( !empty($data[$err]) ) {
			$err = $data[$err];
		}
		if ( !empty($data[$title]) ) {
			$title = $data[$title];
		}
		if ( $db->_debug && $msg_code == _GENERAL ) {
			if ( $errlog != '' ) {
				$err = $err . '<br /><br /><b><u>DEBUG MODE</u></b>' . $debug_text;
			}
		}
	}
	echo "<html>\n<body>\n<h1>" . $title . "</h1>\n<br /><br />\n" . $err;
	echo "&nbsp;<br /><hr />\n" . $custom_error_message . '<hr /><br clear="all">';
	die("</body>\n</html>");
}
?>