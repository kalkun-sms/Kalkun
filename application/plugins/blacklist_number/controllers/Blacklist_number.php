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
 * Blacklist_number Class
 *
 * @package		Kalkun
 * @subpackage	Plugin
 * @category	Controllers
 */
include_once(APPPATH.'plugins/Plugin_controller.php');

class Blacklist_number extends Plugin_controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('blacklist_number_model');
		$this->load->helper('kalkun');
	}

	function index()
	{
		if ($_POST)
		{
			if ($this->input->post('editid_blacklist_number'))
			{
				$this->blacklist_number_model->update();
			}
			else
			{
				$this->blacklist_number_model->add();
			}
			redirect('plugin/blacklist_number');
		}

		$this->load->library('pagination');
		$config['base_url'] = site_url('plugin/blacklist_number/index');
		$config['total_rows'] = $this->blacklist_number_model->get('count');
		$config['per_page'] = $this->Kalkun_model->get_setting()->row('paging');
		$config['cur_tag_open'] = '<span id="current">';
		$config['cur_tag_close'] = '</span>';
		$config['uri_segment'] = 4;
		$this->pagination->initialize($config);

		$data['main'] = 'index';
		$data['blacklist'] = $this->blacklist_number_model->get('paginate', $config['per_page'], $this->uri->segment(4, 0));
		$data['number'] = $this->uri->segment(4, 0) + 1;
		$this->load->view('main/layout', $data);
	}

	function delete()
	{
		if ($_POST)
		{
			$id = intval($this->input->post('id'));
			$this->blacklist_number_model->delete($id);
		}
	}
}
