<?php
/* file: index
** begin: 01/01/2006
** $Revision$
** $Date$
**
** description: startup file.  since everything is handled internally, this is
**      primarily to set the first few definitions and load the system.  it does
**      not need to be modified.
**/

// system constant: if this is not declared, nothing will be loaded
define('PLAY_MUSIC', true);

// root directory: the path to the 'system' folder.
$IL_ROOT = dirname(__FILE__) . '/system/';

/* error message: modify to fit your own site or language needs.  it is used in
 * the off-chance that this script cannot find the primary system ($IL_ROOT).
 */
$IL_ERROR = 'I cannot find the required file %s!  Please contact the system
administrator immediately.';

/* DO NOT EDIT BELOW THIS LINE! */

// file extension
$IL_EXT = substr(strrchr(__FILE__, '.'), 1);

// web root directory: this should be the current directory.
$IL_WEBROOT = dirname(__FILE__) . '/';

$core_path = ($IL_ROOT . 'init.' . $IL_EXT);

if (!file_exists($core_path))
{
    die(sprintf($IL_ERROR, $core_path));
    exit;
}

// everything else is handled in the file /PATH/TO/ROOT/system/init.
require_once $core_path;
unset($core_path);

?>