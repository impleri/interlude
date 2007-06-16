<?php
//
//	file: includes/class_user
//	begin: 01/01/2006
//	$Author$
//	$Revision$
//	$Date$
//
//	description: user class

if (!defined('PLAY_MUSIC'))
{
	die('No peeksies!');
}

/*
 * User class
 * --------------
 * This keeps current user's info handy for checking data and auths.
 *
 * This is based on pthirik's work on the Categories Hierarchy MOD for phpBB2
 *
 * Functions: user, load
 */

class user
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

	function user()
	{
		$this->_page = -10;
	}

	/*
	 * function load
	 * -------------
	 * Loads info from user's table as an array.
	 */

	function load( $uid )
	{
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

?>