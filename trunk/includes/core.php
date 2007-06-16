<?php
//
//	file: includes/core
//	begin: 01/01/2006
//	$Author$
//	$Revision$
//	$Date$
//
//	description: system loader

if (!defined('PLAY_MUSIC'))
{
	die('No peeksies!');
}

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