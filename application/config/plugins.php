<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// phpcs:disable CodeIgniter.Commenting.InlineComment.WrongStyle
/*
|--------------------------------------------------------------------------
| Plugins Directory
|--------------------------------------------------------------------------
|
| Where are the plugins kept?
|
|       Default: FCPATH . 'plugins/' (<root>/plugins/)
*/
// Use plugin_path instead of plugin_dir because that's what is used in
// Plugins_lib.php of upstream. Using 'plugin_dir' is actually a bug in upstream.
$config['plugin_path'] = APPPATH . 'plugins/';

require_once(APPPATH . 'libraries/abstract.plugins.php');
require_once(APPPATH . 'libraries/trait.plugins.php');
