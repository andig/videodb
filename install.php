<?php
/**
 * Installer
 *
 * Create database, tables and config file
 *
 * @package Setup
 * @author  Andreas Goetz <cpuidle@gmx.de>
 * @version $Id: install.php,v 1.49 2013/03/21 16:27:57 andig2 Exp $
 */

error_reporting(E_ALL ^ E_NOTICE);

require_once './core/compatibility.php';
require_once './core/install.core.php';
require_once './core/constants.php';


// required files
$install_sql    = './install/install.sql';
$upgrade_sql	= './install/upgrade.sql';

// button definitions
$button_next    = '&nbsp;&nbsp; Next &nbsp;&nbsp;>>';
$button_prev    = '<<&nbsp;&nbsp; Previous &nbsp;&nbsp;';
$button_upgrade = 'Upgrade';


// extract request parameters and disable warnings
extract($_REQUEST, EXTR_OVERWRITE);

// create default config file if not existing
if (!file_exists(CONFIG_FILE))
{
    copy('./config.sample.php', CONFIG_FILE);
}

// set default
$upgrading = false;

// stepping back?
if (isset($formPrevious))
{
    $step -= 2;
}

// upgrading?
elseif (isset($action) && stristr($action, 'upgrade'))
{
    $upgrading = true;

    // load configuration
	include_once(CONFIG_FILE);

    // begin of upgrade?
	if (empty($step))
    {
        // set initial step for upgrading
        $step = 2;

        // set default db configuration
        if (empty($db_server))
        {
            extract($config, EXTR_OVERWRITE, 'db_');
        }

        // remove files from previous version
        foreach(array(
            'imdb.php', 'amazon.php', 'engines.php', 'functions.php',
            'setupfunctions.php', 'compatibility.php', 'template.php',
            'queryparser.php', 'genres.php', 'output.php', 'session.php', 
            './core/setupfunctions.php', './core/installfunctions.php', './core/xml_functions.php',
            './doc/createtables.sql', './doc/updatedb.sql')
            as $file)
        {
            if (file_exists($file)) @rename($file, $file.'.old');
        }

        // remove old xajax library
        delete_files('lib/xajax/lib');

        // remove old smarty cache
        delete_files('cache/smarty');

        // recursively delete old smarty folder (now lib/smarty)
        delete_files('smarty', true);

        // obsolete templates
        delete_files('templates/advanced', true);
        delete_files('templates/downlord', true);
        delete_files('templates/jeckyll', true);
    }
}

// or first installation step?
elseif (empty($step))
{
    $step = 1;
}



?>

<html>
<head>
	<title>videoDB - Installation</title>
	<link rel="stylesheet" href="templates/modern/modern.css" type="text/css" />
</head>

<body class="odd">

<table width="800" cellspacing="0" cellpadding="10" align="center" bgcolor="white" style="margin-top: 40px">
<tr>
	<td bgcolor="darkred" class="tablemenu">
		<font color="white" size="+2">Installer for <span class='logo'>videoDB</span></font>
	</td>
</tr>
<tr>
	<td>
		<table>
		<tr><td>


<?php


// no messages yet
$message = '';

