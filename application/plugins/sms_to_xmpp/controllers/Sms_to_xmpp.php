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
 * SMS_to_xmpp Class
 *
 * @package		Kalkun
 * @subpackage	Plugin
 * @category	Controllers
 */
include_once(APPPATH.'plugins/Plugin_Controller.php');

class SMS_to_xmpp extends Plugin_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->model('sms_to_xmpp_model', 'plugin_model');
	}
	
	function index()
	{
		$data['title'] = 'XMPP Account Status';
		$data['main'] = 'index';
		$data['status'] = $this->plugin_model->check_status($this->session->userdata('id_user'));
		$data['xmpp'] = $this->plugin_model->get_xmpp($this->session->userdata('id_user'));
		$this->load->view('main/layout', $data);
	}
	
	function add()
	{
		if ($_POST)
		{
			$this->plugin_model->save_xmpp();
			redirect('sms_to_xmpp');
		}
	}
	
	function delete()
	{
		$this->plugin_model->delete_xmpp($this->session->userdata('id_user'));
		redirect('sms_to_xmpp');	
	}
}