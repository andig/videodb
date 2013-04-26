<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage PluginsFunction
 * @author  Andreas Goetz   <cpuidle@gmx.de>
 */

/**
 * Smarty {html_checkbox} function plugin
 *
 * File:       function.html_checkbox.php<br>
 * Type:       function<br>
 * Name:       html_checkbox<br>
 * Purpose:    Prints out a checkbox input<br>
 * Input:<br>
 *           - name       (optional) - string default "checkbox"
 *           - value      (required) - string
 *           - checked    (optional) - array default not set
 *           - id         (optional) - checkbox id (name is default)
 *           - label      (optional) - string for checkbox label
 * Examples:
 * <pre>
 * {html_checkbox value=1 name=horst}
 * {html_checkbox value=1 name=horst label="Select Horst"}
 * </pre>
 * @return string
 * @uses smarty_function_escape_special_chars()
 */
function smarty_function_html_checkbox($params, $template)
{
    require_once(SMARTY_PLUGINS_DIR . 'shared.escape_special_chars.php');

    $name = 'checkbox';
    $value = null;
    $selected = null;
    $label = null;

    $extra = '';

    foreach($params as $_key => $_val) {
        switch($_key) {
            case 'name':
            case 'id':
            case 'value':
            case 'label':
                $$_key = $_val;
                break;

            case 'checked':
            case 'selected':
                $selected = (bool)$_val;
                break;

            default:
                if(!is_array($_val)) {
                    $extra .= ' '.$_key.'="'.smarty_function_escape_special_chars($_val).'"';
                } else {
                    trigger_error("html_checkbox: extra attribute '$_key' cannot be an array", E_USER_NOTICE);
                }
                break;
        }
    }

    // assign default id
    if (empty($id)) $id = $name;
    
    $_output = '';
    if ($label) $_output .= '<nobr><label for="'.smarty_function_escape_special_chars($id).'">';
    
    $_output .= '<input type="checkbox" name="'
        . smarty_function_escape_special_chars($name) . '" id="'
        . smarty_function_escape_special_chars($id)   . '" value="'
        . smarty_function_escape_special_chars($value). '"';

    if ($selected) $_output .= ' checked="checked"';
    $_output .= $extra .' />';
    if ($label) $_output .= $label.'</label></nobr>';

    return $_output;
}

?>
