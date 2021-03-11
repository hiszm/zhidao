<?php
/**
 * Smarty Extend
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty strip_tags modifier Extend
 *
 * Type:     modifier<br>
 * Name:     strip_tags<br>
 * Purpose:  strip html tags from text
 * @link http://Smarty.php.net/manual/en/Language.modifier.strip.tags.php
 *          strip_tags (Smarty online manual)
 * @author   Monte Ohrt <monte at ohrt dot com>
 * @param string
 * @param boolean
 * @return string
 */
function smarty_modifier_strip_tags($string, $replace_with_space = true)
{
    if ($replace_with_space)
        return preg_replace('!<[^>]*?>!', ' ', $string);
    else
        return strip_tags($string);
}

/* vim: set expandtab: */

?>
