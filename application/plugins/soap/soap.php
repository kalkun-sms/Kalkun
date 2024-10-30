<?php
/**
* Plugin Name: SOAP
* Plugin URI: http://azhari.harahap.us
* Version: 0.0.8
* Description: SOAP Server Plugin
* Author: Azhari Harahap
* Author URI: http://azhari.harahap.us
*/

class Soap_plugin extends CI3_plugin_system {

	use plugin_trait;

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
	if ( ! $CI->db->table_exists('plugin_remote_access'))
	{
		$db_driver = $CI->db->platform();
		$db_prop = get_database_property($db_driver);
		execute_sql(APPPATH.'plugins/soap/media/'.$db_prop['file'].'_remote_access.sql');
	}
	return TRUE;
    }
}
