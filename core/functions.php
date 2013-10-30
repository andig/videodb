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
ini_set('include_path', '.' . PATH_SEPARATOR . ini_get('include_path'));

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

// exception handling beyond this point
set_exception_handler('exception_handler');

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

// Remove environment variables from global scope- ensures clean namespace
foreach (array_keys($_ENV) as $key) unset($GLOBALS[$key]);

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
$smarty->compile_dir     = './cache/smarty';            // path to compiled templates
$smarty->cache_dir       = './cache/smarty';            // path to cached html
$smarty->plugins_dir     = array('./lib/smarty/custom', './lib/smarty/plugins');
$smarty->use_sub_dirs    = 0;                           // restrict caching to one folder
$smarty->loadFilter('output', 'trimwhitespace');        // remove whitespace from output
#$smarty->setCaching(Smarty::CACHING_LIFETIME_SAVED);
#$smarty->force_compile  = true;
#$smarty->debugging      = true;
$smarty->error_reporting = E_ALL & ~E_NOTICE;           // added for Smarty 3

// load config
load_config();

// check authentification data for multiuser
if (basename($_SERVER['PHP_SELF']) != 'login.php') auth_check();


/**
 * General functions
 */

/**
 * Global exception handler
 */
function exception_handler($exception)
{
    errorpage('An exception occured: ', $exception->getMessage(), true);
}

/**
 * Checks if the cache directories exist and are writable by the webserver.
 * If they don't exist it tries to create them. If this fails, too a simple
 * error page is displayed.
 * The function checks if the MySQL PHP extensions is loaded, too.
 */
