<?php
/**
 * Convert HTML entities to plain text
 *
 * @package Contrib
 * @author  Andreas Goetz   <cpuidle@gmx.de>
 * @version $Id: decode_entities.php,v 1.6 2008/01/23 09:06:25 andig2 Exp $
 * @meta	ACCESS:PERM_ADMIN
 */

// move out of contrib for includes
chdir('..');

require_once './core/functions.php';
require_once './engines/engines.php';

?>

<html>

<head>
    <title>Decode HTML Entities</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta name="description" content="VideoDB" />
<!--
    <link rel="stylesheet" href="../templates/modern/compact.css" type="text/css" />
-->    
    <style>
        .green { color:green }
    </style>

</head>

<body>

<?

error_reporting(E_ALL ^ E_NOTICE);

if (!$submit) echo "<h2>Warning- be sure to backup your data before submitting the cleanup request!</h2>";

$SQL = 'SELECT * FROM '.TBL_DATA;
$result = runSQL($SQL);

$count = 0;
foreach ($result as $video)
{
	$SQL = '';

	$keys = array();

	foreach ($video as $key => $value)
	{
		if ($key == 'id') continue;

        $new = html_clean_utf8($value);
		if ($new != $value)
		{
			$keys[] = $key;

			if ($SQL) $SQL .= ', ';
			$SQL .= "$key = '".mysql_escape_string($new)."'";
		}
	}    

	if ($SQL)
	{
		$count++;
		echo (($submit) ? 'Converting: ' : '<b>Conversion needed:</b> ').$video['title']."<br/>\n";

		// actually perform the conversion?
		if ($submit) 
		{
            $SQL = "UPDATE ".TBL_DATA." SET $SQL WHERE id = ".$video['id'];
			runSQL($SQL);
		}
		else
		{
			foreach($keys as $key)
			{
				echo $key.': '.htmlentities($video[$key])."<br/>\n";
			}
			echo "<br/>\n";
		}
	}
}

$action = ($submit) ? 'Converted' : 'Analyzed';
echo "$action $count of ".count($result)." movies.<br/>\n";

if (empty($submit))
{
?>
    <form action="<?php echo $_SERVER['PHP_SELF']?>">
        <input type="submit" name="submit" value="Convert" />
    </form>
<?
}
?>

</body>
</html>
