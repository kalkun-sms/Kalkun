<?php
/**
 * Kalkun
 * An open source web based SMS Management
 *
 * @package     Kalkun
 * @author      Kalkun Dev Team
 * @license     https://spdx.org/licenses/GPL-3.0-or-later.html
 * @link        https://kalkun.sourceforge.io/
 */

// ------------------------------------------------------------------------

/**
 * Stop manager Class
 *
 * @package     Kalkun
 * @subpackage  Plugin
 * @category    Controllers
 */
include_once(APPPATH.'plugins/Plugin_controller.php');
include_once(APPPATH.'plugins/Plugin_helper.php');

Plugin_helper::autoloader();

class Stop_manager extends Plugin_controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('stop_manager_model');
	}

	function index()
	{
		$this->load->helper('kalkun');

		if ($_POST && is_null($this->input->post('search_name')))
		{
			$this->_phone_number_validation($this->input->post('destination_number'));
			$this->stop_manager_model->add(
				phone_format_e164($this->input->post('destination_number')),
				($this->input->post('stop_type')) ? $this->input->post('stop_type') : \Kalkun\Plugins\StopManager\MsgIncoming::TYPE_NOT_SET,
				$this->input->post('stop_message')
			);
			redirect('plugin/stop_manager');
		}

		$offset = 0;
		if ( ! is_null($this->input->post('search_name')))
		{
			$data['stoplist'] = $this->stop_manager_model->get('search');
		}
		else
		{
			$this->load->library('pagination');
			$config['base_url'] = site_url('plugin/stop_manager');
			$config['total_rows'] = $this->stop_manager_model->get('count');
			$config['per_page'] = $this->Kalkun_model->get_setting()->row('paging');
			$config['cur_tag_open'] = '<span id="current">';
			$config['cur_tag_close'] = '</span>';
			($this->uri->segment(3, 0) === 'index') ? $config['uri_segment'] = 4 : $config['uri_segment'] = 3;
			$this->pagination->initialize($config);

			$offset = ($this->uri->segment(3, 0) === 'index') ? $this->uri->segment(4, 0) : $this->uri->segment(3, 0);
			if ( ! is_numeric($offset))
			{
				show_404();
			}
			if (intval($offset) >= $this->stop_manager_model->get('count'))
			{
				$offset = 0;
			}
			$data['stoplist'] = $this->stop_manager_model->get('paginate', $config['per_page'], $offset);
		}

		$data['main'] = 'index';
		$data['number'] = $offset + 1;

		$this->load->view('main/layout', $data);
	}

	function _remap($method, $params = array())
	{
		if (method_exists($this, $method))
		{
			return call_user_func_array(array($this, $method), $params);
		}
		else
		{
			if (is_numeric($method))
			{
				if (intval($method) < $this->stop_manager_model->get('count'))
				{
					$this->index($method);
					return;
				}
				else
				{
					$this->index();
					return;
				}
			}
			show_404();
		}
	}

	function delete()
	{
		if ($_POST)
		{
			$from = $this->input->post('from');
			$type = $this->input->post('type');
			$this->stop_manager_model->delete($from, $type);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Check if submitted phone number is valid
	 *
	 * @access	public
	 */
	function _phone_number_validation($phone)
	{
		$this->load->helper('kalkun');
		$result = is_phone_number_valid($phone);

		if ($result !== TRUE)
		{
			show_error($result, 400);
		}
	}
}
