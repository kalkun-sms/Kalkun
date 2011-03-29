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
 * Login Class
 *
 * @package		Kalkun
 * @subpackage	Login
 * @category	Controllers
 */
class Login extends Controller 
{

	/**
	 * Constructor
	 *
	 * @access	public
	 */	
	function Login()
	{
		parent::controller();
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
		if($_POST) $this->Kalkun_model->login();
		$this->load->view('main/login');
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
}

/* End of file login.php */
/* Location: ./application/controllers/login.php */ 