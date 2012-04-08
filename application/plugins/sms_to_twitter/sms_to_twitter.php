<?php
/**
* Plugin Name: SMS to Twitter
* Plugin URI: http://azhari.harahap.us
* Version: 0.1
* Description: Sending Tweet using SMS
* Author: Azhari Harahap
* Author URI: http://azhari.harahap.us
*/

/*
|--------------------------------------------------------------------------
| CONFIGURATION
|--------------------------------------------------------------------------
|
| twitter_code - Twitter code (Don't use space)
|
*/
function sms_to_twitter_initialize()
{
	$config['twitter_code'] = 'TW';
	return $config;
}

// Add hook for incoming message
add_action("message.incoming.before", "sms_to_twitter", 15);

/**
* Function called when plugin first activated
* Utility function must be prefixed with the plugin name
* followed by an underscore.
* 
* Format: pluginname_activate
* 
*/
function sms_to_twitter_activate()
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
function sms_to_twitter_deactivate()
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
function sms_to_twitter_install()
{
	$CI =& get_instance();
	$CI->load->helper('kalkun');
	// check if table already exist
	if (!$CI->db->table_exists('plugin_sms_to_twitter'))
	{
		$db_driver = $CI->db->platform();
		$db_prop = get_database_property($db_driver);
		execute_sql(APPPATH."plugins/sms_to_twitter/media/".$db_prop['file']."_sms_to_twitter.sql");
	}	
    return true;
}

function sms_to_twitter($sms)
{
	$config = sms_to_twitter_initialize();
	$message = $sms->TextDecoded;
	$number = $sms->SenderNumber;
	
	list($code) = explode(" ", $message);
	$twitter_code = $config['twitter_code'];
	$twitter_msg = trim(str_replace($config['twitter_code'], '', $message));
	if (strtoupper($code)==strtoupper($twitter_code))
	{
		$CI =& get_instance();
		$CI->load->model('sms_to_twitter/sms_to_twitter_model', 'plugin_model');
		$CI->load->library('sms_to_twitter/twitter', 'twitter');
		
		// if token exist
		$tokens = $CI->plugin_model->get_token_by_phone($number);
		if (is_array($tokens))
		{
			// Kalkun Twitter keys (DO NOT CHANGE)
			$consumer_key = '23TbUWvaVRenQcNv6MA';
			$consumer_key_secret = 'eBYvkk4dpgx6CS1uTWlrWxKZTY791CJ2cEE24JV4MqQ';
			$CI->twitter->oauth($consumer_key, $consumer_key_secret, $tokens['access_token'], $tokens['access_token_secret']);
			$CI->twitter->call('statuses/update', array('status' => $twitter_msg));
		}
	}
}

/* End of file sms_to_twitter.php */
/* Location: ./application/plugins/sms_to_twitter/sms_to_twitter.php */