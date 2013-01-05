<?php
/**
* Plugin Name: SMS Credit
* Plugin URI: http://azhari.harahap.us
* Version: 0.1
* Description: SMS credit system that allow you to limit user for sending SMS
* Author: Azhari Harahap
* Author URI: http://azhari.harahap.us
*/

/*
|--------------------------------------------------------------------------
| CONFIGURATION
|--------------------------------------------------------------------------
|
*/
function sms_credit_initialize()
{
    $config = array();
    return $config;
}

// Add hook for outgoing message
add_action("message.outgoing", "sms_credit", 11);

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
    $config = sms_credit_initialize();
    // todo
}

/* End of file sms_credit.php */
/* Location: ./application/plugins/sms_credit/sms_credit.php */
