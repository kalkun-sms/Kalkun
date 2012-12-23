<?php
/**
* Plugin Name: SOAP
* Plugin URI: http://oskarholowaty.com/
* Version: 0.0.8
* Description: SOAP Server Plugin
* Author: Oskar Holowaty
* Author URI: http://oskarholowaty.com/
*/


/**
* Function called when plugin first activated
* Utility function must be prefixed with the plugin name
* followed by an underscore.
* 
* Format: pluginname_activate
* 
*/
function soap_activate()
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
function soap_deactivate()
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
function soap_install()
{
    $CI =& get_instance();
    $CI->load->helper('kalkun');
    // check if table already exist
    if (!$CI->db->table_exists('plugin_remote_access'))
    {
        $db_driver = $CI->db->platform();
        $db_prop = get_database_property($db_driver);
        execute_sql(APPPATH."plugins/soap/media/".$db_prop['file']."_remote_access.sql");
    }
    return true;
}
