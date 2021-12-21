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

function sms_to_xmpp_initialize()
{
	$CI = &get_instance();

	$CI->load->add_package_path(APPPATH.'plugins/sms_to_xmpp', FALSE);
	$CI->load->config('sms_to_xmpp', TRUE);

	return $CI->config->config['sms_to_xmpp'];
}

// Add hook for incoming message
add_action('message.incoming.before', 'sms_to_xmpp', 17);

/**
* Function called when plugin first activated
* Utility function must be prefixed with the plugin name
* followed by an underscore.
*
* Format: pluginname_activate
*
*/
function sms_to_xmpp_activate()
{
	return TRUE;
}

/**
* Function called when plugin deactivated
* Utility function must be prefixed with the plugin name
* followed by an underscore.
*
* Format: pluginname_deactivate
*
*/
function sms_to_xmpp_deactivate()
{
	return TRUE;
}

/**
* Function called when plugin first installed into the database
* Utility function must be prefixed with the plugin name
* followed by an underscore.
*
* Format: pluginname_install
*
*/
function sms_to_xmpp_install()
{
	$CI = &get_instance();
	$CI->load->helper('kalkun');
	// check if table already exist
	if ( ! $CI->db->table_exists('plugin_sms_to_xmpp'))
	{
		$db_driver = $CI->db->platform();
		$db_prop = get_database_property($db_driver);
		execute_sql(APPPATH.'plugins/sms_to_xmpp/media/'.$db_prop['file'].'_sms_to_xmpp.sql');
	}
	return TRUE;
}

function sms_to_xmpp($sms)
{
	$config = sms_to_xmpp_initialize();
	$message = $sms->TextDecoded;
	$number = $sms->SenderNumber;

	list($code, $to) = explode(' ', $message);
	$xmpp_code = $config['xmpp_code'];
	$xmpp_message = trim(str_replace($config['xmpp_code'].' '.$to, '', $message));
	if (strtoupper($code) === strtoupper($xmpp_code))
	{
		$CI = &get_instance();
		$CI->load->model('sms_to_xmpp/sms_to_xmpp_model', 'plugin_model');

		// if xmpp account exist
		$xmpp = $CI->plugin_model->get_xmpp_account_by_phone($number);
		if (is_array($xmpp))
		{
			$CI->load->library('encryption');
			$xampp_pass = $CI->encryption->decrypt($xmpp['xmpp_password']);
			if ($xampp_pass === FALSE)
			{
				log_message('error', 'sms_to_xmpp: problem during decryption.');
				show_error('sms_to_xmpp: problem during decryption.', 500, '500 Internal Server Error');
			}
			exec($config['php_path'].' '.$config['php_script'].' '.$xmpp['xmpp_username'].' '
				.$xampp_pass.' '.$xmpp['xmpp_host'].' '.$xmpp['xmpp_server'].' '.$to.' '.$xmpp_message);
		}
	}
}
