<?php
/**
 * Database conversion script for DB v26
 *
 * Rating custom field conversion
 *
 * @package Setup
 * @author  Andreas Goetz <cpuidle@gmx.net>
 * @version $Id: upgrade_v26.php,v 1.2 2007/12/30 11:09:24 andig2 Exp $
 */

/**
 * Rating data migration
 */
function migrate_rating($field)
{
    global $dbh;
    
    $set = runSQL('UPDATE videodata SET rating='.$field.' WHERE '.$field.'>0', $dbh, true);
    return $set;
}

$sql    = "SELECT * FROM config WHERE opt LIKE 'custom%type'";
$set	= runSQL($sql, $dbh, true);
if ($set === false) return(false);

foreach ($set as $row)
{
    if ($row['value'] == 'rating')
    {
        if (preg_match('/(custom\d)/', $row['opt'], $m))
        {
            $field = $m[1];
            $set    = migrate_rating($field);
            if ($set === false) return(false);
            
            $sql    = "UPDATE config SET value='' WHERE opt LIKE '".$field."%'";
            $set    = runSQL($sql, $dbh, true);
            if ($set === false) return(false);
        }    
    }
}

// signal success
return true;

?>
