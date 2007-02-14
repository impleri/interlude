<?php
/***************************************************************************
 * @version $Id: class_style.php,v 1.5 2005/06/25 03:36:10 impleri Exp $
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
 * To do : Add a way to force regeneration of all necessary templates
 *         when something in the db is updated (post, comment, etc)
 *
 ***************************************************************************/

defined( '_IN_PLUS' ) or die( 'Direct Access to this location is not allowed.' );

/*
 * Style class
 * ------------
 * Creates and parses all data to html and outputs to screen.  Deals with templates,
 * CSS styles, imagesets, language files, etc.
 *
 * This is loosely based on pthirik's Categories Hierarchy MOD and eXtreme Styles MOD
 * for phpBB.
 *
 * Functions: style, load, loadPage, loadContainer, ContainerStyle, loadModule, read
 */
 
class style {

	var $_style;
	var $_dir;
	var $_css;
	var $_images;
	var $_tpl;
	var $_lang;
	var $_imgset;
	var $_cache_root;
	var $_cache_key;
	var $_bodycss;
	var $_content;
	var $_hidden;
	var $_type;
	var $_totaltime;
	var $containers;
	var $modules;
	var $constyles;
	var $lang;
	var $images;

	/*
	 * function style (Constructor)
	 * ----------------------------
	 * Sets up initial style info without loading files.
	 */
	 
	function style()
	{
		global $config;
		
		$this->_cache_root = $config->_root . 'cache/';
		$this->_cache_key = $config->data['cache_key'];
		$this->_tpl = ($config->data['default_tpl']) ? $config->data['default_tpl'] : 'streaker';
		$this->_style = ($config->data['default_style']) ? $config->data['default_style'] : 'streaker';
		$this->_imgset = ($config->data['default_imgset']) ? $config->data['default_imgset'] : 'streaker';
		$this->_lang = ($config->data['default_lang']) ? $config->data['default_lang'] : 'en_US';

		// Load Modules
		$module_cached = new cache('dta_modules', $config->data['cache_style']);
		$sql = "SELECT * FROM `%__modules`, `%__containers`
					WHERE `sm_status`='1'
					AND `sm_position` = `sc_name`
					ORDER BY `sm_order`";
		$rows = $module_cached->read($sql, 0, 'sm_id');
		if ( !empty($rows) ) {
			foreach($rows as $row) {
				$this->modules->{$row['sm_position']}[$row['sm_id']] = $row;
			}
		}
	
		$this->lang = array();
	}

	/*
	 * function load
	 * -------------
	 * Loads more specific style info and then loads files.
	 */
	 
	 function load($containers='', $tpl='', $style='', $imgset='', $lang='')
	{
		global $config, $my, $modules;
		$this->_tpl = ($tpl) ? $tpl : (($my->data['u_tpl']) ? $my->data['u_tpl'] : $this->_tpl);
		$this->_style = ($style) ? $style : (($my->data['u_style']) ? $my->data['u_style'] : $this->_style);
		$this->_imgset = ($imgset) ? $imgset : (($my->data['u_imgset']) ? $my->data['u_imgset'] : $this->_imgset);
		$this->_lang = ($lang) ? $lang : (($my->data['u_lang']) ? $my->data['u_lang'] : $this->_lang);
		$this->containers = (!empty($containers)) ? $containers : (($my->data['u_contord']) ? $my->data['u_contord'] : '');
		$this->_dir = $config->_root . 'style/templates/' . $this->_tpl . '/';
		$this->_css = 'http://' . $config->data['server_name'] . '/style/styles/' . $this->_style . '.css';
		$this->_images = $config->_root . 'style/imagesets/' . $this->_imgset . '/';
		$images = array();
		$lang_images = array();
		
		if($this->containers) {
			while(list($k, $v) = @each($this->containers)) {
				while(list($key, $val) = @each($v)) {
					if($this->modules->$k[$key]) {
						$this->modules->$k[$key]->hide = $val;
					}
				}
			}
		}
		
		@include($this->_images . $this->_imgset . '.php');
		@include($this->_images . $this->_lang . '.php');
		if(empty($lang_images)) {
			@include($this->_images . $config->data['default_lang'] . '.php');
		}
		if(empty($lang_images)) {
			@include($this->_images . 'en_US.php');
		}
		$this->images = array_merge($images, $lang_images);
		$lang_files = array_files('language/'.$this->_lang, 'lang');
		foreach($lang_files as $file) {
			include_once($config->_root . 'language/'.$this->_lang . '/' . $file);
		}
		$this->lang = $lang;
		$this->_dateformat = ($my->data['u_dateformat']) ? $my->data['u_dateformat']: "D M d, Y g:i a";
	}
	
	/*
	 * function loadPage
	 * -----------------
	 * Gathers parsed body content, prepares containers, header, and footer,
	 * then outputs page to screen.
	 */
	 
