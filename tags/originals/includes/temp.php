<?php

// database.php

/**
* mosDBTable Abstract Class.
*
* Parent classes to all database derived objects.  Customisation will generally
* not involve tampering with this object.
* @package Mambo
* @author Andrew Eddie <eddieajau@users.sourceforge.net
*/
class mosDBTable {
	/** @var string Name of the table in the db schema relating to child class */
	var $_tbl = '';
	/** @var string Name of the primary key field in the table */
	var $_tbl_key = '';
	/** @var string Error message */
	var $_error = '';
	/** @var mosDatabase Database connector */
	var $_db = null;

	/**
	*	Object constructor to set table and key field
	*
	*	Can be overloaded/supplemented by the child class
	*	@param string $table name of the table in the db schema relating to child class
	*	@param string $key name of the primary key field in the table
	*/
	function mosDBTable( $table, $key, &$db ) {
		$this->_tbl = $table;
		$this->_tbl_key = $key;
		$this->_db =& $db;
	}
	/**
	*	@return string Returns the error message
	*/
	function getError() {
		return $this->_error;
	}
	/**
	* Gets the value of the class variable
	* @param string The name of the class variable
	* @return mixed The value of the class var (or null if no var of that name exists)
	*/
	function get( $_property ) {
		if(isset( $this->$_property )) {
			return $this->$_property;
		} else {
			return null;
		}
	}
	/**
	* Set the value of the class variable
	* @param string The name of the class variable
	* @param mixed The value to assign to the variable
	*/
	function set( $_property, $_value ) {
		$this->$_property = $_value;
	}
	/**
	*	binds a named array/hash to this object
	*
	*	can be overloaded/supplemented by the child class
	*	@param array $hash named array
	*	@return null|string	null is operation was satisfactory, otherwise returns an error
	*/
	function bind( $array, $ignore="" ) {
		if (!is_array( $array )) {
			$this->_error = strtolower(get_class( $this ))."::bind failed.";
			return false;
		} else {
			return mosBindArrayToObject( $array, $this, $ignore );
		}
	}

	/**
	*	binds an array/hash to this object
	*	@param int $oid optional argument, if not specifed then the value of current key is used
	*	@return any result from the database operation
	*/
	function load( $oid=null ) {
		$k = $this->_tbl_key;
		if ($oid !== null) {
			$this->$k = $oid;
		}
		$oid = $this->$k;
		if ($oid === null) {
			return false;
		}
		$this->_db->setQuery( "SELECT * FROM $this->_tbl WHERE $this->_tbl_key='$oid'" );
		return $this->_db->loadObject( $this );
	}

	/**
	*	generic check method
	*
	*	can be overloaded/supplemented by the child class
	*	@return boolean True if the object is ok
	*/
	function check() {
		return true;
	}

	/**
	* Inserts a new row if id is zero or updates an existing row in the database table
	*
	* Can be overloaded/supplemented by the child class
	* @param boolean If false, null object variables are not updated
	* @return null|string null if successful otherwise returns and error message
	*/
	function store( $updateNulls=false ) {
		$k = $this->_tbl_key;
		global $migrate;
		if( $this->$k && !$migrate) {
			$ret = $this->_db->updateObject( $this->_tbl, $this, $this->_tbl_key, $updateNulls );
		} else {
			$ret = $this->_db->insertObject( $this->_tbl, $this, $this->_tbl_key );
		}
		if( !$ret ) {
			$this->_error = strtolower(get_class( $this ))."::store failed <br />" . $this->_db->getErrorMsg();
			return false;
		} else {
			return true;
		}
	}
	/**
	*/
	function move( $dirn, $where='' ) {
		$k = $this->_tbl_key;

		$sql = "SELECT $this->_tbl_key, ordering FROM $this->_tbl";

		if ($dirn < 0) {
			$sql .= "\nWHERE ordering < $this->ordering";
			$sql .= ($where ? "\n	AND $where" : '');
			$sql .= "\nORDER BY ordering DESC\nLIMIT 1";
		} else if ($dirn > 0) {
			$sql .= "\nWHERE ordering > $this->ordering";
			$sql .= ($where ? "\n	AND $where" : '');
			$sql .= "\nORDER BY ordering\nLIMIT 1";
		} else {
			$sql .= "\nWHERE ordering = $this->ordering";
			$sql .= ($where ? "\n	AND $where" : '');
			$sql .= "\nORDER BY ordering\nLIMIT 1";
		}

		$this->_db->setQuery( $sql );
//echo 'A: ' . $this->_db->getQuery();


		$row = null;
		if ($this->_db->loadObject( $row )) {
			$this->_db->setQuery( "UPDATE $this->_tbl SET ordering='$row->ordering'"
			. "\nWHERE $this->_tbl_key='".$this->$k."'"
			);

			if (!$this->_db->query()) {
			    $err = $this->_db->getErrorMsg();
			    die( $err );
			}
//echo 'B: ' . $this->_db->getQuery();

			$this->_db->setQuery( "UPDATE $this->_tbl SET ordering='$this->ordering'"
			. "\nWHERE $this->_tbl_key='".$row->$k."'"
			);
//echo 'C: ' . $this->_db->getQuery();

			if (!$this->_db->query()) {
			    $err = $this->_db->getErrorMsg();
			    die( $err );
			}

			$this->ordering = $row->ordering;
		} else {
			$this->_db->setQuery( "UPDATE $this->_tbl SET ordering='$this->ordering'"
			. "\nWHERE $this->_tbl_key='".$this->$k."'"
			);
//echo 'D: ' . $this->_db->getQuery();


			if (!$this->_db->query()) {
			    $err = $this->_db->getErrorMsg();
			    die( $err );
			}
		}
	}
	/**
	* Compacts the ordering sequence of the selected records
	* @param string Additional where query to limit ordering to a particular subset of records
	*/
	function updateOrder( $where='' ) {
		$k = $this->_tbl_key;

		if (!array_key_exists( 'ordering', get_class_vars( strtolower(get_class( $this )) ) )) {
			$this->_error = "WARNING: ".strtolower(get_class( $this ))." does not support ordering.";
			return false;
		}

		if ($this->_tbl == "#__content_frontpage") {
			$order2 = ", content_id DESC";
		} else {
			$order2 = "";
		}

		$this->_db->setQuery( "SELECT $this->_tbl_key, ordering FROM $this->_tbl"
		. ($where ? "\nWHERE $where" : '')
		. "\nORDER BY ordering".$order2
		);
		if (!($orders = $this->_db->loadObjectList())) {
			$this->_error = $this->_db->getErrorMsg();
			return false;
		}
		// first pass, compact the ordering numbers
		for ($i=0, $n=count( $orders ); $i < $n; $i++) {
			if ($orders[$i]->ordering >= 0) {
				$orders[$i]->ordering = $i+1;
			}
		}

		$shift = 0;
		$n=count( $orders );
		for ($i=0; $i < $n; $i++) {
			//echo "i=$i id=".$orders[$i]->$k." order=".$orders[$i]->ordering;
			if ($orders[$i]->$k == $this->$k) {
				// place 'this' record in the desired location
				$orders[$i]->ordering = min( $this->ordering, $n );
				$shift = 1;
			} else if ($orders[$i]->ordering >= $this->ordering && $this->ordering > 0) {
				$orders[$i]->ordering++;
			}
		}
	//echo '<pre>';print_r($orders);echo '</pre>';
		// compact once more until I can find a better algorithm
		for ($i=0, $n=count( $orders ); $i < $n; $i++) {
			if ($orders[$i]->ordering >= 0) {
				$orders[$i]->ordering = $i+1;
				$this->_db->setQuery( "UPDATE $this->_tbl"
				. "\nSET ordering='".$orders[$i]->ordering."' WHERE $k='".$orders[$i]->$k."'"
				);
				$this->_db->query();
	//echo '<br />'.$this->_db->getQuery();
			}
		}

		// if we didn't reorder the current record, make it last
		if ($shift == 0) {
			$order = $n+1;
			$this->_db->setQuery( "UPDATE $this->_tbl"
			. "\nSET ordering='$order' WHERE $k='".$this->$k."'"
			);
			$this->_db->query();
	//echo '<br />'.$this->_db->getQuery();
		}
		return true;
	}
	/**
	*	Generic check for whether dependancies exist for this object in the db schema
	*
	*	can be overloaded/supplemented by the child class
	*	@param string $msg Error message returned
	*	@param int Optional key index
	*	@param array Optional array to compiles standard joins: format [label=>'Label',name=>'table name',idfield=>'field',joinfield=>'field']
	*	@return true|false
	*/
	function canDelete( $oid=null, $joins=null ) {
		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = intval( $oid );
		}
		if (is_array( $joins )) {
			$select = "$k";
			$join = "";
			foreach( $joins as $table ) {
				$select .= ",\nCOUNT(DISTINCT {$table['idfield']}) AS {$table['idfield']}";
				$join .= "\nLEFT JOIN {$table['name']} ON {$table['joinfield']} = $k";
			}
			$this->_db->setQuery( "SELECT $select\nFROM $this->_tbl\n$join\nWHERE $k = ".$this->$k." GROUP BY $k" );

			if ($obj = $this->_db->loadObject()) {
				$this->_error = $this->_db->getErrorMsg();
				return false;
			}
			$msg = array();
			foreach( $joins as $table ) {
				$k = $table['idfield'];
				if ($obj->$k) {
					$msg[] = $AppUI->_( $table['label'] );
				}
			}

			if (count( $msg )) {
				$this->_error = "noDeleteRecord" . ": " . implode( ', ', $msg );
				return false;
			} else {
				return true;
			}
		}

		return true;
	}

	/**
	*	Default delete method
	*
	*	can be overloaded/supplemented by the child class
	*	@return true if successful otherwise returns and error message
	*/
	function delete( $oid=null ) {
		//if (!$this->canDelete( $msg )) {
		//	return $msg;
		//}

		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = intval( $oid );
		}

		$this->_db->setQuery( "DELETE FROM $this->_tbl WHERE $this->_tbl_key = '".$this->$k."'" );

		if ($this->_db->query()) {
			return true;
		} else {
			$this->_error = $this->_db->getErrorMsg();
			return false;
		}
	}

	function checkout( $who, $oid=null ) {
		if (!array_key_exists( 'checked_out', get_class_vars( strtolower(get_class( $this )) ) )) {
			$this->_error = "WARNING: ".strtolower(get_class( $this ))." does not support checkouts.";
			return false;
		}
		$k = $this->_tbl_key;
		if ($oid !== null) {
			$this->$k = $oid;
		}
		$time = date( "%Y-%m-%d H:i:s" );
		if (intval( $who )) {
			// new way of storing editor, by id
			$this->_db->setQuery( "UPDATE $this->_tbl"
			. "\nSET checked_out='$who', checked_out_time='$time'"
			. "\nWHERE $this->_tbl_key='".$this->$k."'"
			);
		} else {
			// old way of storing editor, by name
			$this->_db->setQuery( "UPDATE $this->_tbl"
			. "\nSET checked_out='1', checked_out_time='$time', editor='".$who."' "
			. "\nWHERE $this->_tbl_key='".$this->$k."'"
			);
		}
		return $this->_db->query();
	}

	function checkin( $oid=null ) {
		if (!array_key_exists( 'checked_out', get_class_vars( strtolower(get_class( $this )) ) )) {
			$this->_error = "WARNING: ".strtolower(get_class( $this ))." does not support checkin.";
			return false;
		}
		$k = $this->_tbl_key;
		if ($oid !== null) {
			$this->$k = $oid;
		}
		$time = date("H:i:s");
		$this->_db->setQuery( "UPDATE $this->_tbl"
		. "\nSET checked_out='0', checked_out_time='0000-00-00 00:00:00'"
		. "\nWHERE $this->_tbl_key='".$this->$k."'"
		);
		return $this->_db->query();
	}

	function hit( $oid=null ) {
		global $mosConfig_enable_log_items;

		$k = $this->_tbl_key;
		if ($oid !== null) {
			$this->$k = intval( $oid );
		}
		$this->_db->setQuery( "UPDATE $this->_tbl SET hits=(hits+1) WHERE $this->_tbl_key='$this->id'" );
		$this->_db->query();

		if (@$mosConfig_enable_log_items) {
			$now = date( "Y-m-d" );
			$this->_db->setQuery( "SELECT hits"
			. "\nFROM #__core_log_items"
			. "\nWHERE time_stamp='$now' AND item_table='$this->_tbl' AND item_id='".$this->$k."'"
			);
			$hits = intval( $this->_db->loadResult() );
			if ($hits) {
				$this->_db->setQuery( "UPDATE #__core_log_items SET hits=(hits+1)"
				. "\nWHERE time_stamp='$now' AND item_table='$this->_tbl' AND item_id='".$this->$k."'"
				);
				$this->_db->query();
			} else {
				$this->_db->setQuery( "INSERT INTO #__core_log_items VALUES"
				. "\n('$now','$this->_tbl','".$this->$k."','1')"
				);
				$this->_db->query();
			}
		}
	}

	/**
	* Generic save function
	* @param array Source array for binding to class vars
	* @param string Filter for the order updating
	* @returns TRUE if completely successful, FALSE if partially or not succesful.
	*/
	function save( $source, $order_filter ) {
		if (!$this->bind( $_POST )) {
			return false;
		}
		if (!$this->check()) {
			return false;
		}
		if (!$this->store()) {
			return false;
		}
		if (!$this->checkin()) {
			return false;
		}
		$filter_value = $this->$order_filter;
		$this->updateOrder( $order_filter ? "`$order_filter`='$filter_value'" : "" );
		$this->_error = '';
		return true;
	}

	/**
	* Generic Publish/Unpublish function
	* @param array An array of id numbers
	* @param integer 0 if unpublishing, 1 if publishing
	* @param integer The id of the user performnig the operation
	*/
	function publish_array( $cid=null, $publish=1, $myid=0 ) {
		if (!is_array( $cid ) || count( $cid ) < 1) {
			$this->_error = "No items selected.";
			return false;
		}

		$cids = implode( ',', $cid );

		$this->_db->setQuery( "UPDATE $this->_tbl SET published='$publish'"
		. "\nWHERE $this->_tbl_key IN ($cids) AND (checked_out=0 OR (checked_out='$myid'))"
		);
		if (!$this->_db->query()) {
			$this->_error = $this->_db->getErrorMsg();
			return false;
		}

		if (count( $cid ) == 1) {
			$this->checkin( $cid[0] );
		}
		$this->_error = '';
		return true;
	}

	/**
	* Export item list to xml
	* @param boolean Map foreign keys to text values
	*/
	function toXML( $mapKeysToText=false ) {
		$xml = '<record table="' . $this->_tbl . '"';
		if ($mapKeysToText) {
			$xml .= ' mapkeystotext="true"';
		}
		$xml .= '>';
		foreach (get_object_vars( $this ) as $k => $v) {
			if (is_array($v) or is_object($v) or $v === NULL) {
				continue;
			}
			if ($k[0] == '_') { // internal field
				continue;
			}
			$xml .= '<' . $k . '><![CDATA[' . $v . ']]></' . $k . '>';
		}
		$xml .= '</record>';

		return $xml;
	}
}

