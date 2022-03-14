<?php
/**
 * Kalkun
 * An open source web based SMS Management
 *
 * @package		Kalkun
 * @author		Kalkun Dev Team
 * @license		https://spdx.org/licenses/GPL-3.0-or-later.html
 * @link		https://kalkun-sms.github.io/
 */

// ------------------------------------------------------------------------

/**
 * Sms_to_twitter Class
 *
 * @package		Kalkun
 * @subpackage	Plugin
 * @category	Controllers
 */
include_once(APPPATH.'plugins/Plugin_controller.php');

class Sms_to_twitter extends Plugin_controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('sms_to_twitter_model');
		$this->load->config('sms_to_twitter', TRUE);
	}

	function index()
	{
		$data['title'] = 'Twitter Connect Status';
		$data['main'] = 'index';
		$data['status'] = $this->sms_to_twitter_model->check_token($this->session->userdata('id_user'));
		$this->load->view('main/layout', $data);
	}

	function connect()
	{
		// Database check
		if ($this->sms_to_twitter_model->check_token($this->session->userdata('id_user')))
		{
			$this->session->set_flashdata('notif', 'Already connected to Twitter');
			redirect('plugin/sms_to_twitter');
		}

		$this->load->library('twitter');

		// Twitter keys
		$consumer_key = $this->config->item('consumer_key', 'sms_to_twitter');
		$consumer_key_secret = $this->config->item('consumer_key_secret', 'sms_to_twitter');
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
			redirect('plugin/sms_to_twitter');
		}

		if (isset($auth['access_token']) && isset($auth['access_token_secret']))
		{
			// Save to database
			$param['id_user'] = $this->session->userdata('id_user');
			$param['access_token'] = $auth['access_token'];
			$param['access_token_secret'] = $auth['access_token_secret'];
			$this->sms_to_twitter_model->save_token($param);
			redirect('plugin/sms_to_twitter');
		}
	}

	function disconnect()
	{
		$this->sms_to_twitter_model->delete_token($this->session->userdata('id_user'));
		redirect('plugin/sms_to_twitter');
	}
}
