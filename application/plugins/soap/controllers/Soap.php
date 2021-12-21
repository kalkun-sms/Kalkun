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
 * Soap Class
 *
 * @package		Kalkun
 * @subpackage	Plugin
 * @category	Controllers
 */
include_once(APPPATH.'plugins/Plugin_controller.php');

class Soap extends Plugin_controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('Kalkun_model');
		$this->load->model('soap_model', 'Plugin_model');
	}

	function index()
	{
		if ($_POST)
		{
			if ($this->input->post('editid_remote_access'))
			{
				$this->Plugin_model->updateRemoteAccess();
			}
			else
			{
				if ($this->input->post('notifiy') === 'on')
				{
					$i = 0;
				}
				else
				{
					$this->Plugin_model->addRemoteAccess();
				}
			}
			redirect('plugin/soap');
		}

		$this->load->library('pagination');
		$config['base_url'] = site_url().'/plugin/soap';
		$config['total_rows'] = $this->Plugin_model->getRemoteAccess('count');
		$config['per_page'] = 10;
		//$config['per_page'] = $this->Kalkun_model->getSetting('paging', 'value')->row('value');
		$config['cur_tag_open'] = '<span id="current">';
		$config['cur_tag_close'] = '</span>';
		$config['uri_segment'] = 3;

		$this->pagination->initialize($config);

		$data['main'] = 'index';
		$data['remote_access'] = $this->Plugin_model->getRemoteAccess('paginate', $config['per_page'], $this->uri->segment(3, 0));
		//TODO - GET NOTIFICATION
		$data['notification'] = array();
		$data['number'] = $this->uri->segment(3, 0) + 1;
		$this->load->view('main/layout', $data);
	}

	function delete_remote_access($id)
	{
		$this->Plugin_model->delRemoteAccess($id);
		redirect('plugin/soap');
	}

	function delete_notification()
	{
		// TODO - delete notifiy
	}
}
