<?php
/**
* Plugin Name: SMS to Email
* Plugin URI: http://github.com/kingster
* Version: 0.1
* Description: Forward incoming SMS to an Email address
* Author: Kinshuk Bairagi
* Author URI: http://github.com/kingster
*/

/*
|--------------------------------------------------------------------------
| CONFIGURATION
|--------------------------------------------------------------------------
|
| protocol - The mail sending protocol (mail, sendmail, or smtp)
| smtp_host - SMTP Server Address
| smtp_port - SMTP Port
| smtp_user - SMTP Username
| smtp_pass - SMTP Password
| charset - Character set (utf-8, iso-8859-1, etc.)
| wordwrap - Enable word-wrap
| mail_from - Sender Email
| mail_subject - The mail subject
|
*/
function sms_to_email_initialize()
{
	$config['protocol'] = 'mail';
	$config['smtp_host'] = "localhost";
	$config['smtp_port'] = "25";
	$config['smtp_user'] = "username";
	$config['smtp_pass'] = "password";
	$config['charset'] = 'utf-8';
	$config['wordwrap'] = TRUE;
	$config['mail_from'] = 'postmaster@domain.com';
	$config['mail_subject'] = 'Kalkun New SMS';
	return $config;
}

// Add hook for incoming message
add_action("message.incoming.after", "sms_to_email", 10);

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
    return true;
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
    return true;
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
	$CI =& get_instance();
	$CI->load->helper('kalkun');
	// check if table already exist
	if (!$CI->db->table_exists('plugin_sms_to_email'))
	{
		$db_driver = $CI->db->platform();
		$db_prop = get_database_property($db_driver);
		execute_sql(APPPATH."plugins/sms_to_email/media/".$db_prop['file']."_sms_to_email.sql");
	}	
    return true;
}


function sms_to_email($sms)
{
	$config = sms_to_email_initialize();
	$message = $sms->TextDecoded;
	$from = $sms->SenderNumber;
	$msg_user = $sms->msg_user;
	$CI =& get_instance();    	
    $CI->load->library('email');
    $CI->load->model('Phonebook_model');
    $CI->load->model('sms_to_email/sms_to_email_model', 'plugin_model');
    
    $active  = $CI->plugin_model->get_setting($msg_user);
    if($active->num_rows()==0 OR $active->row('email_forward') != 'true') return;
    $CI->email->initialize($config);
    $mail_to = $active->row('email_id');            
    $qry = $CI->Phonebook_model->get_phonebook(array('option'=>'bynumber', 'number'=>$from , 'id_user'=>$msg_user));
    if($qry->num_rows()!=0) $from = $qry->row('Name');
    $CI->email->from($config['mail_from'], $from);
    $CI->email->to($mail_to); 
    $CI->email->subject($config['mail_subject']);
    $CI->email->message($message."\n\n". "- ".$from);
    $CI->email->send();
}

/* End of file sms_to_email.php */
/* Location: ./application/plugins/sms_to_email/sms_to_email.php */