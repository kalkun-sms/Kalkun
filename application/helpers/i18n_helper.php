<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Kalkun
 * An open source web based SMS Manager
 *
 * @copyright 2021 Fab Stz
 * @author Fab Stz <fabstz-it@yahoo.fr>
 * @license MIT
 * @link https://github.com/kalkun-sms/Kalkun/
 */

function tr($label, $context = NULL, ...$params)
{
	return call_user_func_array(array(get_instance()->lang, 'line'), func_get_args());
}
