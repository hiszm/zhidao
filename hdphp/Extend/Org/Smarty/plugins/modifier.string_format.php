<?php
/**
 * Smarty Extend
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty string_format modifier Extend
 *
 * Type:     modifier<br>
 * Name:     string_format<br>
 * Purpose:  format strings via sprintf
 * @link http://Smarty.php.net/manual/en/Language.modifier.string.format.php
 *          string_format (Smarty online manual)
 * @author   Monte Ohrt <monte at ohrt dot com>
 * @param string
 * @param string
 * @return string
 */
function smarty_modifier_string_format($string, $format)
{
    return sprintf($format, $string);
}

/* vim: set expandtab: */

?>
