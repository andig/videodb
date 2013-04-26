<?php
/**
 * Access Control Management
 *
 * Access permission administration functions
 *
 * @author  Chinamann <chinamann@users.sourceforge.net>
 * @version $Id: permissions.php,v 2.4 2009/04/04 16:22:16 andig2 Exp $
 */

require_once './core/functions.php';

localnet_or_die();
permission_or_die(PERM_ADMIN);


/**
 * Return if Checkbox is checked
 *
 * @param string $name Name of a checkbox
 * @return boolean     true if checked
 */
function getStateOfCheckbox($name) 
{
    global $$name;
    return (!empty($$name));
}

if (!empty($from_uid))
{
	$WHERE = "";
	if ($config['denyguest']) 
    {
        $WHERE = ' WHERE A.id != '.$config['guestid'].
                 '   AND C.id != '.$config['guestid'];
    }
	
    // current user permissions
    $result = runSQL(
           'SELECT 
         CASE WHEN B.permissions IS NULL THEN 1 ELSE 0 END AS newentry, '.
                   $from_uid.' AS from_uid,
                   C.name AS from_name,
                   A.id AS to_uid,
                   A.name AS to_name,
         CASE WHEN B.permissions IS NULL THEN 0 ELSE B.permissions END AS permissions 
              FROM '.TBL_USERS.' A 
   LEFT OUTER JOIN '.TBL_PERMISSIONS.' B 
                   ON A.id = B.TO_UID 
                   AND B.FROM_UID = '. $from_uid .
      ' INNER JOIN '.TBL_USERS.' C ON '.$from_uid.' = C.ID' . $WHERE .
        ' ORDER BY A.id'
    );
    
    foreach ($result as $perm)
    {
        $perm['read']   = ($perm['permissions'] & PERM_READ);
        $perm['write']  = ($perm['permissions'] & PERM_WRITE);

        // process SAVE
        if (!empty($save)) 
        {

            // make sure read is allowed when write is set
            $rfn = 'readflag_'.$perm['to_uid'];
            if (getStateOfCheckbox('writeflag_'.$perm['to_uid'])) $$rfn = '1';

            // changed?
            if ($perm['read'] != getStateOfCheckbox('readflag_'.$perm['to_uid']) ||
                $perm['write'] != getStateOfCheckbox('writeflag_'.$perm['to_uid'])) 
            {
                // update
                $newperm = PERM_READ * getStateOfCheckbox('readflag_'.$perm['to_uid']) +
                           PERM_WRITE * getStateOfCheckbox('writeflag_'.$perm['to_uid']);
                $SQL    = 'REPLACE INTO '.TBL_PERMISSIONS." SET from_uid=".$from_uid.", to_uid=".$perm['to_uid'].", permissions=".$newperm;
                runSQL($SQL);
                
                $perm['read']   = getStateOfCheckbox('readflag_'.$perm['to_uid']);
                $perm['write']  = getStateOfCheckbox('writeflag_'.$perm['to_uid']);
/*
                if ($perm['newentry']) // new
                { 
                    // insert
                    $newperm = PERM_READ * getStateOfCheckbox('readflag_'.$perm['to_uid']) +
                               PERM_WRITE * getStateOfCheckbox('writeflag_'.$perm['to_uid']);
                    $INSERT = 'INSERT INTO '.TBL_PERMISSIONS." SET from_uid=".$from_uid.", to_uid=".$perm['to_uid'].", permissions=".$newperm;
                    runSQL($INSERT);
                    $perm['read']   = getStateOfCheckbox('readflag_'.$perm['to_uid']);
                    $perm['write']  = getStateOfCheckbox('writeflag_'.$perm['to_uid']);
                } 
                else // old
                {
                    if ((getStateOfCheckbox('readflag_'.$perm['to_uid']) + getStateOfCheckbox('writeflag_'.$perm['to_uid'])) == 0) 
                    {
                        // delete
                        $DELETE = "DELETE FROM ".TBL_PERMISSIONS." WHERE from_uid=".$from_uid." AND to_uid=".$perm['to_uid'];
                        runSQL($DELETE);
                        $perm['read']   = 0;
                        $perm['write']  = 0;
                    } 
                    else 
                    {
                        // update
                        $newperm = PERM_READ * getStateOfCheckbox('readflag_'.$perm['to_uid']) +
                                   PERM_WRITE * getStateOfCheckbox('writeflag_'.$perm['to_uid']);
                        $UPDATE = "UPDATE ".TBL_PERMISSIONS." SET permissions=".$newperm." WHERE from_uid=".$from_uid." AND to_uid=".$perm['to_uid'];
                        runSQL($UPDATE);
                        $perm['read']   = getStateOfCheckbox('readflag_'.$perm['to_uid']);
                        $perm['write']  = getStateOfCheckbox('writeflag_'.$perm['to_uid']);
                    }
                }
*/
            }
            
            // clear permission cache
            clear_permission_cache();
        }

        $permlist[] = $perm;
    }
}

// prepare templates
tpl_page();

$smarty->assign('permlist', $permlist);
//$smarty->assign('from_name', $permlist[0]['from_name']);
$smarty->assign('from_uid', $permlist[0]['from_uid']);
$smarty->assign('owners', out_owners(false,false,true));
$smarty->assign('message', $message);

// display templates
tpl_display('permissions.tpl');

?>
