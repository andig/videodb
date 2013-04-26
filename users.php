<?php
/**
 * User Management
 *
 * User administration functions
 *
 * @package Multiuser
 * @author  Andreas Gohr <a.gohr@web.de>
 * @author  Andreas Götz <cpuidle@gmx.de>
 * @version $Id: users.php,v 1.23 2013/03/15 16:42:46 andig2 Exp $
 */

require_once './core/functions.php';

localnet_or_die();
permission_or_die(PERM_ADMIN);


/**
 * Create user
 *
 * @param string $user Username
 * @param string $pass Password
 * @param string $perm permission as integer
 * @return boolean     true on success
 */
function create_user($user, $pass, $perm, $email)
{
	global $config;

    // acquire next free "real" user-id
    $SQL = "SELECT (MAX(id)+1) AS id FROM ".TBL_USERS." WHERE id != ".$config['guestid'].";";
    $res = runSQL($SQL);
    $nextid = $res[0]['id'];
    
    $SQL = "INSERT INTO ".TBL_USERS."
               SET id = ".$nextid.",
               	   name = '".addslashes($user)."',
                   passwd = '".md5($pass)."',
                   permissions = $perm,
                   email = '".addslashes($email)."'";
    $res = runSQL($SQL, false);

    // set default read/write permissions for own data
    if ($res !== false) 
    {
        $SQL = 'REPLACE INTO '.TBL_PERMISSIONS." 
                    SET from_uid=".$nextid.", to_uid=".$nextid.", permissions=".PERM_READ."|".PERM_WRITE;
        $res = runSQL($SQL, false);
    }

    return $res;
}

// calculate permissions
$perm = 0;

if ($adminflag) $perm |= PERM_ADMIN + PERM_ADULT;
elseif ($adultflag) $perm |= PERM_ADULT;

if ($writeflag) $perm |= PERM_READ + PERM_WRITE;
elseif ($readflag) $perm |= PERM_READ;

// new user?
if ($newuser)
{
    $message = $lang['msg_usernotcreated'];
    if ($name && $password)
    {
    	if (create_user($name, $password, $perm, $email) !== false)
            // create successful?
    	    $message = $lang['msg_usercreated'];
        else
            // error (e.g. duplicate key)?
            $smarty->assign('alert', true);
    } else {
        // name or password missing?
        $smarty->assign('alert', true);
    }
}

// update user?
elseif ($id && $name)
{
    runSQL("UPDATE ".TBL_USERS."
               SET name = '".addslashes($name)."', permissions = $perm, email = '".addslashes($email)."'
			 WHERE id = $id");
	// new password?
	if (!empty($password))
	{
		$pw = md5($password);
        runSQL("UPDATE ".TBL_USERS." SET passwd = '$pw' WHERE id = '$id'");
		$message = $lang['msg_permpassupd'];
	} else {
		$message = $lang['msg_permupd'];
	}
}

// delete user? - POST only!
elseif ($del && $_POST['del'])
{
    validate_input($del);
    
    // clear user and config
    runSQL('DELETE FROM '.TBL_USERS.' WHERE id = '.$del);
    runSQL('DELETE FROM '.TBL_USERCONFIG.' WHERE user_id = '.$del);

    // clear permissions
    runSQL('DELETE FROM '.TBL_PERMISSIONS.' WHERE from_uid = '.$del);
    
	$message = $lang['msg_userdel'];
    $smarty->assign('alert', true);
}

// current user permissions
$result = runSQL('SELECT id, name, permissions, email
                    FROM '.TBL_USERS.'
                ORDER BY name');
foreach ($result as $user)
{
	// is guest ?
    $user['guest'] = ($user['id'] == $config['guestid']) ? 1 : 0;
	
	// don't show guest user if guest is disabled
	if ($config['denyguest'] && $user['guest']) 
    {
        continue;
    }
	 
	// collect and separate permission information
    $user['read']  = ($user['permissions'] & PERM_READ);
    $user['write'] = ($user['permissions'] & PERM_WRITE);
    $user['admin'] = ($user['permissions'] & PERM_ADMIN);
    $user['adult'] = ($user['permissions'] & PERM_ADULT);
    $userlist[]    = $user;
}

// make sure caches are clean
clear_permission_cache();

// prepare templates
tpl_page('usermanager');

$smarty->assign('userlist', $userlist);
$smarty->assign('message', $message);

// display templates
tpl_display('users.tpl');

?>
