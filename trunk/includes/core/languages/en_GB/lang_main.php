<?php
/***************************************************************************
 * @version $Id$
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

/* Use this schema:
 * $data['L_KEY'] = "Value for SQL field KEY.";
 * $data['E_KEY'] = "Value for explaining SQL field KEY.";
 * $data['N_KEY'] = "Value for Numeral KEY.";
 * $data['G_KEY'] = "Value for General KEY.";
 * $data['T_KEY'] = "Value for HTML Text KEY.";
 * $data['H_KEY'] = "Value for HTML Heading KEY.";
 * $data['S_KEY'] = "Value for System Message KEY.";
 * $data['D_KEY'] = "Value for System Message Header KEY.";
 */

/* User Fields */
$data['L_NAME'] = "Name";
$data['L_USERNAME'] = "Username";
$data['L_EMAIL'] = "Email";
$data['L_UID'] = "User ID";
$data['L_LASTVISIT'] = "Last Visit";

/* Error Headers */
$data['D_PHP_Invalid'] = "Invalid PHP Version";
$data['D_SQL_Connect'] = "SQL Connect Error";
$data['D_SQL_DB'] = "Database Connect Error";
$data['D_DB_Error'] = "Database Error";
$data['D_Install_Dir'] = "CMS Installed";
$data['D_Offline'] = "System Offline";
$data['D_Session'] = "Session Error";
$data['D_Information'] = "Information";
$data['D_General_Error'] = "General Error";
$data['D_Critical_Error'] = "Critical Error";
$data['D_Template_Missing'] = "Template File Missing";

/* Error Messages */
$data['S_PHP_Connect'] = "The system could not find the PHP command <u>mysql_connect</u>.";
$data['S_SQL_Connect'] = "The system could not connect to MySQL.";
$data['S_SQL_DB'] = "The system could not select the database.";
$data['S_Query_Error'] = "There was an error in the SQL query.";
$data['S_Install_Dir'] = "The <u>/install/</u> directory needs to be removed.";
$data['S_Offline'] = "This system is currently offline.";
$data['S_Session_Update'] = "There was an error updating the session in the database.";
$data['S_Session_Insert'] = "There was an error inserting the session into the database.";
$data['S_Custom_Error'] = "Please contact the %ssite administrator%s immediately.";
$data['S_Multiple_Errors'] = "The system committed multiple errors.";
$data['S_Error_Occured'] = "An error has occured.";
$data['S_Critical_Error'] = "A critical error has occured.";
$data['S_Template_Missing'] = "Could not locate a required file";


?>