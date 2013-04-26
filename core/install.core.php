<?php
/**
 * Installer functions
 *
 * Create database, tables and config file
 *
 * @package Setup
 * @author  Andreas Goetz <cpuidle@gmx.de>
 * @version $Id: install.core.php,v 1.11 2010/02/18 15:15:37 andig2 Exp $
 */


/**
 * Build formatted message string for html output
 *
 * @param string    $msg    Message to print
 * @param string    $color  Color code for <div> tag
 * @param boolean   $print  If true, output directly, else append to message string
 */
function showmessage($msg, $color, $print = false)
{
    global $message;
    
    if ($msg) $msg = "<span style='color: $color'>$msg</span><br/><br/>\n";
    if ($print)
    {
        // print directly
        echo $msg;
    }
    else 
    {
        // return
        $message .= $msg;
    }
}

/**
 * Prepare formatted error message for output
 *
 * @param string    $msg    Message to print
 * @param boolean   $print  If true, output directly, else append to message string
 */
function error($msg, $print = false)
{
    showmessage($msg, 'red', $print);
}

/**
 * Prepare formatted warning message for output
 *
 * @param string    $msg    Message to print
 * @param boolean   $print  If true, output directly, else append to message string
 */
function warn($msg, $print = false)
{
    showmessage($msg, 'orange', $print);
}

/**
 * Prepare formatted info message for output
 *
 * @param string    $msg    Message to print
 * @param boolean   $print  If true, output directly, else append to message string
 */
function info($msg, $print = false)
{
    showmessage($msg, 'green', $print);
}


/**
 * Recursively delete files from directory
 * used for Smarty cache cleanup during installation
 *
 * @param string    $dir        Directory name
 * @param boolean   $recursive  Recurse into subfolders
 */
function delete_files($dir, $recursive = false)
{
	if ($dh = @opendir($dir))
	{
		while (($file = readdir($dh)) !== false)
		{
			// next if . or ..
			if (preg_match("/^\.\.?$/", $file)) continue; 

			// recursion?
			if (is_dir("$dir/$file")) 
			{
				if ($recursive) delete_files("$dir/$file", $recursive);
			}
			else 
			{
				// delete file
				unlink("$dir/$file");
			}
		}
		closedir($dh);

		if ($recursive) rmdir($dir);
    }
}

/**
 * Parse config file and replace settings according 
 * to associate array parameter
 *
 * @param   array   new parameter values
 */
function parse_config($vars)
{
    $raw = explode("\n", file_get_contents(CONFIG_FILE));

	for ($i = 0; $i < count($raw); $i++) 
	{
		foreach ($vars as $name => $val)
		{
			if (preg_match("/^(.*?'$name'.*?=\s*)(.*?)(\s*;.*?)$/", $raw[$i], $matches))
            {
				# quoted?
				if (preg_match("/^[\"'].*[\"']$/", $matches[2])) $val = "'$val'";
				$matches[2] = $val;
				$raw[$i]    = join('', array_slice($matches,1));
			}
		}
	}

    // fallback if config file is empty or invalid
    if (count($raw) < 4)
    {
        $raw = array('<?php');
        foreach ($vars as $name => $val)
        {
            $line   = "\$config['$name'] = ";
            $line  .= (is_numeric($val)) ? "$val;" : "'$val';";
            $raw[]  = $line;    
        }
        $raw[] = '?>';
    }

	return join("\n", $raw);
}

/**
 * Parse database upgrade SQL file and 
 * build associate array of upgrade sql steps per version
 *
 * @return  array   associative array of upgrade steps
 */
function parse_upgrades($upgrade_file)
{
	$cfg = file_get_contents($upgrade_file);
	$raw = preg_split('/# changes in DB version /i', $cfg);

    // loop through list of db upgrades split by comments
	foreach ($raw as $str)
	{
        // upgrade version comment found?
		if (preg_match('/(\d+)\s*#\s*(.+)/s', $str, $m)) 
        {
			$key = $m[1];
			$str = $m[2];
		}
		else 
        {
			if (empty($upgrades['3'])) 
            {
				// this is the first supported version
				$key = 3;
			}
			else 
            {
                // unexpected first db upgrade version found
                trigger_error('Could not parse <a href="'.$upgrade_file.'">'.$upgrade_file.'</a>, please fix!', E_USER_ERROR);
            }
		}
		$upgrades["$key"] = $str;
	}
	return $upgrades;
}

