<?php
/**
 * Database conversion script for DB v20
 *
 * @package Setup
 * @author  Andreas Goetz <cpuidle@gmx.net>
 * @version $Id: upgrade_v20.php,v 1.1 2007/01/04 16:14:16 andig2 Exp $
 */


/*
 * Userseen data migration
 */

$sql	= 'SELECT owner_id, seen
			 FROM videodata 
			WHERE seen > 0';
$set	= runSQL($sql, $dbh, true);
if ($set === false) return(false);

foreach ($set as $row)
{
    // don't convert fishy data
    if (!empty($row['user_id']))
    {
        $sql = "REPLACE INTO ".TBL_USERSEEN." SET user_id=".$row['owner_id'].", video_id=".$row['id'];

        if (runSQL($sql, $dbh) === false) return(false);
    }        
}

// signal success
return true;

?>
