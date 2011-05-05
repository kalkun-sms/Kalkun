<?php
/**
* Plugin Name: Welcome
* Plugin URI: http://azhari.harahap.us
* Version: 0.2
* Description: Example plugin
* Author: Unknown
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
function welcome_activate()
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
function welcome_deactivate()
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
function welcome_install()
{
    return true;
}