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
		if ($this->config->item('disable_outgoing'))
		{
			$data['alerts'][] = tr_raw('Outgoing SMS disabled. Contact system administrator.');
		}
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
				$prefix = tr_raw('date_week').' ';
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

		$result = [
			'labels' => array_reverse($x),
			'datasets' => [
				[
					'label' => tr_raw('Outgoing SMS'),
					'backgroundColor' => '#21759B',
					'data' => array_reverse($yout),
					'borderWidth' => 1,
				],
				[
					'label' => tr_raw('Incoming SMS'),
					'backgroundColor' => '#639F45',
					'data' => array_reverse($yin),
					'borderWidth' => 1,
				],
			],
		];

		header('Content-type: application/json');
		echo json_encode($result);
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
		$status = $this->Kalkun_model->get_gammu_info('last_activity')->row('UpdatedInDB');
		$response['signal'] = intval($this->Kalkun_model->get_gammu_info('phone_signal')->row('Signal'));
		$response['signal_lbl'] = tr_raw('{0}%', NULL, $this->Kalkun_model->get_gammu_info('phone_signal')->row('Signal'));
		$response['battery'] = intval($this->Kalkun_model->get_gammu_info('phone_battery')->row('Battery'));
		$response['battery_lbl'] = tr_raw('{0}%', NULL, $this->Kalkun_model->get_gammu_info('phone_battery')->row('Battery'));
		if ( ! empty($status))
		{
			$status = get_modem_status($status, $this->config->item('modem_tolerant'));
			if ($status === 'connect')
			{
				$response['status'] = 'connected';
				$response['status_lbl'] = tr_raw('Connected');
			}
			else
			{
				$response['status'] = 'disconnected';
				$response['status_lbl'] = tr_raw('Disconnected');
			}
		}
		else
		{
			$response['status'] = 'Unknown';
			$response['status_lbl'] = tr_raw('Unknown');
		}

		if (is_ajax())
		{
			header('Content-type: application/json');
			echo json_encode($response);
		}
		else
		{
			$this->load->view('main/notification');
		}
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
		$unread_count['in'] = $this->Message_model->get_messages([
			'readed' => FALSE,
			'uid' => $this->session->userdata('id_user'),
		])->num_rows();
		$unread_count['draft'] = 0;
		$unread_count['spam'] = $this->Message_model->get_messages([
			'readed' => FALSE,
			'id_folder' => '6',
			'uid' => $this->session->userdata('id_user'),
		])->num_rows();

		header('Content-type: application/json');
		echo json_encode($unread_count);
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
		redirect($this->input->post('source_url') !== NULL ? $this->input->post('source_url') : '');
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
			if ($option === 'password')
			{
				if ($this->config->item('demo_mode') && intval($this->session->userdata('id_user')) === 1)
				{
					$this->session->set_flashdata('notif', tr_raw('Password modification forbidden in demo mode.'));
					redirect('settings/'.$option);
				}
				if ( ! password_verify($this->input->post('current_password'), $this->Kalkun_model->get_setting()->row('password')))
				{
					$this->session->set_flashdata('notif', tr_raw('Wrong password'));
					redirect('settings/'.$option);
				}
			}
			else
			{
				if ($option === 'personal')
				{
					if ($this->input->post('username') !== $this->session->userdata('username'))
					{
						if ($this->Kalkun_model->check_setting(array('option' => 'username', 'username' => $this->input->post('username')))->num_rows > 0)
						{
							$this->session->set_flashdata('notif', tr_raw('Username already taken'));
							redirect('settings/'.$option);
						}
					}
				}
			}
			$this->Kalkun_model->update_setting($option);
			if ($this->config->item('demo_mode')
				&& intval($this->session->userdata('id_user')) === 1
				&& $this->input->post('username') !== 'kalkun')
			{
				$this->session->set_flashdata('notif', tr_raw('Settings saved successfully (except username for kalkun user which can\'t be changed in demo mode)'));
			}
			else
			{
				$this->session->set_flashdata('notif', tr_raw('Settings saved successfully.'));
			}
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

	// --------------------------------------------------------------------

	/**
	 * Check phone number validity
	 *
	 * returns a json string used by jquery validation plugin
	 * "true" if phone number is valid
	 * "an error message" if not
	 */
	function phone_number_validation()
	{
		$result = is_phone_number_valid($this->input->get_post('phone'), $this->input->get_post('region'));

		if ($result === TRUE)
		{
			$result = 'true';
		}

		header('Content-type: application/json');
		echo json_encode($result);
	}

	// --------------------------------------------------------------------

	/**
	 * Check multiple phone number validity
	 *
	 * returns a json string used by jquery validation plugin
	 * "true" if all phone numbers are valid
	 * "an error message with the faulty number" if not
	 */
	function phone_number_validation_multiple()
	{
		$tmp_dest = explode(',', $this->input->get_post('phone'));
		foreach ($tmp_dest as $key => $val)
		{
			$result = is_phone_number_valid($val, $this->input->get_post('region'));
			if ($result !== TRUE)
			{
				header('Content-type: application/json');
				echo json_encode($result.' ('.trim($val).')');
				return;
			}
		}
		header('Content-type: application/json');
		echo json_encode('true');
	}

	function get_csrf_hash()
	{
		header('Content-type: application/json');
		echo json_encode($this->security->get_csrf_hash());
	}
}
