<?php
//
//	file: includes/class_template
//	begin: 01/01/2006
//	$Author$
//	$Revision$
//	$Date$
//
//	description: loads and parses template files, then outputs information

if (!defined('PLAY_MUSIC'))
{
	die('No peeksies!');
}

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