<?php
/**
* Plugin Name: SMS Member
* Plugin URI: http://azhari.harahap.us
* Version: 0.1
* Description: SMS Member a.k.a SMS Content
* Author: Azhari Harahap
* Author URI: http://azhari.harahap.us
*/

/*
|--------------------------------------------------------------------------
| CONFIGURATION
|--------------------------------------------------------------------------
|
| reg_code - Registration code (Don't use space)
| unreg_code - Unregistration code (Don't use space)
|
*/
$config['reg_code'] = 'REG';
$config['unreg_code'] = 'UNREG';

// Add hook for incoming message
add_action("message.incoming.before", "sms_member", 13);

/**
* Function called when plugin first activated
* Utility function must be prefixed with the plugin name
* followed by an underscore.
* 
* Format: pluginname_activate
* 
*/
function sms_member_activate()
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
function sms_member_deactivate()
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
function sms_member_install()
{
    return true;
}

function sms_member($sms)
{
	global $config;
	$message = $sms->TextDecoded;
	$number = $sms->SenderNumber;
	
	list($code) = explode(" ", $message);
	$reg_code = $config['reg_code'];
	$unreg_code = $config['unreg_code'];
	if (strtoupper($code)==strtoupper($reg_code))
	{ 
		register_member($number);
	}
	else if (strtoupper($code)==strtoupper($unreg_code))
	{
		unregister_member($number);
	}
}

// --------------------------------------------------------------------

/**
 * Register member
 *
 * Register member's phone number
 *
 * @access	private   		 
 */
function register_member($number)
{
	$this->load->model('Member_model');
	
	//check if number not registered
	if($this->Member_model->check_member($number)==0)
	$this->Member_model->add_member($number);
}

// --------------------------------------------------------------------

/**
 * Unregister member
 *
 * Unregister member's phone number
 *
 * @access	private   		 
 */	
function unregister_member($number)
{
	$this->load->model('Member_model');
	
	//check if already registered
	if($this->Member_model->check_member($number)==1)
	$this->Member_model->remove_member($number);
}	