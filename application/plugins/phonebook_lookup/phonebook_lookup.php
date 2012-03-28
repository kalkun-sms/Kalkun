<?php
/**
* Plugin Name: Phonebook Lookup
* Plugin URI: http://azhari.harahap.us
* Version: 0.1
* Description: Lookup phone number from specified URL 
* Author: Azhari Harahap
* Author URI: http://azhari.harahap.us
*/

/*
|--------------------------------------------------------------------------
| CONFIGURATION
|--------------------------------------------------------------------------
| 
| url - url location to lookup the phone number, the phone number variable should be #phonenumber#
| 
*/
function phonebook_lookup_initialize()
{
	$config['url'] = 'http://www.krak.dk/person/resultat/#phonenumber#';
	return $config;
}

// Add hook for contact menu
add_action("phonebook.contact.menu", "phonebook_lookup", 10);

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
function phonebook_lookup_deactivate()
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
function phonebook_lookup_install()
{
    return true;
}

function phonebook_lookup($number)
{
	$config = phonebook_lookup_initialize();
	$lookup['url'] = str_replace('#phonenumber#', $number->Number, $config['url']);
	$lookup['title'] = 'Lookup Number';
	return $lookup;
}


/* End of file phonebook_lookup.php */
/* Location: ./application/plugins/phonebook_lookup/phonebook_lookup.php */