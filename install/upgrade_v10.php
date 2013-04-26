<?php
/**
 * Database conversion script for DB v10
 *
 * Handles saving of the various config options.
 *
 * @package Setup
 * @author  Andreas Goetz <cpuidle@gmx.net>
 * @version $Id: upgrade_v10.php,v 1.1 2007/01/04 16:14:16 andig2 Exp $
 */


/*
 * userseen table upgrade
 */

$sql	= 'SELECT video_id, user, users.id AS user_id 
			 FROM userseen 
			 LEFT JOIN users ON userseen.user = users.name';
$set	= runSQL($sql, $dbh, true);
if ($set === false) return(false);

foreach ($set as $row)
{
    // don't convert fishy data
    if (!empty($row['user_id']))
    {
        $sql = "UPDATE userseen SET user_id = ".$row['user_id']." ".
                "WHERE video_id = ".$row['video_id']." AND user = '".$row['user']."'";

        if (runSQL($sql, $dbh) === false) return(false);
    }        
}


/*
 * userconfig table upgrade
 */

$sql	= 'SELECT user, opt, users.id AS user_id 
             FROM userconfig 
             LEFT JOIN users ON userconfig.user = users.name';
$set	= runSQL($sql, $dbh);
if ($set === false) return(false);

foreach ($set as $row)
{
    // don't convert fishy data
    if (!empty($row['user_id']))
    {
        $sql = "UPDATE userconfig SET user_id = ".$row['user_id']." ".
                "WHERE opt = '".$row['opt']."' AND user = '".$row['user']."'";

        if (runSQL($sql, $dbh) === false) return(false);
    }
}


/*
 * videodata table upgrade
 */

$sql    = 'SELECT videodata.id AS id, owner, users.id AS owner_id 
			 FROM videodata 
			 LEFT JOIN users ON videodata.owner = users.name';
$set	= runSQL($sql, $dbh);
if ($set === false) return(false);

foreach ($set as $row)
{
    // don't convert fishy data
    if (!empty($row['owner_id']))
    {
        $sql = "UPDATE videodata SET owner_id = ".$row['owner_id']." ".
                "WHERE id = ".$row['id'];

        if (runSQL($sql, $dbh) === false) return(false);
    }
}

// signal success
return true;

?>
