<?php
/***************************************************************************
 *						 class_cache.php
 *						  -------------------
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

/*
  * Cache class
  * ------------
  * Meta-class for caching
  *
  * Functions: template, load, assign, switch
  */
class cache
{
	var $_path;
	var $_file;
	var $_disabled;
	var $_cached;
	var $_time;

	// Constructor
	function cache($name)
	{
		global $config;
		
		// Get disabled info
		$config_name = "cache_" . $name;
		$this->_disabled = !$config->data[$config_name];
		
		// Set path and file
		$this->_name = "dta_" . $name . ".php";
		$this->_path = $config->root . $config->data['cache_path'];
		
		return;
	}
	
	// Read data (first cache, then db)
	function read($sql='', $force=false, $key_field='')
	{
	
	}
	
	
	
	// For redefinitions in child classes
	function pre_process(&$rows)
	{
	}

	function row_process(&$rows, $row_id)
	{
	}

	function post_process(&$rows)
	{
	}
}