<?php

/**
 * General functions
 *
 * Contains globally available tool functions. It is included in every
 * page and sets up some defaults like error reporting, environment
 * setups and config loading
 *
 * @package Core
 * @author  Andreas Goetz   <cpuidle@gmx.de>
 * @author  Andreas Gohr    <a.gohr@web.de>
 * @author  Chinamann       <chinamann@users.sourceforge.net>
 * @version $Id: functions.core.php,v 1.1 2013/04/26 15:08:30 andig2 Exp $
 */

if (!function_exists('errorpage')) {
	function errorpage($title = 'An error occured', $body = '', $stacktrace = false) {
	}
}

/**
 * Output debug info
 *
 * @author  Andreas Goetz   <cpuidle@gmx.de>
 * @param   mixed   $var    Variable to dump
 * @param   bool    $ret    Return result instead of outputting
 * @param   bool    $plain  Indicate that \n separator is used
 */
function dump($var, $ret = false, $plain = false)
{
    global $argv;

    if (is_array($var) || is_object($var))
        $var = print_r($var, 1);
    else if (is_bool($var))
    	$var = ($var) ? 'TRUE' : 'FALSE';

    $var .= (count($argv) > 0 || $plain) ? "\n" : "<br/>\n";

    if ($ret) return $var;
    echo $var;
}

/**
 * Write variable to file
 *
 * @author Chinamann <chinamann@users.sourceforge.net>
 * @param   string	$filename   Filename to dump to
 * @param   var		$var        Variable to dump
 */
function file_append($filename, $var, $append = true)
{
    $log = fopen($filename, $append ? 'a' : 'w');
    fwrite($log, dump($var, true, true));
    fclose($log);
}

/**
 * Write to debug log
 *
 * @author  Andreas Goetz   <cpuidle@gmx.de>
 * @param   mixed   $var    Variable to dump
 */
function dlog($var)
{
    file_append(LOG_FILE, $var);
}

/**
 * safe formoutputter
 *
 * @param  string $name     The input string
 * @return string           The cleaned string
 */
function formvar($name)
{
	return htmlspecialchars($name);
}

/**
 * Get high resolution time
 *
 * @return integer  current time in microseconds
 */
function getmicrotime()
{
	list($usec, $sec) = explode(' ', microtime());
	return ((float)$usec + (float)$sec);
}

/**
 * Return mysqli db connection object
 *
 * @return mysqli database handle
 */
function getConnection()
{
    global $config, $dbh;

    $dbh = mysqli_connect('p:'.$config['db_server'], $config['db_user'], $config['db_password'], $config['db_database']);
    if (mysqli_connect_error())
        errorpage('DB Connection Error',
                  "<p>Edit the database settings in <code>".CONFIG_FILE."</code>.</p>
                   <p>Alternatively, consider running the <a href='install.php'>installation script</a>.</p>");

    if (DB_CHARSET)
    {
        if (mysqli_set_charset($dbh, DB_CHARSET) === false)
             errorpage('DB Link Error', 'Couldn\'t set encoding to '.DB_CHARSET);
    }

    return($dbh);
}

/**
 * Escape SQL string according to current DB charset settings
 *
 * @param  string $sql_string SQL string to escape
 * @return string             escaped string
 */
function escapeSQL($sql_string)
{
    global $dbh;

    if (!is_resource($dbh)) $dbh = getConnection();

    return(mysqli_real_escape_string($dbh, $sql_string));
}

/**
 * SQL wrapper for all Database accesses
 *
 * @param  string $sql_string The SQL-Statement to execute
 * @return mixed  either the resultset as an array with hashes or the insertid
 */
function runSQL($sql_string, $verify = true)
{
    global $config, $dbh, $SQLtrace;

	if ($config['debug'])
    {
        dlog("\n".$_SERVER['REQUEST_URI']);
        if (function_exists('xdebug_get_function_stack')) dlog(join(' -> ', array_column(xdebug_get_function_stack(), 'function')));
        dlog($sql_string);
		$timestamp = getmicrotime();
	}

    if (!is_resource($dbh)) $dbh = getConnection();
    $res  = mysqli_query($dbh, $sql_string);

    // mysqli_db_query returns either positive result ressource or true/false for an insert/update statement
    if ($res === false)
    {
        $result = false;
        if ($verify)
        {
            // report DB Problem
            errorpage('Database Problem', mysqli_error($dbh)."\n<br />\n".$sql_string, true);
        }
	}
	elseif ($res === true)
	{
        // on insert, return id of created record
		$result = mysqli_insert_id($dbh);
	}
	else
	{
        // return associative result array
        $result = array();

		for ($i=0; $i<mysqli_num_rows($res); $i++)
		{
            $result[] = mysqli_fetch_assoc($res);
		}
		mysqli_free_result($res);
	}
	
	if ($config['debug'])
    {
		$timestamp = getmicrotime() - $timestamp;
        dlog('Time: '.$timestamp);
        // collect all SQL info for debugging
        $SQLtrace[] = array('sql' => $sql_string, 'time' => $timestamp);
	}
	
#	mysqli_close($dbh);
	return $result;
}

/**
 * Checks if the page is accessed from within the local net.
 *
 * @return  bool  true if localnet
 */
function localnet()
{
	global $config;
	return (preg_match('/'.$config['localnet'].'/', $_SERVER['REMOTE_ADDR']));
}

/**
 * checks if the page is accessed from within the local net.
 * If not, displays a simple error page and exits
 */
function localnet_or_die()
{
	if (!localnet()) errorpage('Forbidden', 'You are not allowed to access this page');
}

/**
 * Set connection encoding according to config file or language specification
 */
function db_set_encoding()
{
    global $config, $lang;

    // set connection character set and collation
    if (DB_CHARSET)
    {
        $sql        = "SET NAMES '".DB_CHARSET."'";
        $collation  = ($lang['collation']) ? DB_CHARSET.'_'.$lang['collation'] : DB_COLLATION;
        if ($collation) $sql .= " COLLATE '".$collation."'";

        runSQL($sql);
    }
}

/**
 * Redirect to new location
 *
 * @author  Andreas Goetz   <cpuidle@gmx.de>
 * @param   string  $dest   Redirect destination
 * @todo    Read somewhere that according to RFC redirects need to specify full URI
 */
function redirect($dest)
{
    header('Location: '.$dest);
    exit();
}

/**
 * Convert an array of associative arrays (e.g. a database query result) to an associative key=>value array
 *
 * Sample: array_associate( 0=>(a=>1a, b=1b) 1=>(a=>2a, b=>2b), "a", "b" ) gives 1a=>1b, 2a=>2b
 *
 * If $value is false, the whole array is associated instead of a specific value
 *
 * Sample: array_associate( 0=>(a=>1a, b=1b) 1=>(a=>2a, b=>2b), "a", false ) gives 1a=>(b=>1b), 2a=>(b=>2b
 *
 * TODO     Check if this can be replaced by PHP5.5 array_column() function
 *
 * @author  Andreas Goetz   <cpuidle@gmx.de>
 * @param   $ary    SQL result array
 * @param   $key    key index name
 * @param   $value  value index name
 * @return  array   resulting associative array
 */
function array_associate($ary, $columnKey, $value = false)
{
    $res = array();
    foreach ($ary as $row)
    {
        $res[$row[$columnKey]] = ($value) ? $row[$value] : $row;
    }
    return $res;
}

?>
