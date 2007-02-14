<?php
/***************************************************************************
 *							 core.php
 *							----------------
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

// no enquiring minds
defined( '_PLAY_MUSIC' ) or die('Inquiring minds do not get to know.');

/* Absolute location of config file.
  * Must include flie name.  
  * It is highly recommended that this file be inaccessible from the web.
  * This is the only line you need to edit in this file.
  */
$_config_loc = "C:/Program Files/xampp/il_config.php";

// DEBUG --> Startup running statistics

// CONFIG --> Load configuration

include($_config_loc);
if( !defined("_INSTALLED") )
{
	header('Location: ' . $phpbb_root_path . 'install/install.' . $phpEx);
	exit;
}

// DEBUG --> Startup running statistics

// HOOKS --> Prepare hooks class for plugins