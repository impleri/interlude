<?php
/* file: system/class_debug
** begin: 01/01/2006
** $Revision$
** $Date$
**
** description: the debug systems
**/

if (!defined('PLAY_MUSIC'))
{
	die('Start from the beginning.');
}

//define(LIVE_SITE, 1);

class debug
{
    function __construct()
    {

    }

    function debug()
    {
        return $this->__construct();
    }

    function error(

}

$debug = new debug();

// for a regular, live site, we don't want to see any errors

if (defined('LIVE_SITE'))
{
    error_reporting(0);
}
else
{
    error_reporting(E_ALL);
}
?>