/**
 * Callback function for adding prefix to table name in FROM clauses- extended to include sub queries
 *
 * $match[2] is table
 */
function sql_add_prefix($match)
{
    global $db_prefix;
    
    $match[2] = preg_replace('/(\'?\w+\'?)(\s*\w*)(,?)/', "`$db_prefix$1`$2$3", $match[2]);
    return join(array_slice($match, 1, 3));
}

/**
 * Prefix table names with table name prefix
 * This will only work for simple queries with known structure (createtables.sql, updatedb.sql)
 *
 * @param   string      SQL command
 * @return  string      SQL command with new table names
 */
function prefix_query($query)
{
    global $db_prefix;

    $query = preg_replace('/((CREATE|ALTER)\s+TABLE\s+(IF\s+NOT\s+EXISTS\s+)?)`?(\w+)`?/', "$1`$db_prefix$4`", $query);
    $query = preg_replace('/((INSERT(\s+IGNORE)?|REPLACE)\s+INTO\s+)`?(\w+)`?/', "$1`$db_prefix$4`", $query);

    $query = preg_replace('/(DROP\s+TABLE\s+(IF\s+EXISTS\s+)?)`?(\w+)`?(;?)/', "$1`$db_prefix$3`$4", $query);
    $query = preg_replace('/(UPDATE\s+)`?(\w+)`?/', "$1`$db_prefix$2`", $query);

    // FROM matches at beginning of string or subquery opened by left bracket
    $query = preg_replace_callback( "/(\s+FROM\s+)(.*?)((\s+WHERE|ORDER|LEFT|RIGHT|OUTER|JOIN)|\)|$)/msi", 'sql_add_prefix', $query);

    return $query;
}

/**
 * Run multiple comma-separated queries
 *
 * @todo    Split SQL queries more cleverly (currently not needed)
 *
 * @param   string      SQL commands
 * @param   ressource   database handle
 * @return  mixed       result array or false
 */
function runSQL($sql, $dbh, $verify = false)
{
    $result = true;
    foreach (explode(';', $sql) as $query) 
    {
        $query = trim($query);
        if (empty($query)) continue;

        $query  = prefix_query($query);
        $result = mysql_query($query, $dbh);

        // error running SQL?
        if ($result === false) 
        {
            $sql = $query;
            break;
        }    
    }

    // error running SQL?
    if ($result === false)
    {
        if ($verify)
        {
            error("Error in SQL statement:<br/>".
                  "<code>$sql</code><br/>".mysql_error($dbh));
        }
    }    

    // result set returned?
    elseif ($result !== true)
    {
        $res = array();
        
        for ($i=0; $i < mysql_num_rows($result); $i++)
        {
            $res[] = mysql_fetch_assoc($result);
        }
        mysql_free_result($result);
        
        return $res;
    }
    
    return $result;
}

/**
 * Run all upgrade scripts passed as array step by step
 *
 * @param array $upgrade_steps  Associative array of upgrade sql steps
 */
function db_upgrade($upgrade_steps)
{
    global $dbh, $version;
    global $step;

    foreach ($upgrade_steps as $ver => $sql)
    {
        #info("Upgrading to database version: $ver");
        $sql = preg_replace('/#.*\n/m','',$sql);

        if (runSQL($sql, $dbh) === false)
        {
            error('Error upgrading database, try full install instead of upgrade:<br/>'.mysql_error($dbh).
                  '<br/><br/><pre>'.$sql.'</pre>');
            return false;
        }		

        // perform additional upgrade steps
        $upgrade_file = "./install/upgrade_v$ver.php";
        if (file_exists($upgrade_file))
        {
            $result = include_once($upgrade_file);
            if (!$result) return($false);
        }

        // add DB version information- this will make the separate update statement in upgrade.sql obsolete
        runSQL("REPLACE INTO config (opt,value) VALUES ('dbversion', ".$ver.");", $dbh);
#       runSQL("update config set value=25 where  opt='dbversion'");

        $version = $ver;
    }

    // perform generic upgrade validation
    $upgrade_file = "./install/upgrade.php";
    if (file_exists($upgrade_file))
    {
        $result = include_once($upgrade_file);
        if (!$result) return($false);
    }

    return $version;
}

?>