<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// phpcs:disable CodeIgniter.Commenting.InlineComment.WrongStyle,CodeIgniter.Commenting.InlineComment.LongCommentWithoutSpacing
/*
|--------------------------------------------------------------------------
| Kalkun Metadata (DO NOT CHANGE!!!)
|--------------------------------------------------------------------------
|
*/
$config['kalkun_version'] = '0.7.1';
$config['kalkun_codename'] = 'Yogyakarta';
$config['kalkun_release_date'] = '01 February 2013';
$config['kalkun_upgradeable'] = TRUE;
$config['kalkun_previous_version'] = '0.6';

/*
|--------------------------------------------------------------------------
| Gateway Engine (Default to Gammu)
|--------------------------------------------------------------------------
|
| Valid engine are:
| gammu <http://wammu.eu>
| kannel <http://kannel.org> - Experimental
| clickatell <http://clickatell.com> - Experimental
| ozeking <http://ozekisms.com> - Experimental
| nowsms <http://nowsms.com> - Experimental
| way2sms <http://way2sms.com> - Experimental
| tmobilecz <https://sms.t-mobile.cz/closed.jsp> - Experimental
| connekt <https://github.com/kingster/connekt> - Experimental
*/
$config['gateway']['engine'] = 'gammu';
$config['gateway']['url'] = 'http://localhost:13013';
$config['gateway']['username'] = 'username';
$config['gateway']['password'] = 'password';
$config['gateway']['api_id'] = 'xxx1234567890';
// for tmobilecz you must specify the credentials to log-in to T-Mobile CZ portal
// numeric keys - credentials for specific kalkun user (user's ID from table "user")
// key "default" - credentials for all other Kalkun users
// subkey "user" and "pass" - string - username and password for T-Mobile CZ portal
// subkey "hist" - boolean - save copies of SMS in T-Mobile CZ portal
// subkey "eml" - string - T-Mobile CZ will send copy of SMS to specified e-mail. Leave empty to switch off.
$config['gateway']['tmobileczauth'] = array(
	1 => array('user' => 'admins login',   'pass' => 'his_password',  'hist' => TRUE, 'eml' => ''),
	2 => array('user' => '2nd users login', 'pass' => 'her_password',  'hist' => TRUE, 'eml' => ''),
	'default' => array('user' => 'all others',     'pass' => 'their_password', 'hist' => TRUE, 'eml' => '')
);

/*
|--------------------------------------------------------------------------
| Gammu Location (currently only used if you want to send WAP link)
|--------------------------------------------------------------------------
|
| Gammu Installation Location
| Default Locations... You will need to verify for your system
| Linux : /usr/local/bin/gammu
| Windows : C:\Program Files\Gammu 1.29.92\bin
|
*/
$config['gammu_path'] = 'C:\Gammu 1.29.92\bin';
$config['gammu_sms_inject'] = $config['gammu_path'].DIRECTORY_SEPARATOR.'gammu-smsd-inject';
$config['gammu_config'] = $config['gammu_path'].DIRECTORY_SEPARATOR.'config.ini';

/*
|--------------------------------------------------------------------------
| Conversation Grouping
|--------------------------------------------------------------------------
|
| Enable/disable grouping on message list,
| If set to FALSE, message will be listed as single message.
|
*/
$config['conversation_grouping'] = TRUE;

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
| Can be set to multiple users
| Must be valid user ID
|
*/
$config['inbox_owner_id'] = array('1');

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
| Enable Smileys/Emoticons
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
$config['append_username'] = TRUE;
$config['append_username_message'] = 'Sender: @username';


/*
|--------------------------------------------------------------------------
| SMS Advertise
|--------------------------------------------------------------------------
|
| Advertised message that will be appended at the end of message
|
*/
$config['sms_advertise'] = FALSE;
$config['sms_advertise_message'] = 'This is ads message';

/*
|--------------------------------------------------------------------------
| New incoming message sound
|--------------------------------------------------------------------------
|
| The sound filename (must be located on media/sound directory)
|
*/
$config['new_incoming_message_sound'] = 'bird1.wav';

