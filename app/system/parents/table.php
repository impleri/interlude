<?php
/**
 * generic database record object
 *
 * @package interlude
 * @subpackage framework
 * @copyright Christopher Roussel <christopher@impleri.net>
 */

if (!defined('PLAY_MUSIC')) {
	die('Play it from the top, Sammie.');
}

/**
 * generic table object
 *
 * should be used as parent for all table libraries
 */
class ilParentTable extends ilParentDataObject {
	/**
	 * @var table name (without prefix)
	 */
	protected $_table = '';

	/**
	 * @var table key name
	 */
	protected $_key = 'id';

	/**
	 * one-to-one associations
	 *
	 * if one of these are defined, their objects are added to this one and
	 * relations are connected.
	 *
	 * example: setting ilTablePost::$_belongsTo with topic will populate
	 * ilTablePost::$_topic with the topic associated with the post.
	 */
	protected $_hasOne = array();

	/**
	 * one-to-many associations
	 *
	 * if one of these are defined, their objects are added to this one and
	 * relations are connected.
	 *
	 * example: ilTableTopic has many ilTablePosts (and each ilTablePost belongs
	 * to one ilTableTopic). By defining this property (ilTableTopic::$_hasMany
	 * include posts), fetching a topic will populate ilTableTopic::$_posts with
	 * one ilTablePost per post associated with the topic.
	 */
	protected $_hasMany = array();

	/**
	 * many-to-one associations
	 *
	 * if one of these are defined, their objects are added to this one and
	 * relations are connected.
	 *
	 * example:setting ilTablePost::$_belongsTo with topic will populate
	 * ilTablePost::$_topic with the topic associated with the post.
	 */
	protected $_belongsTo = array();

	/**
	 * many-to-many associations
	 *
	 * if defined, the named object are added to this one and linked automatically
	 *
	 * example:setting this to include 'tags' will associate $this::$_tags with
	 * ilTableTag crossreferenced from a join table.
	 */
	protected $_hasAndBelongsToMany = array();

	/**
	 * autosave
	 *
	 * if set to true (default), the destructor method will save() first.
	 */
	protected $_autosave = true;

	/**
	 * automatic associations
	 *
	 * if set to true (default), common method will also run the same method on
	 * related objects.
	 */
	protected $_autoObjects = true;

	/**
	 * field settings
	 *
	 * if set, the validate() method will automatically check these fields.
	 * @see ilParentTable::validate() for specifics
	 */
	protected $_fields = array();

	/**
	 * constructor method
	 *
	 * @param string table name (can be set in above property definition)
	 * @param string table key (also can be set in above propery definition)
	 * @param ilDatabase database abstraction layer object
	 */
	public function __construct ($table='', $key='', &$db=null) {
		parent::__construct($db);

		// set $table if passed
		if (!empty($table)) {
			$this->_table = $table;
		}

		// set $key if passed
		if (!empty($key)) {
			$this->_key = $key;
		}

		// try to identify table name from class name if not defined
		if (empty($this->_table)) {
			$table = $this->getTableName();
			// still no table name defined
			if (!$table) {
				return false;
			}
			$this->_table = $table;
		}

		$this->reset(false);
	}

	/**
	 * destructor method
	 */
	public function __destruct() {
		$key = $this->_key;
		if ($this->_autosave && !is_null($this->$key)) {
			ilExtensions::triggerAction('before'.$triggerName.'Autosave', $this);
			if ($this->validate()) {
				$this->save($this->_autoObjects);
			}
			ilExtensions::triggerAction('after'.$triggerName.'Autosave', $this);
		}
	}

	/**
	 *static method to load tables
	 *
	 * @param string table name
	 * @param string extension shortcode (default is `il`)
	 * @return object for table
	 */
	public static function &getInstance ($table, $extension='il') {
		static $instances = array();

		$table = ucfirst($table);
		if (!isset($instances[$table])) {
			$class = strtolower($extension) . 'Table' . $table;
			$instances[$table] = new $class($table);
		}

		return $instances[$table];
	}

