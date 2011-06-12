<?php
/**
 * generic database accessible object
 *
 * @package interlude
 * @subpackage framework
 * @copyright Christopher Roussel <christopher@impleri.net>
 */

if (!defined('PLAY_MUSIC')) {
	die('Play it from the top, Sammie.');
}

/**
 *
 *
 */
class ilParentDataObject extends ilParentObject {
	protected $_db = null;

	public function __construct (&$db=null) {
		if (empty($db)) {
			$db = ilFactory::getDatabase();
		}
		$this->_db = $db;

		parent::__construct();
	}

	/**
	 * get table name
	 *
	 * @param string name component to get
	 * @param string type of class for checking
	 * @return string name component requested
	 */
	public function getName($name='name', $type='') {
		$class = ilSplitClassName(get_class($this));

		if (!empty($type)) {
			if ($class['type'] != $type) {
				$this->setError('IL_PARENT_CANNOT_IDENTIFY_NAME');
				return false;
			}
		}

		return $class[$name];
	}

}