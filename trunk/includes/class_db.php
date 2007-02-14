<?php
/***************************************************************************
 * @version $Id: class_db.php,v 1.7 2005/06/25 03:36:10 impleri Exp $
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
 ***************************************************************************/

defined( '_IN_PLUS' ) or die( 'Direct Access to this location is not allowed.' );


/*
 * Database class
 * --------------
 * This is the backbone to the system for database interaction.
 *
 * This is based on Mambo's database class
 *
 * Functions: db, debug, setQuery, query, query, getErrNo, loadResult, loadResultArray,
 *            loadAssocList, loadObject, loadObjectList, insertObject, updateObject,
 *            getNumRows, getTableCreate, getTableFields, getTableList, getVersion
 */
 
class db {
	var $_query;
	var $_errlog;
	var $_prefix;
	var $_resource;
	var $_cursor;
	var $_debug;
	var $_count;
	var $_gentime;
	var $_log;

	/*
	 * function db (Constructor)
	 * -------------------------
	 * Connects to MySQL and selects the database
	 */
	
	function db( $host='localhost', $dbase, $user, $pw, $prefix )
	{
		$query_beg = microtime();
		if (!function_exists( 'mysql_connect' )) {
			message_die(_OFFLINE, 'S_PHP_Connect', 'D_PHP_Invalid');
			exit;
		}
		if (!($this->_resource = @mysql_connect( $host, $user, $pw ))) {
			message_die(_OFFLINE, 'S_SQL_Connect', 'D_SQL_Connect');
			exit;
		}
		if (!mysql_select_db($dbase)) {
			message_die(_OFFLINE, 'S_SQL_DB', 'D_SQL_DB');
			exit;
		}
		$query_end = microtime();
		$gentime = $query_end - $query_beg;
		if($gentime < 0) {
			$gentime = 0;
		}
		$this->_prefix = $prefix;
		$this->_count = 0;
		$this->_gentime = $gentime;
		$this->_log = array();
		$this->_errlog = array();
	} 
	
	/*
	 * function debug
	 * --------------
	 * Sets SQL Debug level
	 */

	function debug( $int )
	{
	    $this->_debug = intval( $int );
	}

	/*
	 * function setQuery
	 * -----------------
	 * Affixes proper prefix to query and loads it for execution
	 */

	function setQuery( $query, $prefix='%__' )
	{
		$query = trim( $query );
		$lit = '';
		$l = strlen( $query );
		$lo = strlen( $prefix );
		$lp = strlen( $this->_prefix );

		for ($n=0; $n < $l; $n++ ) {
			$c = $query{$n};
			$test = substr( $query, $n, $lo );
			if ($test == $prefix) {
				$lit .= $this->_prefix;
				$n += $lo-1;
			}
			else {
				$lit .= $c;
			}
		}
	  	$this->_query = $lit;
	} 
	
	/*
	 * function query
	 * --------------
	 * Executes MySQL query stored by setQuery and adds it to the Debug Log
	 */

	function query()
	{
		global $config;
		$query_beg = microtime();
		$this->_cursor = mysql_query( $this->_query, $this->_resource );
		if (!$this->_cursor) {
			$this->_errlog[] = array(
				'errno' => mysql_errno( $this->_resource ),
				'errmsg' =>  mysql_error( $this->_resource ),
				'sql' => $this->_query,
				'file' => __FILE__,
				'line' => __LINE__,
			);
			message_die(_GENERAL, 'S_Query_Error', 'D_DB_Error', $this->_errlog);
			return false;
		}
		$query_end = microtime();
		$gentime = $query_end - $query_beg;
		if($gentime < 0) {
			$gentime = 0;
		}
		if ($this->_debug <> 0) {
			$this->_gentime = $this->_gentime + $gentime;
		}
		if ($this->_debug == 2) {
			$this->_count++;
	  		$this->_log[] = "SQL Query:<br />Generated in " . round($gentime, 4) . " seconds<br />" . $this->_query;
		}
		return $this->_cursor;
	}

	/*
	 * function loadResult
	 * -------------------
	 * Loads the first column of the first row of the SQL result
	 */

	function loadResult()
	{
		if (!($cur = $this->query())) {
			return null;
		}
		$ret = null;
		if ($row = mysql_fetch_row( $cur )) {
			$ret = $row[0];
		}
		mysql_free_result( $cur );
		return $ret;
	}

	/*
	 * function loadResultArray
	 * ------------------------
	 * Loads the first row of the SQL results into an array
	 */
	function loadResultArray($numinarray = 0) {
		if (!($cur = $this->query())) {
			return null;
		}
		$array = array();
		while ($row = mysql_fetch_row( $cur )) {
			$array[] = $row[$numinarray];
		}
		mysql_free_result( $cur );
		return $array;
	}
	