	/**
	 * get table name
	 *
	 * @param string component name to retrieve
	 * @return string table name lowercased and underscored
	 */
	public function getName($name='nameLower') {
		return parent::getName($name, 'Table');
	}

	/**
	 * get table columns from database
	 *
	 * @return array of database fields
	 */
	protected function getDbFields() {
		static $fields = array();

		if (empty($fields)) {
			$table = $this->_db->getTableDetails($this->_table);
			if ($table->getError()) {
				$this->setError($table->getErrors());
				return array();
			}
			$fields = $table->fields;
		}

		return $fields;
	}

	/**
	 * get associated objects
	 *
	 * @return array of all associated tables
	 */
	public function getRelations() {
		$ret = array_merge($this->_hasOne, $this->_hasMany, $this->_belongsTo, $this->_hasAndBelongsToMany);
		ilExtensions::triggerAction('on'.$triggerName.'GetRelations', $this, $ret);
		return $ret;
	}

	/**
	 * reset properties and objects
	 *
	 * @param boolean reset related objects
	 */
	public function reset ($resetObjects=true) {
		// first reset defined properties to their default values
		$properties = $this->getProperties();
		$defaults = get_class_vars($this);
		ilExtensions::triggerAction('before'.$triggerName.'ResetProperties', $this, $properties, $defaults);
		foreach ($properties as $property) {
			if (isset($defaults[$property])) {
				$this->$property = $defaults[$property];
			}
			else {
				unset($this->$property);
			}
		}
		ilExtensions::triggerAction('after'.$triggerName.'ResetProperties', $this, $properties, $defaults);

		// then reset database columns
		$fields = $this->getDbFields();
		ilExtensions::triggerAction('before'.$triggerName.'ResetFields', $this, $fields);
		if (!empty($fields)) {
			foreach ($fields as $name => $dummy) {
				if (!property_exists($this, $name)) {
					$this->$name = null;
				}
			}
		}
		ilExtensions::triggerAction('after'.$triggerName.'ResetFields', $this, $fields);

		// finally reset associated objects
		if ($resetObjects) {
			$objects = $this->getRelations();
			ilExtensions::triggerAction('before'.$triggerName.'ResetObjects', $this, $objects);
			foreach ($objects as $name) {
				$this->$obj->reset(false);
			}
			ilExtensions::triggerAction('after'.$triggerName.'ResetObjects', $this, $objects);
		}
	}

	/**
	 * create a new record from default properties
	 *
	 * @param boolean create related objects
	 */
	public function new ($newObjects=true) {
		$this->reset($newObjects);
		if (!$this->_db->select($this->_table, array($this->_key => 0))) {
			$this->setError($this->_db->getError());
			return false;
		}
		return true;
	}

	/**
	 * load a record from database
	 *
	 * @param int object id to load
	 * @param boolean load related objects
	 */
	public function load ($id=0, $loadObjects=true) {
		$this->reset($loadObjects);
		if (!$this->_db->select($this->_table, array($this->_key => $id))) {
			$this->setError($this->_db->getError());
			return false;
		}
		return true;
	}

	/**
	 * search table
	 *
	 * @param array search details
	 * @return array|boolean array of objects, false on error
	 */
	public function find ($vars=array()) {}

	/**
	 * bind an array or object
	 *
	 * @param object|array data to bind
	 * @param boolean push data to related objects
	 * @return boolean true on success
	 */
	public function bind ($data, $bindObjects=true) {
		$triggerName = $this->getName('name');
		// first convert objects into arrays
		if (is_object($data)) {
			$data = get_object_vars($data);
		}

		// next bind related objects
		if ($bindObjects) {
			$objects = $this->getRelations();
			ilExtensions::triggerAction('before'.$triggerName.'BindObjects', $this, $objects);
			// should probably do an array_intersect to save time in iterating through needlessly
			// $intersect = array_intersect(array_keys($data), $objects);
			foreach ($objects as $name) {
				if (isset($data[$name])) {
					$obj = '_' . $name;
					if (!$this->$obj->bind($data[$name], false)) {
						$this->setError($this->$obj->getErrors());
						return false;
					}
				}
			}
			ilExtensions::triggerAction('after'.$triggerName.'BindObjects', $this, $objects);
		}

		// then only set properties that should be in this table
		$properties = $this->getProperties();
		ilExtensions::triggerAction('before'.$triggerName.'Bind', $this, $properties);
		foreach ($properties as $property) {
			if (isset($data[$property])) {
				$this->$property = $data[$property];
			}
		}
		ilExtensions::triggerAction('after'.$triggerName.'Bind', $this, $properties);

		return true;
	}

