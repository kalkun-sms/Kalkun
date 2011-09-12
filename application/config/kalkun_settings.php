<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Kalkun Metadata (DO NOT CHANGE!!!)
|--------------------------------------------------------------------------
|
*/
$config['kalkun_version'] = '0.4RC1';
$config['kalkun_codename'] = 'Toba';
$config['kalkun_release_date'] = '12 September 2011';
$config['kalkun_upgradeable'] = TRUE;
$config['kalkun_previous_version'] = '0.3';

/*
|--------------------------------------------------------------------------
| Gammu Location
|--------------------------------------------------------------------------
|
| Gammu Installation Location
| Default Locations... You will need to verify for your system
| Linux : /usr/local/bin/gammu
| Windows : C:\Program Files\Gammu 1.29.92\bin\
|
*/
$config['gammu_path'] = "C:\Gammu 1.29.92\bin\\";
$config['gammu_sms_inject'] = $config['gammu_path']."gammu-smsd-inject";
$config['gammu_config'] = $config['gammu_path']."config.ini";


/*
|--------------------------------------------------------------------------
|   Kalkun Cloud Feature Network Settings
|--------------------------------------------------------------------------
*/
$config['enable_proxy'] = false;
$config['proxy_host'] = 'proxyhost.com';
$config['proxy_port'] = '8080';
$config['proxy_username'] = '';
$config['proxy_password'] = '';


/*
|--------------------------------------------------------------------------
| Modem Tolerant
|--------------------------------------------------------------------------
|
| Modem tolerant (in minutes)
| To decide if the modem connected or not, default is 10 minutes.
|
*/
$config['modem_tolerant'] = '10';

/*
|--------------------------------------------------------------------------
| Inbox owner ID
|--------------------------------------------------------------------------
|
| All message from inbox that don't belongs to anyone will be owned by this user ID.
| Must be valid user ID
|
*/
$config['inbox_owner_id'] = '1';

/*
|--------------------------------------------------------------------------
| Disable Outgoing Message
|--------------------------------------------------------------------------
|
| Disable outgoing messages.
| To decide if the disable outgoing message. If enabled all outgoing 
| messages will be barred.
|
*/

$config['disable_outgoing'] = FALSE ;

/*
|--------------------------------------------------------------------------
| SMS Bomber
|--------------------------------------------------------------------------
|
| Send message repeatedly
|
*/
$config['sms_bomber'] = FALSE;

/*
|--------------------------------------------------------------------------
| Enable Smiles/Emoticons
|--------------------------------------------------------------------------
|
| Enable Smiley/Emoticons for messages
|
*/
$config['enable_emoticons'] = FALSE;

/*
|--------------------------------------------------------------------------
| Append @username
|--------------------------------------------------------------------------
|
| Append @username on sent messages
| Act as identifier which messages sent by which username
| @username will be automatically replaced by username who sent the messages
|
*/
$config['append_username'] = FALSE;
$config['append_username_message'] = "Sender: @username";


/*
|--------------------------------------------------------------------------
| SMS Advertise
|--------------------------------------------------------------------------
|
| Advertised message that will be appended at the end of message
|
*/
$config['sms_advertise'] = FALSE;
$config['sms_advertise_message'] = "This is ads message";


/*
|--------------------------------------------------------------------------
| Registration (Not implemented yet)
|--------------------------------------------------------------------------
|
| Allow user register to your system
|
*/
//$config['registration'] = FALSE;


/*
|--------------------------------------------------------------------------
| NDNC Filter (INDIA)
|--------------------------------------------------------------------------
|
| Filters outgoing messages for numbers registered in NDNC Registry
|
*/
$config['ndnc'] = FALSE;


/*
|--------------------------------------------------------------------------
| Multiple phone/modem support
|--------------------------------------------------------------------------
| 
| state - enables/disabled
| strategy
|	- failover (not implemented yet)
|	- round robin (Must be in array, eg. array('sierra', 'fasttrack'), id and value is NOT used)
|	- scheduled_time (Start-End, Format: HH:MM:SS-HH:MM:SS)
|	- scheduled_day (Start-End, Format: 0-3, Note: 0 = Sunday, 1 = Monday, ..., 6 = Saturday)
|	- scheduled_date (Start:End, Format: YYYY-MM-DD:YYYY-MM-DD)
|	- phone_number_prefix (Must be in array, eg. array('+62813', '+62856'))
|	- phone_number (Must be in array, eg. array('123456789', '987654321'))
| id - Modem ID, must match to PhoneID on smsdrc
| value - Modem value to use based on strategy 
| 
*/
$config['multiple_modem_state'] = TRUE;
$config['multiple_modem_strategy'] = 'round_robin';
$config['multiple_modem'] = array('sierra', 'fasttrack');

/* End of file kalkun_settings.php */
/* Location: ./application/config/kalkun_settings.php */