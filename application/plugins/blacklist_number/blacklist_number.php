<?php
/**
* Plugin Name: Blacklist Number
* Plugin URI: http://azhari.harahap.us
* Version: 0.1
* Description: Autoremove incoming SMS from Blacklist number
* Author: Azhari Harahap
* Author URI: http://azhari.harahap.us
*/

// Add hook for incoming message
add_action("message.incoming.before", "blacklist_number", 10);

/**
* Function called when plugin first activated
* Utility function must be prefixed with the plugin name
* followed by an underscore.
* 
* Format: pluginname_activate
* 
*/
function blacklist_number_activate()
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
function blacklist_number_deactivate()
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
function blacklist_number_install()
{
	$CI =& get_instance();
	
	// check if table already exist
	if (!$CI->db->table_exists('plugin_blacklist_number'))
	{
		$db_driver = $CI->db->platform();
		$db_prop = get_database_property($db_driver);
		execute_sql(APPPATH."plugins/blacklist_number/media/".$db_prop['file']."_blacklist_number.sql");
	}
    return true;
}

function blacklist_number($sms)
{
    $CI =& get_instance();
    
    // Get blacklist number
    $lists = $CI->db->select('phone_number')->get('plugin_blacklist_number')->result_array();
    foreach($lists as $tmp)
    {
    	$evil[] = $tmp['phone_number'];
    }
    
    // Delete message if it's on blacklist number
    if(in_array($sms->SenderNumber, $evil))
    {
    	$CI->db->where('ID',$sms->ID)->delete('inbox');
    	return 'break';
    }
}

/* End of file blacklist_number.php */
/* Location: ./application/plugins/blacklist_number/blacklist_number.php */