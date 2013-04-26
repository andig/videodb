<?php
/**
 * IMDB lookup popup
 *
 * Popup to search at IMDB
 *
 * @package videoDB
 * @author  Andreas Gohr    <a.gohr@web.de>
 * @author  Andreas Goetz   <cpuidle@gmx.de>
 * @version $Id: lookup.php,v 2.33 2010/04/04 10:33:56 andig2 Exp $
 */
 
require_once './core/functions.php';
require_once './engines/engines.php';
require_once './engines/imdb.php';

/**
 * Update item list asynchronously
 *
 * @author  Andreas Goetz   <cpuidle@gmx.de>
 */ 
function ajax_render()
{
    global $smarty, $result;

    // add some delay for debugging
    if ($config['debug'] && $_SERVER['SERVER_ADDR'] == '127.0.0.1') usleep(rand(200,1000)*1000);

    // load languages and config into Smarty
    tpl_language();

    $content = $smarty->fetch('lookup_ajax.tpl');

    header('X-JSON: '.json_encode(array('count' => count($result))));
    exit($content);
}


// determine default engine (first in list)
if (empty($engine)) $engine = engineGetDefault();

// result array
$result = array();

// Undo url encoding. At this point we cannot be sure which encoding is present- either ISO from URL or UTF-8 from form subimssion. Make sure we have unicode again.
$find = html_entity_decode_all(urldecode($find));
if (!is_utf8($find)) $find = utf8_encode($find);

switch ($engine)
{
    case 'amazona2s':
                    // amazona2s
                    $smarty->assign('catalog', array('Books','Classical','DigitalMusic','DVD','Electronics','Magazines','Music','MusicTracks','UnboxVideo','VHS','Video','VideoGames'));
                    if (empty($catalog)) $catalog = 'DVD';
                    $smarty->assign('selectedcatalog', $catalog);

                    if (!empty($find))
                    {
                        $result     = engineSearch($find, $engine, $catalog);
                        $searchurl  = engineGetSearchUrl($find, $engine);
                    }
                    break;
                    
    case 'imdb':
                    // imdb
                    $smarty->assign('searchaka', $searchaka);
                    if (!empty($find))
                    {
                        $result     = engineSearch($find, $engine, $searchaka);
                        $searchurl  = engineGetSearchUrl($find, $engine);
                    }    
                    break;

    default:
                    // tvcom, amazon, google
                    if (!empty($find))
                    {
                        $result     = engineSearch($find, $engine);
                        $searchurl  = engineGetSearchUrl($find, $engine);
                    }
}


$smarty->assign('searchtype', $searchtype);
$smarty->assign('imdbresults', $result);

// process asynchronous refresh
if ($ajax_render)
{
    ajax_render();
}

// prepare templates
tpl_language();
tpl_header();

// prepare template with selected and available engines
tpl_lookup($find, $engine, $searchtype);

$smarty->assign('searchurl', $searchurl);
$smarty->assign('http_error', $CLIENTERROR);

// display templates
smarty_display('lookup.tpl');

?>
