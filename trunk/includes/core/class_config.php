<?php
/* file: system/config
** begin: 01/01/2006
** $Revision$
** $Date$
**
** description: system config
**/

if (!defined('PLAY_MUSIC'))
{
        die('Start from the beginning.');
}

/*
* Config class
* ------------
* Config Data
*
* Functions: config, read
*/

class config extends db_table
{
        var $data;
        var $data_time;
        var $from_cache;
        var $dynamic;
        var $differ;
        var $recache;
        var $_root;

        /*
        * function __construct
        * -----------------------------
        * Creates the config object and stores skeletal info
        */

        function __construct($root, $webroot, $ext)
        {
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
}

$config = new config($IL_ROOT, $IL_WEBROOT, $IL_EXT);
?>