function verify_installation($return = false)
{
    global $config;

    // check MySQL extension
    if (!extension_loaded('mysqli'))
    {
        errorpage('MySQL extension for PHP not loaded',
                  '<p>The MySQL extension for PHP is not loaded.</p>
                   <p>Please make sure the MySQL module for PHP is installed and enabled
                   in your <code>php.ini</code></p>');
    }

    // collect all directory-specific errors
    $error = '';

    // check cache
    foreach (array(CACHE => 0,CACHE.'/smarty' => 0, CACHE.'/imdb' => 1, CACHE.'/img' => 1, CACHE.'/thumbs' => 1) as $dir => $hierarchical)
	{
        // check top-level folders
        $error .= cache_create_folders($dir, $hierarchical ? (int) $config['hierarchical'] : 0);
    }

    if ($return) return $error;
    
	if ($error)
	{
        errorpage('Cache directories not writable',
                  '<p>The cache directories have to be writable by the webserver!</p>
                   <p>Please fix the following errors:</p>
                   <p>'.$error.'</p>');
    }
}

/**
 * Load config options from config.inc.php and database and
 * setup sane defaults.
 * Return configuration in global $config array variable
 *
 * @todo    Add security check if install.php is still available
 * @param   boolean force reload of configuration data
 */
function load_config($force_reload = false)
{
	global $config, $lang, $smarty;
    // configuration cached and not outdated?
    if (!$force_reload && !$config['recompile'] && session_get('config') &&
       (session_get('config_userid') === $_COOKIE['VDBuserid']) &&
       (session_get('config_timestamp') == filemtime(CONFIG_FILE)))
    {
        // load from cache
        $config = session_get('config');
    }
    else
    {
        // check MySQL extension and cache directories
        verify_installation();

        // remember modification time
        session_set('config_timestamp', filemtime(CONFIG_FILE));

        // get config options from the database
        $SELECT = 'SELECT opt,value
                     FROM '.TBL_CONFIG;
        $result = runSQL($SELECT);
        $config = array_merge($config, array_associate($result, 'opt', 'value'));

        // check if database matches the current version
        if ($config['dbversion'] < DB_REQUIRED)
        {
            // run installer
            redirect('install.php?action=upgrade');
        }

        // get user config options from the database
        // does not use get_current_user_id() to allow fallback to login page after loading config
        if (is_numeric($user_id = $_COOKIE['VDBuserid']))
        {
            // store user id in session to identify reload point for config
            session_set('config_userid', $user_id);

            $SQL    = 'SELECT opt, value
                         FROM '.TBL_USERCONFIG.'
                        WHERE user_id = '.$user_id;
            $result = runSQL($SQL);
            $config = array_merge($config, array_associate($result, 'opt', 'value'));
        }

        // set some defaults
        if (empty($config['language'])) $config['language'] = 'en';
        if (empty($config['template'])) $config['template'] = 'modern::compact';
        if (empty($config['filterdefault'])) $config['filterdefault'] = 'unseen';

//      if ($config['IMDBage'] < 1) $config['IMDBage']          = 60*60*24*5;
        if ($config['castcolumns'] < 1) $config['castcolumns']  = 4;
        if ($config['listcolumns'] < 1) $config['listcolumns']  = 1;
        if ($config['thumbAge'] < 1) $config['thumbAge']        = 60*60*24*7*3;
        if ($config['shownew'] < 1) $config['shownew']          = 12;

        // prepare som options for later use
        $config['languages']    = explode('::', $config['languageflags']);

        // prepare template/style
        $tpl                    = explode('::', $config['template']);
        $config['style']        = 'templates/'.$tpl[0].'/'.$tpl[1].'.css';
        $config['templatedir']  = 'templates/'.$tpl[0].'/';
/*
        // multiple style files - use template name as base (e.g. elegant_grey.css)
        if (!file_exists($config['style']))
        {
            // this should be an array
            $config['style']    = array('templates/'.$tpl[0].'/'.$tpl[0].'.css',
                                        'templates/'.$tpl[0].'/'.$tpl[0].'_'.$tpl[1].'.css');
        }
*/
        // check if selected template is valid
        if (!file_exists($config['style']))
        {
            $config['template']    = 'elegant::grey';
            $config['templatedir'] = 'templates/elegant/';
            $config['style']       = 'templates/elegant/grey.css';
		}

        // smarty cacheid for multiuser mode
        $config['cacheid']      = $tpl[0];

        // get installed engines meta information
        if (empty($config['engines']))
        {
            require_once './engines/engines.php';
            $config['engines'] = engineMeta();

            // translate config options of type engine xyz into config[engine]
            foreach ($config['engines'] as $engine => $meta)
            {
                // convert the db engine options into associative array of engine enabled status
                if ($config['engine'.$engine])
                {
                    $config['engine'][$engine] = $config['engine'.$engine];

                    // add meta-engine if enabled
                    engine_setup_meta($engine, $meta);
                }
            }
        }

/*
        // added proxy support for $_ENV
        $proxy = $config['proxy_host'];
        if (empty($proxy))
        {
            $env = array_change_key_case($_ENV);
            $proxy = $env['http_proxy'];
        }
        if (!empty($proxy))
        {
            $uri = parse_url($proxy);
            $config['proxy_host'] = ($uri['scheme']) ? $uri['host'] : $uri['path'];
            $config['proxy_port'] = ($uri['port']) ? $uri['port'] : 8080;
        }
*/
		// store loaded configuration
        session_set('config', $config);
	}

    // setup smarty
    $smarty->template_dir = array($config['templatedir'], 'templates/modern');
    $smarty->assign('template', $config['templatedir']);

    // initialize languages
    $lang = array();

    // load english language as default
    require './language/en.php';

    // override it with local language if nessesary:
    if ($config['language'] != 'en')
    {
        $languages = explode('_', $config['language']);
        $file = '';
        foreach ($languages as $language)
        {
            if ($file) $file .= '_';
            $file .= $language;
            @include './language/'.$file.'.php';

            // convert languages to utf-8 encoding
            if ($lang['encoding'] != 'utf-8')
            {
                $lang = iconv_array($lang['encoding'], 'utf-8', $lang);
                $lang['encoding'] = 'utf-8';
            }
        }
    }

    // set connection character set and collation
#   db_set_encoding();
}

/**
 * Displays an errorpage and exits
 *
 * @param string $title   The pages headline
 * @param string $body    An additional message
 */
function errorpage($title = 'An error occured', $body = '', $stacktrace = false)
{
    global $lang;

    $encoding   = ($lang['encoding']) ? $lang['encoding'] : 'iso-8859-1';

    // stacktrace desired and available?
    if ($stacktrace && function_exists('xdebug_get_function_stack'))
    {
        $body .= '<br/>'.dump(xdebug_get_function_stack(), true);
    }

    echo '<?xml version="1.0" encoding="en"?>';
    echo "
    <html xmlns='http:// www.w3.org/1999/xhtml' xml:lang='en' lang='en' dir='ltr'>
    <head>
        <title>VideoDB - ERROR</title>
        <meta http-equiv='Content-Type' content='text/html; charset=$encoding' />
        <meta name='description' content='VideoDB' />
    </head>
    <body>
        <h1>$title</h1>
        $body
    </body>
    </html>";

	exit;
}

/**
 * Verify variable is valid according to validation function
 *
 * @author Andreas Goetz <cpuidle@gmx.de>
 * @param  string   $var                variable to validate (e.g. $id)
 * @param  string   $validation_func    validation function name (e.g. is_numeric)
 */
function validate_input(&$var, $validation_func = 'is_numeric')
{
    if (function_exists($validation_func))
    {
        if (!$validation_func($var))
        {
            errorpage('Forbidden', 'You are not allowed to access this page.');
        }
    }
}

/**
 * Display template with Smarty
 * If Smarty caching is enabled and cache id present, then cache will be used
 *
 * @author Andreas Goetz <cpuidle@gmx.de>
 * @param   string  $template   Template file name for display
 * @parem   string  $id         Cache id
 */
function smarty_display($template, $id = null)
{
    global $smarty, $config;

    // config[cacheid] is set to the template name
    $smarty->display($template, $id, $config['cacheid']);
}


/**
 * Image handling functions
 */

/**
 * Tries to find the given image in template directory then in the default
 * image directory.
 *
 * @param  string  filename of image
 * @return string  path to the image
 */
function img($img = 'nocover.gif')
{
	global $config;

	$result = 'images/'.$img;
	if (file_exists($config['templatedir'].$result)) $result = $config['templatedir'].$result;
	return ($result);
}

/**
 * Internal function for supporting actor image multi-queries
 */
function get_actor_image_from_cache($result, $name, $actorid)
{
    global $config;

    $imgurl = 'img.php?name='.urlencode($name);
    if ($actorid) $imgurl .= '&actorid='.urlencode($actorid);

    // really an image?
    if (preg_match('/\.(jpe?g|gif|png)$/i', $result['imgurl'], $matches))
    {
        if (cache_file_exists($result['imgurl'], $cache_file, CACHE_IMG, $matches[1]))
        {
            return($cache_file);
        }
    }
    elseif (isset($result['cacheage']) && $result['cacheage'] <= $config['thumbAge'])
    {
        // checked only recently
        return(img());
    }

    return($imgurl);
}

/**
 * get Thumbnail-URL for an actor
 *
 * @param  string  name of the Actor
 * @param  boolean idSearchAllowed can be used to search by name only if searching by id has already been performed before
 * @return string  the URL to the cached image if exists or a link to img.php
 */
function getActorThumbnail($name, $actorid = 0, $idSearchAllowed = true)
{
	global $config;

    $SQL = 'SELECT name, imgurl, UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(checked) AS cacheage
              FROM '.TBL_ACTORS;

	// identify actor by unique actor id, of by name
    if ($actorid && $idSearchAllowed) {
        $result = runSQL($SQL." WHERE actorid='".addslashes($actorid)."'");
	}
	if (!$actorid || (count($result) == 0)) {
        $result = runSQL($SQL." WHERE name='".addslashes(html_entity_decode($name))."'");
	}

    $imgurl = get_actor_image_from_cache($result[0], $name, $actorid);

	return($imgurl);
}

function cleanFilename($filename) {
    return preg_replace('/[^a-z0-9-_ ]/', '_', strtolower($filename));
}

/**
 * get Thumbnail for a movie
 *
 * @param  string   URL
 * @return string   the URL to the cached image if exists or a link to img.php
 */
function getThumbnail($imgurl, $name = '')
{
    // cover url not set? try local path instead
    if (!$imgurl && $name)
    {
        // be careful with the filename here- so clean it
        $localname  = CACHE.'/'.CACHE_LOCAL.'/'.cleanFilename($name).'.jpg';
//      Small performance fix
//      if (file_exists($localname) && filesize($localname)) return($localname);
        if (@filesize($localname) > 0) return($localname);
    }

    // really an image?
    if (preg_match('/\.(jpe?g|gif|png)$/i', $imgurl, $matches))
	{
		// local file? - keep it!
		if (!preg_match('/^http/i', $imgurl)) return($imgurl);

        // file in cache?
        if (cache_file_exists($imgurl, $cache_file, CACHE_IMG, $matches[1]))
        {
            // double-check this is really an image
		    if (@exif_imagetype($cache_file)) {
	            return($cache_file);
			}
		}
        else
		{
            // add cache_ignore=1& to suppress additional cache lookup in img.php
            return('img.php?url='.urlencode($imgurl));
		}
	}

	// no image url given -> nopic
	return(img());
}


/**
 * Authorizatoin and access
 */

/**
 * Perform login as selected user. Sets session cookies accordingly.
 *
 * @author Andreas Goetz <cpuidle@gmx.de>
 */
function login_as($userid, $permanent = false)
{
    global $config;

    if (!$userid || !is_numeric($userid)) errorpage('Error', 'Invalid login attempt');

    $RandNumber = rand(100000000, 999999999);

    // permanent cookie: 1 year, otherwise session only
    $validtime  = ($permanent) ? time() + 60*60*24*365 : null;
    $username   = get_username($userid);

    // get script folder for cookie path
    $subdir     = substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'],'/')) . '/';

    setcookie('VDBuserid',   $userid, $validtime, $subdir);
    setcookie('VDBusername', $username, $validtime, $subdir);
    setcookie('VDBpassword', $RandNumber, $validtime, $subdir);

    // make cookies available right away
    $_COOKIE['VDBuserid']   = $userid;
    $_COOKIE['VDBusername'] = $username;

    if ($userid != $config['guestid'])
    {
        runSQL('UPDATE '.TBL_USERS." SET cookiecode='$RandNumber' WHERE id=$userid");
    }
}

/**
 * Checks if the user was authenticated and if the received auth cookie is valid.
 * Function is called for every page except login.php!
 *
 * TODO Check if guest login shouldn't also be effective if disable public access is enabled
 *      Currently userid returned is 0 in that case
 *
 * @param  string $redirect  Redirect to login page if authentication check unsuccessful
 */
function auth_check($redirect = true)
{
    global $config;

    $result = true;

    // single user mode- login as admin
    if (!$config['multiuser'])
    {
        if (empty($_COOKIE['VDBuserid'])) login_as($config['adminid']);
    }

    // auth check only in multiuser mode
    if ($config['multiuser'] && ($_COOKIE['VDBuserid'] !== $config['guestid']))
    {
        $result = false;

        $referer = substr($_SERVER['PHP_SELF'], strrpos($_SERVER['PHP_SELF'],'/')+1) .'?'. $_SERVER['QUERY_STRING'];

        // already logged in?
        $userid = $_COOKIE['VDBuserid'];
        $user   = $_COOKIE['VDBusername'];
        $pass   = $_COOKIE['VDBpassword'];

        // auth cookies present?
        if (preg_match('/[a-z]+/i', $user) && preg_match('/[0-9]+/', $pass) && is_numeric($userid))
        {
            // Dummy-Query to establish mysql connection.
            // VERY UGLY hack - without an established connection escapeSQL returns false in some PHP/Mysql versions
            // and this leads to getting logged out all the time
            runSQL('SELECT 1');

            // This is the crucial bit, lets just test the cookiecode with SQL again.
            $res    = runSQL("SELECT cookiecode FROM ".TBL_USERS." WHERE name='".escapeSQL($user)."' AND id=$userid");
            $result = $res[0]['cookiecode'] == $pass;
        }

        // HTTP basic authentication (for RSS feed)?

        // Hack for mod_fastcgi [muddle @ 2010-01-17]:
        if (!$result && !isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['Authorization']) && !empty($_SERVER['Authorization']))
        {
            list ($auth_type, $auth_cred) = explode(' ', $_SERVER['Authorization']);
            if ($auth_type == 'Basic')
            {
                list ($auth_user, $auth_pass) = explode(":", base64_decode($auth_cred));
                $_SERVER['PHP_AUTH_USER']	  = $auth_user;
                $_SERVER['PHP_AUTH_PW']		  = $auth_pass;
            }
        }

        if (!$result && isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']))
        {
            $user   = $_SERVER['PHP_AUTH_USER'];
            $pass   = $_SERVER['PHP_AUTH_PW'];

            // check if basic auth headers are valid
            if (preg_match('/[a-z]/i', $user))
            {
                // auth successful if password matches
                $res    = runSQL("SELECT * FROM ".TBL_USERS." WHERE name='".escapeSQL($user)."'");

                // if user is found, set cookie to make sure he's recognized
                if (count($res))
                {
                    $result = md5($pass) == $res[0]['passwd'];
                    if ($result) login_as($res[0]['id']);
                }
            }
        }

        // autologin as guest?
        if (!$result && !$config['denyguest'])
        {
            login_as($config['guestid']);
            $result = true;
        }

        // goto login page if anything was fishy
        if ($redirect && !$result && !defined('AUTH_NOREDIRECT'))
        {
            redirect('login.php?refer='.urlencode($referer));
        }
    }

    return $result;
}


/**
 * Permission handling
 */

/**
 * Setup clean permission cache. Triggers reading database on next permission access
 *
 * @author Andreas Goetz    <cpuidle@gmx.de>
 */
function clear_permission_cache()
{
    $_SESSION['vdb']['permissions'] = null;
}

/**
 * Checks if the logged in user has write permissions for the given video
 *
 * @author Chinamann <chinamann@users.sourceforge.net>
 * @param  integer $perm USER PERMISSIONS
 * @param  integer $id   VideoID
 * @return boolean
 */
function check_videopermission($perm, $id)
{
    return check_permission($perm, get_owner_id($id));
}

/**
 * Used to check permissions on a user for a page
 *
 * @author Mike Clark       <Mike.Clark@Cinven.com>
 * @author Chinamann        <chinamann@users.sourceforge.net>
 * @author Andreas Goetz    <cpuidle@gmx.de>
 * @param  integer $permission Permission to check
 * @param  String  $destUserId UserId to access
 * @return boolean             True if permission exists else false
 */
function check_permission($permission, $destUserId = null)
{
    global $config;

    // everything's allowed in single user mode
    if (!$config['multiuser']) return true;

    // not logged in - this shouldn't happen in theory
    // note: get_current_user_id() is valid at this point - authcheck has already run
    // note: get_current_user_id() could return 0 if guest access is disabled and user has been redirected to login.php
    if (!($userid = get_current_user_id())) return false;

    // check if permissions cache is initialized
    if (!is_array($_SESSION['vdb']['permissions']))
    {
        $_SESSION['vdb']['permissions'] = array();
        $_SESSION['vdb']['permissions']['to_uid'] = array();

        // ALL permissions
        $result = runSQL('SELECT permissions FROM '.TBL_USERS.' WHERE id='.$userid);
        $_SESSION['vdb']['permissions']['all'] = (count($result)) ? $result[0]['permissions'] : 0;

        // user-specific permissions
        $result = runSQL('SELECT * FROM '.TBL_PERMISSIONS.' WHERE from_uid='.$userid);

        // add to cache
        foreach ($result as $row)
        {
            $_SESSION['vdb']['permissions']['to_uid'][$row['to_uid']] = $row['permissions'];
        }
    }    

    // User level permissions
    $permissions |= $_SESSION['vdb']['permissions']['all'];

    // Cross-user permissions for target user
    if ($destUserId && $destUserId !== PERM_ALL)
    {        
        $permissions |= $_SESSION['vdb']['permissions']['to_uid'][$destUserId];
 
        // checking for _any_ cross-user permission? e.g. used for availability of "New", "Search"
        if (($destUserId == PERM_ANY) && ($permissions & $permission) == 0)
        {
            foreach($_SESSION['vdb']['permissions']['to_uid'] as $user_perm)
            {
                $permissions |= $user_perm & $permission;
            }    
        }
    }

    // check permission bits
    return (($permissions & $permission) == $permission);
}

/**
 * Check permissions on a user for a page and display error message on failure
 *
 * @author  unknown
 * @author  Chinamann <chinamann@users.sourceforge.net>
 * @param   integer $permission  Permission to check (admin,write,writeall)
 * @param   String  $destUserId UserId to access
 */
function permission_or_die($permission, $destUserId = false)
{
    if (!check_permission($permission, $destUserId))
    {
        errorpage("Access denied",'You don\'t have enough permissions to access this
                   page try to <a href="login.php">login</a> first.');
    }
}

/**
 * Function to get the owner name from videodata table
 *
 * @author Mike Clark <Mike.Clark@Cinven.com>
 * @param  integer $id       videodata id
 * @param  boolean $diskid   is the given ID a disk ID instead of videoID?
 * @return string  Returns the owner of the given Video or Disk
 */
function get_owner($id, $diskid = false)
{
    $SELECT = "SELECT ".TBL_USERS.".name AS owner
                 FROM ".TBL_DATA.", ".TBL_USERS."
                WHERE ".TBL_USERS.".id = ".TBL_DATA.".owner_id AND ".TBL_DATA.".";

    $SELECT .= ($diskid) ? "diskid = '$id'" : "id = $id";

    $result = runSQL($SELECT);
    return $result[0]['owner'];
}

/**
 * Function to get the owner id from videodata table
 *
 * @author Chinamann <chinamann@users.sourceforge.net>
 * @param  integer $id       videodata id
 * @param  boolean $diskid   is the given ID a disk ID instead of videoID?
 * @return string  Returns the owner of the given Video or Disk
*/
function get_owner_id($id, $diskid = false)
{
    $SELECT = "SELECT owner_id
                 FROM ".TBL_DATA."
                WHERE ";

    $SELECT .= ($diskid) ? "diskid = '$id'" : "id = $id";

    $result = runSQL($SELECT);
    return $result[0]['owner_id'];
}

/**
 * Get list of adult genre ids
 *
 * @return  array   Array of adult genre ids
 */
function get_adult_genres()
{
    global $config;

    $adultgenres = array();
    foreach(explode('::', $config['adultgenres']) as $ag)
    {
        if (empty($ag)) continue;
        $adultgenres[] = $ag;
    }

    return $adultgenres;
}

/**
 * Checks if a movie is not prohibited because of adults content
 *
 * @param   integer $id   video id
 * @return  boolean       Returns true if access is granted
 */
function adultcheck($id)
{
	global $config;
	
    if (check_permission(PERM_ADULT) || empty($config['adultgenres']))
    {
		// no multiuser or adult genres set or we actually do have the
		// permissions - whatever let's watch some pr0n ;-)
		return true;
	}
	
    $adultgenres = 'genre_id='.join(' OR genre_id=', get_adult_genres());
	$select = 'SELECT video_id
			     FROM '.TBL_VIDEOGENRE.'
			    WHERE video_id = '.$id.'
				  AND ('.$adultgenres.')';
	$result = runSQL($select);	

	return(empty($result[0]['video_id']));
}

/**
 * Checks if the given movie was already seen by the logged in user. If no
 * user is logged in the $seen value is returned
 *
 * Gets username from cookie
 *
 * @author  Andreas Goetz   <cpuidle@gmx.de>
 * @param  integer $id    video id
 * @param  boolean $seen  seen
 * @return boolean        True if seen
 *
 * @deprecated
 */
function get_userseen($id)
{
    $user_id= $_COOKIE['VDBuserid'];

    if (empty($user_id))
        errorpage('Security Error',  "User id cookie was unexpectedly not set. Please report this problem to the developers.");

	$SELECT =  'SELECT video_id
                  FROM '.TBL_USERSEEN.', '.TBL_USERS.'
                 WHERE '.TBL_USERSEEN.'.video_id='.$id." AND
                       ".TBL_USERSEEN.".user_id = ".$user_id;
	$result = runSQL($SELECT);

	$result = (count($result) > 0) ? 1 : 0;
	return($result);
}

/**
 * Sets the status in userseen accordingly to the given seen value
 *
 * Gets username from cookie
 *
 * @author  Andreas Goetz   <cpuidle@gmx.de>
 * @param  integer $id    video id
 * @param  boolean $seen  seen
 */
function set_userseen($id, $seen)
{
    $user_id = get_current_user_id();
    
    if (empty($user_id)) errorpage('Security Error',  
        "User id cookie was unexpectedly not set. Please report this problem to the developers.");

    $SQL = ($seen) ? "REPLACE INTO ".TBL_USERSEEN." SET user_id=".$user_id.", video_id='".$id."'"
                   : "DELETE FROM ".TBL_USERSEEN." WHERE user_id=".$user_id." AND video_id='".$id."'";
    runSQL($SQL);

/*
	// future code when userseen contains more user-specific data
	
	// record already exists?
	$SELECT = "SELECT seen, user_id FROM video_user, users ".
			"WHERE video_user.video_id=".$id." AND video_user.user_id = user.id AND users.user='".$_COOKIE['VDBusername']."'";
	$result = runSQL($SELECT);

	if (empty($result[0]['id'])) {
		$SELECT = "SELECT id FROM users ".
				  "WHERE user='".$_COOKIE['VDBusername']."'";
		$result = runSQL($SELECT);

		$SQL	= "INSERT INTO video_user SET user_id='".$result[0]['id']."', id='".$id."', seen='".$seen."'";
	}
	else {
		$SQL	= "UPDATE video_user SET seen='".$seen."' ".
				  "WHERE user_id='".$result[0]['id']."', id='".$id."', ";
	}
	runSQL($SQL);
*/
}

/**
 * Return id of the currently logged in user. 
 * The value returned is safe to use in SQL statements.
 *
 * @author  Andreas Goetz <cpuidle@gmx.de>
 * @result  integer user id
 */
function get_current_user_id()
{
    // make sure userid is numeric- preventing SQL injection attacs
    if (!is_numeric($userid = $_COOKIE['VDBuserid'])) $userid = 0;
#    errorpage('Security Error', 'Invalid user id in cookie: '.$userid, true);
    return $userid;
}

/**
 * Return UserId to a given UserName
 *
 * @author  Chinamann <chinamann@users.sourceforge.net>
 * @param   string  $userName user name
 * @result  integer user id
 */
function get_userid($userName)
{
    $SELECT = "SELECT id
                 FROM ".TBL_USERS."
                WHERE name='".addslashes($userName)."'";
    $result = runSQL($SELECT);
    return $result[0]['id'];
}

/**
 * Return UserName to a given UserId
 *
 * @author Chinamann <chinamann@users.sourceforge.net>
 * @param  integer   $userId user id
 * @param  string            user name
 */
function get_username($userId)
{
    $SELECT = "SELECT name
                 FROM ".TBL_USERS."
                WHERE id=".$userId;
    $result = runSQL($SELECT);
    return $result[0]['name'];
}


?>