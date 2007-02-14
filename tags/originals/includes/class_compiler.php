<?php
/***************************************************************************
 *						 class_compiler.php
 *						  ----------------------
 *	begin		: 1 January 2006
 *	copyright	: impleri
 *	email		: impleri@impleri.net
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
  *  Name: template_compiler class
  *  Description: takes html templates, parses them into php, caches the new
  *      file for faster access, and returns it to the template class for
  *      current use
  *  Functions: template_compiler, 
  */

class template_compiler
{
	var $blocks;
	
	/*
	  *  Name: template_compiler
	  *  Description: constructer for class
	  */
	function template_compiler ()
	{
		global $config, $user;
		
		$blocks = array();
		return;
	}
	
	/*
	  *  Name: template_compiler
	  *  Description: constructer for class
	  */
	function template_compiler ()
	{
		global $config, $user;
		
		$blocks = array();
		return;
	}
}