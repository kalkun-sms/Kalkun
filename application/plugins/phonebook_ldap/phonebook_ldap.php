<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Plugin Name: Phonebook LDAP
* Plugin URI: http://azhari.harahap.us
* Version: 0.1
* Description: Get phonebook contact from LDAP server
* Author: Azhari Harahap
* Author URI: http://azhari.harahap.us
*/

require_once (APPPATH.'plugins/Plugin_helper.php');

// Add hook for contact menu
add_action('phonebook.contact.get', 'phonebook_ldap', 10);

/**
* Function called when plugin first activated
* Utility function must be prefixed with the plugin name
* followed by an underscore.
*
* Format: pluginname_activate
*
*/
function phonebook_ldap_activate()
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
function phonebook_ldap_deactivate()
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
function phonebook_ldap_install()
{
	return TRUE;
}

/**
* Some of code is based on
* http://www.newitperson.com/2010/11/simple-phonebook-list-ldap-codeigniter-datatables/
* with modification
*
*/
function phonebook_ldap($number)
{
	if ( ! extension_loaded('ldap'))
	{
		show_error('phonebook_ldap: PHP extension "ldap" is missing. Install it if you want to use phonebook_ldap plugin.', 500, '500 Internal Server Error');
	}

	$config = Plugin_helper::get_plugin_config('phonebook_ldap');

	// specify the LDAP server to connect to
	$conn = ldap_connect($config['server'], $config['port']);
	if ( ! $conn)
	{
		return FALSE;
	}

	//Set some variables
	ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
	ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);
	// Set timeout to 1sec
	ldap_set_option($conn, LDAP_OPT_NETWORK_TIMEOUT, 1);
	//ldap_set_option($conn, LDAP_OPT_TIMELIMIT, 1);

	// bind to the LDAP server specified above

	try
	{
		$bd = ldap_bind($conn, $config['username'], $config['password']);
	}
	catch (ErrorException $e)
	{
		if ($e->getMessage() === "ldap_bind(): Unable to bind to server: Can't contact LDAP server")
		{
			return FALSE;
		}
	}

	if ( ! $bd)
	{
		return FALSE;
	}
	$justthese = array('ou', 'sn', 'givenname', 'telephonenumber');
	$result = ldap_search($conn, $config['dn'], '(&(objectClass=user)(objectCategory=person))', $justthese);

	//Create result set
	$entries = ldap_get_entries($conn, $result);
	$z = 0;
	for ($i = 0; $i < $entries['count']; $i++)
	{
		// phone number or name not found, continue iteration
		if ( ! array_key_exists('telephonenumber', $entries[$i]) OR ! array_key_exists('givenname', $entries[$i]))
		{
			continue;
		}
		$users[$z]['name'] = $entries[$i]['givenname'][0];
		$users[$z]['id'] = $entries[$i]['telephonenumber'][0];
		if (array_key_exists('sn', $entries[$i]))
		{
			$users[$z]['name'] .= $entries[$i]['sn'][0];
		}
		$z++;
	}
	ldap_close($conn);
	return $users;
}
