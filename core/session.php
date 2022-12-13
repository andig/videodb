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

#require_once './core/functions.php';  // needed for remove_magic_quotes

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

/**
 * get session_default for owner
 * 
 * basically this only executes the extra query when the global $owner is not set and also
 * not available in session data. only then we need the hasAny check. if the global is set
 * or the global is not set but the session is then session_default() will fix both those
 * cases. put into a single function because it gets called from multiple files.
 */
function session_default_owner()
{
    global $owner, $lang;
    if (!isset($owner) && !isset($_SESSION['vdb']['owner'])) {
        $hasAny = runSQL('SELECT COUNT(*) AS num FROM '.TBL_DATA.' WHERE '.TBL_DATA.'.owner_id = ' . get_current_user_id());
        $hasAny = ($hasAny && isset($hasAny[0]['num']) && $hasAny[0]['num'] > 0);
        $default = ($hasAny ? get_username(get_current_user_id()) : $lang['filter_any']);
        return session_default('owner', $default);
    }
    return session_default('owner');
}
