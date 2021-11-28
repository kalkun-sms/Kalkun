<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// phpcs:disable CodeIgniter.Commenting.InlineComment.WrongStyle
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
// phpcs:enable
$config['server'] = 'server.hostname.com';
$config['port'] = '389';
$config['username'] = 'user@server.hostname.com';
$config['password'] = 'password';
$config['dn'] = 'dc=server,dc=hostname,dc=com';