	/*
	 * function loadAssocList
	 * ----------------------
	 * Loads the SQL results into an array of arrays
	 * If $key is specified, the array (of arrays) is indexed by $key
	 */

	function loadAssocList( $key='' )
	{
		if (!($cur = $this->query())) {
			return null;
		}
		$array = array();
		while ($row = mysql_fetch_assoc( $cur )) {
			if ($key) {
				$array[$row[$key]] = $row;
			}
			else {
				$array[] = $row;
			}
		}
		mysql_free_result( $cur );
		return $array;
	}

	/*
	 * function loadObject
	 * -------------------
	 * Loads the first row of the SQL result as an object
	 * If $object specified is not null, only the variables found will be loaded
	 */

	function loadObject( &$object )
	{
		if ($object != null) {
			if (!($cur = $this->query())) {
				return false;
			}
			if ($array = mysql_fetch_assoc( $cur )) {
				mysql_free_result( $cur );
				foreach (get_object_vars($object) as $k => $v) {
					if (isset($array[$k])) {
						$object->$k = $array[$k];
					}
				}
				return true;
			}
			else {
				return false;
			}
		}
		else {
			if ($cur = $this->query()) {
				if ($object = mysql_fetch_object( $cur )) {
					mysql_free_result( $cur );
					return true;
				}
				else {
					$object = null;
					return false;
				}
			}
			else {
				return false;
			}
			}
	}
	
	/*
	 * function loadObjectList
	 * -----------------------
	 * Loads the SQL result into an array of objects
	 * If $key is specified, the array will be indexed by $key
	 */
	function loadObjectList( $key='' )
	{
		if (!($cur = $this->query())) {
			return null;
		}
		$array = array();
		while ($row = mysql_fetch_object( $cur )) {
			if ($key) {
				$array[$row->$key] = $row;
			}
			else {
				$array[] = $row;
			}
		}
		mysql_free_result( $cur );
		return $array;
	}
	
	/*
	 * function insertObject
	 * ---------------------
	 * Inserts all the variables of $object into $table
	 * If the $keyName specified is null, we default to the next available
	 */
	function insertObject( $table, &$object, $keyName = NULL ) 
	{
		$fmtsql = "( %s ) VALUES ( %s ) ";
		$fields = array();
		if( $keyName && !$object->$keyName ) {
			$query = "SELECT MAX( `$keyName` ) FROM $table";
			$this->setQuery( $query );
			$object->$keyName = $this->loadResult() + 1;
		}
		foreach (get_object_vars( $object ) as $k => $v) {
			if (is_array($v) or is_object($v) or $v === NULL) {
				continue;
			}
			if ($k[0] == '_') { // internal field
				continue;
			}
			$fields[] = "`$k`";
			$values[] = "'" . mysql_escape_string( $v ) . "'";
		}
		$this->setQuery( "INSERT INTO `$table` " . sprintf( $fmtsql, implode( ",", $fields ) ,  implode( ",", $values ) ) );
		if (!$this->query()) {
			return false;
		}
		return true;
	}

	/*
	 * function updateObject
	 * ---------------------
	 * Updates the variables of $object in $table where $keyName is specified
	 */
	function updateObject( $table, &$object, $keyName )
	{
		$fmtsql = "SET %s WHERE %s";
		$tmp = array();
		foreach (get_object_vars( $object ) as $k => $v) {
			if( is_array($v) or is_object($v) or $k[0] == '_' ) {
				continue;
			}
			if( $k == $keyName ) {
				$where = "$keyName='" . mysql_escape_string( $v ) . "'";
				continue;
			}
			if( $v == '' ) {
				$val = "''";
			}
			else {
				$val = "'" . mysql_escape_string( $v ) . "'";
			}
			$tmp[] = "`$k`=$val";
		}
		$this->setQuery( "UPDATE `$table` " . sprintf( $fmtsql, implode( ",", $tmp ), $where ) );
		return $this->query();
	}

	/*
	 * function getNumRows
	 * -----------------
	 * Returns the number of result rows from the SQL query
	 */
	 
	function getNumRows( $cur=null )
	{
		return mysql_num_rows( $cur ? $cur : $this->_cursor );
	}
	
	/*
	 * function getTableCreate
	 * -----------------------
	 * Returns an array of the CREATE TABLE function for $tables indexed by $table
	 */
	 
	function getTableCreate( $tables )
	{
		$result = array();

		foreach ($tables as $tblval) {
			$this->setQuery( 'SHOW CREATE table ' . $tblval );
			$this->query();
			$result[$tblval] = $this->loadResultArray( 1 );
		}
		return $result;
	}

