<?php
/**
 * generic user object
 *
 * @package interlude
 * @subpackage framework
 * @copyright Christopher Roussel <christopher@impleri.net>
 */

if (!defined('PLAY_MUSIC')) {
	die('Play it from the top, Sammie.');
}

/*
 * User class
 * --------------
 * This keeps current user's info handy for checking data and auths.
 *
 * Functions: user, load
 */

class ilUser extends ilParentTable
{
	var $uid=null;
	var $data=null;
	var $auths=null;
	var $_page=null;

	/*
	 * function user (Constructor)
	 * ---------------------------
	 * Sets uid variable to what the session class authenticated
	 */
	function user() {
		$this->_page = -10;
	}

	/*
	 * function load
	 * -------------
	 * Loads info from user's table as an array.
	 */

	function load( $uid ) {
		global $db;
		$this->uid = $uid;

		$this->data = array();
		if ( $this->uid > 0 ) {
/*			$sql = 'SELECT u.*, g.*
						FROM ' . USERS_TABLE . ' u
							LEFT JOIN ' . GROUPS_TABLE . ' g
								ON g.group_user_id = u.user_id
						WHERE u.user_id = ' . intval($user_id); */
			$sql = "SELECT * FROM `%__users` WHERE `u_id` = '" . intval($this->uid) . "'";
			$db->setQuery($sql);
			if (!($row = $db->loadAssocList())) {
				return false;
			}
			$this->data = $row[0];
		}
		if ( !empty($this->data) ) {
//			$this->get_groups_list();
		}
	}
} // END class user