/*
|--------------------------------------------------------------------------
| Max sms sent by minute
|--------------------------------------------------------------------------
|
| Usefull because of some carrier is blocking massive messaging.
|
*/
$config['max_sms_sent_by_minute'] = 0;

/*
|--------------------------------------------------------------------------
| Inbox Routing Use Phonebook
|--------------------------------------------------------------------------
|
| If no @username found on new incoming messages,
| then Kalkun will check origin phonenumber on all user phonebook.
|
*/
$config['inbox_routing_use_phonebook'] = FALSE;

/*
|--------------------------------------------------------------------------
| Inbox Routing User Phonenumber
|--------------------------------------------------------------------------
|
| If no @username found on new incoming messages,
| then Kalkun will check origin phonenumber againts user phonenumber.
|
*/
$config['inbox_routing_user_phonenumber'] = FALSE;

/*
|--------------------------------------------------------------------------
| Only admin can permanently delete
|--------------------------------------------------------------------------
|
| Prevent non-admin user from permanently delete message
|
*/
$config['only_admin_can_permanently_delete'] = FALSE;

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
| NCPR(DND) Filter (INDIA)
|--------------------------------------------------------------------------
|
| Filters outgoing messages for numbers registered in NCPR(DND) Registry
|
*/
$config['ncpr'] = FALSE;

/*
|--------------------------------------------------------------------------
| UNICODE
|--------------------------------------------------------------------------
|
| Enable unicode by default?
| Send as Unicode checkbox will automatically checked
|
*/
$config['unicode'] = FALSE;

/*
|--------------------------------------------------------------------------
| Multiple phone/modem support
|--------------------------------------------------------------------------
|
| state - enables/disabled
| strategy
|	(First)
|	- scheduled_time (Start-End, Format: HH:MM:SS-HH:MM:SS)
|	- scheduled_day (Start-End, Format: 0-3, Note: 0 = Sunday, 1 = Monday, ..., 6 = Saturday)
|	- scheduled_date (Start:End, Format: YYYY-MM-DD:YYYY-MM-DD)
|	- phone_number_prefix (Must be in array, eg. array('+62813', '+62856'))
|	- phone_number (Must be in array, eg. array('123456789', '987654321'))
|	(Second)
|	- failover (not implemented yet)
|	- recent (Must be in array, eg. array('sierra', 'fasttrack'), id and value is NOT used)
|	- round robin (Must be in array, eg. array('sierra', 'fasttrack'), id and value is NOT used)
| id - Modem ID, must match to PhoneID on smsdrc
| value - Modem value to use based on strategy
|
| NOTE: You can also use two strategy at the same time as long as it's valid
| eg. scheduled_time:round_robin (stategy name is divided by ':')
| 	  This combination allow you to select multiple modem for same time range,
|	  and those available/valid modem will be selected again with round robin strategy.
|
| Valid combination format: (First:Second)
| Another combination example (valid):
|	- phone_number_prefix:round_robin
|	- scheduled_day:recent
| Invalid/wrong combination:
|	- round_robin:phone_number_prefix
|	- round_robin:recent
|
*/
$config['multiple_modem_state'] = FALSE;
$config['multiple_modem_strategy'] = 'scheduled_date';
$config['multiple_modem'][0]['id'] = 'sierra';
$config['multiple_modem'][0]['value'] = '2012-04-17:2012-05-17';

$config['multiple_modem'][1]['id'] = 'fasttrack';
$config['multiple_modem'][1]['value'] = '2014-04-17:2014-05-17';


/*
|--------------------------------------------------------------------------
| Multiple phone/modem user preferences (Not implemented yet)
|--------------------------------------------------------------------------
|
| Allow user to select modem when sending message
|
*/
// phpcs:enable
//$config['multiple_modem_compose_state'] = FALSE;
//$config['multiple_modem_compose_criteria'] = 'time';
//$config['multiple_modem_compose_order'] = 'desc';
