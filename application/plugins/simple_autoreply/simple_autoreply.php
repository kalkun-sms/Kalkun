<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Plugin Name: Simple Autoreply
* Plugin URI: http://azhari.harahap.us
* Version: 0.1
* Description: Simple a.k.a stupid autoreply, reply to all incoming message
* Author: Azhari Harahap
* Author URI: http://azhari.harahap.us
*/

require_once (APPPATH.'plugins/Plugin_helper.php');

class Simple_autoreply_plugin extends CI3_plugin_system {

	use plugin_trait;

	public function __construct()
	{
		parent::__construct();
		// Add hook for incoming message
		add_filter('message.incoming.before', array($this, 'simple_autoreply'), 11);
	}

	function simple_autoreply($sms)
	{
	$config = Plugin_helper::get_plugin_config('simple_autoreply');
	$CI = &get_instance();
	$CI->load->model('Message_model');
	$data['class'] = '1';
	$data['dest'] = $sms->SenderNumber;
	$data['date'] = date('Y-m-d H:i:s');
	$data['message'] = $config['message'];
	$data['delivery_report'] = 'default';
	$data['uid'] = $config['uid'];
	$CI->Message_model->send_messages($data);
	}
}
