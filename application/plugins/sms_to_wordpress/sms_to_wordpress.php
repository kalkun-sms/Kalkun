<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
* Plugin Name: SMS to Wordpress
* Plugin URI: http://azhari.harahap.us
* Version: 0.1
* Description: Posting Wordpress blog using SMS
* Author: Azhari Harahap
* Author URI: http://azhari.harahap.us
*/

require_once (APPPATH . 'plugins/Plugin_helper.php');

class Sms_to_wordpress_plugin extends CI3_plugin_system
{
	use plugin_trait;

	public function __construct()
	{
		parent::__construct();
		// Add hook for incoming message
		add_filter('message.incoming.before', array($this, 'sms_to_wordpress'), 16);
	}

	// ------------------------------------------------------------------------

	/**
	 * Install Plugin
	 *
	 * Anything that needs to happen when this plugin gets installed
	 *
	 * @access public
	 * @since   0.1.0
	 * @return bool    TRUE by default
	 */
	public static function install($data = NULL)
	{
		$CI = &get_instance();
		$CI->load->helper('kalkun');
		// check if table already exist
		if ( ! $CI->db->table_exists('plugin_sms_to_wordpress'))
		{
			$db_driver = $CI->db->platform();
			$db_prop = get_database_property($db_driver);
			execute_sql(APPPATH . 'plugins/sms_to_wordpress/media/' . $db_prop['file'] . '_sms_to_wordpress.sql');
		}
		return TRUE;
	}

	function sms_to_wordpress($sms)
	{
		$config = Plugin_helper::get_plugin_config('sms_to_wordpress');
		$message = $sms->TextDecoded;
		$number = $sms->SenderNumber;

		list($code) = explode(' ', $message);
		$wordpress_code = $config['wordpress_code'];
		$wordpress_post = trim(str_replace($config['wordpress_code'], '', $message));
		if (strtoupper($code) === strtoupper($wordpress_code))
		{
			$CI = &get_instance();
			$CI->load->model('sms_to_wordpress/sms_to_wordpress_model', 'sms_to_wordpress_model');

			// if wp url exist
			$wp = $CI->sms_to_wordpress_model->get_wp_url_by_phone($number);
			if ( ! empty($wp))
			{
				$client = new IXR\Client\Client($wp['wp_url']);

				// Post parameter
				$post = array(
					'title' => 'Post from SMS',
					'description' => $wordpress_post,
					//'dateCreated' => (new IXR_Date(time())),
					'post_type' => 'post',
					'post_status' => 'publish',
					'mt_keywords' => 'sms,kalkun',
					'publish' => 1
				);

				$CI->load->library('encryption');
				$wp_pass = $CI->encryption->decrypt($wp['wp_password']);
				if ($wp_pass === FALSE)
				{
					log_message('error', 'sms_to_wordpress: problem during decryption.');
					show_error('sms_to_wordpress: problem during decryption.', 500, '500 Internal Server Error');
				}
				// Debug ON. Now you know what's going on.
				//$client->debug = true;
				// Execute
				$res = $client->query('metaWeblog.newPost', '', $wp['wp_username'], $wp_pass, $post);
			}
		}
	}
}
