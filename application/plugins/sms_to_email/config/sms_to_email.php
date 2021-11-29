<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// phpcs:disable CodeIgniter.Commenting.InlineComment.WrongStyle
/*
|--------------------------------------------------------------------------
| CONFIGURATION
|--------------------------------------------------------------------------
|
| protocol - The mail sending protocol (mail, sendmail, or smtp)
| smtp_host - SMTP Server Address
| smtp_port - SMTP Port
| smtp_user - SMTP Username
| smtp_pass - SMTP Password
| charset - Character set (utf-8, iso-8859-1, etc.)
| wordwrap - Enable word-wrap
| mail_from - Sender Email
| mail_subject - The mail subject
|
*/
// phpcs:enable
$config['protocol'] = 'mail';
$config['smtp_host'] = "localhost";
$config['smtp_port'] = "25";
$config['smtp_user'] = "username";
$config['smtp_pass'] = "password";
$config['charset'] = 'utf-8';
$config['wordwrap'] = TRUE;
$config['mail_from'] = 'postmaster@domain.com';
$config['mail_subject'] = 'Kalkun New SMS';

