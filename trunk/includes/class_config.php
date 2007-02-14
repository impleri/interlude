<?php
/***************************************************************************
 * @version $Id: class_config.php,v 1.8 2005/06/23 14:57:22 impleri Exp $
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
 * To do : Add run_stats class
 *
 ***************************************************************************/

defined( '_IN_PLUS' ) or die( 'Direct Access to this location is not allowed.' );


/*
 * Config class
 * ------------
 * Config Data
 *
 * Functions: config, read
 */
 
class config
{
	var $data;
	var $data_time;
	var $from_cache;
	var $dynamic;
	var $differ;
	var $recache;
	var $_root;

	/*
	 * function config (Constructor)
	 * -----------------------------
	 * Creates the config object and stores skeletal info
	 */
	 
	 function config()
	{
		global $root_path;
		
		$this->data = array();
		$this->data_time = 0;
		$this->from_cache = false;
		$this->recache = false;
		$this->_root = $root_path;
	}
	
	/*
	 * function read
	 * -------------
	 * Loads config data from SQL and cache
	 */
	 
	function read($force=false)
	{
		global $db;
		// get dynamic values
		$sql = "SELECT `sc_name`, `sc_value` 
					FROM `%__config` 
					WHERE `sc_static` = '0'";
		$db->setQuery($sql);
		if (!($rows = $db->loadAssocList('sc_name'))) {
			return false;
		}
		$this->data_time = time();
		foreach($rows as $row) {
			$this->data[$row['sc_name']] = $row['sc_value'];
		}

		// get static values
		$db_cached = new cache('dta_config', $this->data['cache_cfg']);
		$sql = "SELECT `sc_name`, `sc_value`
					FROM `%__config`
					WHERE `sc_static` = '1'";
		$rows = $db_cached->read($sql, $force, 'sc_name');
		if ( !empty($rows) ) {
			foreach($rows as $row) {
				$data[$row['sc_name']] = $row['sc_value'];
			}
			$this->data = array_merge($this->data, $data);
			unset($data);
			$this->data_time = $db_cached->data_time;
		}
		$this->from_cache = $db_cached->from_cache;

		return true;
	}
} // END class config

// Future work/reference

