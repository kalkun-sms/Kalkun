<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Plugin Name: Phonebook Lookup
* Plugin URI: http://azhari.harahap.us
* Version: 0.1
* Description: Lookup phone number from specified URL
* Author: Azhari Harahap
* Author URI: http://azhari.harahap.us
*/

require_once (APPPATH . 'plugins/Plugin_helper.php');

class Phonebook_lookup_plugin extends CI3_plugin_system {

	use plugin_trait;

	public function __construct()
	{
		parent::__construct();
		// Add hook for contact menu
		add_filter('phonebook.contact.menu', array($this, 'phonebook_lookup'), 10);
	}

	function phonebook_lookup($number)
	{
		$config = Plugin_helper::get_plugin_config('phonebook_lookup');
		Plugin_helper::load_lang('phonebook_lookup');
		$lookup['url'] = str_replace('#phonenumber#', $number->Number, $config['url']);
		$lookup['title'] = tr('Lookup Number');
		return $lookup;
	}
}
