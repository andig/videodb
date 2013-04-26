<?php
/**
 * Profile page
 *
 * Handles saving of the various config options for the user.
 *
 * @package Setup
 * @author  Andreas Gohr    <a.gohr@web.de>
 * @version $Id: profile.php,v 2.19 2008/04/20 17:31:20 andig2 Exp $
 */
 
require_once './core/session.php';
require_once './core/functions.php';
require_once './core/setup.core.php';

$user_id    = get_current_user_id();

// really shouldn't happen
if (empty($user_id))
{
	errorpage('Access denied', 'You don\'t have enough permissions to access this '.
			  'page. Please <a href="login.php">login</a> first. '.
              '(This feature is not available in Single User Mode)');
}

// save data
if ($save)
{
    // convert languages array back into string
	$languageflags = @join('::', $languages);
    
	// insert data
	foreach ($SETUP_USER as $opt) 
    {
        $SQL = "REPLACE INTO ".TBL_USERCONFIG." (user_id, opt, value) 
                      VALUES ('".addslashes($user_id)."', '$opt', '".addslashes($$opt)."')";
		runSQL($SQL);
	}

    // update session variables
    update_session();
	
	// reload config
	load_config(true);
    
/*
    // clear compiled templates for new template
    AG: should not be required
    $smarty->clear_compiled_tpl(null, $config['cacheid']);
*/ 
}

// prepare options
$setup = setup_mkOptions(true);

// prepare templates
tpl_page('profile');

$smarty->assign('setup', $setup);

// display templates
tpl_display('profile.tpl');

?>