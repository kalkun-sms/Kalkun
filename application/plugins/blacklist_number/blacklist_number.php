<?php
/**
* Plugin Name: Blacklist Number
* Plugin URI: http://azhari.harahap.us
* Version: 0.1
* Description: Autoremove incoming SMS from Blacklist number
* Author: Azhari Harahap
* Author URI: http://azhari.harahap.us
*/

class Blacklist_number_plugin extends CI3_plugin_system {

	use plugin_trait;

	public function __construct()
	{
		parent::__construct();

		// Add hook for incoming message
		add_action('message.incoming.before', array($this, 'blacklist_number_incoming'), 10);

		// Add hook for outgoing message
		add_action('message.outgoing', array($this, 'blacklist_number_outgoing'), 10);
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
		if ( ! $CI->db->table_exists('plugin_blacklist_number'))
		{
			$db_driver = $CI->db->platform();
			$db_prop = get_database_property($db_driver);
			execute_sql(APPPATH . 'plugins/blacklist_number/media/' . $db_prop['file'] . '_blacklist_number.sql');
		}
		return TRUE;
	}

	function blacklist_number_incoming($sms)
	{
		$CI = &get_instance();
		$CI->load->model('blacklist_number/blacklist_number_model', 'blacklist_number_model');
		$evil = array();

		// Get blacklist number
		$lists = $CI->blacklist_number_model->get('all')->result_array();
		foreach ($lists as $tmp)
		{
			$evil[] = $tmp['phone_number'];
		}

		// Delete message if it's on blacklist number
		if (in_array($sms->SenderNumber, $evil))
		{
			$CI->db->where('ID', $sms->ID)->delete('inbox');
			return 'break';
		}
	}

	function blacklist_number_outgoing($numbers = array())
	{
		$CI = &get_instance();
		$CI->load->model('blacklist_number/blacklist_number_model', 'blacklist_number_model');
		$evil = array();

		// Get blacklist number
		$lists = $CI->blacklist_number_model->get('all')->result_array();
		foreach ($lists as $tmp)
		{
			$evil[] = $tmp['phone_number'];
		}

		// Delete number if it's on blacklist number
		foreach ($numbers as $key => $number)
		{
			if (in_array($number, $evil))
			{
				unset($numbers[$key]);
			}
		}
		return $numbers;
	}
}
