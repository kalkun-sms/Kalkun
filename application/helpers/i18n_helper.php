<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Kalkun
 * An open source web based SMS Manager
 *
 * @copyright 2021 Fab Stz
 * @author Fab Stz <fabstz-it@yahoo.fr>
 * @license <https://spdx.org/licenses/GPL-3.0-or-later.html> GPL-3.0-or-later
 * @link https://kalkun-sms.github.io/
 */

/**
 *
 * translate the label for given context with parameters
 * and escapes the HTML entities with htmlentities
 * for output in HTML.
 *
 * @param type $label
 * @param type $context
 * @param type $params
 * @return type
 */
function tr($label, $context = NULL, ...$params)
{
	return htmlentities(call_user_func_array(array(get_instance()->lang, 'line'), func_get_args()), ENT_QUOTES);
}

/**
 *
 * translate the label for given context with parameters
 * This doesn't apply any conversion for use in HTML or JS
 *
 * @param type $label
 * @param type $context
 * @param type $params
 * @return type
 */
function tr_raw($label, $context = NULL, ...$params)
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

/**
 *
 * translate the label for given context with parameters
 * and encode to JSON so that it is properly escaped in JS
 *
 * @param type $label
 * @param type $context
 * @param type $params
 * @return type
 */
function tr_js($label, $context = NULL, ...$params)
{
	$label = call_user_func_array(array(get_instance()->lang, 'line'), func_get_args());
	return json_protect($label);
}
