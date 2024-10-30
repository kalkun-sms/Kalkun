<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Plugin Name: SMS Credit
* Plugin URI: http://azhari.harahap.us
* Version: 0.1
* Description: SMS credit system that allow you to limit user for sending SMS
* Author: Azhari Harahap
* Author URI: http://azhari.harahap.us
*/

require_once (APPPATH . 'plugins/Plugin_helper.php');

class Sms_credit_plugin extends CI3_plugin_system {

	use plugin_trait;

	public function __construct()
	{
		parent::__construct();
		// Add hook for outgoing message
		add_filter('message.outgoing_all', array($this, 'sms_credit'), 10);
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
		if ( ! $CI->db->table_exists('plugin_sms_credit'))
		{
			$db_driver = $CI->db->platform();
			$db_prop = get_database_property($db_driver);
			execute_sql(APPPATH . 'plugins/sms_credit/media/' . $db_prop['file'] . '_sms_credit.sql');
		}
		return TRUE;
	}

	function sms_credit($sms)
	{
		$CI = &get_instance();
		$CI->load->model('Kalkun_model');
		$CI->load->model('sms_credit/sms_credit_model', 'sms_credit_model');

		$config = Plugin_helper::get_plugin_config('sms_credit');
		$uid = $sms['uid'];

		// check user credit
		$user_package = $CI->sms_credit_model->get_users(array('id' => $uid, 'valid_start' => date('Y-m-d H:i:s'), 'valid_end' => date('Y-m-d H:i:s')))->row_array();

		if (isset($user_package['sms_numbers']))
		{
			$has_package = TRUE;
			$sms_used = $CI->Kalkun_model->get_sms_used('date', array('user_id' => $uid,
				'sms_date_start' => $user_package['valid_start'], 'sms_date_end' => $user_package['valid_end']));
		}
		else
		{
			$has_package = FALSE;
		}

		if (($has_package && $sms_used >= $user_package['sms_numbers']) OR ( ! $has_package && ! $config['allow_user_with_no_package']))
		{
			echo 'Sorry, your sms credit limit exceeded.';
			exit;
		}
	}
}
