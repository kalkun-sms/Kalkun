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
function sms_member_initialize()
{
	$config['reg_code'] = 'REG';
	$config['unreg_code'] = 'UNREG';
	return $config;
}

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
	$CI =& get_instance();
	
	// check if table already exist
	if (!$CI->db->table_exists('plugin_sms_member'))
	{
		$db_driver = $CI->db->platform();
		$db_prop = get_database_property($db_driver);
		execute_sql(APPPATH."plugins/sms_member/media/".$db_prop['file']."_sms_member.sql");
	}
    return true;
}

function sms_member($sms)
{
	$config = sms_member_initialize();
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
 */
function register_member($number)
{	
	//check if number not registered
	if(models_check_member($number)==0)
	models_add_member($number);
}

// --------------------------------------------------------------------

/**
 * Unregister member
 *
 * Unregister member's phone number
 */	
function unregister_member($number)
{	
	//check if already registered
	if(models_check_member($number)==1)
	models_remove_member($number);
}

// --------------------------------------------------------------------

/**
 * Models
 *
 * Handle database activity 
 */
function models_check_member($number)
{
	$CI =& get_instance();
    $CI->load->model('Kalkun_model');
    
	$CI->db->from('plugin_sms_member');
	$CI->db->where('phone_number', $number);
	return $CI->db->count_all_results();    		
}

function models_add_member($number)
{
	$CI =& get_instance();
    $CI->load->model('Kalkun_model');
    	
	$data = array('phone_number' => $number, 'reg_date' => date ('Y-m-d H:i:s'));
	$CI->db->insert('plugin_sms_member', $data);
}

function models_remove_member()
{
	$CI =& get_instance();
    $CI->load->model('Kalkun_model');
    
	$CI->db->where('phone_number', $number);		
	$CI->db->delete('plugin_sms_member');	
}

/* End of file sms_member.php */
/* Location: ./application/plugins/sms_member/sms_member.php */