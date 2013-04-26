<?php
/**
 * Ask to borrow form
 *
 * Allows to request a movie
 *
 * @package videoDB
 * @author  Andreas Gohr <a.gohr@web.de>
 * @version $Id: borrowask.php,v 2.13 2008/06/15 13:58:13 andig2 Exp $
 */

require_once './core/functions.php';

// Auth-Checks
$user_id    = get_current_user_id();
$user       = get_username($user_id);

if (empty($user))
{
	errorpage('Access denied','You don\'t have enough permissions to access this
				page try to <a href="login.php">login</a> first. (This feature is not
				available in Single User Mode)');
}
if (empty($id) || empty($diskid)) 
{
	errorpage('Error', 'No Ids given');
}

$owner       = get_owner($diskid, true);
$result      = runSQL('SELECT email FROM '.TBL_USERS." WHERE name = '".addslashes($owner)."'");
$owner_email = $result[0]['email'];
$result      = runSQL('SELECT email FROM '.TBL_USERS." WHERE id = '".addslashes($user_id)."'");
$user_email  = $result[0]['email'];
$result      = runSQL('SELECT title FROM '.TBL_DATA." WHERE id = '".addslashes($id)."'");
$title       = $result[0]['title'];

$mail        = $lang['msg_borrowaskmail'];
$subject     = $lang['msg_borrowasksubject'];
$url         = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME']).'/show.php?id='.$id;

// replace place holders
$mail = str_replace('%id%', $id, $mail);
$mail = str_replace('%diskid%', $diskid, $mail);
$mail = str_replace('%owner%', $owner, $mail);
$mail = str_replace('%ownermail%', $owner_email, $mail);
$mail = str_replace('%user%', $user, $mail);
$mail = str_replace('%usermail%', $user_email, $mail);
$mail = str_replace('%title%', $title, $mail);
$mail = str_replace('%url%', $url, $mail);

$subject = str_replace('%id%', $id, $subject);
$subject = str_replace('%diskid%', $diskid, $subject);
$subject = str_replace('%owner%', $owner, $subject);
$subject = str_replace('%ownermail%', $owner_email, $subject);
$subject = str_replace('%user%', $user, $subject);
$subject = str_replace('%usermail%', $user_email, $subject);
$subject = str_replace('%title%', $title, $subject);
$subject = str_replace('%url%', $url, $subject);


// prepare templates
tpl_page();

/*
$smarty->assign('success', @mail($owner_email, $subject, $mail));
Fix for https://sourceforge.net/tracker/?func=detail&atid=586362&aid=1570618&group_id=88349
*/
$smarty->assign('success', @mail($owner_email, $subject, $mail, "From: $user <$user_email>\r\nReply-To: $user_email\r\n"));

// display templates
smarty_display('borrowask.tpl');

?>
