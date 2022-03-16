<?php
/**
 * Kalkun
 * An open source web based SMS Management
 *
 * @package		Kalkun
 * @author		Kalkun Dev Team
 * @license		https://spdx.org/licenses/GPL-3.0-or-later.html
 * @link		https://kalkun.sourceforge.io/
 */

// ------------------------------------------------------------------------

/**
 * Server_alert Class
 *
 * @package		Kalkun
 * @subpackage	Plugin
 * @category	Controllers
 */
include_once(APPPATH.'plugins/Plugin_controller.php');

class Server_alert extends Plugin_controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('server_alert_model');
		$this->load->helper('kalkun');
	}

	function index()
	{
		if ($_POST)
		{
			if ($this->input->post('editid_server_alert'))
			{
				$this->server_alert_model->update();
			}
			else
			{
				$this->server_alert_model->add();
			}
			redirect('plugin/server_alert');
		}

		$this->load->library('pagination');
		$config['base_url'] = site_url('plugin/server_alert/index');
		$config['total_rows'] = $this->server_alert_model->get('count');
		$config['per_page'] = $this->Kalkun_model->get_setting()->row('paging');
		$config['cur_tag_open'] = '<span id="current">';
		$config['cur_tag_close'] = '</span>';
		$config['uri_segment'] = 4;
		$this->pagination->initialize($config);

		$data['main'] = 'index';
		$data['alert'] = $this->server_alert_model->get('paginate', $config['per_page'], $this->uri->segment(4, 0));
		$data['number'] = $this->uri->segment(4, 0) + 1;

		$data['time_interval'] = $this->server_alert_model->get_time_interval();
		$this->load->view('main/layout', $data);
	}

	function delete()
	{
		if ($_POST)
		{
			$id = intval($this->input->post('id'));
			$this->server_alert_model->delete($id);
		}
	}

	function change_state()
	{
		if ($_POST)
		{
			$id = intval($this->input->post('id'));
			$this->server_alert_model->change_state($id, 'true');
		}
	}

	function get_time_interval()
	{
		echo 'Total Time Interval : '.$this->server_alert_model->get_time_interval().' seconds';
	}
}
