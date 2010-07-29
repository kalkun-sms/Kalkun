<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

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


/* End of file kalkun_settings.php */
/* Location: ./system/application/config/kalkun_settings.php */