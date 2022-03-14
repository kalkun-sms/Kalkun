<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Kalkun
 * An open source web based SMS Manager
 *
 * @copyright 2021 Kalkun Dev Team
 * @author Kalkun Dev Team
 * @license <https://spdx.org/licenses/GPL-3.0-or-later.html> GPL-3.0-or-later
 * @link https://kalkun-sms.github.io/
 */

class Plugin_helper {

	public static function autoloader()
	{
		spl_autoload_register(function ($class_name) {
			if (strpos($class_name, 'Kalkun\\Plugins') === 0)
			{
				$class = array_slice(explode('\\', $class_name), -1)[0];

				// Remove 'Kalkun\\' at the beginning
				$path = '';
				$pos = strpos($class_name, 'Kalkun\\');
				if ($pos === 0)
				{
					$path = substr_replace($class_name, '', $pos, strlen('Kalkun\\'));
				}

				$path = str_replace('\\', DIRECTORY_SEPARATOR, $path);

				// Convert CamelCase to underscore lowercase MyClass -> my_class
				$path = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $path));

				$dirs = explode(DIRECTORY_SEPARATOR, $path);
				$plugin_dir_path = implode(DIRECTORY_SEPARATOR, array_slice($dirs, 0, 2));

				$fullpath = APPPATH.$plugin_dir_path.'/libraries/'.$class.'.php';
				if (file_exists($fullpath))
				{
					require_once $fullpath;
				}
			}
		});
	}

	public static function get_plugin_config($plugin_name)
	{
		$CI = &get_instance();
		$CI->load->add_package_path(APPPATH.'plugins/'.$plugin_name, FALSE);
		$CI->load->config($plugin_name, TRUE);
		$CI->load->remove_package_path(APPPATH.'plugins/'.$plugin_name, FALSE);

		return $CI->config->config[$plugin_name];
	}

	public static function load_lang($plugin_name, $idiom = NULL)
	{
		$CI = &get_instance();
		$CI->load->add_package_path(APPPATH.'plugins/'.$plugin_name, FALSE);
		$CI->lang->load($plugin_name, $idiom);
		$CI->load->remove_package_path(APPPATH.'plugins/'.$plugin_name, FALSE);

		return $CI->config->config[$plugin_name];
	}
}
