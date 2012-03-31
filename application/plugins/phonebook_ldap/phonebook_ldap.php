<?php
/**
* Plugin Name: Phonebook LDAP
* Plugin URI: http://azhari.harahap.us
* Version: 0.1
* Description: Get phonebook contact from LDAP server 
* Author: Azhari Harahap
* Author URI: http://azhari.harahap.us
*/

/*
|--------------------------------------------------------------------------
| CONFIGURATION
|--------------------------------------------------------------------------
| 
| server - ldap server hostname
| port - ldap server port (default is 389)
| username - ldap username
| password - ldap password
| dn - Distinguished Name
| 
*/
function phonebook_ldap_initialize()
{
	$config['server'] = 'server.hostname.com';
	$config['port'] = '389';
	$config['username'] = 'user@server.hostname.com';
	$config['password'] = 'password';
	$config['dn'] = 'dc=server,dc=hostname,dc=com';
	return $config;
}

// Add hook for contact menu
add_action("phonebook.contact.get", "phonebook_ldap", 10);

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
function phonebook_ldap_deactivate()
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
function phonebook_ldap_install()
{
    return true;
}

/**
* Some of code is based on 
* http://www.newitperson.com/2010/11/simple-phonebook-list-ldap-codeigniter-datatables/
* with modification
* 
*/
function phonebook_ldap($number)
{
	$config = phonebook_ldap_initialize();
	
	// specify the LDAP server to connect to
	$conn = ldap_connect($config['server'], $config['port']);
	if (!$conn) return FALSE;
	
	//Set some variables
	ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
	ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);
	
	// bind to the LDAP server specified above
	$bd = ldap_bind($conn, $config['username'], $config['password']);
	if(!$bd) return FALSE;
	$justthese = array("ou", "sn", "givenname", "telephonenumber");
	$result = ldap_search($conn, $config['dn'], "(&(objectClass=user)(objectCategory=person))", $justthese);
	
	//Create result set
	$entries = ldap_get_entries($conn, $result);
	$z=0;
	for ($i=0; $i < $entries["count"]; $i++)
	{
		// phone number or name not found, continue iteration
		if (!array_key_exists("telephonenumber", $entries[$i]) OR !array_key_exists("givenname", $entries[$i])) 
		{
			continue;
		}
		$users[$z]['name'] = $entries[$i]["givenname"][0];
		$users[$z]['id'] = $entries[$i]["telephonenumber"][0];
		if (array_key_exists("sn", $entries[$i]))
		{
			$users[$z]['name'] .= $entries[$i]["sn"][0];
		}
		$z++;
	}
	ldap_close($conn);
	return $users;
}


/* End of file phonebook_ldap.php */
/* Location: ./application/plugins/phonebook_ldap/phonebook_ldap.php */