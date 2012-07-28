<?php
/**
 * Kalkun
 * An open source web based SMS Management
 *
 * @package		Kalkun
 * @author		Kalkun Dev Team
 * @license		http://kalkun.sourceforge.net/license.php
 * @link		http://kalkun.sourceforge.net
 */

// ------------------------------------------------------------------------

/**
 * SMS_to_twitter Class
 *
 * @package		Kalkun
 * @subpackage	Plugin
 * @category	Controllers
 */
include_once(APPPATH.'plugins/Plugin_Controller.php');

class SMS_to_twitter extends Plugin_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->model('sms_to_twitter_model', 'plugin_model');
	}
	
	function index()
	{
		$data['title'] = 'Twitter Connect Status';
		$data['main'] = 'index';
		$data['status'] = $this->plugin_model->check_token($this->session->userdata('id_user'));
		$this->load->view('main/layout', $data);
	}
	
	function connect()
	{
		// Database check
		if ($this->plugin_model->check_token($this->session->userdata('id_user')))
		{
			$this->session->set_flashdata('notif', 'Already connected to Twitter');
			redirect('sms_to_twitter');
		}
		
		$this->load->library('twitter');
				
		// Kalkun Twitter keys (DO NOT CHANGE)
		$consumer_key = '23TbUWvaVRenQcNv6MA';
		$consumer_key_secret = 'eBYvkk4dpgx6CS1uTWlrWxKZTY791CJ2cEE24JV4MqQ';
		$tokens['access_token'] = NULL;
		$tokens['access_token_secret'] = NULL;
		$callback_url = site_url('sms_to_twitter/connect');
		
		try
		{
			$auth = $this->twitter->oauth($consumer_key, $consumer_key_secret, $tokens['access_token'], $tokens['access_token_secret'], $callback_url);
		}
		catch (EpiOAuthException $e)
		{
			$this->session->set_flashdata('notif', 'Cannot connect to Twitter');
			redirect('sms_to_twitter');
		}
		
		if (isset($auth['access_token']) && isset($auth['access_token_secret']))
		{
			// Save to database
			$param['id_user'] = $this->session->userdata('id_user');
			$param['access_token'] = $auth['access_token'];
			$param['access_token_secret'] = $auth['access_token_secret'];
			$this->plugin_model->save_token($param);
			redirect('sms_to_twitter');
		}
	}
	
	function disconnect()
	{
		$this->plugin_model->delete_token($this->session->userdata('id_user'));
		redirect('sms_to_twitter');
	}
}