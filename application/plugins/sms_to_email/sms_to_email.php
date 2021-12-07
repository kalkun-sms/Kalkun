<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Plugin Name: SMS to Email
* Plugin URI: http://github.com/kingster
* Version: 0.1
* Description: Forward incoming SMS to an Email address
* Author: Kinshuk Bairagi
* Author URI: http://github.com/kingster
*/

function sms_to_email_initialize()
{
	$CI = &get_instance();

	$CI->load->add_package_path(APPPATH.'plugins/sms_to_email', FALSE);
	$CI->load->config('sms_to_email', TRUE);

	return $CI->config->config['sms_to_email'];
}

// Add hook for incoming message
add_action('message.incoming.after', 'sms_to_email', 10);

/**
* Function called when plugin first activated
* Utility function must be prefixed with the plugin name
* followed by an underscore.
*
* Format: pluginname_activate
*
*/
function sms_to_email_activate()
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
function sms_to_email_deactivate()
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
function sms_to_email_install()
{
	$CI = &get_instance();
	$CI->load->helper('kalkun');
	// check if table already exist
	if ( ! $CI->db->table_exists('plugin_sms_to_email'))
	{
		$db_driver = $CI->db->platform();
		$db_prop = get_database_property($db_driver);
		execute_sql(APPPATH.'plugins/sms_to_email/media/'.$db_prop['file'].'_sms_to_email.sql');
	}
	return TRUE;
}


function sms_to_email($sms)
{
	$config = sms_to_email_initialize();
	$message = $sms->TextDecoded;
	$from = $sms->SenderNumber;
	$msg_user = $sms->msg_user;
	$CI = &get_instance();
	$CI->load->library('email');
	$CI->load->model('Phonebook_model');
	$CI->load->model('sms_to_email/sms_to_email_model', 'plugin_model');

	if( ! is_array($sms->msg_user))
	{
		unset($msg_user);
		$msg_user[] = $sms->msg_user;
	}

	foreach($msg_user as $uid)
	{
		$active = $CI->plugin_model->get_setting($uid);
		if($active->num_rows() === 0 OR $active->row('email_forward') !== 'true') continue;
		$CI->email->initialize($config);
		$mail_to = $active->row('email_id');
		$qry = $CI->Phonebook_model->get_phonebook(array('option' => 'bynumber', 'number' => $from, 'id_user' => $uid));
		if($qry->num_rows() !== 0) $from = $qry->row('Name');
		$CI->email->from($config['mail_from'], $from);
		$CI->email->to($mail_to);
		$CI->email->subject($config['mail_subject']);
		$CI->email->message($message."\n\n". '- '.$from);
		$CI->email->send();
	}
}
