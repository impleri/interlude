<?php
/* file: system/init
** begin: 01/01/2006
** $Revision$
** $Date$
**
** description: core startup file
**/

if (!defined('PLAY_MUSIC'))
{
	die('Start from the beginning.');
}


// check config
$inc_path = $IL_ROOT . 'config.' . $IL_EXT;
if (!file_exists($inc_path))
{
    header('Location: install/install.php');
    exit;
}
require_once $inc_path;
if (!defined('IL_INSTALLED'))
{
    header('Location: install/install.php');
    exit;
}

// load the backend
$init = array('functions', 'class_debug', 'class_db', 'class_config', 'core');
foreach ($init as $inc)
{
    $inc_path = $IL_ROOT . $inc . '.' . $IL_EXT;
    if (!file_exists($inc_path))
    {
        die(sprintf($IL_ERROR, $inc_path));
    }
    require_once $inc_path;
}

?>