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

require_once (APPPATH.'plugins/Plugin_helper.php');

class Sms_to_email_plugin extends CI3_plugin_system {

	use plugin_trait;

	public function __construct()
	{
		parent::__construct();
		// Add hook for incoming message
		add_filter('message.incoming.after', array($this, 'sms_to_email'), 10);
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
	$config = Plugin_helper::get_plugin_config('sms_to_email');
	$message = $sms->TextDecoded;
	$from = $sms->SenderNumber;
	$msg_user = $sms->msg_user;
	$CI = &get_instance();
	$CI->load->library('email');
	$CI->load->model('Phonebook_model');
	$CI->load->model('sms_to_email/sms_to_email_model', 'sms_to_email_model');

	if ( ! is_array($sms->msg_user))
	{
		unset($msg_user);
		$msg_user[] = $sms->msg_user;
	}

	foreach ($msg_user as $uid)
	{
		$active = $CI->sms_to_email_model->get_setting($uid);
		if ($active->num_rows() === 0 OR $active->row('email_forward') !== 'true')
		{
			continue;
		}
		$CI->email->initialize($config);
		$mail_to = $active->row('email_id');
		$qry = $CI->Phonebook_model->get_phonebook(array('option' => 'bynumber', 'number' => $from, 'id_user' => $uid));
		if ($qry->num_rows() !== 0)
		{
			$from = $qry->row('Name');
		}
		$CI->email->from($config['mail_from'], $from);
		$CI->email->to($mail_to);
		$CI->email->subject($config['mail_subject']);
		$CI->email->message($message."\n\n". '- '.$from);
		$CI->email->send();
	}
	}
}
