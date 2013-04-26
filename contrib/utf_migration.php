<?php
/**
 * UTF-8 Migration Utility
 * 
 * @package Contrib
 * @author  Andreas Goetz   <cpuidle@gmx.de>
 * $Id: utf_migration.php,v 1.2 2008/01/06 12:30:00 andig2 Exp $
 */

chdir('..');
require_once './core/functions.php';
require_once './core/encoding.php';

?>

<html>
<head>
    <title>Migrate database contents to UTF-8</title>
    <meta http-equiv="Content-Type" content="text/html; charset=<?=$targetencoding?>" />
</head>

<body>

<b>
Attention: Be sure to perform a backup before running the encoding migration!
</b>

<h2>1. Choose source and target encoding</h2>

<form action="<?=$PHP_SELF?>">

    <div>
    Source encoding:
    <select name="sourceencoding">
    <option value="iso-8859-1" <? if($sourceencoding=='iso-8859-1'|| empty($sourceencoding)) echo 'selected="selected"' ?>>iso-8859-1</option>
    <option value="iso-8859-7" <? if($sourceencoding=='iso-8859-7') echo 'selected="selected"' ?>>iso-8859-7</option>
    <option value="iso-8859-9" <? if($sourceencoding=='iso-8859-9') echo 'selected="selected"' ?>>iso-8859-9</option>
    <option value="windows-1251" <? if($sourceencoding=='windows-1251') echo 'selected="selected"' ?>>windows-1251</option>
    <option value="koi8-r" <? if($sourceencoding=='koi8-r') echo 'selected="selected"' ?>>koi8-r</option>
    <option value="utf-8" <? if($sourceencoding=='utf-8') echo 'selected="selected"' ?>>utf-8</option>
    </select>
    </div>

    <div>
    Target encoding:
    <select name="targetencoding">
    <option value="iso-8859-1" <? if($sourceencoding=='iso-8859-1') echo 'selected="selected"' ?>>iso-8859-1</option>
    <option value="iso-8859-7" <? if($targetencoding=='iso-8859-7') echo 'selected="selected"' ?>>iso-8859-7</option>
    <option value="iso-8859-9" <? if($targetencoding=='iso-8859-9') echo 'selected="selected"' ?>>iso-8859-9</option>
    <option value="windows-1251" <? if($targetencoding=='windows-1251') echo 'selected="selected"' ?>>windows-1251</option>
    <option value="koi8-r" <? if($targetencoding=='koi8-r') echo 'selected="selected"' ?>>koi8-r</option>
    <option value="utf-8" <? if($targetencoding=='utf-8' || empty($targetencoding)) echo 'selected="selected"' ?>>utf-8</option>
    </select>
    </div>

    <div>
    Simulate only: <input type="checkbox" name="simulate" checked="checked" />
    </div>

    <input type="submit" value="Submit" />

</form>

<?

/**
 * SQL function
 */
function sql_native($sql_string)
{
    global $config, $db_native;

    if (!is_resource($db_native))
    {
        $db_native =  mysql_pconnect($config['db_server'], $config['db_user'], $config['db_password']) or
                errorpage('DB Connection Error',
                          "<p>Edit the database settings in <code>".CONFIG_FILE."</code>.</p>
                           <p>Alternatively, consider running the <a href='install.php'>installation script</a>.</p>");

        mysql_select_db($config['db_database'], $db_native) || 
                errorpage('DB Connection Error',
                          "Couldn't select database: ".$config['db_database'].
                          "<p>Please verify your database is up and running any validate your database settings in <code>".CONFIG_FILE."</code>.</p>
                           <p>Alternatively, consider running the <a href='install.php'>installation script</a>.</p>");
    }
    
    $res  = mysql_query($sql_string, $db_native);
    
    // mysql_db_query returns either positive result ressource or true/false for an insert/update statement
    if ($res === false)
    {
        // report DB Problem
        errorpage('Database Problem', mysql_error($db_native)."\n<br />\n".$sql_string);
    }
    elseif ($res === true)
    {
        // on insert, return id of created record
        $result = mysql_insert_id($db_native);
    }
    else
    {
        // return associative result array
        $result = array();

        for ($i=0; $i<mysql_num_rows($res); $i++)
        {
            $result[] = mysql_fetch_assoc($res);
        }
        mysql_free_result($res);
    }

    return $result;
}

function db_encode($s)
{
    if (is_numeric($s)) return $s;
    elseif (empty($s)) return 'NULL';
    else return "'".mysql_escape_string($s)."'";
}

$db_encodings   = array('iso-8859-1'=>'latin1', 'iso-8859-7'=>'latin7', 'iso-8859-9'=>'latin9', 'windows-1251'=>'cp1251', 'koi8-r'=>'koi8r', 'utf-8'=>'utf8');
$tables         = array(TBL_DATA, TBL_ACTORS);

extract($_REQUEST);

$db_sourceencoding = $db_encodings[$sourceencoding];
$db_targetencoding = $db_encodings[$targetencoding];

if ($sourceencoding && $targetencoding && ($sourceencoding != $targetencoding))
{    
#    if (!preg_match('/^(\w\d)+$/', $sourceencoding)) die ('Security violation');
#    if (!preg_match('/^(\w\d)+$/', $targetencoding)) die ('Security violation');

?>

<h2>2. Validate data correctness and execute</h2>

<?
    dump("Converting from $sourceencoding to $targetencoding");
    
    sql_native("SET NAMES '".$db_targetencoding."'");
    
    foreach ($tables as $table)
    {
        dump("Table: ".$table);
        
        $res = sql_native('SELECT * FROM '.$table);
        dump("Items: ".count($res)."<br/>");
        
        $enc = iconv_array($sourceencoding, $targetencoding, $res);
        
        for ($i=0; $i<count($enc); $i++)
        {
            $row    = $enc[$i];
            
            // check if encoding really changed
            if (join(array_values($row)) == join(array_values($res[$i]))) continue;
            
            $id     = $row['id'];
            if (!$id) die("No ID found");
            unset($row['id']);
            
            $SQL = '';
            foreach ($row as $key=>$val)
            {
                if ($SQL) $SQL .= ', ';
                $SQL .= $key.'='.db_encode($val);
            }
            $SQL = "UPDATE $table SET ".$SQL." WHERE id=".$id;
            dump($SQL);
            
            if (!$simulate) sql_native($SQL);
        }    
    }
}

?>
</body>
</html>
