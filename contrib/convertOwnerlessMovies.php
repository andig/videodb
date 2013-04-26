<?php
/**
 * transfer ownerless to moves to one user
 *
 * @package Contrib
 * @author  Chinamann <chinamann@users.sourceforge.net>
 * 
 * @meta	ACCESS:PERM_ADMIN
 */

// move out of contrib for includes
chdir('..');

require_once './core/functions.php';
require_once './core/output.php';

// check for localnet
localnet_or_die(); 

// multiuser permission check
permission_or_die(PERM_ADMIN);

$owners = out_owners(null, 0, true);

if (empty($owner_id)) $owner_id = $_COOKIE['VDBuserid'];

if ($convert)
{
    runSQL("UPDATE ".TBL_DATA."
               SET owner_id = ".$owner_id."
             WHERE owner_id = 0");
    // show the saved movie
    header('Location: ../index.php');
    exit();
}
else
{
?>

<html>

<head>
    <title>Transfer ownerless movies to a user</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
</head>

<body>
	<form action="<?php echo $_SERVER['PHP_SELF']?>">
		<h3>Do you realy want to convert all ownerless movies to be owned by <SELECT name='owner_id'>
<?

	foreach (array_keys($owners) as $owner) 
	{
		if ($owner == $owner_id) $selected = "selected"; 
		else $selected = "";
		print "<OPTION $selected value='$owner'>".$owners[$owner]."</OPTION>\n";	
	}
?></SELECT> ?</h3>
    
        <input type="submit" name="convert" value="Convert" />
    </form>
<?
}
?>

</body>
</html>
