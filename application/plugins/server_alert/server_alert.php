<?php
/**
* Plugin Name: Server Alert
* Plugin URI: http://azhari.harahap.us
* Version: 0.1
* Description: Send alert SMS when your server down
* Author: Azhari Harahap
* Author URI: http://azhari.harahap.us
*/


/**
* Function called when plugin first activated
* Utility function must be prefixed with the plugin name
* followed by an underscore.
* 
* Format: pluginname_activate
* 
*/
function server_alert_activate()
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
function server_alert_deactivate()
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
function server_alert_install()
{
	$CI =& get_instance();
	$CI->load->helper('kalkun');
	// check if table already exist
	if (!$CI->db->table_exists('plugin_server_alert'))
	{
		$db_driver = $CI->db->platform();
		$db_prop = get_database_property($db_driver);
		execute_sql(APPPATH."plugins/server_alert/media/".$db_prop['file']."_server_alert.sql");
	}
    return true;
}
