<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Plugin Name: Phonebook Lookup
* Plugin URI: http://azhari.harahap.us
* Version: 0.1
* Description: Lookup phone number from specified URL
* Author: Azhari Harahap
* Author URI: http://azhari.harahap.us
*/

require_once (APPPATH.'plugins/Plugin_helper.php');

// Add hook for contact menu
add_action('phonebook.contact.menu', 'phonebook_lookup', 10);

/**
* Function called when plugin first activated
* Utility function must be prefixed with the plugin name
* followed by an underscore.
*
* Format: pluginname_activate
*
*/
function phonebook_lookup_activate()
{
	return TRUE;
}

/**
* Function called when plugin deactivated
* Utility function must be prefixed with the plugin name
* followed by an underscore.
*
* Format: pluginname_deactivate
*
*/
function phonebook_lookup_deactivate()
{
	return TRUE;
}

/**
* Function called when plugin first installed into the database
* Utility function must be prefixed with the plugin name
* followed by an underscore.
*
* Format: pluginname_install
*
*/
function phonebook_lookup_install()
{
	return TRUE;
}

function phonebook_lookup($number)
{
	$config = Plugin_helper::get_plugin_config('phonebook_lookup');
	Plugin_helper::load_lang('phonebook_lookup');
	$lookup['url'] = str_replace('#phonenumber#', $number->Number, $config['url']);
	$lookup['title'] = tr('Lookup Number');
	return $lookup;
}
