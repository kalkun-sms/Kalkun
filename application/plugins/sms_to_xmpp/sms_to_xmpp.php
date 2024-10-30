<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Plugin Name: SMS to XMPP
* Plugin URI: http://azhari.harahap.us
* Version: 0.1
* Description: Send XMPP chat from SMS
* Author: Azhari Harahap
* Author URI: http://azhari.harahap.us
*/

require_once (APPPATH . 'plugins/Plugin_helper.php');

class Sms_to_xmpp_plugin extends CI3_plugin_system {

	use plugin_trait;

	public function __construct()
	{
		parent::__construct();
		// Add hook for incoming message
		add_filter('message.incoming.before', array($this, 'sms_to_xmpp'), 17);
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
		if ( ! $CI->db->table_exists('plugin_sms_to_xmpp'))
		{
			$db_driver = $CI->db->platform();
			$db_prop = get_database_property($db_driver);
			execute_sql(APPPATH . 'plugins/sms_to_xmpp/media/' . $db_prop['file'] . '_sms_to_xmpp.sql');
		}
		return TRUE;
	}

	function sms_to_xmpp($sms)
	{
		$config = Plugin_helper::get_plugin_config('sms_to_xmpp');
		$message = $sms->TextDecoded;
		$number = $sms->SenderNumber;

		list($code, $to) = explode(' ', $message);
		$xmpp_code = $config['xmpp_code'];
		$xmpp_message = trim(str_replace($config['xmpp_code'] . ' ' . $to, '', $message));
		if (strtoupper($code) === strtoupper($xmpp_code))
		{
			$CI = &get_instance();
			$CI->load->model('sms_to_xmpp/sms_to_xmpp_model');

			// if xmpp account exist
			$xmpp = $CI->sms_to_xmpp_model->get_xmpp_account_by_phone($number);
			if ( ! empty($xmpp))
			{
				$CI->load->library('encryption');
				$xampp_pass = $CI->encryption->decrypt($xmpp['xmpp_password']);
				if ($xampp_pass === FALSE)
				{
					log_message('error', 'sms_to_xmpp: problem during decryption.');
					show_error('sms_to_xmpp: problem during decryption.', 500, '500 Internal Server Error');
				}
				exec($config['php_path'] . ' ' . $config['php_script'] . ' ' . $xmpp['xmpp_username'] . ' '
						. $xampp_pass . ' ' . $xmpp['xmpp_host'] . ' ' . $xmpp['xmpp_server'] . ' ' . $to . ' ' . $xmpp_message);
			}
		}
	}
}
