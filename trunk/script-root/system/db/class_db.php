<?php
/* file: system/class_db
** begin: 01/01/2006
** $Revision$
** $Date$
**
** description: practical dbal layer.
**/

if (!defined('PLAY_MUSIC'))
{
	die('Start from the beginning.');
}

require($IL_ROOT . 'dbal/' . $db_config['dbal'] . '.' . $IL_EXT);

class db_class extends db_layer
{
    private var $sql;
    private var $keys;
    private var $vals;

        function __construct($server, $user, $pass, $db, $persistent=true)
        {
            parent::__construct();
            return parent::connect($server, $user, $pass, $db, $persistent);
        }

        function __destruct()
        {
            return parent::disconnect();
        }
}

/*
 * Table class
 * --------------
 * This is a parent class for interaction with the db
 * on a simple level
 *
 * Functions: select, insert, update, delete
 */

class db_table
{
	function __construct()
	{

	}

	function __destruct()
	{

	}

	function select($fields=false, $wheres=false, $joins=false, $as=false)
	{
		global $db;

		if(!$fields)
		{
			$query = 'SELECT ' . ($as) ? $as . '.*' : '*';
		}
		else
		{
			if(!is_array($fields))
			{
				$fields = array($fields);
			}

			if($as)
			{
				$extra = $as . '.';
			}
			else
			{
				$extra = '';
			}

			$query = 'SELECT ' . $extra . $db->escape(implode(', ' . $extra, $fields), NAME);
		}

		$query .= "\nFROM " . $db->escape($this->table, NAME);

		if($as)
		{
			$query .= ' AS' . $as;
		}

		if(is_array($joins))
		{
			foreach ($joins as $type => $join)
			{
				$query .= "\n" . strtoupper($type) ' JOIN ' . $join['table'] . ' AS ' . $join['as'] . ' ON ' . $join['as'] . '.' . $join['link'] . '=' . $as . '.' . $join['link'];
			}
		}

		if(is_array($wheres))
		{
			$query .= "\nWHERE " . implode("\nAND ", $wheres);
		}

		$res = $db->query($query, false, __LINE__, __FILE__);
		unset($query, $extra);
		return $res;
	}

	function insert($fields, $key=false)
	{
		$query = 'INSERT INTO ' . $db->escape($this->table, NAME);
		$query .= "\n(" . $db->escape(implode(', ', array_keys($fields)), NAME) . ')';
		$query .= "\n VALUES (" . implode(', ', array_values($fields)) . ')';

		$db->query($query, false, __LINE__, __FILE__);
		unset($query);

		if($key)
		{
			return $db->nextid();
		}

		return;
	}

	function update($fields, $wheres=true)
	{
		$query = 'UPDATE ' . $db->escape($this->table, NAME);
		$query .="\nSET ";
		$first = true;
		foreach($fields as $field=>$value)
		{
			if(!$first)
			{
				$query .= ', ';
			}
			$query .= $db->escape($field, NAME) . ' = ' . $db->escape($value);
			$first = false;
		}

		if(is_array($wheres))
		{
			$query .= "\nWHERE " . implode("\nAND ", $wheres);
		}
		elseif($wheres)
		{
			$query .= "\nWHERE " . $this->key . '=' . $this->data[$this->key];
		}

		$db->query($query, false, __LINE__, __FILE__);
		unset($query, $first);

		return;
	}

	function delete($where)
	{
		$query = 'DELETE FROM ' . $db->escape($this->table, NAME);
		$query .= "\nWHERE" . $where;
		$db->query($query, false, __LINE__, __FILE__);
		unset($query);
		return;
	}


}

$db = new db_class($db_config['sqlserver'], $db_config['sqluser'], $db_config['sqlpass'], $db_config['sqldb'], $db_config['sqlpersistent']);
unset($db_config);

?>