	 function loadPage( $body, $hdrftr='1' )
	{
		global $config, $db;
		
		while(list($k, $v) = @each($body)) {
			if($k == 'style') {
				$this->_bodycss = '-' . $v;
			}
			elseif($k == 'content') {
				$this->_content = $v;
			}
			elseif($k == 'frame') {
				$file = '';
				$sql = "SELECT * FROM `%__containers`";
				$db->setQuery($sql);
				$cons = $db->loadObjectList('sc_name');
				foreach($cons as $con) {
					$this->constyles[$con->sc_name] = $con;
					$this->constyles[$con->sc_name]->sc_style =  ($con->sc_style) ? '-' . $con->sc_style : '-' . $con->sc_type;
					$this->constyles[$con->sc_name]->con_type =  ($con->sc_type) ? '-' . $con->sc_type : '';
				}
				if(file_exists( $this->_dir . $v . '.htm' )) {
					$frame = $this->read($v);
				}
				else {
					$frame = $this->read('main_frame');
				}
			}
			else {
				message_die(_GENERAL, 'S_Template_Missing', 'D_Template_Missing', $db->_errlog);
			}
		}
		include( $config->_root . 'extensions/header.php');
		include( $config->_root . 'extensions/footer.php');
		echo $header . $frame . $footer;
		return;
	}
	
	/*
	 * function loadContainer
	 * ----------------------
	 * Takes $container, loads its modules, and returns to the HTML. 
	 */
	 
	 function loadContainer( $container )
	{
		unset($this->parse);
		while(list($k, $v) = @each($this->modules->$container)) {
			$this->parse[$k] = $this->modules->{$container}[$k];
		}
		if($this->parse) {
			$file = ($this->constyles[$container]->sc_type) ? $this->constyles[$container]->sc_type . '_box' : 'v_box';
			echo $this->read($file);
		} else {
			echo $this->read('overall_body');
		}
		return;
	}
	
	/*
	 * function loadModule
	 * -------------------
	 * Takes $module, loads it, and if enabled, caches it. Also sets the 
	 * CSS suffix to $style.
	 */
	 
	 function loadModule( $module )
	{
		global $config;
		if(file_exists($config->_root . 'modules/' . $module['sm_filename'] . '.php')) {
			$this->_here = $module;
			echo $this->read($module['sm_filename'], '', $module['sm_id'], $module['sm_cache']);
		}
		else {
			message_die(_GENERAL, 'S_Template_Missing', 'D_Template_Missing', $db->_errlog);
		}
		return;
	}
	
	/*
	 * function read
	 * --------------
	 * Attempts to read HTML from cache.  Ends if successful.  Otherwise,
	 * executes $query, parses $filename, and, if cache is enabled, writes
	 * to cache file.
	 */
	 
	function read($filename, $query='', $item='', $cache_enabled=false)
	{
		global $db, $config, $my, $seesion, $auths, $module;
		
		$data = null;
		if($query != '') {
			if ( $config->data['enable_cache'] && $cache_enabled ) {
				@include($this->_cache_root.$this->_tpl.'.'.$this->_lang.'.'.$filename.'.'.$item.'.php');
				@include($this->_cache_root.$this->_tpl.'.'.$this->_lang.'.'.$filename.'.php');
				if (!empty($gentime) && $cache_key == $config->data['cache_key']) {
					if($gentime > (time() - $config->data['recache_tpl'])) {
						if ($db->_debug == 2) {
							$db->_log[] = "SQL Query:<br /> <span style=\"color:#ff0000\">(cached)</span><br />" . $sql;
						}
						return stripslashes($data);
					}
				}
			}
			$db->setQuery($query);
			$sql = $db->_query;
			if (!($rows = $db->loadObjectList())) {
				message_die(_GENERAL, 'S_Template_Missing', 'D_Template_Missing', $db->_errlog);
			}
		}
		ob_start();
		if(@file_exists($config->_root . 'modules/' . $filename . '.php')) {
			@include($config->_root . 'modules/' . $filename . '.php');
		}
		elseif(@file_exists($this->_dir . $filename . '.htm')) {
			@include($this->_dir . $filename . '.htm');
		}
		else {
			message_die(_GENERAL, $filename, 'D_Template_Missing', $db->_errlog);
		}
		$data = addslashes(ob_get_clean());
		if ( !$config->data['enable_cache'] || !$cache_enabled ) {
			return stripslashes($data);
		}
		$fmt_file = '<' . "?php\n" . 
		"// Generated : %s (GMT)\n" . 
		'$gentime = %s;'."\n" . 
		'$cache_key = \'%s\';'."\n" .
		'$sql = \'%s\';'."\n" . 
		'$data = "%s";'."\n" . 
		'?' . '>';
		
		if($item) {
			$handle = @fopen($this->_cache_root.$this->_tpl.'.'.$this->_lang.'.'.$filename.'.'.$item.'.php', 'w');
		} else {
			$handle = @fopen($this->_cache_root.$this->_tpl.'.'.$this->_lang.'.'.$filename.'.php', 'w');
		}
		@flock($handle, LOCK_EX);
		@fwrite($handle, sprintf($fmt_file, date('Y-m-d H:i:s'), time(), $this->_cache_key, $sql, $data));
		@flock($handle, LOCK_UN);
		@fclose($handle);
		@umask(0000);
		@chmod($handle, 0666);
		return stripslashes($data);
	}
} // END class style

?>