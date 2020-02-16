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
 * SMS_to_wordpress Class
 *
 * @package		Kalkun
 * @subpackage	Plugin
 * @category	Controllers
 */
include_once(APPPATH.'plugins/Plugin_Controller.php');

class SMS_to_wordpress extends Plugin_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->model('sms_to_wordpress_model', 'plugin_model');
	}
	
	function index()
	{
		$data['title'] = 'Wordpress Blog Status';
		$data['main'] = 'index';
		$data['status'] = $this->plugin_model->check_status($this->session->userdata('id_user'));
		$data['wp'] = $this->plugin_model->get_wp($this->session->userdata('id_user'));
		$this->load->view('main/layout', $data);
	}
	
	function add()
	{
		if ($_POST)
		{
			$this->plugin_model->save_wp();
			redirect('sms_to_wordpress');
		}
	}
	
	function delete()
	{
		$this->plugin_model->delete_wp($this->session->userdata('id_user'));
		redirect('sms_to_wordpress');	
	}
}