// handle installation step
switch ($step)
{

    case 3:     /*
                 * database parameters have been collected, start actual installation
                 */

                // connect to database server
                $dbh = mysqli_connect($db_server, $db_user, $db_password);
				if (mysqli_connect_error()) {
					error("Can't connect: ".mysqli_error());
					$step--;
					break;
				}
				
                // check database existance
				if (mysqli_select_db($dbh, $db_database))
                {
					error("DB already exists: ".$db_database);
				}
                else
                {
                    // database doesn't exist, create it
					info("Creating database...");

                    // try to create the database..
                    if (!@mysqli_query($dbh, "CREATE DATABASE `".$db_database."`".((DB_CHARSET) ? " DEFAULT CHARACTER SET '".DB_CHARSET."'" : '')))
                    {
						error("Can't create database: ".mysqli_error($dbh));
						$step--;
						continue;
					}
                    else
                    {
                        // ..and select it
						if (!mysqli_select_db($dbh, $db_database))
                        {
							error("Can't select database: ".mysqli_error($dbh));
							$step--;
							continue;
						}
					}
				}
				
                // check if tables with this prefix already exist
                global $db_prefix;
				$rs = mysqli_query($dbh, "SHOW TABLES FROM `".$db_database."` LIKE '".$db_prefix."%'" ) or trigger_error("Can't execute: ".mysqli_error($dbh), E_USER_ERROR);
				if (mysqli_num_rows($rs) > 0)
                {
					error("DB has already tables with this prefix!");
					break;
				}

                // add root user warning
                if ($db_user == 'root')
                {
                    warn("You've used 'root' as database username. Root is often the master administration account.
                          For security reasons it is recommended that you choose a different username after
                          the installation.");
                }

                // continue with table installation
				$step++;
				

    case 4:     /*
                 * continue installation by upgrading or installing tables and (re)moving files (upgrade only)
                 */

                if ($upgrading)
                {
					// re-connect if not continued from step 3
					if (!isset($dbh))
                    {
						$dbh = @mysqli_connect($db_server, $db_user, $db_password);
                        if (mysqli_connect_error()) trigger_error("Can't connect: ".mysqli_error(), E_USER_ERROR);
						mysqli_select_db($dbh, $db_database) or trigger_error("Can't select database: ".mysqli_error($dbh), E_USER_ERROR);
					}

					// get version
					$sql = "SELECT value FROM {$db_prefix}config WHERE opt = 'dbversion'";
					$rs = mysqli_query($dbh, $sql);
					if ($rs) list($version) = mysqli_fetch_row($rs);

                    // successfully retrieved installed version?
					if (!($rs && $version))
                    {
						error("Error getting DB version, try full install instead of upgrade: ".mysqli_error($dbh));
						error("<br/><br/><pre>$sql</pre>");
						$step--;
						break;
					}

                    // already correct db version? this might happen if just the username/ password were wrong
                    if ($version >= DB_REQUIRED)
                    {
                        info("Database upgrade not required.");
                        break;
                    }

					// perform actual upgrade
                    $upgrades = parse_upgrades($upgrade_sql);
					if ($version >= max(array_keys($upgrades)))
                    {
						info("Database upgrade not required.");
						$step--;
						break;
					}
					else
                    {
                        // upgrade
                        info("<br/>Upgrading tables...");
                        info("Old database version: $version");

						$sql_array = array();
						// select the relevant upgrades (> current version)
						foreach ($upgrades as $ver => $value)
                        {
							if ($ver > $version) $sql_array["$ver"] = $value;
						}

                        // upgrades successful?
                        $version = db_upgrade($sql_array);
						if (is_numeric($version))
                        {
                            info("New database version: $version");
                            // dev-time sanity check
                            if ($version > DB_REQUIRED) warn("Upgraded database version $version is higher than required database version ".DB_REQUIRED);
                        }
                        else
                        {
                            // error
                            $step--;
                        }
					}
				}
				else
				{
					// install
					info("<br/>Installing tables...");

					// re-connect if not continued from step 3
					if (!$dbh) {
						$dbh = @mysqli_connect($db_server, $db_user, $db_password);
                        if (mysqli_connect_error()) trigger_error("Can't connect: ".mysqli_error(), E_USER_ERROR);
						mysqli_select_db($dbh, $db_database) or trigger_error("Can't select database: ".mysqli_error($dbh), E_USER_ERROR);
					}

					// open SQL script from doc directory
					$sql = file_get_contents($install_sql);
                    if (!$sql) trigger_error('Couldn\'t open SQL file: '.$install_sql, E_USER_ERROR);

                    if (runSQL($sql, $dbh) === false) {
						error('Error creating tables: '.mysqli_error($dbh));
						error('<br/><br/><pre>'.$sql.'</pre>');
						$step--;
					}
					else
                    {
                        $write_config_file = true;
/*
						// create config file
						info("<br/>Writing config file...");
						$config = parse_config(array(
							'db_server'		=> $db_server,
							'db_user'		=> $db_user,
							'db_password'	=> $db_password,
                            'db_database'   => $db_database,
                            'db_prefix'     => $db_prefix), true);

                        if (!file_put_contents(CONFIG_FILE, $config))
                        {
                            error('<br/>Could not write config file '.CONFIG_FILE.'!
                                   <br/>Please make sure your config file contains the following lines:<br/><br/>'.
                                   highlight_string($config, 1));
						}
*/
					}
				}

				break;
}


// determine next installation step
switch ($step)
{

    case 4:     // start videoDB
                $action_target = 'index.php';
                break;
    default:    // continue installation
                $action_target = 'install.php';
}

?>
            <form name='form1' method='post' action='<?php echo $action_target?>'>
            <table>
<?php

if ($upgrading)
{
    echo "<input type='hidden' name='action' value='upgrade'/>";
}

switch ($step)
{

	case 4:		// start VideoDB
                $installed = ($upgrading) ? 'upgraded' : 'installed';

?>				<tr><td colspan="2">
                    <?php echo $message?>
					<b>Installation successful!</b><br/><br/>
					VideoDB database and tables have been successfully <?php echo $installed?>.<br/>
					<br/>
                    <?php
                        // different settings than config file specified?
                        if ($write_config_file ||
                            ($db_server     != $config['db_server']) ||
                            ($db_database   != $config['db_database']) ||
                            ($db_user       != $config['db_user']) ||
                            ($db_password   != $config['db_password']) ||
                            ($db_prefix     != $config['db_prefix']))
                        {
                            // create config file
                            info("<br/>Writing config file...");
                            $config_file = parse_config(array(
                                'db_server'     => $db_server,
                                'db_user'       => $db_user,
                                'db_password'   => $db_password,
                                'db_database'   => $db_database,
                                'db_prefix'     => $db_prefix), true);

                            if (!file_put_contents(CONFIG_FILE, $config_file))
                            {
                                error('<br/>Could not write config file '.CONFIG_FILE.'!
                                       <br/>Please make sure your config file contains the following lines:<br/><br/>'.
                                       highlight_string($config_file, 1));
                            }
/*
                            warn('Your username/ password chosen for this upgrade do not match your config file. Please make sure
                                  to update the config file.', true);
*/
                        }

                        warn('For security reasons this file (install.php) should be deleted after the installation.
                              You can later adjust the database settings by modifying the '.CONFIG_FILE.' file.', true);
                    ?>
					Click <b>Start</b> to begin using <b>videoDB</b>...<br/>
					<br/>
				</td></tr>

				<tr><td colspan="2">
					<input type="hidden" name="step" value="<?php echo $step?>"/>
					<input type="submit" name="submit" value="Start &nbsp;&nbsp;>>" id="focus"/>
					<script language='JavaScript'>form1.focus.focus();</script>
				</td></tr>
<?php			break;

	case 3:		// confirm installation if tables exist
                $install = ($upgrading) ? 'Installing' : 'Upgrading';

                if ($upgrading)
                {
?>
                    <tr><td colspan="2">
                        <br/><b><?php echo $install?> database and tables.</b><br/><br/>
                        <?php echo $message?>
                        <br/>
                    </td></tr>
<?php
                } else {
?>
                    <tr><td colspan="2">
                        <br/><b><?php echo $install?> database and tables.</b><br/><br/>
                        <?php echo $message?>
                        You have selected a <b>non-empty</b> database. Installing into a non-empty database might lead to data loss and is only recommended for experienced users.
                        <br/>
                    </td></tr>

                    <tr><td colspan="2">
                        <br/>
                        Choose <b>Upgrade</b> to upgrade the existing installation (recommended).
                        Before upgrading, please make sure to backup your database!<br/>
                        <br/>
                    </td></tr>

                    <tr><td colspan="2">
                        <input type="submit" name="action" value="<?php echo $button_upgrade?>" id="focus"/>
                        <br/>
                    </td></tr>

                    <tr><td colspan="2">
                        <br/>
                        Click <b>Next</b> to install the tables into the existing <b>non-empty</b> database...<br/>
                        <br/>
                    </td></tr>
<?php
                }
?>
				<tr><td colspan="2">
					<input type="hidden" name="db_server" value="<?php echo $db_server?>"/>
					<input type="hidden" name="db_user" value="<?php echo $db_user?>"/>
					<input type="hidden" name="db_password" value="<?php echo $db_password?>"/>
                    <input type="hidden" name="db_database" value="<?php echo $db_database?>"/>
                    <input type="hidden" name="db_prefix" value="<?php echo $db_prefix?>"/>

					<input type="hidden" name="step" value="<?php echo $step+1?>"/>
					<input type="submit" name="formPrevious" value="<?php echo $button_prev?>"/>
					<input type="submit" name="submit" value="<?php echo $button_next?>"/>
					<script language='JavaScript'>form1.focus.focus();</script>
				</td></tr>
<?php			break;

	case 2:		// get setup parameters
                $install = ($upgrading) ? 'upgrade' : 'install';

                // load default configuration- don't overwrite any values
                include_once(CONFIG_FILE);
                extract($config, EXTR_SKIP, 'db_');

                // should not be required- all default data should already be in config.inc.php
				if (empty($db_server))  $db_server	= 'localhost';
				if (empty($db_user))	$db_user	= 'root';
				if (empty($db_password)) $db_password = '';
				if (empty($db_database)) $db_database = 'videodb';
?>
                <tr><td colspan="2">
<?php
                    if ($upgrading)
                    {
                        // skip the database creation step
                        $step++;
?>
                        <br/><b>Database Upgrade</b><br/>
                        <br/>Your database does not match the current version of VideoDB and needs to be upgraded.<br/>
                        <br/>
                        <?php warn("Please make sure to backup your database before proceeding!", true); ?>
                        <b>Select database and user.</b><br/>
                        <br/>
<?php
                    } else {
?>
                        <br/><b>Select database and user.</b><br/>
                        <br/>
                        <b>Note:</b> unless you're installing into an existing database, the database login must have <b>'Create Database'</b> rights.
                        <br/><br/>
<?php
                    }
?>
                    <?php echo $message?>
				</td></tr>

				<tr><td>Server:</td><td><input type="text" name="db_server" value="<?php echo $db_server?>" id="focus"/></td></tr>
				<tr><td>User:</td><td><input type="text" name="db_user" value="<?php echo $db_user?>"/></td></tr>
				<tr><td>Password:</td><td><input type="password" name="db_password" value="<?php echo $db_password?>"/></td></tr>
                <tr><td>Database:</td><td><input type="text" name="db_database" value="<?php echo $db_database?>"/></td></tr>
                <tr><td>Table prefix:</td><td><input type="text" name="db_prefix" value="<?php echo $db_prefix?>"/> (only required for new installations)</td></tr>

				<tr><td colspan="2">
					<br/>
                    Click <b>Next</b> to <?php echo $install?> database and tables...<br/>
					<br/>
				</td></tr>

				<tr><td colspan="2">
					<input type="hidden" name="step" value="<?php echo $step+1?>"/>
					<input type="submit" name="formPrevious" value="<?php echo $button_prev?>"/>
					<input type="submit" name="submit" value="<?php echo $button_next?>"/>
					<script language='JavaScript'>form1.focus.focus();form1.focus.select();</script>
				</td></tr>
<?php			break;

	case 1:
	default:	// start setup

?>				<tr><td colspan="2">
					<br/>This is the installer for <span style="font-weight:bold;"><a style="color://333399" href="http://www.videodb.net">videoDB</a></span>. You will require:
					<ol>
						<li><b>PHP &gt;= 4.2.0</b> with GD library and session support configured.</li><br/><br/>
						<li>A <b>MySQL database</b>, login (username and password) with create/drop table rights.</li><br/><br/>
						<li>If you want this installer to create the config file for you, it needs permission to write to web server's the videoDB <b>root directory</b>.</li><br/><br/>
					</ol>
					The installer will create a database in your MySQL installation, create and populate the required tables, and generate the videoDB configuration file.<br/><br/>
<!--
					<input type="checkbox" name="backedup"> <b>I have backed up my database incase of a problem with this upgrade.</b><br/><br/>
-->
					Click <b>Next</b> to setup the database connection...<br/><br/>

					<input type="hidden" name="step" value="<?php echo $step+1?>"/>
					<input type="submit" name="submit" value="<?php echo $button_next?>" id="focus"/>
					<script language='JavaScript'>form1.focus.focus();</script>
				</td></tr>
<?php
}

?>
            </table>
            </form>

			<br/><font size="1" color="#CCCCCC">Step: <?php echo $step?></font>
		</td></tr>
		</table>
	</td>
</tr>
</table>

</body>
</html>
