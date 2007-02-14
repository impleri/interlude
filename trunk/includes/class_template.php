<?php
/***************************************************************************
 *						 class_template.php
 *						  ----------------------
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
  * Template class
  * ------------
  * Template/Style Object
  *
  * Functions: template, load, assign, switch
  */
class template
{
	// variables
	var $_data = array();
	var $_files = array();
	var $_mains = array();
	var $compiled_code = array();
	var $template_root;
	var $cache_root;
	var $_check;
	var $main_name;
	var $alt_name;
	var $_debug;
	
	
	// Constructor
	function template()
	{
		// Set file directory and template
		
		// Check cache info
	
	}
	
	// Load (cache or source) & compile (if necessary) template
	function load($file)
	{
	
	}
	
	// Send output
	function output()
	{
	
	}
	
	// Assign variable(s)
	function assign($vars, $value=false)
	{
	
	}
	
	// Assign variable(s) to specific block
	function assign_to_block($block, $vars, $value=false)
	{
	
	}
	
	// Unset variable(s)
	function decease($vars)
	{
	
	}
}