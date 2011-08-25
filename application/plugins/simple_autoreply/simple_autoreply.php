<?php
/**
* Plugin Name: Simple Autoreply
* Plugin URI: http://azhari.harahap.us
* Version: 0.1
* Description: Simple a.k.a stupid autoreply, reply to all incoming message
* Author: Azhari Harahap
* Author URI: http://azhari.harahap.us
*/

/*
|--------------------------------------------------------------------------
| CONFIGURATION
|--------------------------------------------------------------------------
| 
| uid - identifier of which user sent the autoreply, uid 1 is the default value
| message - the message you want to sent
| 
*/
function simple_autoreply_initialize()
{
	$config['uid'] = '1';
	$config['message'] = "Thanks for sending me the message";
	return $config;
}

// Add hook for incoming message
add_action("message.incoming.before", "simple_autoreply", 11);

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
function simple_autoreply_deactivate()
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
function simple_autoreply_install()
{
    return true;
}

function simple_autoreply($sms)
{
	$config = simple_autoreply_initialize();
    $CI =& get_instance();
    $CI->load->model('Message_model');
	$data['coding'] = 'default';
	$data['class'] = '1';
	$data['dest'] = $sms->SenderNumber;
	$data['date'] = date('Y-m-d H:i:s');
	$data['message'] = $config['message'];
	$data['delivery_report'] = 'default';
	$data['uid'] = $config['uid'];	
	$CI->Message_model->send_messages($data);
}

/* End of file simple_autoreply.php */
/* Location: ./application/plugins/simple_autoreply/simple_autoreply.php */