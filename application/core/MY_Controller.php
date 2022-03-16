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
 * MY_Controller Class
 *
 * Base controller
 *
 * @package		Kalkun
 * @subpackage	Base
 * @category	Controllers
 */
class MY_Controller  extends CI_Controller {

	/**
	 * Constructor
	 *
	 * @access	public
	 */
	function __construct($login = TRUE)
	{
		parent::__construct();

		set_error_handler(function($errno, $errstr, $errfile, $errline) {
			throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
		});

		// installation mode
		if (file_exists(FCPATH.'install'))
		{
			redirect('install');
		}

		$this->load->library('session');
		$this->load->database();

		if ($login)
		{
			// session check
			if ($this->session->userdata('loggedin') === NULL)
			{
				if ($this->input->post('idiom') !== NULL)
				{
					redirect('login?l='.$this->input->post('idiom'));
				}
				if ($this->input->get('l') !== NULL)
				{
					redirect('login?l='.$this->input->get('l'));
				}
				$this->session->set_flashdata('bef_login_post_data', $this->input->post());

				$request_uri_qry_string = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
				if ( ! empty($request_uri_qry_string))
				{
					$request_uri_qry_string = '?'.$request_uri_qry_string;
				}
				redirect('login?r_url='.urlencode(current_url().$request_uri_qry_string));
			}

			$this->load->model('Kalkun_model');

			// language
			$this->load->helper('i18n');
			$lang = $this->Kalkun_model->get_setting()->row('language');
			$this->lang->load('kalkun', $lang);
			$this->lang->load('date', $lang);

			// Message routine
			$this->_message_routine();
		}
	}

	function _message_routine()
	{
		$this->load->model('User_model');
		$this->load->model('Message_model');
		$uid = $this->session->userdata('id_user');

		$outbox = $this->Message_model->get_user_outbox($uid);
		foreach ($outbox->result() as $tmp)
		{
			$id_message = $tmp->id_outbox;

			// if still on outbox, means message not delivered yet
			if ($this->Message_model->get_messages(array('id_message' => $id_message, 'type' => 'outbox'))->num_rows() > 0)
			{
				// do nothing
			}
			// if exist on sentitems then update sentitems ownership, else delete user_outbox
			else
			{
				if ($this->Message_model->get_messages(array('id_message' => $id_message, 'type' => 'sentitems'))->num_rows() > 0)
				{
					$this->Message_model->insert_user_sentitems($id_message, $uid);
					$this->Message_model->delete_user_outbox($id_message);
				}
				else
				{
					$this->Message_model->delete_user_outbox($id_message);
				}
			}
		}
	}
}