// sql/run stat class
/*
class stat_run_class
{
	var $endtime;

	function stat_run_class($endtime)
	{
		$this->endtime = $endtime;
	}

	function display()
	{
		global $db, $template, $user, $config;
		global $starttime, $trc_loc_start, $trc_loc_end;
		global $lang;

		if ( !defined('DEBUG_RUN_STATS') )
		{
			return;
		}

		// lang keys
		if ( empty($lang) || empty($lang['Stat_surround']) )
		{
			$lang = array(
				'Stat_surround' => '[ %s ]',
				'Stat_sep' => ' - ',
				'Stat_page_duration' => 'Time: %.4fs',
				'Stat_local_duration' => 'local trace: %.4fs',
				'Stat_part_php' => 'PHP: %.2d%%',
				'Stat_part_sql' => 'SQL: %.2d%%',
				'Stat_queries_total' => 'Queries: %2d (%.4fs)',
				'Stat_queries_db' => 'db: %2d (%.4fs)',
				'Stat_queries_cache' => 'cache: %2d (%.4fs/%.4fs)',
				'Stat_gzip_enable' => 'GZIP on',
				'Stat_debug_enable' => 'Debug on',

				'Stat_request' => 'Request',
				'Stat_line' => 'Line:&nbsp;%d',
				'Stat_cache' => 'cache:&nbsp;%.4fs',
				'Stat_db' => 'db:&nbsp;%.4fs',
				'Stat_table' => 'Table',
				'Stat_type' => 'Type',
				'Stat_possible_keys' => 'Possible keys',
				'Stat_key' => 'Used key',
				'Stat_key_len' => 'Key length',
				'Stat_ref' => 'Ref.',
				'Stat_rows' => 'Rows',
				'Stat_Extra' => 'Comment',
				'Stat_Comment' => 'Comment',
			);
		}

		// trace informations
		$pag_duration = convert_microtime($this->endtime) - convert_microtime($starttime);
		$sql_dump = '';
		$sql_duration = $count_cached = $dur_cached = $dur_cached_real = 0;

		// browse all requests
		$color = false;
		$trc_sql = $db->trc_sql;
		$count_trc_sql = count($trc_sql);
		for ( $i = 0; $i < $count_trc_sql; $i++ )
		{
			// cached request
			$cached = isset($trc_sql[$i]['cached']) && $trc_sql[$i]['cached'];

			// duration
			$sql_dur = convert_microtime($trc_sql[$i]['end']) - convert_microtime($trc_sql[$i]['start']);
			$sql_duration += $sql_dur;
			if ( $cached )
			{
				$count_cached++;
				$dur_cached += $sql_dur;
			}

			// dump informations
			if ( defined('DEBUG_SQL') && ($user->data['user_level'] == ADMIN) )
			{
				$sql_real_dur = 0;
				if ( $cached )
				{
					$db->trc_sql = array();
					$sql = $trc_sql[$i]['sql'];
					$db->sql_query($sql, false, __LINE__, __FILE__);
					$sql_real_dur = convert_microtime($db->trc_sql[0]['end']) - convert_microtime($db->trc_sql[0]['start']);
					$dur_cached_real += $sql_real_dur;
				}

				// display
				$color = !$color;
				$template->assign_block_vars('stat_run', array(
					'STAT_FILE' => $trc_sql[$i]['file'],
					'STAT_LINE' => sprintf($lang['Stat_line'], $trc_sql[$i]['line']),
					'STAT_TIME_CACHE' => $cached ? sprintf($lang['Stat_cache'], $sql_dur) : '',
					'STAT_TIME_DB' => $cached ? sprintf($lang['Stat_db'], $sql_real_dur) : sprintf($lang['Stat_db'], $sql_dur),
					'STAT_REQUEST' => preg_replace('/[\n\r\s\t]+/', ' ', $trc_sql[$i]['sql']),
				));
				$template->set_switch('stat_run.light', $color);
				$template->set_switch('stat_run.cached', $cached);

				// for mysql, explain request
				$request_explain = '';
				if ( in_array(SQL_LAYER, array('mysql', 'mysql4', 'postgresql')) && !preg_match('/^(UPDATE|INSERT|DELETE|SHOW)/i', $trc_sql[$i]['sql']) )
				{
					// get explainations
					$sql = 'EXPLAIN ' . $trc_sql[$i]['sql'];
					$result = $db->sql_query($sql, false, __LINE__, __FILE__);
					$first_table = true;
					$explain_color = false;
					while ( $row = $db->sql_fetchrow($result) )
					{
						// send legend
						if ( $first_table )
						{
							$template->set_switch('stat_run.explain');
							foreach ( $row as $key => $value )
							{
								if ( !is_integer($key) )
								{
									$template->assign_block_vars('stat_run.explain.cell', array(
										'STAT_LEGEND' => isset($lang['Stat_' . $key]) ? $lang['Stat_' . $key] : str_replace('_', ' ', $key),
									));
								}
							}
						}
						$first_table = false;

						// send explain values
						$explain_color = !$explain_color;
						$template->set_switch('stat_run.explain.table');
						foreach ( $row as $key => $value )
						{
							if ( !is_integer($key) )
							{
								$template->assign_block_vars('stat_run.explain.table.cell', array(
									'STAT_VALUE' => $value,
								));
								$template->set_switch('stat_run.explain.table.cell.light', $explain_color);
							}
						}
					}
				}
			}
		}

		// duration
		$duration = array(
			sprintf($lang['Stat_page_duration'], $pag_duration),
		);
		if ( !empty($trc_loc_start) || !empty($trc_loc_end) )
		{
			$duration[] = sprintf($lang['Stat_local_duration'], convert_microtime(empty($trc_loc_end)? $trc_end : $trc_loc_end) - convert_microtime(empty($trc_loc_start) ? $starttime : $trc_loc_start));
		}

		// parts
		$sql_part = round(($sql_duration / $pag_duration) * 100);
		$parts = array(
			sprintf($lang['Stat_part_php'], 100 - $sql_part),
			sprintf($lang['Stat_part_sql'], $sql_part),
		);

		// queries
		$queries = defined('DEBUG_SQL') && !empty($count_cached) ? array(
			sprintf($lang['Stat_queries_total'], $count_trc_sql, $sql_duration),
			sprintf($lang['Stat_queries_db'], $count_trc_sql - $count_cached, $sql_duration - $dur_cached),
			sprintf($lang['Stat_queries_cache'], $count_cached, $dur_cached, $dur_cached_real),
		) : array(
			sprintf($lang['Stat_queries_total'], $count_trc_sql - $count_cached, $sql_duration - $dur_cached),
		);

		// setup
		$setup = array();
		if ( $config->data['gzip_compress'] )
		{
			$setup[] = $lang['Stat_gzip_enable'];
		}
		if ( DEBUG )
		{
			$setup[] = $lang['Stat_debug_enable'];
		}

		// display stats
		$template->assign_vars(array(
			'L_STAT_PAGE_DUR' => sprintf($lang['Stat_surround'], implode($lang['Stat_sep'], $duration)),
			'L_STAT_PARTS' => sprintf($lang['Stat_surround'], implode($lang['Stat_sep'], $parts)),
			'L_STAT_QUERIES' => sprintf($lang['Stat_surround'], implode($lang['Stat_sep'], $queries)),
			'L_STAT_SETUP' => empty($setup) ? '' : sprintf($lang['Stat_surround'], implode($lang['Stat_sep'], $setup)),
			'L_STAT_REQUEST' => $lang['Stat_request'],
		));

		// display all
		$template->set_filenames(array('stat_run' => 'stat_run_body.tpl'));
		$template->pparse('stat_run');
	}
}
*/
?>