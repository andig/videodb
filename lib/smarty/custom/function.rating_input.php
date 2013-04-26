<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 * @author  Andreas Goetz   <cpuidle@gmx.de>
 */

/**
 * Smarty {rating_input} function plugin
 *
 * File:       function.rating_input.php<br>
 * Type:       function<br>
 * Name:       rating_input<br>
 * Purpose:    Prints out a rating input control<br>
 * Input:<br>
 *           - name       (optional) - string default "checkbox"
 *           - value      (required) - string
 *           - id         (optional) - checkbox id (name is default)
 * @return string
 */
function smarty_function_rating_input($params, &$smarty)
{
    require_once(SMARTY_PLUGINS_DIR . 'shared.escape_special_chars.php');
    //$smarty->loadPlugin('Smarty_shared_escape_special_chars');

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
                    $smarty->trigger_error("rating_input: extra attribute '$_key' cannot be an array", E_USER_NOTICE);
                }
                break;
        }
    }

    // assign default id
    if (empty($id)) $id = $name;

    $_output = '';

    $_output .= '<input type="text" size="10" maxlength="4"'.
                ' name="'.smarty_function_escape_special_chars($name).'"'.
                ' id="'.smarty_function_escape_special_chars($id).'"'.
                ' value="'.smarty_function_escape_special_chars($value).'"';

    $_output .= $extra .' />';

    for ($i=1; $i<=10; $i++)
    {
        $_output .= " <a href='#' onclick='document.edi.".$name.".value=\"".$i.'.0"\'>'.$i.'</a>';
    }

    return $_output;
}

?>