	/**
	 * validate columns
	 *
	 * @param boolean validate related objects
	 * @return boolean true on success
	 */
	public function validate ($validateObjects=true) {
		// validate our fields
		ilExtensions::triggerAction('before'.$triggerName.'Validate', $this);
		foreach ($this->_fields as $name => $tests) {
			foreach ($tests as $test => $value) {
				switch ($test) {
					case 'present':
						if (empty($this->$name)) {
							$this->setError('IL_TABLE_FIELD_INVALID_PRESENT');
							return false;
						}
						break;
					case 'min_length':
						if (strlen($this->$name) < $value) {
							$this->setError('IL_TABLE_FIELD_INVALID_MIN_LENGTH');
							return false;
						}
						break;
					case 'max_length':
						if (strlen($this->$name) > $value) {
							$this->setError('IL_TABLE_FIELD_INVALID_MAX_LENGTH');
							return false;
						}
						break;
				}
			}
		}
		ilExtensions::triggerAction('after'.$triggerName.'Validate', $this);

		// then validate objects
		if ($validateObjects) {
			$objects = $this->getRelations();
			ilExtensions::triggerAction('before'.$triggerName.'ValidateObjects', $this, $objects);
			foreach ($objects as $name) {
				$obj = '_' . $name;
				if (!$this->$obj->validate(false)) {
					$this->setError($this->$obj->getErrors());
				}
			}
			ilExtensions::triggerAction('after'.$triggerName.'ValidateObjects', $this, $objects);
		}

		return true;
	}

	/**
	 * save object to database
	 *
	 * @param boolean save related objects
	 * @return boolean true on success
	 */
	public function save ($saveObjects=true) {
		//do save

		// then save objects
		if ($saveObjects) {
			$objects = $this->getRelations();
			foreach ($objects as $name) {
				$obj = '_' . $name;
				if (!$this->$obj->save(false)) {
					$this->setError($this->$obj->getErrors());
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * shortcut to bind, validate, and save in one go
	 *
	 * @param object|array data to save
	 * @param boolean store related objects
	 * @return boolean true on success
	 */
	public function store ($data=null, $storeObjects=null) {
		if (is_null($storeObjects)) {
			$storeObjects = $this->_autoObjects;
		}

		if (!empty($data)) {
			if (!$this->bind($data, $storeObjects)) {
				return false;
			}
		}

		if (!$this->validate($storeObjects)) {
			return false;
		}

		if (!$this->save($storeObjects)) {
			return false;
		}

		return true;
	}

	/**
	 * move record(s) to trash
	 *
	 * @param int|array object id(s) to move to trash
	 * @return boolean true on success
	 */
	public function trash($ids=null) {}

	/**
	 * retrieve record from trash
	 *
	 * @param int|array object id(s) to move from trash
	 * @return boolean true on success
	 */
	public function rescue($ids=array()) {}

	/**
	 * delete record from trash
	 *
	 * @param int|array object id(s) to remove from trash
	 * @return boolean true on success
	 */
	public function purge($ids=array()) {}

	/**
	 * toggle a boolean field on/off
	 *
	 * @param string field to toggle
	 * @param boolean toggle value
	 * @return string table name lowercased and underscored
	 */
	protected function toggle ($field, $on=true) {
		$this->$field = $on;
		$this->save(false);
	}
}
