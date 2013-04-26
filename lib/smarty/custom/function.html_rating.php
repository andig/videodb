<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 * @author  Andreas Goetz   <cpuidle@gmx.de>
 */

/**
 * Smarty {html_rating} function plugin
 *
 * File:       function.html_rating.php<br>
 * Type:       function<br>
 * Name:       html_rating<br>
 * Purpose:    Prints out a rating as a series of stars<br>
 * Input:<br>
 *           - name       (optional) - string default "checkbox"
 *           - value      (required) - string
 *           - id         (optional) - checkbox id (name is default)
 * @return string
 */
function smarty_function_html_rating($params, &$smarty)
{
    $name = 'rating';
    $value = null;

    $extra = '';

    foreach($params as $_key => $_val) {
        switch($_key) {
            case 'name':
            case 'id':
            case 'value':
                $$_key = $_val;
                break;

            default:
                if(!is_array($_val)) {
                    $extra .= ' '.$_key.'="'.smarty_function_escape_special_chars($_val).'"';
                } else {
                    $smarty->trigger_error("rating: extra attribute '$_key' cannot be an array", E_USER_NOTICE);
                }
                break;
        }
    }

    // assign default id
    if (empty($id)) $id = $name;

    $_output = '';

    $rcv = ceil($value);
    for ($i=0; $i< $rcv; $i++)
    {
        $_output .= '<img src="'.img('goldstar.gif').'" width="20" height="18" />';
    }
    for ($i=0; $i< (10 - $rcv); $i++)
    {
        $_output .= '<img src="'.img('greystar.gif').'" width="20" height="18" />';
    }

    $_output .= $extra;

    $_output .= " ($value)";

    return $_output;
}

?>
