<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Plugin Name: SMS to Twitter
* Plugin URI: http://azhari.harahap.us
* Version: 0.1
* Description: Sending Tweet using SMS
* Author: Azhari Harahap
* Author URI: http://azhari.harahap.us
*/

require_once (APPPATH.'plugins/Plugin_helper.php');

class Sms_to_twitter_plugin extends CI3_plugin_system {

	use plugin_trait;

	public function __construct()
	{
		parent::__construct();
		// Add hook for incoming message
		add_filter('message.incoming.before', array($this, 'sms_to_twitter'), 15);
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
	if ( ! $CI->db->table_exists('plugin_sms_to_twitter'))
	{
		$db_driver = $CI->db->platform();
		$db_prop = get_database_property($db_driver);
		execute_sql(APPPATH.'plugins/sms_to_twitter/media/'.$db_prop['file'].'_sms_to_twitter.sql');
	}
	return TRUE;
    }

	function sms_to_twitter($sms)
	{
	$config = Plugin_helper::get_plugin_config('sms_to_twitter');
	$message = $sms->TextDecoded;
	$number = $sms->SenderNumber;

	list($code) = explode(' ', $message);
	$twitter_code = $config['twitter_code'];
	$twitter_msg = trim(str_replace($config['twitter_code'], '', $message));
	if (strtoupper($code) === strtoupper($twitter_code))
	{
		$CI = &get_instance();
		$CI->load->model('sms_to_twitter/sms_to_twitter_model', 'sms_to_twitter_model');
		$CI->load->library('sms_to_twitter/twitter', 'twitter');

		// if token exist
		$tokens = $CI->sms_to_twitter_model->get_token_by_phone($number);
		if (is_array($tokens))
		{
			// Kalkun Twitter keys
			$consumer_key = $config['consumer_key'];
			$consumer_key_secret = $config['consumer_key_secret'];
			$CI->twitter->oauth($consumer_key, $consumer_key_secret, $tokens['access_token'], $tokens['access_token_secret']);
			$CI->twitter->call('statuses/update', array('status' => $twitter_msg));
		}
	}
	}
}