<?php
/**
 * Smarty Extend
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty cat modifier Extend
 *
 * Type:     modifier<br>
 * Name:     cat<br>
 * Date:     Feb 24, 2003
 * Purpose:  catenate a value to a variable
 * Input:    string to catenate
 * Example:  {$var|cat:"foo"}
 * @link http://Smarty.php.net/manual/en/Language.modifier.cat.php cat
 *          (Smarty online manual)
 * @author   Monte Ohrt <monte at ohrt dot com>
 * @version 1.0
 * @param string
 * @param string
 * @return string
 */
function smarty_modifier_cat($string, $cat)
{
    return $string . $cat;
}

/* vim: set expandtab: */

?>
