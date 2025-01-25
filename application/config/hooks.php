<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// phpcs:disable CodeIgniter.Commenting.InlineComment.WrongStyle
/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	https://codeigniter.com/userguide3/general/hooks.html
|
*/
// phpcs:enable
$hook['pre_controller'] = array(
        'class'    => 'MY_Hooks',
        'function' => 'kalkun_set_error_handler',
        'filename' => 'MY_Hooks.php',
        'filepath' => 'hooks',
        'params'   => array()
);

$hook['post_controller'] = array(
        'class'    => 'MY_Hooks',
        'function' => 'kalkun_restore_error_handler',
        'filename' => 'MY_Hooks.php',
        'filepath' => 'hooks',
        'params'   => array()
);
