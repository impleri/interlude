<?php
//
//	file: includes/class_template_compiler
//	begin: 01/01/2006
//	$Author$
//	$Revision$
//	$Date$
//
//	description: template compiler workhorse

if (!defined('PLAY_MUSIC'))
{
	die('No peeksies!');
}

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