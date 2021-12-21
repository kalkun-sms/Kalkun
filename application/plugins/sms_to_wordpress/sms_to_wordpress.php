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

function sms_to_wordpress_initialize()
{
	$CI =& get_instance();

	$CI->load->add_package_path(APPPATH.'plugins/sms_to_wordpress', FALSE);
	$CI->load->config('sms_to_wordpress', TRUE);

	return $CI->config->config['sms_to_wordpress'];
}

// Add hook for incoming message
add_action("message.incoming.before", "sms_to_wordpress", 16);

/**
* Function called when plugin first activated
* Utility function must be prefixed with the plugin name
* followed by an underscore.
* 
* Format: pluginname_activate
* 
*/
function sms_to_wordpress_activate()
{
    return true;
}

/**
* Function called when plugin deactivated
* Utility function must be prefixed with the plugin name
* followed by an underscore.
* 
* Format: pluginname_deactivate
* 
*/
function sms_to_wordpress_deactivate()
{
    return true;
}

/**
* Function called when plugin first installed into the database
* Utility function must be prefixed with the plugin name
* followed by an underscore.
* 
* Format: pluginname_install
* 
*/
function sms_to_wordpress_install()
{
	$CI =& get_instance();
	$CI->load->helper('kalkun');
	// check if table already exist
	if (!$CI->db->table_exists('plugin_sms_to_wordpress'))
	{
		$db_driver = $CI->db->platform();
		$db_prop = get_database_property($db_driver);
		execute_sql(APPPATH."plugins/sms_to_wordpress/media/".$db_prop['file']."_sms_to_wordpress.sql");
	}
    return true;
}

function sms_to_wordpress($sms)
{
	$config = sms_to_wordpress_initialize();
	$message = $sms->TextDecoded;
	$number = $sms->SenderNumber;
	
	list($code) = explode(" ", $message);
	$wordpress_code = $config['wordpress_code'];
	$wordpress_post = trim(str_replace($config['wordpress_code'], '', $message));
	if (strtoupper($code)===strtoupper($wordpress_code))
	{
		$CI =& get_instance();
		$CI->load->model('sms_to_wordpress/sms_to_wordpress_model', 'plugin_model');

		require_once 'vendor/kissifrot/php-ixr/src/Client/Client.php';
		require_once 'vendor/kissifrot/php-ixr/src/Message/Error.php';
		require_once 'vendor/kissifrot/php-ixr/src/Message/Message.php';
		require_once 'vendor/kissifrot/php-ixr/src/Request/Request.php';
		require_once 'vendor/kissifrot/php-ixr/src/DataType/Value.php';

		// if wp url exist
		$wp = $CI->plugin_model->get_wp_url_by_phone($number);
		if (is_array($wp))
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

