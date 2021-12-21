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
 * Kalkun Class
 *
 * @package		Kalkun
 * @subpackage	Base
 * @category	Controllers
 */
class Kalkun extends MY_Controller {

	/**
	 * Constructor
	 *
	 * @access	public
	 */
	function __construct()
	{
		parent::__construct();
	}

	// --------------------------------------------------------------------

	/**
	 * Index/Dashboard
	 *
	 * Display dashboard page
	 *
	 * @access	public
	 */
	function index()
	{
		$this->load->model('Phonebook_model');
		$data['main'] = 'main/dashboard/home';
		$data['title'] = 'Dashboard';
		$data['data_url'] = site_url('kalkun/get_statistic');
		if ($this->config->item('disable_outgoing'))
		{
			$data['alerts'][] = '<div class="warning">'.lang('kalkun_outgoing_sms_disabled_contact_sysadmin').'</div>';
		}
		$this->load->view('main/layout', $data);
	}

	// --------------------------------------------------------------------

	/**
	 * About
	 *
	 * Display about page
	 *
	 * @access	public
	 */
	function about()
	{
		$data['main'] = 'main/about';
		$this->load->view('main/layout', $data);
	}

	// --------------------------------------------------------------------

	/**
	 * Get Statistic
	 *
	 * Get statistic data that used to render the graph
	 *
	 * @param string (days, weeks, months)
	 * @access	public
	 */
	function get_statistic($type = 'days')
	{
		// count number of days
		switch ($type)
		{
			case 'days':
			default:
				$days = 10;
				$format = 'M-d';
				break;

			case 'weeks':
				$days = 30;
				$format = 'W';
				$prefix = ucwords(lang('kalkun_week')).' ';
				break;

			case 'months':
				$days = 60;
				$format = 'M-Y';
				break;
		}

		// generate data points
		$x = array();
		for ($i = 0; $i <= $days; $i++)
		{
			$key = date($format, mktime(0, 0, 0, date('m'), date('d') - $i, date('Y')));

			if (isset($prefix))
			{
				$key = $prefix.$key;
			}

			if ( ! isset($yout[$key]))
			{
				$yout[$key] = 0;
			}

			if ( ! isset($yin[$key]))
			{
				$yin[$key] = 0;
			}

			if ( ! in_array($key, $x))
			{
				$x[] = $key;
			}

			$param['sms_date'] = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - $i, date('Y')));
			if ($this->session->userdata('level') !== 'admin')
			{
				$param['user_id'] = $this->session->userdata('id_user');
			}
			$yout[$key] += $this->Kalkun_model->get_sms_used('date', $param, 'out');
			$yin[$key] += $this->Kalkun_model->get_sms_used('date', $param, 'in');
		}

		$yout = array_values($yout);
		$yin = array_values($yin);
		$points = count($x) - 1;

		echo '{
			"labels": '.json_encode(array_reverse($x)).',
			"datasets": [
				{
					"label": "'.lang('kalkun_outgoing_sms').'",
					"backgroundColor": "#21759B",
					"data": '.json_encode(array_reverse($yout)).',
					"borderWidth": 1
				}
				,
				{
					"label": "'.lang('kalkun_incoming_sms').'",
					"backgroundColor": "#639F45",
					"data": '.json_encode(array_reverse($yin)).',
					"borderWidth": 1
				}
			]
		}';
	}

	// --------------------------------------------------------------------

	/**
	 * Notification
	 *
	 * Display notification
	 * Modem status
	 * Used by the autoload function and called via AJAX.
	 *
	 * @access	public
	 */
	function notification()
	{
		$this->load->view('main/notification');
	}

	// --------------------------------------------------------------------

	/**
	 * Unread Count
	 *
	 * Show unread inbox/spam/draft and alert when new sms arrived
	 * Used by the autoload function and called via AJAX.
	 *
	 * @access	public
	 */
	function unread_count()
	{
		$tmp_unread = $this->Message_model->get_messages(array('readed' => FALSE, 'uid' => $this->session->userdata('id_user')))->num_rows();
		$in = ($tmp_unread > 0) ? '('.$tmp_unread.')' : '';

		$tmp_unread = 0;
		$draft = ($tmp_unread > 0) ? '('.$tmp_unread.')' : '';

		$tmp_unread = $this->Message_model->get_messages(array('readed' => FALSE, 'id_folder' => '6', 'uid' => $this->session->userdata('id_user')))->num_rows();
		$spam = ($tmp_unread > 0) ? '('.$tmp_unread.')' : '';

		echo $in. '/' . $draft . '/' . $spam;
	}

	// --------------------------------------------------------------------

	/**
	 * Add Folder
	 *
	 * Add custom folder
	 *
	 * @access	public
	 */
	function add_folder()
	{
		$this->Kalkun_model->add_folder();
		redirect($this->input->post('source_url'));
	}

	// --------------------------------------------------------------------

	/**
	 * Rename Folder
	 *
	 * Rename custom folder
	 *
	 * @access	public
	 */
	function rename_folder()
	{
		$this->Kalkun_model->rename_folder();
		redirect($this->input->post('source_url'));
	}

	// --------------------------------------------------------------------

	/**
	 * Delete Folder
	 *
	 * Delete custom folder
	 *
	 * @access	public
	 */
	function delete_folder($id_folder = NULL)
	{
		$this->Kalkun_model->delete_folder($id_folder);
		redirect('/', 'refresh');
	}

	// --------------------------------------------------------------------

	/**
	 * Settings
	 *
	 * Display and handle change on settings/user preference
	 *
	 * @access	public
	 */
	function settings($type = NULL)
	{
		$this->load->helper('country_dial_code_helper');
		$data['title'] = 'Settings';
		$valid_type = array('general', 'personal', 'appearance', 'password', 'save', 'filters');
		if ( ! in_array($type, $valid_type))
		{
			show_404();
		}

		if ($_POST && $type === 'save')
		{
			$option = $this->input->post('option');
			// check password
			if ($option === 'password' && ! password_verify($this->input->post('current_password'), $this->Kalkun_model->get_setting()->row('password')))
			{
				$this->session->set_flashdata('notif', lang('kalkun_wrong_password'));
				redirect('settings/'.$option);
			}
			else
			{
				if ($option === 'personal')
				{
					if ($this->input->post('username') !== $this->session->userdata('username'))
					{
						if ($this->Kalkun_model->check_setting(array('option' => 'username', 'username' => $this->input->post('username')))->num_rows > 0)
						{
							$this->session->set_flashdata('notif', lang('kalkun_username_exists'));
							redirect('settings/'.$option);
						}
					}
				}
			}
			$this->Kalkun_model->update_setting($option);
			$this->session->set_flashdata('notif', lang('kalkun_settings_saved'));
			redirect('settings/'.$option);
		}

		if ($type === 'filters')
		{
			$data['filters'] = $this->Kalkun_model->get_filters($this->session->userdata('id_user'));
			$data['my_folders'] = $this->Kalkun_model->get_folders('all');
		}

		$data['main'] = 'main/settings/setting';
		$data['settings'] = $this->Kalkun_model->get_setting();
		$data['type'] = 'main/settings/'.$type;

		$this->load->view('main/layout', $data);
	}

	// --------------------------------------------------------------------

	/**
	 * Delete Filter
	 *
	 * @access	public
	 */
	function delete_filter($id_filter = NULL)
	{
		$this->Kalkun_model->delete_filter($id_filter);
	}
}
