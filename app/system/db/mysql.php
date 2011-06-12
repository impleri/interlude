<?php
/**
 * mysql db connector
 *
 * @package interlude
 * @subpackage app
 * @copyright Christopher Roussel <christopher@impleri.net>
 */

if (!defined('PLAY_MUSIC')) {
	die('Play it from the top, Sammie.');
}

class ilDatabaseMysql extends ilParentDatabase
{
	private $sql;
	private $keys;
	private $vals;

	function __construct($server, $user, $pass, $db, $persistent=true)
	{
		parent::__construct();
		return parent::connect($server, $user, $pass, $db, $persistent);
	}

	function __destruct()
	{
		return parent::disconnect();
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
