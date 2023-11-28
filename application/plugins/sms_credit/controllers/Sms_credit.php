<?php
/**
 * Kalkun
 * An open source web based SMS Management
 *
 * @package     Kalkun
 * @author      Kalkun Dev Team
 * @license     https://spdx.org/licenses/GPL-2.0-or-later.html
 * @link        https://kalkun.sourceforge.io/
 */

// ------------------------------------------------------------------------

/**
 * Sms_credit Class
 *
 * @package     Kalkun
 * @subpackage  Plugin
 * @category    Controllers
 */
include_once(APPPATH.'plugins/Plugin_controller.php');

class Sms_credit extends Plugin_controller {

	/**
	 * Constructor
	 *
	 * @access	public
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->model('sms_credit_model');
	}

	// --------------------------------------------------------------------

	/**
	 * Index
	 *
	 * Display list of all users
	 *
	 * @access  public
	 */
	function index()
	{
		$param = array();
		if ($_POST)
		{
			$param['q'] = $this->input->post('search_name');
			$data['query'] = $param['q'];
		}

		$this->load->library('pagination');
		$config['base_url'] = site_url('plugin/sms_credit/index');
		$config['total_rows'] = $this->sms_credit_model->get_users()->num_rows();
		$config['per_page'] = $this->Kalkun_model->get_setting()->row('paging');
		$config['cur_tag_open'] = '<span id="current">';
		$config['cur_tag_close'] = '</span>';
		$config['uri_segment'] = 4;
		$this->pagination->initialize($config);
		$data['pagination_links'] = $this->pagination->create_links();
		$param['limit'] = $config['per_page'];
		$param['offset'] = $this->uri->segment(4, 0);

		$data['main'] = 'index';
		$data['title'] = 'Users Credit';
		$data['users'] = $this->sms_credit_model->get_users($param);
		$data['packages'] = $this->sms_credit_model->get_packages();

		$this->load->view('main/layout', $data);
	}

	// --------------------------------------------------------------------

	/**
	 * Add Users
	 *
	 * Add an User with packages
	 *
	 * @access  public
	 */
	function add_users()
	{
		if ($_POST)
		{
			if (empty($this->input->post('id_user')))
			{
				// add user
				$this->sms_credit_model->add_users();
			}
			else
			{
				// edit user
				$this->sms_credit_model->change_users_package();
			}

			redirect('plugin/sms_credit');
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Delete Users
	 *
	 * Delete an User
	 *
	 * @access  public
	 */
	function delete_users()
	{
		if ($_POST)
		{
			$id = intval($this->input->post('id'));
			$this->sms_credit_model->delete_users($id);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Packages
	 *
	 * Display list of all packages
	 *
	 * @access  public
	 */
	function packages()
	{
		$this->load->library('pagination');
		$config['base_url'] = site_url('plugin/sms_credit/packages');
		$config['total_rows'] = $this->sms_credit_model->get_packages()->num_rows();
		$config['per_page'] = $this->Kalkun_model->get_setting()->row('paging');
		$config['cur_tag_open'] = '<span id="current">';
		$config['cur_tag_close'] = '</span>';
		$config['uri_segment'] = 4;
		$this->pagination->initialize($config);
		$data['pagination_links'] = $this->pagination->create_links();
		$param['limit'] = $config['per_page'];
		$param['offset'] = $this->uri->segment(4, 0);

		if ($_POST)
		{
			$data['query'] = $this->input->post('query');
			$data['packages'] = $this->sms_credit_model->search_packages($data['query']);
		}
		else
		{
			$data['packages'] = $this->sms_credit_model->get_packages($param);
		}

		$data['main'] = 'packages';
		$data['title'] = 'Credit Package';
		$this->load->view('main/layout', $data);
	}

	// --------------------------------------------------------------------

	/**
	 * Add Packages
	 *
	 * Add Packages
	 *
	 * @access  public
	 */
	function add_packages()
	{
		if ($_POST)
		{
			$param['id_credit_template'] = $this->input->post('id_package');

			if (empty($param['id_credit_template']))
			{
				unset($param['id_credit_template']);
			}

			$param['template_name'] = trim($this->input->post('package_name'));
			$param['sms_numbers'] = trim($this->input->post('sms_amount'));
			$this->sms_credit_model->add_packages($param);
			redirect('plugin/sms_credit/packages');
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Delete Packages
	 *
	 * Delete Packages
	 *
	 * @access  public
	 */
	function delete_packages()
	{
		if ($_POST)
		{
			$id = intval($this->input->post('id'));
			$this->sms_credit_model->delete_packages($id);
		}
	}
}
