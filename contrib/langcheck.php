<?php
/**
 * Language Checker
 *
 * Checks languagefiles for completeness
 *
 * @package Contrib
 * @version $Id: langcheck.php,v 1.7 2013/03/10 17:08:36 andig2 Exp $
 */

$LANGDIR    = '../language';
$base_lang  = 'en';
#include($LANGDIR.'/'.$base_lang.'_withtooltips.php');
$tooltip_lang = array(); # $lang
unset($lang);
include($LANGDIR.'/en.php');

error_reporting(E_WARNING);

?>
<html>
  <head>
    <title>Translation statistics</title>
  </head>
  <body>
<?php

function printlang($missing, $code, $type)
{
    $c = count($missing);
    print '<b>'.$code.'</b> ';
    
    if ($c)
    {
        print $c.' translations '.$type;
        print '<br /><br />';
        
        foreach($missing as $key)
        {
            print $key.'<br />';
        }
    } 
    else 
    {
        print 'complete';
    }
    print '<br /><hr noshade="noshade" size="1" />';
}

function getlangs(&$tooltipLangs)
{
    global $LANGDIR;
    
    if ($dh = opendir($LANGDIR)) 
    {
        while (($file = readdir($dh)) !== false) 
        {
            if(preg_match("/(.*)\.php$/",$file,$matches))
            {
                $langs[]=$matches[1];
                if(substr($file, -16) == "withtooltips.php") 
                {
                    $tooltipLangs[] = $matches[1];
                }
            }
        }
        closedir($dh);
    }
    else
    {
        print "could not open language directory $LANGDIR";
        exit;
    }
    
    return $langs;
}

function loadlang($code)
{
    global $LANGDIR;
    
    include($LANGDIR.'/'.$code.'.php');
    return $lang;
}


foreach(getlangs($tooltipLangs) as $code) if ($base_lang !== $code)
{
    $foreign = loadlang($code);

    $missing    = array();
    $identical  = array();
    
	$useLang = $lang;
        
    foreach (array_keys($useLang) as $key)
    {
        if(empty($foreign[$key]))
        {
            $missing[]=$key;
        }

        if ($useLang[$key] == $foreign[$key])
        {
            $identical[] = $key;
        }
    }
    
    printlang($missing, $code, 'missing');
    
    if ($_GET['copycheck']) printlang($identical, $code, 'identical');
}

?>
  </body>
</html>
