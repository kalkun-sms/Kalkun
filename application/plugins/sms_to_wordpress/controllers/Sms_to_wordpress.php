<?php
/**
 * Kalkun
 * An open source web based SMS Management
 *
 * @package		Kalkun
 * @author		Kalkun Dev Team
 * @license		https://spdx.org/licenses/GPL-2.0-or-later.html
 * @link		https://kalkun.sourceforge.io/
 */

// ------------------------------------------------------------------------

/**
 * Sms_to_wordpress Class
 *
 * @package		Kalkun
 * @subpackage	Plugin
 * @category	Controllers
 */
include_once(APPPATH.'plugins/Plugin_controller.php');

class Sms_to_wordpress extends Plugin_controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('sms_to_wordpress_model');
	}

	function index()
	{
		$data['title'] = 'Wordpress Blog Status';
		$data['main'] = 'index';
		$data['status'] = $this->sms_to_wordpress_model->check_status($this->session->userdata('id_user'));
		$data['wp'] = $this->sms_to_wordpress_model->get_wp($this->session->userdata('id_user'));
		$this->load->view('main/layout', $data);
	}

	function add()
	{
		if ($_POST)
		{
			$this->sms_to_wordpress_model->save_wp();
			redirect('plugin/sms_to_wordpress');
		}
	}

	function delete()
	{
		if ($_POST)
		{
			$this->sms_to_wordpress_model->delete_wp($this->session->userdata('id_user'));
		}
	}
}
