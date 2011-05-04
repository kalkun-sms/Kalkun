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
add_action("message.incoming", "blacklist_number", 10);

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
    return true;
}

function blacklist_number($text)
{
    $CI = get_instance();
    $CI->load->model('Message_model');
    
    // Delete message if it's on blacklist number
    // $CI->db->query("delete from inbox ... ");
}