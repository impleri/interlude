<?php
/* file: system/core
** begin: 01/01/2006
** $Revision$
** $Date$
**
** description: the workhorse
**/

if (!defined('PLAY_MUSIC'))
{
	die('Start from the beginning.');
}

/* This is the workhorse class that does the bulk of the processing.
 * It is static, so it does not let loaded as a variable
 */

static class interlude
{

    /*
     * method: process
     * ---------------
     * this does all the magic
     */
    public function process()
    {
        global $debug;

        if (interlude::preload())
        {
            interlude::load();
        }
        if ($debug->fatality())
        {
            interlude::halt();
        }
            interlude::display();
            interlude::unload();
    }

    /*
     * method: preload
     * ---------------
     * this determines which plugins are available and sets everything to load
     * as cleanly as possible
     */
    private function preload()
    {

    }

    /*
     * method: load
     * ------------
     * this detects what needs to be done and loads the appropriate parts
     */
    private function load()
    {

    }

    /*
     * method: display
     * ---------------
     * this sets everything for output to wherever
     */
    private function display()
    {

    }

    /*
     * method: unload
     * ---------------
     * this reclaims as much memory as possible
     */
    private function unload()
    {

    }

    /*
     * method: halt
     * ---------------
     * if there is a big error, this stops everything gracefully
     */
    private function halt()
    {

    }
}

?>