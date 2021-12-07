<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Plugin Name: SMS Credit
* Plugin URI: http://azhari.harahap.us
* Version: 0.1
* Description: SMS credit system that allow you to limit user for sending SMS
* Author: Azhari Harahap
* Author URI: http://azhari.harahap.us
*/

function sms_credit_initialize()
{
	$CI =& get_instance();

	$CI->load->add_package_path(APPPATH.'plugins/sms_credit', FALSE);
	$CI->load->config('sms_credit', TRUE);

	return $CI->config->config['sms_credit'];
}

// Add hook for outgoing message
add_action("message.outgoing_all", "sms_credit", 10);

/**
* Function called when plugin first activated
* Utility function must be prefixed with the plugin name
* followed by an underscore.
* 
* Format: pluginname_activate
* 
*/
function sms_credit_activate()
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
function sms_credit_deactivate()
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
function sms_credit_install()
{
    $CI =& get_instance();
    $CI->load->helper('kalkun');
    // check if table already exist
    if (!$CI->db->table_exists('plugin_sms_credit'))
    {
        $db_driver = $CI->db->platform();
        $db_prop = get_database_property($db_driver);
        execute_sql(APPPATH."plugins/sms_credit/media/".$db_prop['file']."_sms_credit.sql");
    }
    return true;
}

function sms_credit($sms)
{
    $CI =& get_instance();
    $CI->load->model('Kalkun_model');
    $CI->load->model('sms_credit/sms_credit_model', 'plugin_model');

    $config = sms_credit_initialize();
    $uid = $sms['uid'];

    // check user credit
    $user_package = $CI->plugin_model->get_users(array('id' => $uid, 'valid_start' => date('Y-m-d H:i:s'), 'valid_end' => date('Y-m-d H:i:s')))->row_array();

    if(isset($user_package['sms_numbers']))
    {
        $has_package = TRUE;
        $sms_used = $CI->Kalkun_model->get_sms_used('date', array('user_id' => $uid, 
                   'sms_date_start' => $user_package['valid_start'], 'sms_date_end' => $user_package['valid_end']));
    }
    else
    {
        $has_package = FALSE;
    }

    if(($has_package && $sms_used >= $user_package['sms_numbers']) OR (!$has_package && !$config['allow_user_with_no_package']))
    {
        echo "Sorry, your sms credit limit exceeded.";
        exit;
    }
}

