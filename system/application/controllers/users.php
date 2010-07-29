<?php
Class Users extends MY_Controller 
{	
	function Users()
	{
		parent::MY_Controller();
		
		// check level
		if($this->session->userdata('level')!='admin')
		{
			$this->session->set_flashdata('notif', 'Access denied');
			redirect('/');
		}
		
		$this->load->model('User_model');
	}
	
	function index()
	{
		$data['title'] = 'Users';
		$this->load->library('pagination');
		$config['base_url'] = site_url().'/users/index/';
		$config['total_rows'] = $this->User_model->getUsers(array('option' => 'all'))->num_rows();
		$config['per_page'] = $this->Kalkun_model->getSetting()->row('paging');
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

	function add_user()
	{
		$type = $this->input->post('type');
		$data['tmp'] = "";
		
		if($type=='edit')
		{
			$id_user = $this->input->post('param1');
		 	$data['users'] = $this->User_model->getUsers(array('option' => 'by_iduser', 'id_user' => $id_user));
		}
		$this->load->view('main/users/add_user', $data);	
	}	
	
	function add_user_process()
	{
		$this->User_model->adduser();
		if($this->input->post('id_user')) echo "<div class=\"notif\">User has been updated.</div>";
		else echo "<div class=\"notif\">User has been added.</div>";
	}	
	
	function del_users()
	{
		// get and delete all user_outbox
		$res = $this->Message_model->getUserOutbox($this->input->post('id_user'));
		foreach($res->result as $tmp) $this->Message_model->delMessages('single', 'outbox', 'outbox', $tmp->id_outbox);
		
		// get and delete all user_inbox
		$res = $this->Message_model->getUserInbox($this->input->post('id_user'));
		foreach($res->result as $tmp) $this->Message_model->delMessages('single', 'inbox', 'permanent', $tmp->id_inbox);

		// get and delete all user_sentitems
		$res = $this->Message_model->getUserSentitems($this->input->post('id_user'));
		foreach($res->result as $tmp) $this->Message_model->delMessages('single', 'sentitems', 'permanent', $tmp->id_sentitems);
		
		// delete the rest (user, user_settings, pbk, pbk_groups, user_folders, sms_used)
		$this->User_model->delUsers($this->input->post('id_user'));	
	}
}	
?>