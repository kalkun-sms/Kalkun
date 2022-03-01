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

// Add hook for incoming message
add_action('message.incoming.before', 'simple_autoreply', 11);

/**
* Function called when plugin first activated
* Utility function must be prefixed with the plugin name
* followed by an underscore.
*
* Format: pluginname_activate
*
*/
function simple_autoreply_activate()
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
function simple_autoreply_deactivate()
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
function simple_autoreply_install()
{
	return TRUE;
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
