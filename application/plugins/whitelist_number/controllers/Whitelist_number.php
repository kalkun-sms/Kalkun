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

	function delete()
	{
		if ($_POST)
		{
			$id = intval($this->input->post('id'));
			$this->whitelist_number_model->delete($id);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Check pattern validity
	 *
	 * returns a json string used by jquery validation plugin
	 * "true" if pattern is valid for use by preg_match()
	 * "an error message" if not
	 */
	function preg_match_pattern_validation()
	{
		$result = 'false'; // Default to "false"

		$pattern = $this->input->get_post('pattern');

		try
		{
			preg_match($pattern, 'test string');
			$result = 'true';
		}
		catch (Exception $e)
		{
			$result = $e->getMessage();
		}
		header('Content-type: application/json');
		echo json_encode($result);
	}
}
