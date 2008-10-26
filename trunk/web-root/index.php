<?php
/* file: index
 * begin: 01/01/2006
 * $Revision$
 * $Date$
 *
 * description: startup file.  since everything is handled internally, this is
 *      primarily to set the first few definitions and load the system.
 */

/* INSTRUCTIONS: set the value of this file to the core system directory
 * This can be either an absolute path (like /home/USER/interlude/) or a
 * relative one (like ../../interlude).  The system will try to resolve it to
 * an absolute one later on.  Be sure that it is enclosed in quotation marks
 * (' or ") and the line ends with a semi-colon(;), just like it is now.
 */
$core_path = '/path/to/core/file/startup.php';

/* error message: modify to fit your own site or language needs.  it is used in
 * the off-chance that this script cannot find the primary system ($IL_ROOT).
 */
$IL_ERROR = 'I cannot find the required file %s!  Please contact the system
administrator immediately.';

/* DO NOT EDIT BELOW THIS LINE! */

// basic variables
$IL_EXT = substr(strrchr(__FILE__, '.'), 1);
$IL_WEBROOT = dirname(__FILE__) . '/';

if (!file_exists($core_path))
{
    die(sprintf($IL_ERROR, $core_path));
    exit;
}

// system constant: if this is not declared, nothing will load
define('PLAY_MUSIC', true);

// everything else is handled in the file /PATH/TO/ROOT/system/init.
require_once $core_path;
unset($core_path);

?>