/* Here are the functions from Mambo unused here

	/*
	 * Escapes a string for MySQL
	 */
	function getEscaped( $text ) {
		return mysql_escape_string( $text );
	}

	/*
	 * Prepares text for screen output as a quote
	 */
	function Quote( $text ) {
		return '\'' . mysql_escape_string( $text ) . '\'';
	}

	/*
	 * Return SQL Explain for error
	 */
	function explain() {
		$temp = $this->_query;
		$this->_sql = "EXPLAIN $this->_query";
		$this->query();

		if (!($cur = $this->query())) {
			return null;
		}
		$first = true;

		$buf = "<table cellspacing=\"1\" cellpadding=\"2\" border=\"0\" bgcolor=\"#000000\" align=\"center\">";
		$buf .= $this->getQuery();
		while ($row = mysql_fetch_assoc( $cur )) {
			if ($first) {
				$buf .= "<tr>";
				foreach ($row as $k=>$v) {
					$buf .= "<th bgcolor=\"#ffffff\">$k</th>";
				}
				$buf .= "</tr>";
				$first = false;
			}
			$buf .= "<tr>";
			foreach ($row as $k=>$v) {
				$buf .= "<td bgcolor=\"#ffffff\">$v</td>";
			}
			$buf .= "</tr>";
		}
		$buf .= "</table><br />&nbsp;";
		mysql_free_result( $cur );

		$this->_sql = $temp;

		return "<div style=\"background-color:#FFFFCC\" align=\"left\">$buf</div>";
	}

	/**
	* @return The first row of the query.
	*/
	function loadRow() {
		if (!($cur = $this->query())) {
			return null;
		}
		$ret = null;
		if ($row = mysql_fetch_row( $cur )) {
			$ret = $row;
		}
		mysql_free_result( $cur );
		return $ret;
	}

	/**
	* Load a list of database rows (numeric column indexing)
	* @param string The field name of a primary key
	* @return array If <var>key</var> is empty as sequential list of returned records.
	* If <var>key</var> is not empty then the returned array is indexed by the value
	* the database key.  Returns <var>null</var> if the query fails.
	*/
	function loadRowList( $key='' ) {
		if (!($cur = $this->query())) {
			return null;
		}
		$array = array();
		while ($row = mysql_fetch_array( $cur )) {
			if ($key) {
				$array[$row[$key]] = $row;
			} else {
				$array[] = $row;
			}
		}
		mysql_free_result( $cur );
		return $array;
	}
	
	/**
	* @param boolean If TRUE, displays the last SQL statement sent to the database
	* @return string A standised error message
	*/
	function stderr( $showSQL = false ) {
		return "DB function failed with error number $this->_errorNum"
		."<br /><font color=\"red\">$this->_errorMsg</font>"
		.($showSQL ? "<br />SQL = <pre>$this->_sql</pre>" : '');
	}

	function insertid()
	{
		return mysql_insert_id();
	}
	
	/**
	* Fudge method for ADOdb compatibility
	*/
	function GenID( $foo1=null, $foo2=null ) {
		return '0';
	}





// template.php

