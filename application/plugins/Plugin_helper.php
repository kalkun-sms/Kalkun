<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Kalkun
 * An open source web based SMS Manager
 *
 * @copyright 2021 Kalkun dev team
 * @author Kalkun dev team
 * @license <https://spdx.org/licenses/GPL-3.0-or-later.html> GPL-3.0-or-later
 * @link https://github.com/kalkun-sms/Kalkun/
 */
  
class Plugin_helper {


	public static function get_plugin_config($plugin_name)
	{
		$CI = &get_instance();
		$CI->load->add_package_path(APPPATH.'plugins/'.$plugin_name, FALSE);
		$CI->load->config($plugin_name, TRUE);
		$CI->load->remove_package_path(APPPATH.'plugins/'.$plugin_name, FALSE);

		return $CI->config->config[$plugin_name];
	}
}
