<?php
/**
 * Kalkun
 * An open source web based SMS Management
 *
 * @package		Kalkun
 * @author		Kalkun Dev Team
 * @license		https://spdx.org/licenses/GPL-3.0-or-later.html
 * @link		https://kalkun-sms.github.io/
 */

// ------------------------------------------------------------------------

/**
 * Login Class
 *
 * @package		Kalkun
 * @subpackage	Login
 * @category	Controllers
 */
class Login extends CI_Controller {

	public $idiom = 'english';

	/**
	 * Constructor
	 *
	 * @access	public
	 */
	function __construct()
	{
		parent::__construct();

		// language
		$this->load->helper('i18n');
		$i18n = new MY_Lang();
		if ($this->input->post('idiom') !== NULL)
		{
			$this->idiom = $this->input->post('idiom');
		}
		else
		{
			if ($this->input->get('l') !== NULL)
			{
				$this->idiom = $this->input->get('l');
			}
			else
			{
				$this->idiom = $i18n->get_idiom();
			}
		}
		$this->lang->load('kalkun', $this->idiom);

		$this->load->library('session');
		$this->load->database();
		$this->load->model('Kalkun_model');
	}

	// --------------------------------------------------------------------

	/**
	 * Index
	 *
	 * Display login form and handle login process
	 *
	 * @access	public
	 */
	function index()
	{
		$this->load->helper('form');
		$this->session->set_flashdata(
			'bef_login_post_data',
			$this->session->flashdata('bef_login_post_data')
		);
		if ($_POST && empty($this->input->post('change_language')))
		{
			$this->Kalkun_model->login();
		}

		$data['idiom'] = $this->idiom;
		$data['language_list'] = $this->lang->kalkun_supported_languages();
		$this->load->view('main/login', $data);
	}

	// --------------------------------------------------------------------

	/**
	 * Logout
	 *
	 * Logout process, destroy user session
	 *
	 * @access	public
	 */
	function logout()
	{
		$this->session->sess_destroy();
		redirect('login');
	}

	// --------------------------------------------------------------------

	/**
	 * Forgot Password
	 *
	 * Forgot password form
	 *
	 * @access	public
	 */
	function forgot_password()
	{
		$this->load->model('Message_model');
		$this->load->helper('form');

		if ($_POST && empty($this->input->post('change_language')))
		{
			$token = $this->Kalkun_model->forgot_password();

			if ( ! $token)
			{
				// Remain silent
			}
			else
			{
				// Send token to user
				$data['class'] = '1';
				$data['dest'] = $token['phone'];
				$data['date'] = date('Y-m-d H:i:s');
				$data['message'] = tr_raw('To reset your Kalkun password please visit {0}', NULL, site_url('login/password_reset/'.$token['token']).'?l='.$this->idiom);
				$data['delivery_report'] = 'default';
				$data['uid'] = 1;
				$this->Message_model->send_messages($data);
			}
			if (empty($this->session->flashdata('errorlogin')))
			{
				$this->session->set_flashdata('errorlogin', tr_raw('If you are a registered user, a SMS has been sent to you.'));
			}
			redirect('login/forgot_password?l='.$this->idiom);
		}
		$data['language_list'] = $this->lang->kalkun_supported_languages();
		$data['idiom'] = $this->idiom;
		$this->load->view('main/forgot_password', $data);
	}

	// --------------------------------------------------------------------

	/**
	 * Password Reset
	 *
	 * Password reset form
	 *
	 * @access	public
	 */
	function password_reset($token = NULL)
	{
		$this->load->helper('form');

		if ($_POST && empty($this->input->post('change_language')))
		{
			$token = $this->input->post('token');
			$user_token = $this->Kalkun_model->valid_token($token);
			$this->Kalkun_model->update_password($user_token['id_user']);
			$this->Kalkun_model->delete_token($user_token['id_user']);
			$this->session->set_flashdata('errorlogin', tr_raw('Password changed successfully.'));
			redirect('login?l='.$this->idiom);
		}

		if ( ! $this->Kalkun_model->valid_token($token))
		{
			$this->session->set_flashdata('errorlogin', tr_raw('Token invalid.'));
			redirect('login/forgot_password?l='.$this->idiom);
		}
		else
		{
			$data['token'] = $token;
			$data['idiom'] = $this->idiom;
			$data['language_list'] = $this->lang->kalkun_supported_languages();
			$data['idiom'] = $this->idiom;
			$this->load->view('main/password_reset', $data);
		}
	}
}
