<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// phpcs:disable CodeIgniter.Commenting.InlineComment.WrongStyle
/*
|--------------------------------------------------------------------------
| CONFIGURATION
|--------------------------------------------------------------------------
|
| xmpp_code - XMPP code (Don't use space)
|
*/
// phpcs:enable
$config['php_path'] = '/usr/bin/php';
$config['php_script'] = realpath(dirname(__FILE__)).'/libraries/abhinavsingh-JAXL-5829c3b/sendMessage.php';
$config['xmpp_code'] = 'XMPP';