	/*
	 * function getTableFields
	 * -----------------------
	 * Returns an array of the table fields for $tables indexed by $table
	 * Ex: $fields['TABLE']['FIELD'] = TYPE
	 */
	 
	function getTableFields( $tables )
	{
		$result = array();

		foreach ($tables as $tblval) {
			$this->setQuery( 'SHOW FIELDS FROM ' . $tblval );
			$this->query();
			$fields = $this->loadObjectList();
			foreach ($fields as $field) {
				$result[$tblval][$field->Field] = preg_replace("/[(0-9)]/",'', $field->Type );
			}
		}
		return $result;
	}

	/*
	 * function getTableList
	 * ---------------------
	 * Returns an array of all tables in the SQL database
	 */

	function getTableList()
	{
		$this->setQuery( 'SHOW tables' );
		$this->query();
		return $this->loadResultArray();
	}
	
	/*
	 * function getVersion
	 * -------------------
	 * Returns the MySQL Version
	 */
	 
	function getVersion()
	{
		return mysql_get_server_info();
	}
} // END class db


/*
 * Cache class
 * -----------
 * This is what caches the SQL data for faster access.
 *
 * This is based on pthirik's work done for the Categories Hierarchy MOD for phpBB2
 *
 * Functions: cache, read, write
 */
 
class cache
{
	var $cache_file;
	var $cache_disabled;
	var $from_cache;
	var $data_time;

	/*
	 * function cache (Constructor)
	 * ----------------------------
	 * Creates the cache object and stores basic info
	 */
	 
	 function cache($cache_file='', $cache_enabled=true)
	{
		global $config;

		$this->cache_file = $cache_file;
		if(!$config->data['enable_cache'] || !$cache_enabled) {
			$this->cache_disabled = true;
		} else {
			$this->cache_disabled = false;
		}
	}

	/*
	 * function read
	 * --------------
	 * Reads the cached data, performs the SQL command if unable to read the cache
	 * Writes the cache if it reads from the SQL
	 */
	 
	function read($query='', $force=false, $key_field='')
	{
		global $db, $config;

		$gentime = 0;
		$data = array();
		$this->cache_disabled |= empty($config->data['cache_key']);
		if ( !$force && !$this->cache_disabled ) {
			$query_beg = microtime();
			@include($config->_root . 'cache/' . $this->cache_file . '.php');
			if ( !empty($gentime) && ($cache_key == $config->data['cache_key']) ) {
				if ($db->_debug == 2) {
			  		$db->_log[] = "SQL Query:<br /><span style=\"color:#ff0000\">Data Cached</span><br />" . $newsql;
				}
			}
			else {
				$gentime = 0;
			}
		}
		$this->from_cache = !empty($gentime);
		$this->data_time = $gentime;

		if ( !$this->from_cache ) {
			$db->setQuery($query);
			$newsql = $db->_query;
			if ( !($rows = $db->loadAssocList($key_field)) ) {
				return false;
			}
			$this->data_time = time();
			if($key_field) {
				foreach($rows as $row) {
					$data[$row[$key_field]] = $row;
				}
			} 
			else {
				foreach($data as $row) {
					$data[] = $row;
				}
			}

			if ( !$this->cache_disabled ) {
				$this->write($data, $newsql);
			}
		}
		return $data;
	}

	/*
	 * function write
	 * --------------
	 * Writes $data to a cache file
	 */

 	function write(&$data, &$query)
	{
		global $db, $config;

		$fmt_file = '<' . '?php
//---------------------------------------------
// Generated : %s (GMT)
// SQL : %s
//---------------------------------------------
defined( \'_IN_PLUS\' ) or die( \'Direct Access to this location is not allowed.\' );
$gentime = %s;
$cache_key = \'%s\';
$newsql = "%s";
$data = unserialize(\'%s\');

?' . '>';

		// output to file
		$handle = @fopen($config->_root . 'cache/' . $this->cache_file . '.php', 'w');
		@flock($handle, LOCK_EX);
		@fwrite($handle, sprintf($fmt_file, date('Y-m-d H:i:s', $this->data_time), preg_replace('/[\n\r\s\t]+/', ' ', $query), $this->data_time, $config->data['cache_key'], preg_replace('/[\n\r\s\t]+/', ' ', $query), str_replace('\'', '\\\'', str_replace('\\', '\\\\', serialize($data)))));
		@flock($handle, LOCK_UN);
		@fclose($handle);
		@umask(0000);
		@chmod($handle, 0666);
	}
} // END class cache

?>