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
			$data['alerts'][] = '<div class="warning">'.tr('Outgoing SMS disabled. Contact system administrator.').'</div>';
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
				$prefix = tr('date_week').' ';
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
					'label' => tr('Outgoing SMS'),
					'backgroundColor' => '#21759B',
					'data' => array_reverse($yout),
					'borderWidth' => 1,
				],
				[
					'label' => tr('Incoming SMS'),
					'backgroundColor' => '#639F45',
					'data' => array_reverse($yin),
					'borderWidth' => 1,
				],
			],
		];

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
				$this->session->set_flashdata('notif', tr('Wrong password'));
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
							$this->session->set_flashdata('notif', tr('Username already taken'));
							redirect('settings/'.$option);
						}
					}
				}
			}
			$this->Kalkun_model->update_setting($option);
			$this->session->set_flashdata('notif', tr('Settings saved successfully.'));
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
		$result = 'false'; // Default to "false"
		try
		{
			$phone = $this->input->post('phone');
			$region = $this->input->post('region');

			// Check if is possible number
			$phoneNumberUtil = \libphonenumber\PhoneNumberUtil::getInstance();
			$region = ($region !== NULL) ? $region : $this->Kalkun_model->get_setting()->row('country_code');
			$phoneNumberObject = $phoneNumberUtil->parse($phone, $region);
			$is_possible = $phoneNumberUtil->isPossibleNumber($phoneNumberObject);

			// Check if is mobile number
			$type = $phoneNumberUtil->getNumberType($phoneNumberObject);
			$is_mobile = ($type === \libphonenumber\PhoneNumberType::MOBILE
				|| $type === \libphonenumber\PhoneNumberType::FIXED_LINE_OR_MOBILE);

			// Check if is possible short number
			$shortNumberUtil = \libphonenumber\ShortNumberInfo::getInstance();
			$is_possible_short = $shortNumberUtil->isPossibleShortNumber($phoneNumberObject);

			if ($is_possible && $is_mobile || $is_possible_short)
			{
				$result = 'true';
			}
			else
			{
				$result = tr('Please specify a valid mobile phone number');
			}
		}
		catch (Exception $e)
		{
			$result = $e->getMessage();
		}
		echo json_encode($result);
	}
}
