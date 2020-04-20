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
 * SMS_to_email Class
 *
 * @package		Kalkun
 * @subpackage	Plugin
 * @category	Controllers
 */
include_once(APPPATH.'plugins/Plugin_Controller.php');

class SMS_to_email extends Plugin_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->model('sms_to_email_model', 'plugin_model');
	}
	
	function index()
	{
		$this->load->helper('form');
		$data['title'] = 'SMS to Email Settings';
		$data['main'] = 'index';
		$data['settings'] = $this->plugin_model->get_setting($this->session->userdata('id_user'));
		if ($data['settings']->num_rows()==1) $data['mode'] = 'edit';
		else $data['mode'] = 'add';
		$this->load->view('main/layout', $data);
	}
	
	function save()
	{
		if ($_POST)
		{
			$this->plugin_model->save_setting();
			redirect('plugin/sms_to_email');
		}
	}
}
	
/* End of file sms_to_email.php */
/* Location: ./application/plugins/sms_to_email/controllers/sms_to_email.php */