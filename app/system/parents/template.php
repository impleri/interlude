<?php
/**
 * generic template object
 *
 * @package interlude
 * @subpackage framework
 * @copyright Christopher Roussel <christopher@impleri.net>
 */

if (!defined('PLAY_MUSIC')) {
	die('Play it from the top, Sammie.');
}

/*
  * Template class
  * ------------
  * Template/Style Object
  *
  * Functions: template, load, assign, switch
  */
class ilParentTemplate extends ilParentCache {
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
	function template() {
		// Set file directory and template

		// Check cache info

	}

	// Load (cache or source) & compile (if necessary) template
	function load($file) {

	}

	// Send output
	function output() {

	}

	// Assign variable(s)
	function assign($vars, $value=false) {

	}

	// Assign variable(s) to specific block
	function assign_to_block($block, $vars, $value=false) {

	}

	// Unset variable(s)
	function decease($vars) {

	}
}
