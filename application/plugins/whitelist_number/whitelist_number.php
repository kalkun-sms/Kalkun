<?php
/**
* Plugin Name: Whitelist Number
* Plugin URI: ?
* Version: 1.0
* Description: Autoremove outgoing SMS not in whitelist
* Author: Максим Морэ
* Author URI: https://bitbucket.org/maxsamael
*/

class Whitelist_number_plugin extends CI3_plugin_system {

	use plugin_trait;

	public function __construct()
	{
		parent::__construct();
		// Add hook for outgoing message
		add_filter('message.outgoing', array($this, 'whitelist_number_outgoing'), 1);
	}

	// ------------------------------------------------------------------------

	/**
	 * Install Plugin
	 *
	 * Anything that needs to happen when this plugin gets installed
	 *
	 * @access public
	 * @since   0.1.0
	 * @return bool    TRUE by default
	 */
	public static function install($data = NULL)
	{
		$CI = &get_instance();
		$CI->load->helper('kalkun');
		// check if table already exist
		if ( ! $CI->db->table_exists('plugin_whitelist_number'))
		{
			$db_driver = $CI->db->platform();
			$db_prop = get_database_property($db_driver);
			execute_sql(APPPATH . 'plugins/whitelist_number/media/' . $db_prop['file'] . '_whitelist_number.sql');
		}
		return TRUE;
	}

	function whitelist_number_outgoing($numbers = array())
	{
		$CI = &get_instance();
		$CI->load->model('whitelist_number/whitelist_number_model', 'whitelist_number_model');
		$heaven = array();

		// Get whitelist number
		$lists = $CI->whitelist_number_model->get('all')->result_array();
		foreach ($lists as $tmp)
		{
			$heaven[] = $tmp['match'];
		}

		// Delete number if it's on whitelist number
		foreach ($numbers as $key => $number)
		{
			foreach ($heaven as $match)
			{
				if (preg_match($match, $number))
				{
					continue;
				}
				else
				{
					unset($numbers[$key]);
				}
			}
		}
		return $numbers;
	}
}
