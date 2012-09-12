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
 * Users Class
 *
 * @package		Kalkun
 * @subpackage	Users
 * @category	Controllers
 */
class Users extends MY_Controller 
{	

	/**
	 * Constructor
	 *
	 * @access	public
	 */	
	function __construct()
	{
		parent::__construct();
		
		// check level
		if($this->session->userdata('level')!='admin')
		{
			$this->session->set_flashdata('notif', 'Access denied');
			redirect('/');
		}
		
		$this->load->model('User_model');
	}

	// --------------------------------------------------------------------
	
	/**
	 * Index
	 *
	 * Display list of all users
	 *
	 * @access	public   		 
	 */	
	function index()
	{
		$data['title'] = lang('tni_user_word');
		$this->load->library('pagination');
		$config['base_url'] = site_url().'/users/index/';
		$config['total_rows'] = $this->User_model->getUsers(array('option' => 'all'))->num_rows();
		$config['per_page'] = $this->Kalkun_model->get_setting()->row('paging');
		$config['cur_tag_open'] = '<span id="current">';
		$config['cur_tag_close'] = '</span>';
		$config['uri_segment'] = 3;
		
		$this->pagination->initialize($config);
		$param = array('option' => 'paginate', 'limit' => $config['per_page'], 'offset' => $this->uri->segment(3,0));		
		
		$data['main'] = 'main/users/index';
		if($_POST) $data['users'] = $this->User_model->getUsers(array('option' => 'search'));
		else $data['users'] = $this->User_model->getUsers($param);		
		
		$this->load->view('main/layout', $data);
	}

	// --------------------------------------------------------------------
	
	/**
	 * Add user
	 *
	 * Display Add/Update an user form
	 *
	 * @access	public   		 
	 */	
	function add_user()
	{
		$this->load->helper('form');
		$type = $this->input->post('type');
		$data['tmp'] = "";
		
		if($type=='edit')
		{
			$id_user = $this->input->post('param1');
		 	$data['users'] = $this->User_model->getUsers(array('option' => 'by_iduser', 'id_user' => $id_user));
		}
		$this->load->view('main/users/add_user', $data);	
	}	

	// --------------------------------------------------------------------
	
	/**
	 * Add user process
	 *
	 * Process the add/update user
	 *
	 * @access	public   		 
	 */	
	function add_user_process()
	{
		$this->User_model->adduser();
		if($this->input->post('id_user')) echo "<div class=\"notif\">User has been updated.</div>";
		else echo "<div class=\"notif\">User has been added.</div>";
	}	

	// --------------------------------------------------------------------
	
	/**
	 * Delete user
	 *
	 * Delete an user
	 * All data related to deleted user (sms, phonebook, preference, etc) also deleted 
	 *
	 * @access	public   		 
	 */		
	function delete_user()
	{
		$uid = $this->input->post('id_user');
		
		// get and delete all user_outbox
		$res = $this->Message_model->get_messages(array('uid' => $uid, 'type' => 'outbox'));
		foreach($res->result as $tmp) 
		{
			$param = array('type' => 'single', 'option' => 'outbox', 'id_message' => $tmp->id_outbox);
			$this->Message_model->delMessages($param);
		}
		
		// get and delete all user_inbox
		$res = $this->Message_model->get_messages(array('uid' => $uid, 'type' => 'inbox'));
		foreach($res->result as $tmp) 
		{
			$param = array('type' => 'single', 'option' => 'permanent', 'source' => 'inbox', 'id_message' => $tmp->id_inbox);
			$this->Message_model->delete_messages($param);
		}
		
		// get and delete all user_sentitems
		$res = $this->Message_model->delete_messages(array('uid' => $uid, 'type' => 'sentitems'));
		foreach($res->result as $tmp)
		{
			$param = array('type' => 'single', 'option' => 'permanent', 'source' => 'sentitems', 'id_message' => $tmp->id_sentitems);
			$this->Message_model->delete_messages($param);
		}
		
		// delete the rest (user, user_settings, pbk, pbk_groups, user_folders, sms_used)
		$this->User_model->delUsers($this->input->post('id_user'));	
	}
}	

/* End of file users.php */
/* Location: ./application/controllers/users.php */ 