// internal xs mod definitions. do not edit.
define('XS_TAG_NONE', 0);
define('XS_TAG_PHP', 1);
define('XS_TAG_BEGIN', 2);
define('XS_TAG_END', 3);
define('XS_TAG_INCLUDE', 4);
define('XS_TAG_IF', 5);
define('XS_TAG_ELSE', 6);
define('XS_TAG_ELSEIF', 7);
define('XS_TAG_ENDIF', 8);
define('XS_TAG_DEFINE', 9);
define('XS_TAG_UNDEFINE', 10);
define('XS_TAG_BEGINELSE', 11);

	// Hash of filenames for each template handle.
	var $files = array();
	var $files_cache = array(); // array of cache files that exists
	var $files_cache2 = array(); // array of cache files (exists or not exists)
	// Search/replace for unknown files
	var $cache_search = array();
	var $cache_replace = array();
	// this will hash handle names to the compiled code for that handle.
	var $compiled_code = array();
	// This will hold the uncompiled code for that handle.
	var $uncompiled_code = array();
	// counter for include
	var $include_count = 0;
	// These handles will be parsed if pparse() is executed.
	// Can be used to automatically include header/footer if there is any content.
	var $preparse = '';
	var $postparse = '';
	
	
	/**
	 * Block-level variable assignment. Adds a new block iteration with the given
	 * variable assignments. Note that this should only be called once per block
	 * iteration.
	 */
	function assign_block_vars($blockname, $vararray)
	{
		if (strstr($blockname, '.'))
		{
			// Nested block.
			$blocks = explode('.', $blockname);
			$blockcount = sizeof($blocks) - 1;

			$str = &$this->_tpldata; 
			for($i = 0; $i < $blockcount; $i++) 
			{ 
				$str = &$str[$blocks[$i].'.']; 
				$str = &$str[sizeof($str)-1]; 
			} 
			// Now we add the block that we're actually assigning to. 
			// We're adding a new iteration to this block with the given 
			//	variable assignments. 
			$str[$blocks[$blockcount].'.'][] = $vararray;
		}
		else
		{
			// Top-level block.
			// Add a new iteration to this block with the variable assignments
			// we were given.
			$this->_tpldata[$blockname.'.'][] = $vararray;
		}

		return true;
	}
	
	/**
	 * Root-level variable assignment. Adds to current assignments, overriding
	 * any existing variable assignment with the same name.
	 */
	function assign_var($varname, $varval)
	{
		$this->vars[$varname] = $varval;

		return true;
	}
		
	/**
	 * Destroys this template object. Should be called when you're done with it, in order
	 * to clear out the template data so you can load/parse a new template set.
	 */
	function destroy()
	{
		$this->_tpldata = array('.' => array(0 => array()));
		$this->vars = &$this->_tpldata['.'][0];
	}

	/**
	 * Clears list of compiled files.
	 */
	function clear_files()
	{
		$this->files = array();
		$this->files_cache = array();
		$this->files_cache2 = array();
		$this->compiled_code = array();
		$this->uncompiled_code = array();
	}

	/**
	 * Loads replacements from .cfg file
	 */
	function load_replacements($file)
	{
		if(@file_exists($file))
		{
			$replace = array();
			@include($file);
			$this->replace = array_merge($this->replace, $replace);
		}
	}

	/**
	 * Sets the template filenames for handles. $filename_array
	 * should be a hash of handle => filename pairs.
	 */
	function set_filenames($filename_array)
	{
		if (!is_array($filename_array))
		{
			return false;
		}

		foreach($filename_array as $handle => $filename)
		{
			$this->set_filename($handle, $filename);
		}

		return true;
	}


	/**
	 * Assigns template filename for handle.
	 */
	function set_filename($handle, $filename, $quiet = false)
	{
		global $config;
		if(strpos($filename, '..') !== false)
		{
			$can_cache = false;
		}
		$this->files[$handle] = $this->_dir . $filename . '.html';
		$this->files_cache[$handle] = '';
		$this->files_cache2[$handle] = '';
		// checking if we have valid filename
		if(!$this->files[$handle])
		{
			if($quiet)
			{
				return false;
			}
			else
			{
				message_die('_STYLE', '_file');
			}
		}
		// creating cache filename
		if($can_cache)
		{
			$this->files_cache2[$handle] = $config->_root . 'cache/tpl_' . $this->_tpl . '.' . $filename . '.php';
			if(@file_exists($this->files_cache2[$handle]))
			{
				$this->files_cache[$handle] = $this->files_cache2[$handle];
			}
		}
		// checking if tpl and/or php file exists
		if(empty($this->files_cache[$handle]) && !@file_exists($this->files[$handle]))
		{
			if($quiet)
			{
				return false;
			}
			else
			{
				message_die('_STYLE', '_missing');
			}
		}
		// checking if we should recompile cache
		if(!empty($this->files_cache[$handle]) && !empty($config->data['auto_recompile']))
		{
			$cache_time = @filemtime($this->files_cache[$handle]);
			if(@filemtime($this->files[$handle]) > $cache_time || $board_config['xs_template_time'] > $cache_time)
			{	
				// file was changed. don't use cache file (will be recompled if configuration allowes it)
				$this->files_cache[$handle] = '';
			}
		}
		return true;
	}

	/**
	 * includes file or executes code
	 */
	function execute($filename, $code, $handle)
	{
		global $lang, $theme, $board_config;
		$template = $theme['template_name'];
		global $$template;
		$theme_info = &$$template;
		if($board_config['xs_add_comments'] && $handle)
		{
			echo '<!-- template ', $this->files[$handle], ' start -->';
		}
		if($filename)
		{
			include($filename);
		}
		else
		{
			eval($code);
		}
		if($board_config['xs_add_comments'] && $handle)
		{
			echo '<!-- template ', $this->files[$handle], ' end -->';
		}
		return true;
	}

	/**
	 * Load the file for the handle, compile the file,
	 * and run the compiled code. This will print out
	 * the results of executing the template.
	 */
	function pparse($handle)
	{
		global $board_config;
		// parsing header if there is one
		if($this->preparse || $this->postparse)
		{
			$preparse = $this->preparse;
			$postparse = $this->postparse;
			$this->preparse = '';
			$this->postparse = '';
			if($preparse)
			{
				$this->pparse($preparse);
			}
			if($postparse)
			{
				$str = $handle;
				$handle = $postparse;
				$this->pparse($str);
			}
		}
		// checking if handle exists
		if (empty($this->files[$handle]) && empty($this->files_cache[$handle]))
		{
			die("Template->loadfile(): No files found for handle $handle");
		}
		$this->xs_startup();
		$force_recompile = empty($this->uncompiled_code[$handle]) ? false : true;
		// checking if php file exists.
		if (!empty($this->files_cache[$handle]) && !$force_recompile)
		{
			// php file exists - running it instead of tpl
			$this->execute($this->files_cache[$handle], '', $handle);
			return true;
		}
		if (!$this->loadfile($handle))
		{
			die("Template->pparse(): Couldn't load template file for handle $handle");
		}
		// actually compile the template now.
		if (empty($this->compiled_code[$handle]))
		{
			// Actually compile the code now.
			if(!empty($this->files_cache2[$handle]) && empty($this->files_cache[$handle]) && !$force_recompile)
			{
				$this->compiled_code[$handle] = $this->compile2($this->uncompiled_code[$handle], $handle, $this->files_cache2[$handle]);
			}
			else
			{
				$this->compiled_code[$handle] = $this->compile2($this->uncompiled_code[$handle], '', '');
			}
		}
		// Run the compiled code.
		if (empty($this->files_cache[$handle]) || $force_recompile)
		{
			$this->execute('', $this->compiled_code[$handle], $handle);
		}
		else
		{
			$this->execute($this->files_cache[$handle], '', $handle);
		}
		return true;
	}

	/**
	 * Precompile file
	 */
	function precompile($template, $filename)
	{
		global $precompile_num, $board_config;
		if(empty($precompile_num))
		{
			$precompile_num = 0;
		}
		$precompile_num ++;
		$handle = 'precompile_' . $precompile_num;
		// save old configuration
		$root = $this->root;
		$tpl_name = $this->tpl;
		$old_config = $this->use_cache;
		$old_autosave = $this->auto_compile;
		// set temporary configuration
		$this->root = $this->tpldir . $template;
		$this->tpl = $template;
		$this->use_cache = 1;
		$this->auto_compile = 1;
		// set filename
		$res = $this->set_filename($handle, $filename, true, true);
		if(!$res || !$this->files_cache2[$handle])
		{
			$this->root = $root;
			$this->tpl = $tpl_name;
			$this->use_cache = $old_config;
			$this->auto_compile = $old_autosave;
			return false;
		}
		$this->files_cache[$handle] = '';
		// load template
		$res = $this->loadfile($handle);
		if(!$res || empty($this->uncompiled_code[$handle]))
		{
			$this->root = $root;
			$this->tpl = $tpl_name;
			$this->use_cache = $old_config;
			$this->auto_compile = $old_autosave;
			return false;
		}
		// compile the code
		$this->compile2($this->uncompiled_code[$handle], $handle, $this->files_cache2[$handle]);
		// restore confirugation
		$this->root = $root;
		$this->tpl = $tpl_name;
		$this->use_cache = $old_config;
		$this->auto_compile = $old_autosave;
		return true;
	}

	/**
	 * Inserts the uncompiled code for $handle as the
	 * value of $varname in the root-level. This can be used
	 * to effectively include a template in the middle of another
	 * template.
	 * Note that all desired assignments to the variables in $handle should be done
	 * BEFORE calling this function.
	 */
	function assign_var_from_handle($varname, $handle)
	{
		ob_start();
		$res = $this->pparse($handle);
		$this->vars[$varname] = ob_get_contents();
		ob_end_clean();
		return $res;
	}

	/**
	 * If not already done, load the file for the given handle and populate
	 * the uncompiled_code[] hash with its code. Do not compile.
	 */
	function loadfile($handle)
	{
		global $board_config;
		// If cached file exists do nothing - it will be included via include()
		if(!empty($this->files_cache[$handle]))
		{
			return true;
		}

		// If the file for this handle is already loaded and compiled, do nothing.
		if (!empty($this->uncompiled_code[$handle]))
		{
			return true;
		}

		// If we don't have a file assigned to this handle, die.
		if (empty($this->files[$handle]))
		{
			die("Template->loadfile(): No file specified for handle $handle");
		}

		$filename = $this->files[$handle];

		$str = implode('', @file($filename));
		if (empty($str))
		{
			die("Template->loadfile(): File $filename for handle $handle is empty");
		}

		$this->uncompiled_code[$handle] = $str;

		return true;
	}



	/**
	 * Generates a reference to the given variable inside the given (possibly nested)
	 * block namespace. This is a string of the form:
	 * ' . $this->_tpldata['parent.'][$_parent_i]['$child1.'][$_child1_i]['$child2.'][$_child2_i]...['varname'] . '
	 * It's ready to be inserted into an "echo" line in one of the templates.
	 * NOTE: expects a trailing "." on the namespace.
	 */
	function generate_block_varref($namespace, $varname, $use_isset = true)
	{
		// Strip the trailing period.
		$namespace = substr($namespace, 0, strlen($namespace) - 1);

		// Get a reference to the data block for this namespace.
		$varref = $this->generate_block_data_ref($namespace, true);
		// Prepend the necessary code to stick this in an echo line.

		// Append the variable reference.
		$varref .= '[\'' . $varname . '\']';

		if($use_isset)
		{
			$varref = '<'.'?php echo isset(' . $varref . ') ? ' . $varref . ' : \'\'; ?'.'>';
		}
		else
		{
			$varref = '<'.'?php echo ' . $varref . '; ?'.'>';
		}

		return $varref;

	}

	/**
	 * Root-level variable assignment. Adds to current assignments, overriding
	 * any existing variable assignment with the same name.
	 */
	function assign_vars($vararray, $block='')
	{
		if($block != '') {
			foreach($vararray as $key => $val)
			{
				$this->vars[$block][$key] = $val;
			}
		}
		else {
			foreach($vararray as $key => $val)
			{
				$this->vars[$key] = $val;
			}
		}
		return true;
	}
	
	/**
	 * Generates a reference to the array of data values for the given
	 * (possibly nested) block namespace. This is a string of the form:
	 * $this->_tpldata['parent.'][$_parent_i]['$child1.'][$_child1_i]['$child2.'][$_child2_i]...['$childN.']
	 *
	 * If $include_last_iterator is true, then [$_childN_i] will be appended to the form shown above.
	 * NOTE: does not expect a trailing "." on the blockname.
	 */
	function generate_block_data_ref($blockname, $include_last_iterator, $defop = false)
	{
		// Get an array of the blocks involved.
		$blocks = explode('.', $blockname);
		$blockcount = sizeof($blocks) - 1;
		if($defop)
		{
			$varref = '$this->_tpldata[\'DEFINE\']';
			// Build up the string with everything but the last child.
			for ($i = 0; $i < $blockcount; $i++)
			{
				$varref .= "['" . $blocks[$i] . ".'][\$" . $blocks[$i] . '_i]';
			}
			// Add the block reference for the last child.
			$varref .= "['" . $blocks[$blockcount] . ".']";
			// Add the iterator for the last child if requried.
			if ($include_last_iterator)
			{
				$varref .= '[$' . $blocks[$blockcount] . '_i]';
			}
			return $varref;
		}
		if($include_last_iterator)
		{
			return '$'. $blocks[$blockcount]. '_item';
		}
		else
		{
			return '$'. $blocks[$blockcount-1]. '_item[\''. $blocks[$blockcount]. '.\']';
		}
	}

	function compile_code($filename, $code, $use_isset = false)
	{
		//	$filename - file to load code from. used if $code is empty
		//	$code - tpl code
		//	$use_isset - if false then compiled code looks more beautiful and easier
		//      to understand and it adds error_reporting() to supress php warnings.
		//      if true then isset() is used to check variables instead of supressing
		//	    php warnings. note: for extreme styles mod 2.x it works only for
		//		block variables and for usual variables its always true.

		// load code from file
		if(!$code && !empty($filename))
		{
			$code = @implode('', @file($filename));
		}

		// Replace phpBB 2.2 <!-- (END)PHP --> tags
		$search = array('<!-- PHP -->', '<!-- ENDPHP -->');
		$replace = array('<'.'?php ', ' ?'.'>');
		$code = str_replace($search, $replace, $code);

		// Break it up into lines and put " -->" back.
		$code_lines = explode(' -->', $code);
		$count = count($code_lines);
		for ($i = 0; $i < ($count - 1); $i++)
		{
			$code_lines[$i] .= ' -->';
		}

		$block_nesting_level = 0;
		$block_names = array();
		$block_names[0] = ".";
		$block_items = array();
		$count_if = 0;

		// prepare array for compiled code
		$compiled = array();
		$count_bugs = count($this->bugs);

		// array of switches
		$sw = array();

		// replace all short php tags
		$new_code = array();
		$line_count = count($code_lines);
		for($i=0; $i<$line_count; $i++)
		{
			$line = $code_lines[$i];
			$pos = strpos($line, '<?');
			if($pos === false)
			{
				$new_code[] = $line;
				continue;
			}
			if(substr($line, $pos, 5) === '<?php')
			{
				// valid php tag. skip it
				$new_code[] = substr($line, 0, $pos + 5);
				$code_lines[$i] = substr($line, $pos + 5);
				$i --;
				continue;
			}
			// invalid php tag
			$new_code[] = substr($line, 0, $pos) . '<?php echo \'<?\'; ?>';
			$code_lines[$i] = substr($line, $pos + 2);
			$i --;
		}
		$code_lines = $new_code;

		// main loop
		$line_count = count($code_lines);
		for($i=0; $i<$line_count; $i++)
		{
			$line = $code_lines[$i];
			// reset keyword type
			$keyword_type = XS_TAG_NONE;
			// check if we have valid keyword in current line
			$pos1 = strpos($line, '<!-- ');
			if($pos1 === false)
			{
				// no keywords in this line
				$compiled[] = $this->_compile_text($line, $use_isset);
				continue;
			}
			// find end of html comment
			$pos2 = strpos($line, ' -->', $pos1);
			if($pos2 !== false)
			{
				// find end of keyword in comment
				$pos3 = strpos($line, ' ', $pos1 + 5);
				if($pos3 !== false && $pos3 <= $pos2)
				{
					$keyword = substr($line, $pos1 + 5, $pos3 - $pos1 - 5);
					// check keyword against list of supported keywords. case-sensitive
					if($keyword === 'BEGIN')
					{
						$keyword_type = XS_TAG_BEGIN;
					}
					elseif($keyword === 'END')
					{
						$keyword_type = XS_TAG_END;
					}
					elseif($keyword === 'INCLUDE')
					{
						$keyword_type = XS_TAG_INCLUDE;
					}
					elseif($keyword === 'IF')
					{
						$keyword_type = XS_TAG_IF;
					}
					elseif($keyword === 'ELSE')
					{
						$keyword_type = XS_TAG_ELSE;
					}
					elseif($keyword === 'ELSEIF')
					{
						$keyword_type = XS_TAG_ELSEIF;
					}
					elseif($keyword === 'ENDIF')
					{
						$keyword_type = XS_TAG_ENDIF;
					}
					elseif($keyword === 'DEFINE')
					{
						$keyword_type = XS_TAG_DEFINE;
					}
					elseif($keyword === 'UNDEFINE')
					{
						$keyword_type = XS_TAG_UNDEFINE;
					}
					elseif($keyword === 'BEGINELSE')
					{
						$keyword_type = XS_TAG_BEGINELSE;
					}
				}
			}
			if(!$keyword_type)
			{
				// not valid keyword. process the rest of line
				$compiled[] = $this->_compile_text(substr($line, 0, $pos1 + 4), $use_isset);
				$code_lines[$i] = substr($line, $pos1 + 4);
				$i --;
				continue;
			}
			// remove code before keyword
			if($pos1 > 0)
			{
				$compiled[] = $this->_compile_text(substr($line, 0, $pos1), $use_isset);
			}
			// remove keyword
			$keyword_str = substr($line, $pos1, $pos2 - $pos1 + 4);
			$params_str = $pos2 == $pos3 ? '' : substr($line, $pos3 + 1, $pos2 - $pos3 - 1);
			$code_lines[$i] = substr($line, $pos2 + 4);
			$i--;
			// Check keywords

			/*
			* <!-- BEGIN -->
			*/
			if($keyword_type == XS_TAG_BEGIN)
			{
				$params = explode(' ', $params_str);
				$num_params = count($params);
				// get variable name
				if($num_params == 1)
				{
					$var = $params[0];

				}
				elseif($num_params == 2)
				{
					if($params[0] === '')
					{
						$var = $params[1];
					}
					elseif($params[1] === '')
					{
						$var = $params[0];
					}
					else
					{
						// invalid tag
						$compiled[] = $keyword_str;
						continue;
					}
				}
				else
				{
					// invalid tag
					$compiled[] = $keyword_str;
					continue;
				}
				// check variable for matching end
				if($this->xs_check_switches)
				{
					$found = 0;
					$str = '<!-- END ' . $var . ' -->';
					for ($j = $i+1; ($j < $line_count) && !$found; $j++)
					{
						$pos = strpos($code_lines[$j], $str);
						if($pos !== false)
						{
							$found = 1;
							$found_var = $var;
						}
					}
					if(!$found && ($this->xs_check_switches == 1))
					{
						// checking list of known buggy switches
						$item = -1;
						for($j=0; $j<$count_bugs; $j++)
						{
							if($this->bugs[$j][0] === $var)
							{
								$item = $j;
							}
						}
						if($item >= 0)
						{
							$str1 = '<!-- END ' . $this->bugs[$item][1] . ' -->';
							for ($j = $i+1; ($j < $line_count) && !$found; $j++)
							{
								$pos = strpos($code_lines[$j], $str1);
								if($pos !== false)
								{
									$found_var = $this->bugs[$item][1];
									$found = 1;
									$code_lines[$j] = str_replace($str, $str1, $code_lines[$j]);
								}
							}
						}
					}
					if(!$found)
					{
						$compiled[] = $keyword_str;
						continue;
					}
					// adding to list of switches
					if(isset($sw[$found_var]))
					{
						$sw[$found_var] ++;
					}
					else
					{
						$sw[$found_var] = 1;
					}
				}
				// adding code
				$block_nesting_level++;
				$block_names[$block_nesting_level] = $var;
				if(isset($block_items[$var]))
				{
					$block_items[$var] ++;
				}
				else
				{
					$block_items[$var] = 1;
				}
				if ($block_nesting_level < 2)
				{
					// Block is not nested.
					$line = '<'."?php\n\n";
					if($use_isset)
					{
						$line .= '$'. $var. '_count = ( isset($this->_tpldata[\''. $var. '.\']) ) ?  sizeof($this->_tpldata[\''. $var. '.\']) : 0;';
					}
					else
					{
						$line .= '$'. $var. '_count = sizeof($this->_tpldata[\''. $var. '.\']);';
					}
					$line .= "\n" . 'for ($'. $var. '_i = 0; $'. $var. '_i < $'. $var. '_count; $'. $var. '_i++)';
					$line .= "\n". '{'. "\n";
					$line .= ' $'. $var. '_item = &$this->_tpldata[\''. $var. '.\'][$'. $var. '_i];'."\n";
					$line .= " \${$var}_item['S_ROW_COUNT'] = \${$var}_i;\n";
					$line .= " \${$var}_item['S_NUM_ROWS'] = \${$var}_count;\n";
					$line .= "\n?".">";
				}
				else
				{
					// This block is nested.
					// Generate a namespace string for this block.
					$namespace = implode('.', $block_names);
					// strip leading period from root level..
					$namespace = substr($namespace, 2);
					// Get a reference to the data array for this block that depends on the
					// current indices of all parent blocks.
					$varref = $this->generate_block_data_ref($namespace, false);
					// Create the for loop code to iterate over this block.
					$line = '<'."?php\n\n";
					if($use_isset)
					{
						$line .= '$'. $var. '_count = ( isset('. $varref. ') ) ? sizeof('. $varref. ') : 0;';
					}
					else
					{
						$line .= '$'. $var. '_count = sizeof('. $varref. ');';
					}
					$line .= "\n". 'for ($'. $var. '_i = 0; $'. $var. '_i < $'. $var. '_count; $'. $var. '_i++)';
					$line .= "\n". '{'. "\n";
					$line .= ' $'. $var. '_item = &'. $varref. '[$'. $var. '_i];'."\n";
					$line .= " \${$var}_item['S_ROW_COUNT'] = \${$var}_i;\n";
					$line .= " \${$var}_item['S_NUM_ROWS'] = \${$var}_count;\n";
					$line .= "\n?".">";
				}
				$compiled[] = $line;
				continue;
			}
			/*
			* <!-- END -->
			*/
			if($keyword_type == XS_TAG_END)
			{
				$params = explode(' ', $params_str);
				$num_params = count($params);
				if($num_params == 1)
				{
					$var = $params[0];
				}
				elseif($num_params == 2 && $params[0] === '')
				{
					$var = $params[1];
				}
				elseif($num_params == 2 && $params[1] === '')
				{
					$var = $params[0];
				}
				else
				{
					$compiled[] = $keyword_str;
					continue;
				}
				if($this->xs_check_switches)
				{	
					// checking if this switch was opened
					if(!isset($sw[$var]) || ($sw[$var] < 1))
					{	
						// there is no opening switch
						$compiled[] = $keyword_str;
						continue;
					}
					$sw[$var] --;
				}
				// We have the end of a block.
				$line = '<'."?php\n\n";
				$line .= '} // END ' . $var . "\n\n";
				$line .= 'if(isset($' . $var . '_item)) { unset($' . $var . '_item); } ';
				$line .= "\n\n?".">";
				if(isset($block_items[$var]))
				{
					$block_items[$var] --;
				}
				else
				{
					$block_items[$var] = -1;
				}
				unset($block_names[$block_nesting_level]);
				$block_nesting_level--;
				$compiled[] = $line;
				continue;
			}
			/*
			* <!-- BEGINELSE -->
			*/
			if($keyword_type == XS_TAG_BEGINELSE)
			{
				if($block_nesting_level)
				{
					$var = $block_names[$block_nesting_level];
					$compiled[] = '<' . '?php } if(!$' . $var . '_count) { ?' . '>';
				}
				else
				{
					$compiled[] = $keyword_str;
					continue;
				}
			}
			/*
			* <!-- INCLUDE -->
			*/
			if($keyword_type == XS_TAG_INCLUDE)
			{
				$params = explode(' ', $params_str);
				$num_params = count($params);
				if($num_params != 1)
				{
					$compiled[] = $keyword_str;
					continue;
				}
				$line = '<'.'?php ';
				$filehash = md5($params_str . $this->include_count . time());
				$line .= ' $this->set_filename(\'xs_include_' . $filehash . '\', \'' . $params_str .'\', true); ';
				$line .= ' $this->pparse(\'xs_include_' . $filehash . '\'); ';
				$line .= ' ?'.'>';
				$this->include_count ++;
				$compiled[] = $line;
				continue;
			}
			/*
			* <!-- IF -->
			*/
			if($keyword_type == XS_TAG_IF || $keyword_type == XS_TAG_ELSEIF)
			{
				if(!$count_if)
				{
					$keyword_type = XS_TAG_IF;
				}
				$str = $this->compile_tag_if($params_str, $keyword_type == XS_TAG_IF ? false : true);
				if($str)
				{
					$compiled[] = '<?php ' . $str . ' ?>';
					if($keyword_type == XS_TAG_IF)
					{
						$count_if ++;
					}
				}
				else
				{
					$compiled[] = $keyword_str;
				}
				continue;
			}
			/*
			* <!-- ELSE -->
			*/
			if($keyword_type == XS_TAG_ELSE && $count_if > 0)
			{
				$compiled[] = '<?php } else { ?>';
				continue;
			}
			/*
			* <!-- ENDIF -->
			*/
			if($keyword_type == XS_TAG_ENDIF && $count_if > 0)
			{
				$compiled[] = '<?php } ?>';
				$count_if --;
				continue;
			}
			/*
			* <!-- DEFINE -->
			*/
			if($keyword_type == XS_TAG_DEFINE)
			{
				$str = $this->compile_tag_define($params_str);
				if($str)
				{
					$compiled[] = '<?php ' . $str . ' ?>';
				}
				else
				{
					$compiled[] = $keyword_str;
				}
			}
			/*
			* <!-- UNDEFINE -->
			*/
			if($keyword_type == XS_TAG_UNDEFINE)
			{
				$str = $this->compile_tag_undefine($params_str);
				if($str)
				{
					$compiled[] = '<?php ' . $str . ' ?>';
				}
				else
				{
					$compiled[] = $keyword_str;
				}
			}
		}
		
		// bring it back into a single string.
		$code_header = '';
		$code_footer = '';
		if(!$use_isset)
		{
			$code_header =	"<". "?php\n\$old_level = @error_reporting(E_ERROR | E_WARNING | E_PARSE); \n?".">";
			$code_footer = '<'."?php @error_reporting(\$old_level); ?".'>';
		}

		return $code_header . implode('', $compiled) . $code_footer;
	}

	/*
	* Compile code between tags
	*/
	function _compile_text($code, $use_isset)
	{
		if(strlen($code) < 3)
		{
			return $code;
		}
		// change template varrefs into PHP varrefs
		// This one will handle varrefs WITH namespaces
		$varrefs = array();
		preg_match_all('#\{(([a-z0-9\-_]+?\.)+?)([a-z0-9\-_]+?)\}#is', $code, $varrefs);
		$varcount = sizeof($varrefs[1]);
		$search = array();
		$replace = array();
		for ($i = 0; $i < $varcount; $i++)
		{
			$namespace = $varrefs[1][$i];
			$varname = $varrefs[3][$i];
			$new = $this->generate_block_varref($namespace, $varname, $use_isset);
			$search[] = $varrefs[0][$i];
			$replace[] = $new;
		}
		if(count($search) > 0)
		{
			$code = str_replace($search, $replace, $code);
		}
		// This will handle the remaining root-level varrefs
		$code = preg_replace('#\{([a-z0-9\-_]*?)\}#is', '<'.'?php echo isset($this->vars[\'\1\']) ? $this->vars[\'\1\'] : $this->lang(\'\1\'); ?'.'>', $code);
		$code = preg_replace('#\{\$([a-z0-9\-_]*?)\}#is', '<'.'?php echo isset($this->_tpldata[\'DEFINE\'][\'.\'][\'\\1\']) ? $this->_tpldata[\'DEFINE\'][\'.\'][\'\\1\'] : \'\'; ?'.'>', $code);
		return $code;
	}

	//
	// Compile IF tags - much of this is from Smarty with
	// some adaptions for our block level methods
	//
	function compile_tag_if($tag_args, $elseif)
	{
        /* Tokenize args for 'if' tag. */
        preg_match_all('/(?:
                         "[^"\\\\]*(?:\\\\.[^"\\\\]*)*"         |
                         \'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'     |
                         [(),]                                  |
                         [^\s(),]+)/x', $tag_args, $match);

        $tokens = $match[0];
        $is_arg_stack = array();

        for ($i = 0; $i < count($tokens); $i++)
		{
			$token = &$tokens[$i];

			switch ($token)
			{
                case '!':
                case '%':
                case '!==':
                case '==':
                case '===':
                case '>':
                case '<':
                case '!=':
                case '<>':
                case '<<':
                case '>>':
                case '<=':
                case '>=':
                case '&&':
                case '||':
				case '|':
				case '^':
				case '&':
				case '~':
				case ')':
				case ',':
				case '+':
				case '-':
				case '*':
				case '/':
				case '@':
					break;	

				case 'eq':
					$token = '==';
					break;

				case 'ne':
				case 'neq':
					$token = '!=';
					break;

				case 'lt':
					$token = '<';
					break;

				case 'le':
				case 'lte':
					$token = '<=';
					break;

				case 'gt':
					$token = '>';
					break;

				case 'ge':
				case 'gte':
					$token = '>=';
					break;

				case 'and':
					$token = '&&';
					break;

				case 'or':
					$token = '||';
					break;

				case 'not':
					$token = '!';
					break;

				case 'mod':
					$token = '%';
					break;

				case '(':
					array_push($is_arg_stack, $i);
					break;

				case 'is':
					$is_arg_start = ($tokens[$i-1] == ')') ? array_pop($is_arg_stack) : $i-1;
					$is_arg	= implode('	', array_slice($tokens,	$is_arg_start, $i -	$is_arg_start));

					$new_tokens	= $this->_parse_is_expr($is_arg, array_slice($tokens, $i+1));

					array_splice($tokens, $is_arg_start, count($tokens), $new_tokens);

					$i = $is_arg_start;

				default:
					if (preg_match('#^(([a-z0-9\-_]+?\.)+?)?(\$)?([A-Z]+[A-Z0-9\-_]+)$#s', $token, $varrefs))
					{
						$token = (!empty($varrefs[1])) ? $this->generate_block_data_ref(substr($varrefs[1], 0, -1), true, $varrefs[3]) . '[\'' . $varrefs[4] . '\']' : (($varrefs[3]) ? '$this->_tpldata[\'DEFINE\'][\'.\'][\'' . $varrefs[4] . '\']' : '$this->vars[\'' . $varrefs[4] . '\']');
					}
					break;
            }
        }

		$code = (($elseif) ? '} elseif (' : 'if (') . (implode(' ', $tokens) . ') { ');
	
		return $code;
	}

	// This is from Smarty
	function _parse_is_expr($is_arg, $tokens)
	{
		$expr_end =	0;
		$negate_expr = false;

		if (($first_token = array_shift($tokens)) == 'not')
		{
			$negate_expr = true;
			$expr_type = array_shift($tokens);
		}
		else
		{
			$expr_type = $first_token;
		}

		switch ($expr_type)
		{
			case 'even':
				if (@$tokens[$expr_end] == 'by')
				{
					$expr_end++;
					$expr_arg =	$tokens[$expr_end++];
					$expr =	"!(($is_arg	/ $expr_arg) % $expr_arg)";
				}
				else
				{
					$expr =	"!($is_arg % 2)";
				}
				break;

			case 'odd':
				if (@$tokens[$expr_end] == 'by')
				{
					$expr_end++;
					$expr_arg =	$tokens[$expr_end++];
					$expr =	"(($is_arg / $expr_arg)	% $expr_arg)";
				}
				else
				{
					$expr =	"($is_arg %	2)";
				}
				break;

			case 'div':
				if (@$tokens[$expr_end] == 'by')
				{
					$expr_end++;
					$expr_arg =	$tokens[$expr_end++];
					$expr =	"!($is_arg % $expr_arg)";
				}
				break;

			default:
				break;
		}

		if ($negate_expr)
		{
			$expr =	"!($expr)";
		}

		array_splice($tokens, 0, $expr_end,	$expr);

		return $tokens;
	}


	function compile_tag_define($tag_args)
	{
		preg_match('#^(([a-z0-9\-_]+?\.)+?)?\$([A-Z][A-Z0-9_\-]*?) = (\'?)(.*?)(\'?)$#', $tag_args, $match);

		if (empty($match[3]) || empty($match[5]))
		{
			return '';
		}

		// Are we a string?
		if ($match[4] && $match[6])
		{
			$match[5] = "'" . addslashes(str_replace(array('\\\'', '\\\\'), array('\'', '\\'), $match[5])) . "'";
		}
		else
		{
			preg_match('#(true|false|\.)#i', $match[5], $type);

			switch (strtolower($type[1]))
			{
				case 'true':
				case 'false':
					$match[5] = strtoupper($match[5]);
					break;
				case '.';
					$match[5] = doubleval($match[5]);
					break;
				default:
					$match[5] = intval($match[5]);
					break;
			}
		}

		return (($match[1]) ? $this->generate_block_data_ref(substr($match[1], 0, -1), true, true) . '[\'' . $match[3] . '\']' : '$this->_tpldata[\'DEFINE\'][\'.\'][\'' . $match[3] . '\']') . ' = ' . $match[5] . ';';
	}

	function compile_tag_undefine($tag_args)
	{
		preg_match('#^(([a-z0-9\-_]+?\.)+?)?\$([A-Z][A-Z0-9_\-]*?)$#', $tag_args, $match);
		if (empty($match[3]))
		{
			return '';
		}
		return 'unset(' . (($match[1]) ? $this->generate_block_data_ref(substr($match[1], 0, -1), true, true) . '[\'' . $match[3] . '\']' : '$this->_tpldata[\'DEFINE\'][\'.\'][\'' . $match[3] . '\']') . ');';
	}

	/**
	 * Compiles code and writes to cache if needed
	 */
	function compile2($code, $handle, $cache_file)
	{
		$code = $this->compile_code('', $code, XS_USE_ISSET);
		if($cache_file && !empty($this->use_cache) && !empty($this->auto_compile))
		{
			$res = $this->write_cache($cache_file, $code);
			if($handle && $res)
			{
				$this->files_cache[$handle] = $cache_file;
			}
		}
		$code = '?'.'>'.$code.'<'."?php\n";
		return $code;
	}

	/**
	 * Compiles the given string of code, and returns
	 * the result in a string.
	 * If "do_not_echo" is true, the returned code will not be directly
	 * executable, but can be used as part of a variable assignment
	 * for use in assign_code_from_handle().
	 * This function isn't used and kept only for compatibility with original template.php
	 */
	function compile($code, $do_not_echo = false, $retvar = '')
	{
		$code = ' ?'.'>' . $this->compile_code('', $code, true) . '<'."?php \n";
		if($do_not_echo)
		{
			$code = "ob_start();\n". $code. "\n\${$retvar} = ob_get_contents();\nob_end_clean();\n";
		}
		return $code;
	}

	function xs_startup()
	{
		global $phpEx, $board_config, $phpbb_root_path;
		if(empty($this->xs_started))
		{	// adding predefined variables
			$this->xs_started = 1;
			// file extension with session ID (eg: "php?sid=123&" or "php?")
			// can be used to make custom URLs without modding phpbb
			// contains "&" or "?" at the end so you can easily append paramenters
			$php = append_sid($phpEx);
			if(strpos($php, '?'))
			{
				$php .= '&';
			}
			else
			{
				$php .= '?';
			}
			$this->vars['PHP'] = $php;
			// adding language variable (eg: "english" or "german")
			// can be used to make truly multi-lingual templates
			$this->vars['LANG'] = $board_config['default_lang'];
			// adding current template
			$tpl = $this->root . '/'; // $phpbb_root_path . 'templates/' . $this->tpl . '/';
			if(substr($tpl, 0, 2) === './')
			{
				$tpl = substr($tpl, 2, strlen($tpl));
			}
			$this->vars['TEMPLATE'] = $tpl;
			$this->vars['TEMPLATE_NAME'] = $this->tpl;
			$this->_tpldata['switch_xs_enabled.'] = array(array('version' => $this->xs_versiontxt));
		}
	}

	/**
	 * Checks for empty variable and shows language variable if possible.
	 */
	function lang($var)
	{
		global $lang;
		if(substr($var, 0, 2) === 'L_')
		{
			$var = substr($var, 2);
			// check variable as it is
			if(isset($lang[$var]))
			{
				return $lang[$var];
			}
			// check variable in lower case
			if(isset($lang[strtolower($var)]))
			{
				return $lang[strtolower($var)];
			}
			// check variable with first letter in upper case
			$str = ucfirst(strtolower($var));
			if(isset($lang[$str]))
			{
				return $lang[$str];
			}
			return ''; //str_replace('_', ' ', $var);
		}
		return '';
	}

	//
	//
	// Functions added for USERGROUP MOD (optimized)
	//
	//
	function append_var_from_handle_to_block($blockname, $varname, $handle)
	{
		$this->assign_var_from_handle('_tmp', $handle);
		// assign the value of the generated variable to the given varname.
		$this->append_block_vars($blockname, array($varname => $this->vars['_tmp']));
		return true;
	}

	function append_block_vars($blockname, $vararray)
	{
		if(strstr($blockname, '.'))
		{
			// Nested block.
			$blocks = explode('.', $blockname);
			$blockcount = sizeof($blocks) - 1;
			$str = &$this->_tpldata;
			for($i = 0; $i < $blockcount; $i++)
			{
				$str = &$str[$blocks[$i].'.'];
				$str = &$str[sizeof($str)-1];
			}
			// Now we add the block that we're actually assigning to.
			// We're adding a new iteration to this block with the given
			//   variable assignments.
			$str = &$str[$blocks[$blockcount].'.'];
			$count = sizeof($str) - 1;
			if($count >= 0)
			{
				// adding only if there is at least one item
				$str[$count] = array_merge($str[$count], $vararray);
			}
		}
		else
		{
			// Top-level block.
			// Add a new iteration to this block with the variable assignments
			// we were given.
			$str = &$this->_tpldata[$blockname.'.'];
			$count = sizeof($str) - 1;
			if($count >= 0)
			{
				// adding only if there is at least one item
				$str[$count] = array_merge($str[$count], $vararray);
			}
		}
		return true;
	}

	/*
	* Flush a root level block, so it becomes empty.
	*/
	function flush_block_vars($blockname)
	{
		// Top-level block.
		// flush a existing block we were given.
		$current_iteration = sizeof($this->_tpldata[$blockname . '.']) - 1;
		unset($this->_tpldata[$blockname . '.']);
		return true;
	}

	/*
	* Add style configuration
	*/
	function _add_config($tpl, $add_vars = true)
	{
		global $phpbb_root_path;
		if(@file_exists($phpbb_root_path . 'templates/' . $tpl . '/xs_config.cfg'))
		{
			$style_config = array();
			include($phpbb_root_path . 'templates/' . $tpl . '/xs_config.cfg');
			if(count($style_config))
			{
				global $board_config, $db;
				for($i=0; $i<count($style_config); $i++)
				{
					$this->style_config[$style_config[$i]['var']] = $style_config[$i]['default'];
					if($add_vars)
					{
						$this->vars['TPL_CFG_' . strtoupper($style_config[$i]['var'])] = $style_config[$i]['default'];
					}
				}
				$str = $this->_serialize($this->style_config);
				$config_name = 'xs_style_' . $tpl;
				$board_config[$config_name] = $str;
				$sql = "INSERT INTO " . CONFIG_TABLE . " (config_name, config_value) VALUES ('" . addslashes($config_name) . "', '" . addslashes($str) . "')";
				$db->sql_query($sql);
				// recache config table for cat_hierarchy 2.1.0
				global $config;
				if(isset($config->data) && $config->data === $board_config && isset($config->data['mod_cat_hierarchy']))
				{
					$config->read(true);
				}
				return true;
			}
		}
		return false;
	}

	function add_config($tpl)
	{
		$config_name = 'xs_style_' . $tpl;
		global $board_config;
		$result = false;
		if(empty($board_config[$config_name]))
		{
			$old = $this->style_config;
			$result = $this->_add_config($tpl, false);
			$this->style_config = $old;
		}
		return $result;
	}

	/*
	* Refresh config data
	*/
	function _refresh_config($tpl, $add_vars = false)
	{
		global $phpbb_root_path;
		if(@file_exists($phpbb_root_path . 'templates/' . $tpl . '/xs_config.cfg'))
		{
			$style_config = array();
			include($phpbb_root_path . 'templates/' . $tpl . '/xs_config.cfg');
			if(count($style_config))
			{
				global $board_config, $db;
				for($i=0; $i<count($style_config); $i++)
				{
					if(!isset($this->style_config[$style_config[$i]['var']]))
					{
						$this->style_config[$style_config[$i]['var']] = $style_config[$i]['default'];
						if($add_vars)
						{
							$this->vars['TPL_CFG_' . strtoupper($style_config[$i]['var'])] = $style_config[$i]['default'];
						}
					}
				}
				$str = $this->_serialize($this->style_config);
				$config_name = 'xs_style_' . $tpl;
				if(isset($board_config[$config_name]))
				{
					$sql = "UPDATE " . CONFIG_TABLE . " SET config_value='" . addslashes($str) . "' WHERE config_name='" . addslashes($config_name) . "'";
				}
				else
				{
					$sql = "INSERT INTO " . CONFIG_TABLE . " (config_name, config_value) VALUES ('" . addslashes($config_name) . "', '" . addslashes($str) . "')";
				}
				$db->sql_query($sql);
				$board_config[$config_name] = $str;
				// recache config table for cat_hierarchy 2.1.0
				global $config;
				if(isset($config->data) && $config->data === $board_config && isset($config->data['mod_cat_hierarchy']))
				{
					$config->read(true);
				}
				return true;
			}
		}
		return false;
	}

	function refresh_config($tpl = '')
	{
		if($tpl === '')
		{
			$tpl = $this->tpl;
		}
		if($tpl == $this->tpl)
		{
			$result = $this->_refresh_config($tpl, true);
		}
		else
		{
			$old = $this->style_config;
			$result = $this->_refresh_config($tpl, false);
			$this->style_config = $old;
		}
		return $result;
	}

	/*
	* Get style configuration
	*/
	function _get_config($tpl, $add_config)
	{
		$this->style_config = array();
		if(empty($tpl))
		{
			$tpl = $this->tpl;
		}
		$config_name = 'xs_style_' . $tpl;
		global $board_config;
		if(empty($board_config[$config_name]))
		{
			if($add_config)
			{
				$this->_add_config($tpl, $tpl === $this->tpl ? true : false);
			}
			return $this->style_config;
		}
		$this->style_config = $this->_unserialize($board_config[$config_name]);
		if($tpl === $this->tpl)
		{
			foreach($this->style_config as $var => $value)
			{
				$this->vars['TPL_CFG_' . strtoupper($var)] = $value;
			}
		}
		return $this->style_config;
	}

	function get_config($tpl = '', $add_config = true)
	{
		if(empty($tpl))
		{
			if(empty($this->tpl))
			{
				return array();
			}
			$this->_get_config($this->tpl, $add_config);
			return $this->style_config;
		}
		else
		{
			$old_config = $this->style_config;
			$result = $this->_get_config($tpl, $add_config);
			$this->style_config = $old_config;
			return $result;
		}
	}

	/*
	* Split/merge config data.
	* Using this function instead of (un)serialize because it generates smaller string so it can be stored in phpbb_config
	*/
	function _serialize($array)
	{
		if(!is_array($array))
		{
			return '';
		}
		$str = '';
		foreach($array as $var => $value)
		{
			if($str)
			{
				$str .= '|';
			}
			$str .= $var . '=' . str_replace('|', '', $value);
		}
		return $str;
	}
	function _unserialize($str)
	{
		$array = array();
		$list = explode('|', $str);
		for($i=0; $i<count($list); $i++)
		{
			$row = explode('=', $list[$i], 2);
			if(count($row) == 2)
			{
				$array[$row[0]] = $row[1];
			}
		}
		return $array;
	}

}

