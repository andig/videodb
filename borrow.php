<?php
/**
 * Borrow Manager
 *
 * Handles lending of disks
 *
 * @package videoDB
 * @author  Andreas Gohr <a.gohr@web.de>
 * @version $Id: borrow.php,v 2.21 2013/03/10 16:20:10 andig2 Exp $
 */

require_once './core/functions.php';
require_once './core/output.php';

// check for localnet
localnet_or_die();

// permission check
permission_or_die(PERM_WRITE, PERM_ANY); 

// borrowmanagement for single disk
$editable = false;
if (!empty($diskid))
{
    if (check_permission(PERM_WRITE, get_owner_id($diskid,true)))
	{
		$editable = true;
		if ($return) {
            $SQL    = "DELETE FROM ".TBL_LENT." WHERE diskid = '".addslashes($diskid)."'";
            runSQL($SQL);
		}
		if (!empty($who)) {
			$who = addslashes($who);
            $SQL    = "INSERT INTO ".TBL_LENT." SET who = '".addslashes($who)."', diskid = '".addslashes($diskid)."'";
            runSQL($SQL);
		}

        $SQL    = "SELECT who, DATE_FORMAT(dt,'%d.%m.%Y') AS dt 
                     FROM ".TBL_LENT." 
                    WHERE diskid = '".addslashes($diskid)."'";
        $result = runSQL($SQL);
		
		$who = $result[0]['who'];
		$dt  = $result[0]['dt'];
	}
}

$WHERES = '';

if ($config['multiuser']) 
{
    // get owner from session- or use current user
    session_default('owner', get_username(get_current_user_id()));

    // build html select box
    $all = $lang['filter_any'];
    $smarty->assign('owners', out_owners(array($all => $all), PERM_READ));
    $smarty->assign('owner', $owner);

    // if we don't have read all permissions, limit visibility using cross-user permissions
    if (!check_permission(PERM_READ))
    {
        $JOINS   = ' LEFT JOIN '.TBL_PERMISSIONS.' ON '.TBL_DATA.'.owner_id = '.TBL_PERMISSIONS.'.to_uid';
        $WHERES .= ' AND '.TBL_PERMISSIONS.'.from_uid = '.get_current_user_id().' AND '.TBL_PERMISSIONS.'.permissions & '.PERM_READ.' != 0';
    }
        
    // further limit to single owner
    if ($owner != $all) $WHERES .= " AND ".TBL_USERS.".name = '".addslashes($owner)."'";
}

// overview on lent disks
$SQL    = "SELECT who, DATE_FORMAT(dt,'%d.%m.%Y') as dt, ".TBL_LENT.".diskid,
                  CASE WHEN subtitle = '' THEN title ELSE CONCAT(title,' - ',subtitle) END AS title,
                  ".TBL_DATA.".id, COUNT(".TBL_LENT.".diskid) AS count, ".TBL_USERS.".name AS owner
             FROM ".TBL_LENT.", ".TBL_DATA."
        LEFT JOIN ".TBL_USERS." ON owner_id = ".TBL_USERS.".id
           $JOINS
            WHERE ".TBL_LENT.".diskid = ".TBL_DATA.".diskid 
          $WHERES
         GROUP BY ".TBL_LENT.".diskid, ".TBL_DATA.".id
         ORDER BY who, ".TBL_LENT.".diskid";
$result = runSQL($SQL);

// check permissions
for($i=0; $i < count($result); $i++)
{
    $result[$i]['editable'] = check_permission(PERM_WRITE, get_userid($result[$i]['owner']));
}

// prepare templates
tpl_page();

$smarty->assign('diskid', $diskid);
$smarty->assign('who', $who);
$smarty->assign('dt',  $dt);
$smarty->assign('editable',   $editable);
$smarty->assign('borrowlist', $result);

// display templates
tpl_display('borrow.tpl');

?>
