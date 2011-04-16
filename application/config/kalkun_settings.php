<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Kalkun Metadata (DO NOT CHANGE!!!)
|--------------------------------------------------------------------------
|
*/
$config['kalkun_version'] = '0.2.10';
$config['kalkun_release_date'] = '11 April 2011';
$config['kalkun_upgradeable'] = TRUE;
$config['kalkun_previous_version'] = '0.2.9';

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
| SMS Bomber
|--------------------------------------------------------------------------
|
| Send message repeatedly
|
*/
$config['sms_bomber'] = FALSE;


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
| Server Alert (Under Maintenance)
|--------------------------------------------------------------------------
|
| Alert you whenever your server down
|
*/
//$config['server_alert'] = TRUE;


/*
|--------------------------------------------------------------------------
| SMS Content
|--------------------------------------------------------------------------
|
| ... is a feature that let your member register first before get updates from you.
|
*/
$config['sms_content'] = FALSE;

// Registration code (Don't use space)
$config['sms_content_reg_code'] = 'REG';
$config['sms_content_unreg_code'] = 'UNREG';


/*
|--------------------------------------------------------------------------
| SMS to Email  Mail Settings
|--------------------------------------------------------------------------
|
| Forward Incomming sms to a email address
|
*/
 
$config['protocol'] = 'mail'; // mail/smtp
$config['smtp_host'] = "localhost";
$config['smtp_port'] = "25";
$config['smtp_user'] = "username";
$config['smtp_pass'] = "password";
$config['charset'] = 'utf-8';
$config['wordwrap'] = TRUE;
$config['mail_from'] = 'postmaster@domain.com' ;


/*
|--------------------------------------------------------------------------
| NDNC Filter (INDIA)
|--------------------------------------------------------------------------
|
| Filters outgoing messages for numbers registered in NDNC Registry
|
*/
$config['ndnc'] = TRUE;

/*
|--------------------------------------------------------------------------
| Simple Autoreply (Experimental)
|--------------------------------------------------------------------------
|
| Always reply (automatically) every incoming message
|
*/
$config['simple_autoreply'] = FALSE;
$config['simple_autoreply_uid'] = '1'; // set user id who sent the message, must be valid ID
$config['simple_autoreply_msg'] = "Thanks for sending me the message";

/*
|--------------------------------------------------------------------------
| Executed External Script (Experimental)
|--------------------------------------------------------------------------
|
| Execute external script if condition match
| 
| state - enables/disabled
| path - path of the shell program (bash), not path to script to be executed
| name - the script name to be executed
| key - what condition we looking at (sender or content)
| type - pattern matching used (match or contain)
| value - the value to matching with
| parameter - extra parameter to send to the script (phone,content,id), each value divided by |
| 
*/
$config['ext_script_state'] = FALSE;
$config['ext_script_path'] = '/bin/sh';
$config['ext_script'][0]['name'] = '/usr/local/reboot_server.sh';
$config['ext_script'][0]['key'] = 'content';
$config['ext_script'][0]['type'] = 'match';
$config['ext_script'][0]['value'] = 'reboot';
$config['ext_script'][0]['parameter'] = 'phone|id|content';

$config['ext_script'][1]['name'] = '/usr/local/check_user.sh';
$config['ext_script'][1]['key'] = 'sender';
$config['ext_script'][1]['type'] = 'contain';
$config['ext_script'][1]['value'] = '+62';
$config['ext_script'][1]['parameter'] = 'phone|content';

/*
|--------------------------------------------------------------------------
| Multiple phone/modem support
|--------------------------------------------------------------------------
| 
| state - enables/disabled
| strategy - failover, scheduled_time, scheduled_day, scheduled_date, phone_number_prefix, phone_number
| id - Modem ID, must match to PhoneID on smsdrc
| value - Modem value to use based on strategy 
| 
*/
$config['multiple_modem_state'] = FALSE;
$config['multiple_modem_strategy'] = 'scheduled_time';
$config['multiple_modem'][0]['id'] = 'sierra';
$config['multiple_modem'][0]['value'] = '08:00:00-12:00:00';

$config['multiple_modem'][1]['id'] = 'fasttrack';
$config['multiple_modem'][1]['value'] = '13:00:00-24:00:00';

/* End of file kalkun_settings.php */
/* Location: ./application/config/kalkun_settings.php */