function xs_switch($tpl, $name)
{
	return (isset($tpl->_tpldata[$name.'.']) && count($tpl->_tpldata[$name.'.']) > 0);
}
*
class template_class extends style
{
	var $custom_root;

	function template_class($root='.', $custom_root='')
	{
		$this->custom_root = $custom_root;
		parent::Template($root);
	}

	// ensure extreme style v2 compliancy adding the xs_include switch
	function make_filename($filename, $xs_switch=false)
	{
		if ( (substr($filename, 0, 1) != '/') && !empty($this->custom_root) && !defined('IN_ADMIN') )
		{
       		$w_filename = phpbb_realpath($this->root . '/' . $this->custom_root . '/' . $filename);
			if ( file_exists($w_filename) && !is_dir($w_filename) && !is_link($w_filename) )
			{
				$filename = $this->custom_root . '/' . $filename;
			}
		}
		return parent::make_filename($filename, $xs_switch);
	}

	// ensure extreme style v2 compliancy
	function subtemplates_make_filename($filename)
	{
		return $this->make_filename($filename);
	}

	function set_switch($switch_name, $value=true)
	{
		$this->assign_block_vars($switch_name . ($value ? '' : '_ELSE'), array());
	}

	// recall from memory the result of a parsing without sending it to browser
	function save(&$save)
	{
		$save = $this->_tpldata;
		$this->_tpldata = array();
		if ( defined('XS_TAG_INCLUDE') )
		{
			$this->_tpldata = array('.' => array(0 => array()));
			$this->vars = &$this->_tpldata['.'][0];
		}
	}

