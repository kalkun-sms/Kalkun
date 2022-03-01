<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Kalkun
 * An open source web based SMS Manager
 *
 * @copyright 2021 Fab Stz
 * @author Fab Stz <fabstz-it@yahoo.fr>
 * @license <https://spdx.org/licenses/GPL-3.0-or-later.html> GPL-3.0-or-later
 * @link https://github.com/kalkun-sms/Kalkun/
 */

/**
 *
 * translate the label for given context with parameters
 *
 * @param type $label
 * @param type $context
 * @param type $params
 * @return type
 */
function tr($label, $context = NULL, ...$params)
{
	return call_user_func_array(array(get_instance()->lang, 'line'), func_get_args());
}

/**
 *
 * translate the label for given context with parameters
 * and by applying escaping of the $chars_to_escape
 * (might be usefull is the output is for example used in javascript)
 *
 * @param type $chars_to_escape
 * @param type $label
 * @param type $context
 * @param type $params
 * @return type
 */
function tr_addcslashes($chars_to_escape, $label, $context = NULL, ...$params)
{
	$args = array_slice(func_get_args(), 1);
	$label = call_user_func_array(array(get_instance()->lang, 'line'), $args);
	return addcslashes($label, $chars_to_escape);
}
