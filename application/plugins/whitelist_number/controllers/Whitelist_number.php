<?php
/**
 * Kalkun
 * An open source web based SMS Management
 *
 * @package		Kalkun
 * @author		Kalkun Dev Team
 * @license		https://spdx.org/licenses/GPL-3.0-or-later.html
 * @link		http://kalkun.sourceforge.net
 */

// ------------------------------------------------------------------------

/**
 * Blacklist_number Class
 *
 * @package		Kalkun
 * @subpackage	Plugin
 * @category	Controllers
 */
include_once(APPPATH.'plugins/Plugin_controller.php');

class Whitelist_number extends Plugin_controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('Kalkun_model');
		$this->load->model('whitelist_number_model');
	}

	function index()
	{
		if ($_POST)
		{
			if ($this->input->post('editid_whitelist'))
			{
				$this->whitelist_number_model->update();
			}
			else
			{
				$this->whitelist_number_model->add();
			}
			redirect('plugin/whitelist_number');
		}

		$this->load->library('pagination');
		$config['base_url'] = site_url('plugin/whitelist_number');
		$config['total_rows'] = $this->whitelist_number_model->get('count');
		$config['per_page'] = $this->Kalkun_model->get_setting()->row('paging');
		$config['cur_tag_open'] = '<span id="current">';
		$config['cur_tag_close'] = '</span>';
		$config['uri_segment'] = 3;
		$this->pagination->initialize($config);

		$data['main'] = 'index';
		$data['whitelist'] = $this->whitelist_number_model->get('paginate', $config['per_page'], $this->uri->segment(3, 0));
		$data['number'] = $this->uri->segment(3, 0) + 1;
		$this->load->view('main/layout', $data);
	}

	function delete($id)
	{
		$this->whitelist_number_model->delete($id);
		redirect('plugin/whitelist_number');
	}
}
