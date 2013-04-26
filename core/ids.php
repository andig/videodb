<?php

/**
 * IDS setup
 *
 * @package Core
 * @author  Andreas Goetz   <cpuidle@gmx.de>
 * @version $Id: ids.php,v 1.2 2010/02/01 22:38:42 andig2 Exp $
 */

// set the include path properly for PHPIDS
set_include_path(
    get_include_path()
    . PATH_SEPARATOR
    . './lib/'
);

if (!session_id()) {
    session_start();
}

require_once 'IDS/Init.php';

try {   
    $init = IDS_Init::init(dirname(__FILE__) . '/../lib/IDS/Config/Config.ini');
    $init->config['General']['base_path'] = dirname(__FILE__) . '/../lib/IDS/';
    $init->config['General']['use_base_path'] = true;
    $init->config['Caching']['caching'] = 'file';

    $request = array(
      'GET' => $_GET,
      'POST' => $_POST,
      'COOKIE' => $_COOKIE
    );
    
    $ids = new IDS_Monitor($request, $init);
    
    $result = $ids->run();
    if (!$result->isEmpty() && $result->getImpact() > 50)
    {
        require_once 'IDS/Log/Database.php';
        require_once 'IDS/Log/Composite.php';
        $compositeLog = new IDS_Log_Composite();
        $compositeLog->addLogger(
            IDS_Log_Database::getInstance($init)
        );
        $compositeLog->execute($result);

        $hta = @file_get_contents('.htaccess');
        if (preg_match('/(.+?)^(allow from all.*)/ms', $hta, $m))
        {
            $addr   = $_SERVER['REMOTE_ADDR'];
            
            // block whole subnet
            $addr   = implode('.', array_slice(explode('.', $addr), 0, 3));
            
            $hta = $m[1] . 'deny from '.$addr."\n" . $m[2];
            @file_put_contents('.htaccess', $hta);
        }

        header("HTTP/1.0 403 Forbidden");
        die('Your IP has been blocked.<br/>To find out why visit <a href="http://sourceforge.net/mailarchive/forum.php?forum_name=videodb-devel">http://sourceforge.net/mailarchive/forum.php?forum_name=videodb-devel</a>');
    }
} catch (Exception $e) {
   //this shouldn't happen and if it does you don't want the notification public.
}

?>