	function restore(&$save)
	{
		$this->_tpldata = $save;
		if ( defined('XS_TAG_INCLUDE') )
		{
			$this->vars = &$this->_tpldata['.'][0];
		}
	}

	function get_pparse($handle)
	{
		ob_start();
		$this->pparse($handle);
		$res = ob_get_contents();
		ob_end_clean();
		return $res;
	}

	// insert a tpl (xs already covers the include functionality, so let's it do the job if present)
	function compile($code, $do_not_echo = false, $retvar = '')
	{
		// only constants
		return defined('XS_TAG_INCLUDE') ? parent::compile($code, $do_not_echo, $retvar) : preg_replace('#<!-- INCLUDE ([a-zA-Z0-9\_\-\+\.]+?) -->#', '\'; $this->_tpl_include(\'' . "\\1" . '\'' . ($do_not_echo ? ', true, ' . $retvar : '') . '); ' . ($do_not_echo ? '$' . $retvar . '.= \'' : 'echo \''), parent::compile($code, $do_not_echo, $retvar));
	}

	function _tpl_include($filename, $do_not_echo = false, $retvar = '')
	{
		if ( !empty($filename) )
		{
			$this->files[$filename] = $this->make_filename($filename);
			$this->loadfile($filename);
			eval($this->compile($this->uncompiled_code[$filename], $do_not_echo, $retvar));
		}
	}
}

