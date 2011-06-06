<?php
//
//	file: includes/class_auth
//	begin: 01/01/2006
//	$Author$
//	$Revision$
//	$Date$
//
//	description: session handler

if (!defined('PLAY_MUSIC'))
{
	die('No peeksies!');
}

/*
 * Session class
 * -------------
 * This is the class for session management.
 *
 * This is based on Mambo's session class
 *
 * Functions: session, init, login, logout, load, purge
 */

class session
{
	var $_sessionid = null;
	var $_s_cookie = null;
	var $_d_cookie = null;
	var $_user_ip = null;
	var $s_id = null;
	var $s_time = null;
	var $s_start = null;
	var $s_userid = null;
	var $s_page = null;
	var $s_ipaddr = null;
	var $s_logged_in = null;
	var $s_admin = null;

	/*
	 * function session (Constructor)
	 * ------------------------------
	 * Currently blank
	 */

	function session()
	{
		global $db, $config, $my;
		$this->purge($config->data['lifetime']);

		$this->_s_cookie = trim($_REQUEST[$config->data['cookie'].'_id']);
		$this->_d_cookie = trim($_REQUEST[$config->data['cookie'].'_data']);
		$this->_user_ip = get_ip();

		$sql = "SELECT * FROM `%__sessions` WHERE `s_id`='".$this->_s_cookie."' AND `s_ipaddr`='".$this->_user_ip."'";
		$db->setQuery($sql);
		if ( $db->loadObject($this) ) {
			$this->_sessionid = $this->s_id;
			$this->s_time = time();
		} else {
			list($sec, $usec) = explode(' ', microtime());
			mt_srand((float) $sec + ((float) $usec * 100000));
			$this->_sessionid = md5(uniqid(mt_rand(), true));
			$this->s_id = $this->_sessionid;
			$this->s_start = time();
			$this->s_time = time();
			$this->s_userid = '-1';
			$this->s_ipaddr = $this->_user_ip;
			$this->s_logged_in = '0';
			$this->s_admin = '0';

		}
	}

	/*
	 * function init
	 * -------------
	 * Initializes and updates the session in the database
	 * Also attempts login if a remember cookie is set
	 */

	function init($page=-10)
	{
		global $db, $config, $my;

		$this->s_page = $page;
		if($this->s_time == $this->s_start) {
			if(!($db->insertObject("%__sessions", $this, "s_id"))) {
				message_die(_GENERAL, 'S_Session_Insert', 'D_Session', $db->_errlog);
				exit;
			}
		}
		else {
			if(!($db->updateObject("%__sessions", $this, "s_id"))) {
				message_die(_GENERAL, 'S_Session_Update', 'D_Session', $db->_errlog);
				exit;
			}
		}

		setcookie( $config->data['cookie'].'_id', $this->s_id, time() + $config->data['lifetime'], $config->data['cookieserver'] );

		if ($d_cookie) {
			$d_cookie= unserialize($d_cookie);
			$this->login($d_cookie['name'], $d_cookie['pw']);
		}
		$my->_page = $page;
		$my->load( $this->s_userid );
	}

	/*
	 * function login
	 * --------------
	 * Takes login information from post and checks the info with
	 * the users table.  If valid, updates sessions table.
	 */

	function login( $username=null,$passwd=null )
	{
		global $db, $config;

		$s_cookie = trim($_REQUEST[$config->data['cookie'].'_id']);
		$d_cookie = trim($_REQUEST[$config->data['cookie'].'_data']);
		$user_ip = get_ip();

		if (!$username || !$passwd) {
			$username = trim( $_REQUEST['username']);
			$passwd = trim( $_REQUEST['pass']);
			$passwd = md5( $passwd );
		}
		$remember = trim( $_REQUEST['remember']);

		if (!$username || !$passwd) {
			echo "<script>alert(\""._LOGIN_INCOMPLETE."\"); window.history.go(-1); </script>\n";
			exit;
		} else {
			$db->setQuery( "SELECT `u_id`, `u_active`"
			. "\nFROM `%__users`"
			. "\nWHERE `username`='$username' AND `u_password`='$passwd'"
			);
			$row = null;
			if ($db->loadObject( $row )) {
				if ($row->u_active == 0) {
					echo "<script>alert(\""._LOGIN_BLOCKED."\"); window.history.go(-1); </script>\n";
					exit;
				}

				$this->s_time = time();
				$this->s_userid = $row->u_id;
				$this->s_logged_in = '1';

				if(!($db->updateObject("%__sessions", $this, "s_id"))) {
					message_die(_GENERAL, 'S_Session_Update', 'D_Session', $db->_errlog);
					exit;
				}

				$now = time();
				$query = "UPDATE `%__users` SET `u_lastvisit`='$now' where `u_id`='$row->u_id'";
				$db->setQuery($query);
				if (!$db->query()) {
					message_die(_GENERAL, 'S_Users_Update', 'D_Session', $db->_errlog);
					exit;
				}

				if ($remember==true) {
					$lifetime = time() + 31536000;
					$d_cookie=array();
					$d_cookie['name'] = $username;
					$d_cookie['pw'] = $passwd;
					$d_cookie = serialize($d_cookie);
					setcookie( $config->data['cookie'].'_data', $d_cookie, $lifetime, $config->data['cookieserver'] );
				}
			} else {
				echo "<script>alert(\""._LOGIN_INCORRECT."\"); window.history.go(-1); </script>\n";
				exit;
			}
		}
	}

	/*
	 * function logout
	 * ---------------
	 * Reverts session data to Anonymous and updates the session table.
	 * Deletes remember cookies if they are set.
	 */

	function logout() {
		global $db;

		$this->s_time = time();
		$this->s_userid = '-1';
		$this->s_logged_in = '0';
		$this->s_admin = '0';

		if(!($db->updateObject("%__sessions", $this, "s_id"))) {
			message_die(_GENERAL, 'S_Users_Update', 'D_Session', $db->_errlog);
			exit;
		}

		$lifetime = time() - 31536000;
		setcookie( $config->data['cookie'].'_data', " ", $lifetime, $config->data['cookieserver'] );
		setcookie( $config->data['cookie'].'_id', " ", $lifetime, $config->data['cookieserver'] );
	}

	/*
	 * function purge
	 * --------------
	 * Deletes all rows in sessions table older than $inc (1 hour).
	 */

	 function purge( $inc=3600 ) {
		global $db;

		$past = time() - $inc;
		$query = "DELETE FROM `%__sessions`"
		. "\nWHERE (`s_time` < '$past')";
		$db->setQuery($query);

		return $db->query();
	}
} // END class session

?>