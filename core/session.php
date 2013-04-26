<?php
/**
 * Session functions
 *
 * Moved all session functions into one file, 
 * include this where session starting might be required
 *
 * @package Core
 * @author  Andreas Goetz <cpuidle@gmx.de>
 * @version $Id: session.php,v 1.13 2008/02/28 20:01:17 andig2 Exp $
 */

// start session
session_start();

require_once './core/functions.php';  // needed for remove_magic_quotes

// get rid of magic quotes
if (get_magic_quotes_gpc())
{
    if (isset($_SESSION)) remove_magic_quotes($_SESSION);
}

/**
 * Get session value or specified default
 */
function session_get($varname, $default=null)
{
    return ($_SESSION['vdb'][$varname]) ? $_SESSION['vdb'][$varname] : $default;
}    

/**
 * Set session value or specified default
 */
function session_set($varname, $value)
{
    $_SESSION['vdb'][$varname] = $value;
}    

/**
 * Upsert session value with current value of global variable or specified default
 */
function session_default($varname, $default=null)
{
    global $$varname;

    if (!isset($$varname))
    {
        $$varname = (isset($_SESSION['vdb'][$varname])) ? $_SESSION['vdb'][$varname] : $default;
    }
    $_SESSION['vdb'][$varname] = $$varname;
}    

?>