// user.php


	function get_groups_list($force=false)
	{
		global $db, $config;

		// force the group list to be updated
		$force |= empty($this->data['group_user_list']) || empty($this->data['group_user_id']) || empty($this->data['group_id']);

		// search for the individual user group
		if ( $force )
		{
			// get the individual group id
			$sql = 'SELECT ug.group_id
						FROM ' . USER_GROUP_TABLE . ' ug, ' . GROUPS_TABLE . ' g
						WHERE ug.user_id = ' . intval($this->data['user_id']) . '
							AND g.group_id = ug.group_id
							AND g.group_single_user = ' . true;
			$result = $db->sql_query($sql, false, __LINE__, __FILE__);
			$create = false;
			if ( $row = $db->sql_fetchrow($result) )
			{
				$this->data['group_id'] = intval($row['group_id']);
				$this->data['group_user_id'] = intval($this->data['user_id']);
				$fields = array(
					'group_user_id' => $this->data['group_user_id'],
				);
			}
			else
			{
				// no individual group : this should never occur !
				$create = true;

				// get a new group id
				$sql = 'SELECT group_id
							FROM ' . GROUPS_TABLE . '
							ORDER BY group_id DESC
							LIMIT 1';
				$result = $db->sql_query($sql, false, __LINE__, __FILE__);
				$row = $db->sql_fetchrow($result);
				$this->data['group_id'] = intval($row['group_id']) + 1;
				$this->data['group_user_id'] = intval($this->data['user_id']);

				// create individual group
				$fields = array(
					'group_id' => $this->data['group_id'],
					'group_type' => GROUP_CLOSED,
					'group_name' => '',
					'group_description' => 'Personal User',
					'group_moderator' => 0,
					'group_single_user' => 1,
					'group_user_id' => $this->data['group_user_id'],
				);
			}

			// search for the user's membership
			$this->data['group_user_list'] = '';
			if ( $this->data['user_id'] == ANONYMOUS )
			{
				$this->data['group_user_list'] = ',' . GROUP_ANONYMOUS . ',';
			}
			else
			{
				$groups = array();
				$groups[GROUP_REGISTERED] = true;
				$groups[ intval($this->data['group_id']) ] = true;

				// search for regular groups
				$sql = 'SELECT group_id
							FROM ' . USER_GROUP_TABLE . '
							WHERE user_id = ' . intval($this->data['user_id']) . '
								AND group_id <> ' . intval($this->data['group_id']) . '
							ORDER BY group_id';
				$result = $db->sql_query($sql, false, __LINE__, __FILE__);
				while ( $row = $db->sql_fetchrow($result) )
				{
					$groups[ intval($row['group_id']) ] = true;
				}
				if ( !empty($groups) )
				{
					$groups = array_keys($groups);
					$this->data['group_user_list'] = ',' . implode(',', $groups) . ',';
				}
			}

			// update the individual group (or create it if appropriate)
			$fields += array(
				'group_user_list' => $this->data['group_user_list'],
			);
			$db->sql_statement($fields);
			if ( $create )
			{
				$sql = 'INSERT INTO ' . GROUPS_TABLE . '
							(' . $db->sql_fields . ') VALUES (' . $db->sql_values . ')';
				$db->sql_query($sql, false, __LINE__, __FILE__);
			}
			else
			{
				$sql = 'UPDATE ' . GROUPS_TABLE . '
							SET ' . $db->sql_update . '
							WHERE group_id = ' . intval($this->data['group_id']);
				$db->sql_query($sql, false, __LINE__, __FILE__);
			}
		}

		return empty($this->data['group_user_list']) ? array() : explode(',', substr($this->data['group_user_list'], 1, strlen($this->data['group_user_list'])-2));
	}

	function join_groups($group_ids, $pending=true)
	{
		global $db, $config, $forums;

		if ( empty($group_ids) )
		{
			return;
		}
		if ( !is_array($group_ids) )
		{
			$group_ids = array($group_ids);
		}

		if ( !$pending )
		{
			// remove pending status
			$sql = 'DELETE FROM ' . USER_GROUP_TABLE . '
						WHERE group_id IN (' . implode(', ', $group_ids) . ')
							AND user_id = ' . intval($this->data['user_id']);
			$db->sql_query($sql, false, __LINE__, __FILE__);
		}

		// add to groups
		$db->sql_stack_reset();
		$count_group_ids = count($group_ids);
		for ( $i = 0; $i < $count_group_ids; $i++ )
		{
			$fields = array(
				'group_id' => intval($group_ids[$i]),
				'user_id' => intval($this->data['user_id']),
				'user_pending' => intval($pending),
			);
			$db->sql_stack_statement($fields);
		}
		$db->sql_stack_insert(USER_GROUP_TABLE, false, __LINE__, __FILE__);
	}

	function leave_groups($group_ids)
	{
		global $db, $config, $forums;

		if ( empty($group_ids) )
		{
			return;
		}
		if ( !is_array($group_ids) )
		{
			$group_ids = array($group_ids);
		}

		// remove links
		$sql = 'DELETE FROM ' . USER_GROUP_TABLE . '
					WHERE group_id IN (' . implode(', ', $group_ids) . ')
						AND user_id = ' . intval($this->data['user_id']);
		$db->sql_query($sql, false, __LINE__, __FILE__);
	}

	function recache_groups()
	{
		global $db, $config, $forums;

		// delete auths so they will be recreated the next page
		$sql = 'DELETE FROM ' . USERS_CACHE_TABLE . '
					WHERE user_id = ' . intval($this->data['user_id']);
		$db->sql_query($sql, false, __LINE__, __FILE__);

		// recache group list
		$this->get_groups_list(true);

		// recache moderators
		include_once($config->url('includes/class_forums'));
		$moderators = new moderators();
		$moderators->set_users_status();
		$moderators->read(true);
	}

	function set()
	{
		global $db, $config, $userdata, $forums, $forum_id, $themes, $theme, $lang;

		if ( !empty($this->data) )
		{
			return;
		}

		$this->data = &$userdata;
		$this->cache = array();
		$this->cache_time = array();

		// anonymous individual group data are missing in some cases
		if ( !$this->data['session_logged_in'] && ($this->data['group_id'] != GROUP_ANONYMOUS) )
		{
			$sql = 'SELECT * FROM ' . GROUPS_TABLE . '
				WHERE group_id = ' . GROUP_ANONYMOUS;
			$result = $db->sql_query($sql, false, __LINE__, __FILE__);
			if ( $row = $db->sql_fetchrow($result) )
			{
				$userdata = array_merge($this->data, $row);
			}
		}

		// default values
		$default_values = array(
			'user_dateformat' => 'default_dateformat',
			'user_timezone' => 'board_timezone',
			'user_dst' => 'board_dst',
			'user_lang' => 'default_lang',
		);
		foreach ( $default_values as $user_field => $config_field )
		{
			if ( empty($this->data[$user_field]) )
			{
				$this->data[$user_field] = isset($config->data[$config_field]) ? $config->data[$config_field] : '';
			}
		}

		// get extended lang
		$this->get_extended_lang();

		// read styles
		$themes_exists = true;
		if ( empty($themes) )
		{
			$themes = new themes();
			$themes->read();
			$themes_exists = false;
		}

		// set forum specific template
		$user_style = empty($this->data['group_style']) ? $this->data['user_style'] : $this->data['group_style'];
		if ( defined('IN_ADMIN') || !$this->data['group_force_style'] )
		{
			if ( defined('IN_ADMIN') || $config->data['override_user_style'] || !$this->data['session_logged_in'] || !isset($themes->data[$user_style]) )
			{
				$user_style = $config->data['default_style'];
			}
			if ( !empty($forum_id) && !defined('IN_ADMIN') )
			{
				if ( empty($forums) )
				{
					include_once($config->url('includes/class_forums'));
					$forums = new forums();
					$forums->read();
				}
				if ( isset($forums->data[$forum_id]) && isset($themes->data[ intval($forums->data[$forum_id]['forum_style']) ]) )
				{
					$user_style = $forums->data[$forum_id]['forum_style'];
				}
			}
		}

		// check if the style exists
		if ( !isset($themes->data[$user_style]) && ($user_style != $config->data['default_style']) )
		{
			$user_style = $config->data['default_style'];
		}

		// read user or config style
		if ( !($theme = setup_style($user_style)) && ($user_style != $config->data['default_style']) )
		{
			$user_style = $config->data['default_style'];
			$theme = setup_style($user_style);
		}

		// delete themes
		if ( !$themes_exists )
		{
			unset($themes);
		}
	}

	function get_extended_lang()
	{
		global $config, $lang;

		// switch for the admin part of lang_extend_*
		$lang_extend_admin = defined('IN_ADMIN');


		// get the dirs to process (english is default for all mods)
		$langs = array('english');

		// add default lang
		if ( $config->data['default_lang'] != 'english' )
		{
			$langs[] = $config->data['default_lang'];
		}
		// add user lang
		if ( !empty($this->data['user_lang']) && !in_array($this->data['user_lang'], $langs) )
		{
			$langs[] = $this->data['user_lang'];
		}

		// additional language file to load before the lang_extend_* ones
		$additionals = array_flip(array('lang_extend_phpbb'));

		// get all the langs
		$count_langs = count($langs);
		$count_additionals = count($additionals);
		for ( $i = 0; $i < $count_langs; $i++ )
		{
			if ( $dir = @opendir($config->root . 'language/lang_' . $langs[$i]) )
			{
				// include the fixes on phpBB main language files
				if ( !empty($additionals) )
				{
					foreach ( $additionals as $file => $dummy )
					{
						@include($config->url('language/lang_' . $langs[$i] . '/' . $file));
					}
				}

				// include other extensions
				while( $file = @readdir($dir) )
				{
					if ( preg_match('/^lang_extend_.*?\.' . $config->ext . '$/', $file) && !isset($additionals[ substr($file, 0, (strlen($file) - strlen($config->ext) - 1)) ]) )
					{
						include($config->root . 'language/lang_' . $langs[$i] . '/' . $file);
					}
				}
				@closedir($dir);

				// include the personalisations
				@include($config->url('language/lang_' . $langs[$i] . '/lang_extend'));
			}
		}

		// fix datetime lang array
		if ( !empty($lang['datetime']) )
		{
			foreach ( $lang['datetime'] as $key => $val )
			{
				$lang[$key] = $val;
			}
		}

		// fix direction
		$lang['DIRECTION'] = strtolower($lang['DIRECTION']);
	}

	function get_cache($cache_ids='')
	{
		global $db, $config;

		// init auth types required
		if ( empty($cache_ids) )
		{
			return;
		}
		if ( !is_array($cache_ids) )
		{
			$cache_ids = array($cache_ids);
		}
		$count_cache_ids = count($cache_ids);

		// get caches from user cache
		$sql_where = (count($cache_ids) > 1) ? 'cache_id IN(\'' . implode('\', \'', $cache_ids) . '\')' : 'cache_id = \'' . $cache_ids[0] . '\'';
		$sql = 'SELECT cache_id, cache_data, cache_time
					FROM ' . USERS_CACHE_TABLE . '
					WHERE user_id = ' . intval($this->data['user_id']) . '
						AND ' . $sql_where;
		$result = $db->sql_query($sql, false, __LINE__, __FILE__);
		while ( $row = $db->sql_fetchrow($result) )
		{
			if ( !empty($row['cache_time']) && ($row['cache_time'] >= max($config->data['cache_time_' . $row['cache_id'] ], $config->data['cache_time_' . POST_FORUM_URL ])) )
			{
				$this->cache[ $row['cache_id'] ] = unserialize(stripslashes($row['cache_data']));
				$this->cache_time[ $row['cache_id'] ] = $row['cache_time'];
			}
		}

		// caches to process
		$process = array();
		for ( $i = 0; $i < $count_cache_ids; $i++ )
		{
			if ( empty($this->cache_time[ $cache_ids[$i] ]) )
			{
				$process[] = $cache_ids[$i];
			}
		}

		// refresh required auth
		if ( !empty($process) )
		{
			$user_auths = new auth_class();
			$process = $user_auths->get($this, $process, $this->cache, $this->cache_time);

			// recache result
			$this->write_cache($process);
		}
	}

	function write_cache($cache_ids='')
	{
		global $db;

		if ( empty($cache_ids) )
		{
			return;
		}
		if ( !is_array($cache_ids) )
		{
			$cache_ids = array($cache_ids);
		}
		$count_cache_ids = count($cache_ids);

		// delete remaining caches
		$sql_where = ($count_cache_ids > 1) ? 'cache_id IN(\'' . implode('\', \'', $cache_ids) . '\')' : 'cache_id = \'' . $cache_ids[0] . '\'';
		$sql = 'DELETE FROM ' . USERS_CACHE_TABLE . '
					WHERE user_id = ' . intval($this->data['user_id']) . '
						AND ' . $sql_where;
		$db->sql_query($sql, false, __LINE__, __FILE__);

		// insert new values
		$db->sql_stack_reset();
		for ( $i = 0; $i < $count_cache_ids; $i++ )
		{
			$fields = array(
				'user_id' => $this->data['user_id'],
				'cache_id' => $cache_ids[$i],
				'cache_time' => $this->cache_time[ $cache_ids[$i] ],
				'cache_data' => serialize($this->cache[ $cache_ids[$i] ]),
			);
			$db->sql_stack_statement($fields);
		}
		$db->sql_stack_insert(USERS_CACHE_TABLE, false, __LINE__, __FILE__);
	}

	function cache($cache_id, &$data, $data_time=0)
	{
		global $db;

		$this->cache[$cache_id] = $data;
		$this->cache_time[$cache_id] = empty($data_time) ? time() : $data_time;
		$this->write_cache($cache_id);
	}

	function auth($auth_type, $auth_names, $obj_ids)
	{
		$is_main_admin = strpos(' ' . $this->data['group_user_list'], ',' . GROUP_FOUNDER . ',');

		// short way : founder can do everything everywhere
		if ( $is_main_admin && (($auth_type != POST_FORUM_URL) || ($auth_names != 'auth_mod_display')) )
		{
			return true;
		}

		// unknown auth type
		if ( !isset($this->cache[$auth_type]) || !isset($this->cache[$auth_type]['def']) || !isset($this->cache[$auth_type]['val']) )
		{
			return false;
		}

		// prepare arrays
		if ( !is_array($obj_ids) )
		{
			$obj_ids = array($obj_ids);
		}
		if ( !is_array($auth_names) )
		{
			$auth_names = array($auth_names);
		}

		// check auth for all auths asked and all objects asked
		// auth_idx : cache[auth_type]['def'][auth_name]
		// auth_value : cache[auth_type]['val'][obj_id][auth_idx]
		$count_auth_names = count($auth_names);
		$count_obj_ids = count($obj_ids);
		$auth_value = 0;
		for ( $i = 0; $i < $count_auth_names; $i++ )
		{
			if ( isset($this->cache[$auth_type]['def'][ $auth_names[$i] ]) )
			{
				for ( $j = 0; $j < $count_obj_ids; $j++ )
				{
					if ( isset($this->cache[$auth_type]['val'][ $obj_ids[$j] ]) )
					{
						$auth_value = max($auth_value, intval($this->cache[$auth_type]['val'][ $obj_ids[$j] ][ $this->cache[$auth_type]['def'][ $auth_names[$i] ] ]));
					}
				}
			}
		}
		return in_array($auth_value, array(1, FORCE));
	}

	function img($key)
	{
		global $images, $config;
		return !empty($key) && isset($images[$key]) ? $config->root . $images[$key] : (eregi('^(ht|f)tp:', $key) ? $key : (@file_exists(@phpbb_realpath($config->root . $key)) ? $config->root . $key : './' .$key));
	}

	function lang($key)
	{
		global $lang;
		return !empty($key) && isset($lang[$key]) ? $lang[$key] : $key;
	}

	// this one will convert a user timestamp to the system timestamp
	// it can be used to convert an inputed by the user timestamp into the time() generated by the system
	function cvt_user_to_sys_date($user_timestamp)
	{
		if ( empty($user_timestamp) )
		{
			return 0;
		}

		// get user timezone & dst
		$user_timezone = (intval($this->data['user_timezone']) + intval($this->data['user_dst'])) * 3600;

		// get board timezone
		$sys_timezone = $user_timestamp - mktime(@gmdate('H', $user_timestamp), @gmdate('i', $user_timestamp), @gmdate('s', $user_timestamp), @gmdate('m', $user_timestamp), @gmdate('d', $user_timestamp), @gmdate('Y', $user_timestamp));

		// apply the time zone diff to the user timestamp
		return ($user_timestamp - $user_timezone) + $sys_timezone;
	}

	// this one will convert a system timestamp to the user timestamp
	function cvt_sys_to_user_date($sys_timestamp)
	{
		$date = explode(', ', $this->date($sys_timestamp, 'H, i, s, m, d, Y', false));
		return mktime($date[0], $date[1], $date[2], $date[3], $date[4], $date[5]);
	}

	function date($time=0, $fmt='', $today_yesterday=true)
	{
		global $config, $lang;

		// fix parms with default
		$fmt = empty($fmt) ? $this->data['user_dateformat'] : $fmt;
		$time = empty($time) ? time() : $time;

		// get user timezone & dst
		$time_zone = (intval($this->data['user_timezone']) + intval($this->data['user_dst'])) * 3600;

		// get date standard format
		$d_day = $time + $time_zone;
		$res = @gmdate($fmt, $d_day);

		// apply today/yesterday choice
		// this one was inspirated by Netclectic's mod "Today at/Yesterday at" : http://www.phpbb.com/phpBB/viewtopic.php?t=158812
		$smart_date = ($config->data['smart_date_over'] || empty($this->data['user_smart_date'])) ? intval($config->data['smart_date']) : (intval($this->data['user_smart_date']) != DENY);
		if ( $today_yesterday && $smart_date )
		{
			// get user current day
			$now = time() + $time_zone;
			$today = @gmmktime(0, 0, 0, @gmdate('m', $now), @gmdate('d', $now), @gmdate('Y', $now));

			// is the d day between user's yesterday and today ?
			if ( ($d_day >= $today - 86400) && ($d_day < $today + 86400) )
			{
				// get new fmt for time and compute
				$new_fmt = sprintf(strpos(' ' . $fmt, 'h') ? 'h%s a' : (strpos(' ' . $fmt, 'H') ? 'H%s' : (strpos(' '. $fmt, 'g') ? 'g%s a' : (strpos(' ' . $fmt, 'G') ? 'G%s' : ''))), strpos(' ' . $fmt, 's') ? ':i:s' : ':i');
				$res = empty($new_fmt) ? $this->lang(($d_day >= $today) ? 'Today' : 'Yesterday') : sprintf($this->lang(($d_day >= $today) ? 'Today_at': 'Yesterday_at'), @gmdate($new_fmt, $time + $time_zone));
			}
		}
		return strtr($res, $lang['datetime']);
	}

	function get_cookies_setup()
	{
		global $config;

		$keep_unreads = (intval($config->data['keep_unreads']) > 0) && (($this->data['user_keep_unreads'] != DENY) || $config->data['keep_unreads_over']);
		$keep_unreads_db = $keep_unreads && (intval($config->data['keep_unreads']) == KEEP_UNREAD_DB) && $this->data['session_logged_in'];

		// get the cookies basename per user_id if keep_unread sat
		$user_id = $this->data['session_logged_in'] ? $this->data['user_id'] : '_';
		$base_name = $config->data['cookie_name'] . ( $keep_unreads ? '_' . $user_id : '');

		return array(
			'keep_unreads' => $keep_unreads,
			'keep_unreads_db' => $keep_unreads_db,
			'base_name' => $base_name,
			'path' => $config->data['cookie_path'],
			'domain' => $config->data['cookie_domain'],
			'secure' => $config->data['cookie_secure'],
		);
	}

	function read_cookies($keep_forums=false)
	{
		global $db, $HTTP_COOKIE_VARS;

		// read cookies
		if ( isset($this->cookies) )
		{
			return;
		}

		// read setup
		$cookies_setup = $this->get_cookies_setup();
		foreach ( $cookies_setup as $var => $value )
		{
			$$var = $value;
		}

		// get default cookies
		$this->cookies = array(
			'f_all' => isset($HTTP_COOKIE_VARS[$base_name . '_f_all']) ? intval($HTTP_COOKIE_VARS[$base_name . '_f_all']) : 0,
			'forums' => isset($HTTP_COOKIE_VARS[$base_name . '_f']) ? unserialize($HTTP_COOKIE_VARS[$base_name . '_f']) : array(),
			'topics' => isset($HTTP_COOKIE_VARS[$base_name . '_t']) ? unserialize($HTTP_COOKIE_VARS[$base_name . '_t']) : array(),
		);

		$unreads = array();
		$last_extraction = $this->data['session_logged_in'] ? $this->data['user_lastvisit'] : time()-300;
		if ( $keep_unreads )
		{
			// get unreaded topic_ids and the extraction date
			if ( $keep_unreads_db )
			{
				$unreads = empty($this->data['user_unread_topics']) ? array() : unserialize($this->data['user_unread_topics']);
				if ( !empty($this->data['user_unread_date']) )
				{
					$last_extraction = $this->data['user_unread_date'];
				}
			}
			else
			{
				$unreads = isset($HTTP_COOKIE_VARS[$base_name . '_t_u']) ? unserialize($HTTP_COOKIE_VARS[$base_name . '_t_u']) : array();
				$date = intval($HTTP_COOKIE_VARS[$base_name . '_t_ud']);
				if ( !empty($date) )
				{
					$last_extraction = $date;
				}
			}

			// re-add floor to topic_time for each topic_id
			$floor = 0;
			if ( !empty($unreads) )
			{
				$floor = intval($unreads[0]);
				unset($unreads[0]);
			}
			if ( $floor )
			{
				foreach( $unreads as $topic_id => $topic_time )
				{
					$unreads[$topic_id] += $floor;
				}
			}
		}
		else
		{
			// reclaim some memory
			if ( isset($this->data['user_unread_topics']) )
			{
				unset($this->data['user_unread_topics']);
			}
			$unreads = array();
		}

		$this->cookies['unreads'] = array();
		$this->cookies['unreads_date'] = time();
		$this->cookies['f_unreads'] = array();
		if ( $this->data['session_logged_in'] || $keep_unread )
		{
			// get new unreaded topics
			$count_unreads = count($unreads);
			$sql = 'SELECT topic_id, topic_time, topic_last_time, forum_id
						FROM ' . TOPICS_TABLE . '

						WHERE topic_moved_id = 0
							AND ' . ($count_unreads ? '(' : '') . 'topic_last_time > ' . intval($last_extraction) .
								($count_unreads ? (($count_unreads > 1) ? ' OR topic_id IN(' . implode(', ', array_keys($unreads)) . ')' : ' OR topic_id = ' . _first_key($unreads)) . ')' : '');
			$result = $db->sql_query($sql, false, __LINE__, __FILE__);
			while ( $row = $db->sql_fetchrow($result) )
			{
				// last time we've marked readed a forum or the topic
				$cooky_all = empty($this->cookies['f_all']) ? 0 : intval($this->cookies['f_all']);
				$cooky_f = empty($this->cookies['forums'][ $row['forum_id'] ]) ? 0 : intval($this->cookies['forums'][ $row['forum_id'] ]);
				$cooky_t = empty($this->cookies['topics'][ $row['topic_id'] ]) ? 0 : intval($this->cookies['topics'][ $row['topic_id'] ]);
				$last_topic_mark = max($cooky_all, $cooky_f, $cooky_t);

				// if we've marked the topics since the last unreaded extraction, it is no more unreaded
				if ( ($last_topic_mark > $last_extraction) && isset($unreads[ $row['topic_id'] ]) )
				{
					unset($unreads[ $row['topic_id'] ]);
				}

				// some post may have been deleted
				if ( !empty($unreads[ $row['topic_id'] ]) && ($row['topic_last_time'] < $unreads[ $row['topic_id'] ]) )
				{
					$unreads[ $row['topic_id'] ] = intval($row['topic_last_time']);
				}

				// let's try to get a last visit to the topic time
				$last_topic_visit = $last_topic_mark;

				// no mark yet : get the previous last visit time for this topic
				if ( empty($last_topic_visit) && !empty($unreads[ $row['topic_id'] ]) )
				{
					$last_topic_visit = intval($unreads[ $row['topic_id'] ]);
				}

				// no mark nor first visit : this is a bran new topic for us :)
				if ( empty($last_topic_visit) )
				{
					$last_topic_visit = intval($last_extraction);
				}

				// does the topic has moved since the last time we've visited it ?
				if ( $row['topic_last_time'] > $last_topic_visit )
				{
					$this->cookies['unreads'][ $row['topic_id'] ] = $last_topic_visit;
					$this->cookies['f_unreads'][ $row['forum_id'] ] = true;

					// clean if present the topic level cookie mark
					if ( isset($this->cookies['topics'][ $row['topic_id'] ]) )
					{
						unset($this->cookies['topics'][ $row['topic_id'] ]);
					}

					// we need to keep the forum where stand the unreaded topics
					if ( $keep_forums )
					{
						$this->cookies['unreads_per_forums'][ $row['forum_id'] ][] = $row['topic_id'];
					}
				}
			}
		}
	}

	function write_cookies($cookies_asked='')
	{
		global $db;

		// cookies not readed : read them
		if ( !isset($this->cookies) )
		{
			$this->read_cookies();
		}

		// make an array with the cookies asked
		if ( empty($cookies_asked) )
		{
			$cookies_asked = array_keys($this->cookies);
		}
		if ( !is_array($cookies_asked) )
		{
			$cookies_asked = array($cookies_asked);
		}

		// read setup
		$cookies_setup = $this->get_cookies_setup();
		foreach ( $cookies_setup as $var => $value )
		{
			$$var = $value;
		}
		$one_year = time() + 31536000;

		// store cookies
		$count_cookies_asked = count($cookies_asked);
		for ( $i = 0; $i < $count_cookies_asked; $i++ )
		{
			$cookie = $cookies_asked[$i];
			switch ( $cookie )
			{
				// default phpBB cookies : cookies duration : session
				case 'f_all':
					setcookie($base_name . '_f_all', intval($this->cookies[$cookie]), 0, $path, $domain, $secure);
					break;
				case 'forums':
					setcookie($base_name . '_f', serialize($this->cookies[$cookie]), 0, $path, $domain, $secure);
					break;
				case 'topics':
					// sort the topics by lower time first
					$count_cookies = count($this->cookies[$cookie]);
					if ( $count_cookies )
					{
						asort($this->cookies[$cookie]);
					}
					// limit the number of topics to 150
					while ( ($count_cookies > 150) && (list($topic_id, $topic_time) = each($this->cookies[$cookie])) )
					{
						unset($this->cookies[$cookie][$topic_id]);
						$count_cookies--;
					}
					// cookie duration : session
					setcookie($base_name . '_t', serialize($this->cookies[$cookie]), 0, $path, $domain, $secure);
					break;

				// unreaded topics : cookie duration : one year
				case 'unreads':
					if ( $keep_unreads )
					{
						// sort the topics by lower time first
						$count_cookies = count($this->cookies[$cookie]);
						if ( $count_cookies )
						{
							asort($this->cookies[$cookie]);
						}
						// limit the number of topics to 300
						while ( ($count_cookies > 300) && (list($topic_id, $topic_time) = each($this->cookies[$cookie])) )
						{
							unset($this->cookies[$cookie][$topic_id]);
							$count_cookies--;
						}

						// substract the lower time to reduce the cookie size
						$floor = 0;
						if ( $count_cookies )
						{
							$floor = $this->cookies[$cookie][ _first_key($this->cookies[$cookie]) ];
							foreach ( $this->cookies[$cookie] as $topic_id => $topic_time )
							{
								$this->cookies[$cookie][$topic_id] -= $floor;
							}
							$this->cookies[$cookie][0] = $floor;
						}

						// finaly, output the value
						if ( $keep_unreads_db )
						{
							// update users table
							$sql = 'UPDATE ' . USERS_TABLE . '
										SET user_unread_topics = ' . ($floor ? '\'' . serialize($this->cookies[$cookie]) . '\'' : '\'\'') . ',
											user_unread_date = ' . intval($this->cookies['unreads_date']) . '
										WHERE user_id = ' . intval($this->data['user_id']);
							$db->sql_query($sql, false, __LINE__, __FILE__);
						}
						else
						{
							setcookie($base_name . '_t_u', serialize(($floor ? $this->cookies['unreads'] : array())), $one_year, $path, $domain, $secure);
							setcookie($base_name . '_t_ud', intval($this->cookies['unreads_date']), $one_year, $path, $domain, $secure);
						}
					}
					break;
				default:
					break;
			}
		}
	}

	function delete()
	{
		global $db, $config, $forums;

		// do not allow anonymous, founder or admin users to be deleted
		$group_user_list = $this->get_groups_list();
		$group_user_list = empty($group_user_list) ? array() : array_flip($group_user_list);
		if ( isset($group_user_list[GROUP_FOUNDER]) || isset($group_user_list[GROUP_ADMIN]) || isset($group_user_list[GROUP_ANONYMOUS]) )
		{
			return false;
		}
		unset($group_user_list);

		// get a group founder member (first will be fine)
		$sql = 'SELECT user_id
					FROM ' . USER_GROUP_TABLE . '
					WHERE group_id = ' . intval(GROUP_FOUNDER) . '
						AND user_pending <> ' . true . '
						AND user_id <> ' . intval($this->data['user_id']) . '
					ORDER BY user_id
					LIMIT 1';
		$result = $db->sql_query($sql, false, __LINE__, __FILE__);
		$row = $db->sql_fetchrow($result);
		$user_founder = intval($row['user_id']);
		if ( empty($user_founder) )
		{
			return false;
		}

		// posts
		$fields = array(
			'poster_id' => DELETED,
			'post_username' => $this->data['username'],
		);
		$db->sql_statement($fields);
		$sql = 'UPDATE ' . POSTS_TABLE . '
					SET ' . $db->sql_update . '
					WHERE poster_id = ' . intval($this->data['user_id']);
		$db->sql_query($sql, false, __LINE__, __FILE__);

		// topics : first post
		$fields = array(
			'topic_poster' => DELETED,
			'topic_first_username' => $this->data['username'],
		);
		$db->sql_statement($fields);
		$sql = 'UPDATE ' . TOPICS_TABLE . '
					SET ' . $db->sql_update . '
					WHERE topic_poster = ' . intval($this->data['user_id']);
		$db->sql_query($sql, false, __LINE__, __FILE__);

		// topics : last post
		$fields = array(
			'topic_last_poster' => DELETED,
			'topic_last_username' => $this->data['username'],
		);
		$db->sql_statement($fields);
		$sql = 'UPDATE ' . TOPICS_TABLE . '
					SET ' . $db->sql_update . '
					WHERE topic_last_poster = ' . intval($this->data['user_id']);
		$db->sql_query($sql, false, __LINE__, __FILE__);

		// votes table
		$fields = array(
			'vote_user_id' => DELETED,
		);
		$db->sql_statement($fields);
		$sql = 'UPDATE ' . VOTE_USERS_TABLE . '
					SET ' . $db->sql_update . '
					WHERE vote_user_id = ' . intval($this->data['user_id']);
		$db->sql_query($sql, false, __LINE__, __FILE__);

		// watch table
		$sql = 'DELETE FROM ' . TOPICS_WATCH_TABLE . '
					WHERE user_id = ' . intval($this->data['user_id']);
		$db->sql_query($sql, false, __LINE__, __FILE__);

		// forums table
		$fields = array(
			'forum_last_poster' => DELETED,
			'forum_last_username' => $this->data['username'],
		);
		$db->sql_statement($fields);
		$sql = 'UPDATE ' . FORUMS_TABLE . '
					SET ' . $db->sql_update . '
					WHERE forum_last_poster = ' . intval($this->data['user_id']);
		$db->sql_query($sql, false, __LINE__, __FILE__);

		// ban list
		$sql = 'DELETE FROM ' . BANLIST_TABLE . '
					WHERE ban_userid = ' . intval($this->data['user_id']);
		$db->sql_query($sql, false, __LINE__, __FILE__);

		// private messages
		$sql = 'SELECT privmsgs_id
					FROM ' . PRIVMSGS_TABLE . '
					WHERE privmsgs_from_userid = ' . intval($this->data['user_id']) . '
						OR privmsgs_to_userid = ' . intval($this->data['user_id']) . '
					ORDER BY privmsgs_id';
		$result = $db->sql_query($sql, false, __LINE__, __FILE__);
		$privmsgs_ids = array();
		while ( $row = $db->sql_fetchrow($result) )
		{
			$privmsgs_ids[] = intval($row['privmsgs_id']);
		}
		$db->sql_freeresult($result);
		if ( !empty($privmsgs_ids) )
		{
			// private message texts
			$sql = 'DELETE FROM ' . PRIVMSGS_TEXT_TABLE . '
						WHERE privmsgs_text_id IN(' . implode(', ', $privmsgs_ids) . ')';
			$db->sql_query($sql, false, __LINE__, __FILE__);
			unset($privmsgs_ids);

			// private message headers
			$sql = 'DELETE FROM ' . PRIVMSGS_TABLE . '
						WHERE privmsgs_from_userid = ' . intval($this->data['user_id']) . '
							OR privmsgs_to_userid = ' . intval($this->data['user_id']);
			$db->sql_query($sql, false, __LINE__, __FILE__);
		}

		// get all the personnal user groups
		$owned_group_ids = array();

		// individual
		if ( !empty($this->data['group_id']) )
		{
			$owned_group_ids[] = intval($this->data['group_id']);
		}

		// friends
		if ( !empty($this->data['group_friends_id']) )
		{
			$owned_group_ids[] = intval($this->data['group_friends_id']);
		}

		// foes
		if ( !empty($this->data['group_foes_id']) )
		{
			$owned_group_ids[] = intval($this->data['group_foes_id']);
		}

		// delete memberships
		$sql_where = empty($owned_group_ids) ? '' : ' OR group_id IN(' . implode(', ', $owned_group_ids) . ')';
		$sql = 'DELETE FROM ' . USER_GROUP_TABLE . '
					WHERE user_id = ' . intval($this->data['user_id']) . $sql_where;
		$db->sql_query($sql, false, __LINE__, __FILE__);

		// delete groups
		if ( !empty($owned_group_ids) )
		{
			// delete groups owned by this user (own, friends and foes)
			$sql = 'DELETE FROM ' . GROUPS_TABLE . '
						WHERE group_id IN(' . implode(', ', $owned_group_ids) . ')';
			$db->sql_query($sql, false, __LINE__, __FILE__);

			// remove from users groups list the groups deleted
			$sql_where = 'group_user_list LIKE \'%,' . implode(',%\' OR group_user_list LIKE \'%,', $owned_group_ids) . ',%\'';
			$sql = 'SELECT group_user_id, group_id, group_user_list
						FROM ' . GROUPS_TABLE . '
						WHERE group_single_user = ' . true . '
							AND group_user_id <> ' . intval($this->data['user_id']) . '
							AND (' . $sql_where . ')';
			$result = $db->sql_query($sql, false, __LINE__, __FILE__);
			$count_owned_group_ids = count($owned_group_ids);
			$user_ids = array();
			while ( $row = $db->sql_fetchrow($result) )
			{
				// keep in memory the updated users
				$user_ids[] = intval($row['group_user_id']);

				// remove the groups from the list
				$group_user_list = array_flip(explode(',', substr($row['group_user_list'], 1, strlen($row['group_user_list']) - 2)));
				for ( $i = 0; $i < $count_owned_group_ids; $i++ )
				{
					if ( isset($group_user_list[ $owned_group_ids[$i] ]) )
					{
						unset($group_user_list[ $owned_group_ids[$i] ]);
					}
				}

				// update the list
				$fields = array(
					'group_user_list' => empty($group_user_list) ? '' : ',' . implode(',', array_keys($group_user_list)) . ',',
				);
				$db->sql_statement($fields);
				$sql = 'UPDATE ' . GROUPS_TABLE . '
							SET ' . $db->sql_update . '
							WHERE group_id = ' . intval($row['group_id']);
				$db->sql_query($sql, false, __LINE__, __FILE__);
			}

			// recache the touched users
			if ( !empty($user_ids) )
			{
				$sql = 'DELETE FROM ' . USERS_CACHE_TABLE . '
							WHERE user_id IN(' . implode(', ', $user_ids) . ')';
				$db->sql_query($sql, false, __LINE__, __FILE__);
			}
		}

		// replace the group moderators
		$fields = array(
			'group_moderator' => intval($user_founder),
		);
		$db->sql_statement($fields);
		$sql = 'UPDATE ' . GROUPS_TABLE . '
					SET ' . $db->sql_update . '
					WHERE group_moderator = ' . intval($this->data['user_id']);
		$db->sql_query($sql, false, __LINE__, __FILE__);

		// delete auths
		$sql = 'DELETE FROM ' . AUTHS_TABLE . '
					WHERE group_id IN(' . implode(', ', $owned_group_ids) . ')
						OR (obj_type = \'' . POST_GROUPS_URL . '\'
							AND obj_id IN(' . implode(', ', $owned_group_ids) . '))';
		$db->sql_query($sql, false, __LINE__, __FILE__);

		// finaly, delete the user
		$sql = 'DELETE FROM ' . USERS_TABLE . '
					WHERE user_id = ' . intval($this->data['user_id']);
		$db->sql_query($sql, false, __LINE__, __FILE__);

		// recache the last user stats
		$this->read_stats(true);

		// recache moderators
		if ( empty($forums) || !is_object($forums) )
		{
			include_once($config->url('includes/class_forums'));
		}
		$moderators = new moderators();
		$moderators->read(true);

		return true;
	}

	function read_stats($force=true)
	{
		global $config, $db;

		if ( $force || empty($config->data['stat_last_user']) )
		{
			// update last user stats
			$sql = 'SELECT user_id, username
						FROM ' . USERS_TABLE . '
						WHERE user_id <> ' . ANONYMOUS . '
						ORDER BY user_id DESC';
			$result = $db->sql_query($sql, false, __LINE__, __FILE__);
			$config->set('stat_total_users', intval($db->sql_numrows($result)));
			$row = $db->sql_fetchrow($result);
			$config->set('stat_last_user', intval($row['user_id']));
			$config->set('stat_last_username', $row['username']);
		}
		return array('user_id' => intval($config->data['stat_last_user']), 'username' => $config->data['stat_last_username']);
	}

?>