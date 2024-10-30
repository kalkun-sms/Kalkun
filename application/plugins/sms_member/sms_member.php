<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Plugin Name: SMS Member
* Plugin URI: http://azhari.harahap.us
* Version: 0.1
* Description: SMS Member a.k.a SMS Content
* Author: Azhari Harahap
* Author URI: http://azhari.harahap.us
*/

require_once (APPPATH.'plugins/Plugin_helper.php');

class Sms_member_plugin extends CI3_plugin_system {

	use plugin_trait;

	public function __construct()
	{
		parent::__construct();
		// Add hook for incoming message
		add_filter('message.incoming.before', array($this, 'sms_member'), 13);
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
	if ( ! $CI->db->table_exists('plugin_sms_member'))
	{
		$db_driver = $CI->db->platform();
		$db_prop = get_database_property($db_driver);
		execute_sql(APPPATH.'plugins/sms_member/media/'.$db_prop['file'].'_sms_member.sql');
	}
	return TRUE;
	}

	function sms_member($sms)
	{
	$config = Plugin_helper::get_plugin_config('sms_member');
	$message = $sms->TextDecoded;
	$number = $sms->SenderNumber;

	list($code) = explode(' ', $message);
	$reg_code = $config['reg_code'];
	$unreg_code = $config['unreg_code'];
	if (strtoupper($code) === strtoupper($reg_code))
	{
		register_member($number);
	}
	else
	{
		if (strtoupper($code) === strtoupper($unreg_code))
		{
			unregister_member($number);
		}
	}
	}

	// --------------------------------------------------------------------

	/**
	 * Register member
	 *
	 * Register member's phone number
	 */
	function register_member($number)
	{
	$CI = &get_instance();
	$CI->load->model('sms_member/sms_member_model', 'sms_member_model');

	//check if number not registered
	if ($CI->sms_member_model->check_member($number) === 0)
	{
		$CI->sms_member_model->add_member($number);
	}
	}

	// --------------------------------------------------------------------

	/**
	 * Unregister member
	 *
	 * Unregister member's phone number
	 */
	function unregister_member($number)
	{
	$CI = &get_instance();
	$CI->load->model('sms_member/sms_member_model', 'sms_member_model');

	//check if already registered
	if ($CI->sms_member_model->check_member($number) === 1)
	{
		$CI->sms_member_model->remove_member($number);
	}
	}
}