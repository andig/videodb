<?php
/**
 * Login Screen
 *
 * Handles the login process
 *
 * @package Multiuser
 * @author  Mike Clark <Mike.Clark@Cinven.com>
 * @version $Id: login.php,v 2.27 2010/04/04 10:34:21 andig2 Exp $
 */
 
require_once './core/session.php';
require_once './core/functions.php';


/**
 * Remove all session data after login or logout
 *
 * @author Andreas Goetz    <cpuidle@gmx.de>
 */
function clear_session()
{
    $_SESSION['vdb'] = array();

    // get script folder for cookie path
    $subdir = substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'],'/')) . '/';

    setcookie('VDBuserid',   '', time()-7200, $subdir);
    setcookie('VDBusername', '', time()-7200, $subdir);
    setcookie('VDBpassword', '', time()-7200, $subdir);
}

// make sure caches are clean
clear_permission_cache();

// Cookie exists but user and pass wasn't given? -> logout
if (!isset($username) && !isset($password) && 
    isset($_COOKIE['VDBusername']) && isset($_COOKIE['VDBpassword'])) 
{
    clear_session();
	redirect('login.php?error='.urlencode($lang['msg_loggedoff']));
}

// login not yet successful
$login = false;

// Check that user entered stuff in username and password boxes
if (!empty($username) && !empty($password))
{
	// Lets check the format of username to make sure its ok
	if (!preg_match('/[a-z]/i', $username)) 
    {
		$error = $lang['msg_invalidchar'];
	} 
    else 
    {
        $res = runSQL("SELECT passwd, id FROM ".TBL_USERS." WHERE name='$username'");

		// if the md5 of the entered password = whats in the database then
		// set all the cookies up again
		if (md5($password) == $res[0]['passwd'])
		{
			$userid = $res[0]['id'];
            login_as($userid, $permanent);
			$login  = true;
		}
        else
        {
			$error = $lang['msg_loginfailed'];
		}
	}
}

if ($login)
{
	if (empty($refer)) $refer = 'index.php';
	redirect(urldecode($refer));
}
else
{
    // prepare templates
    tpl_page('multiuser');

	$smarty->assign('error', $error);
	$smarty->assign('refer', $refer);

    // display templates
    tpl_display('login.tpl');
}

?>
