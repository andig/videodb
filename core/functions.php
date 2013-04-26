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
 * @version $Id: functions.php,v 1.131 2013/04/26 15:08:30 andig2 Exp $
 */

// add pwd to include_path
ini_set('include_path', '.'.PATH_SEPARATOR.ini_get('include_path'));

// Remove environment variables from global scope- ensures clean namespace
foreach (array_keys($_ENV) as $key) unset($GLOBALS[$key]);

// global const CONFIG_FILE is not yet defined at this point
if (!@include_once './config.inc.php')
{
	errorpage('Could not find configuration file <code>config.inc.php</code>',
	          "<p>Please make sure you've run the <a href='install.php'>installation script</a>.</p>");
}

if (@$config['offline'])
{
    errorpage('Maintenance', 'videoDB is currently offline for maintenance. Please check back later.');
}

// Uncomment the following line to enable phpIDS
// requires phpIDS to be installed in lib/IDS
// require_once './core/ids.php';

require_once './core/functions.core.php';
require_once './core/constants.php';
require_once './core/session.php';
require_once './core/encoding.php';
require_once './core/template.php';
require_once './core/cache.php';
require_once './core/compatibility.php';
require_once './lib/smarty/SmartyBC.class.php';

/* --------------------------------------------------------------------*/
// Set up some defaults

error_reporting($config['debug'] ? E_ALL ^ E_NOTICE : E_ERROR + E_PARSE);

// force magic quotes off
ini_set('magic_quotes_runtime', 0);
if (get_magic_quotes_gpc())
{
	if (!empty($_REQUEST)) remove_magic_quotes($_REQUEST);
	ini_set('magic_quotes_gpc', 0);
}

// register_globals off? Well I like it...
extract($_REQUEST);

// security check
if ($id) validate_input($id);
if ($ajax_update) validate_input($ajax_update);

// Smarty setup
$smarty = new SmartyBC();
$smarty->compile_dir	 = './cache/smarty';	        // path to compiled templates
$smarty->cache_dir		 = './cache/smarty';	        // path to cached html
$smarty->plugins_dir     = array('./lib/smarty/custom', './lib/smarty/plugins');
$smarty->use_sub_dirs	 = 0;                           // restrict caching to one folder
$smarty->loadFilter('output', 'trimwhitespace');        // remove whitespace from output
#$smarty->setCaching(Smarty::CACHING_LIFETIME_SAVED);
#$smarty->force_compile  = true;
#$smarty->debugging      = true;
$smarty->error_reporting = E_ALL & ~E_NOTICE;           // added for Smarty 3

// load config
load_config();

// check authentification data for multiuser
if (basename($_SERVER['PHP_SELF']) != 'login.php') auth_check();

?>