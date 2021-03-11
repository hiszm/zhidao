<?php
/**
 * Smarty Extend
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty indent modifier Extend
 *
 * Type:     modifier<br>
 * Name:     indent<br>
 * Purpose:  indent lines of text
 * @link http://Smarty.php.net/manual/en/Language.modifier.indent.php
 *          indent (Smarty online manual)
 * @author   Monte Ohrt <monte at ohrt dot com>
 * @param string
 * @param integer
 * @param string
 * @return string
 */
function smarty_modifier_indent($string,$chars=4,$char=" ")
{
    return preg_replace('!^!m',str_repeat($char,$chars),$string);
}

?>
