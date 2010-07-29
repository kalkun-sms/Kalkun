<?php
Class Plugin extends MY_Controller {
	
	function Plugin()
	{
		parent::MY_Controller();		
		
		// session check
		if($this->session->userdata('loggedin')==NULL) redirect('login');
								
		$this->load->database();						
		$this->lang->load('kalkun', $this->Kalkun_model->getSetting('language', 'value')->row('value'));
	}
	
	function index() 
	{
		$data['main'] = 'main/plugin/index';
		$this->load->view('main/layout', $data);
	}
	
	function change_status($name, $state)
	{
		$data = array('plugin_status' => $state);
		$this->db->where('plugin_name', $name);
		$this->db->update('plugin', $data);
		
		redirect('plugin/'.$name);
	}
	
	function sms_bomber()
	{
		$data['main'] = 'main/messages/compose';
		$data['pbkgroup'] = $this->Kalkun_model->getPhonebook('group');
		$this->load->view('main/layout', $data);		
	}



	//=================================================================
	// BLACKLIST NUMBER
	//=================================================================		
	
	function blacklist_number()
	{
		if($_POST) 
		{	
			if($this->input->post('editid_blacklist_number')) $this->Plugin_model->updateBlacklistNumber();
			else $this->Plugin_model->addBlacklistNumber();
			redirect('plugin/blacklist_number');
		}

		$this->load->library('pagination');
		$config['base_url'] = site_url().'/plugin/blacklist_number';
		$config['total_rows'] = $this->Plugin_model->getBlacklistNumber('count');
		$config['per_page'] = $this->Kalkun_model->getSetting('paging', 'value')->row('value');
		$config['cur_tag_open'] = '<span id="current">';
		$config['cur_tag_close'] = '</span>';		
		$config['uri_segment'] = 3;
		
		$this->pagination->initialize($config);
				
		$data['main'] = 'main/plugin/blacklist_number';
		$data['blacklist'] = $this->Plugin_model->getBlacklistNumber('paginate', $config['per_page'], $this->uri->segment(3,0));
		$data['number'] = $this->uri->segment(3,0)+1;
		$this->load->view('main/layout', $data);
	}
	
	function delete_blacklist_number($id)
	{
		$this->Plugin_model->delBlacklistNumber($id);
		redirect('plugin/blacklist_number');
	}
	
	
	
	//=================================================================
	// SERVER ALERT
	//=================================================================		
	
	function server_alert()
	{
		if($_POST) 
		{	
			if($this->input->post('editid_server_alert')) $this->Plugin_model->updateServerAlert();
			else $this->Plugin_model->addServerAlert();
			redirect('plugin/server_alert');
		}

		$this->load->library('pagination');
		$config['base_url'] = site_url().'/plugin/server_alert';
		$config['total_rows'] = $this->Plugin_model->getServerAlert('count');
		$config['per_page'] = $this->Kalkun_model->getSetting('paging', 'value')->row('value');
		$config['cur_tag_open'] = '<span id="current">';
		$config['cur_tag_close'] = '</span>';		
		$config['uri_segment'] = 3;
		
		$this->pagination->initialize($config);
				
		$data['main'] = 'main/plugin/server_alert';
		$data['alert'] = $this->Plugin_model->getServerAlert('paginate', $config['per_page'], $this->uri->segment(3,0));
		$data['number'] = $this->uri->segment(3,0)+1;
		$this->load->view('main/layout', $data);
	}
	
	function delete_server_alert($id)
	{
		$this->Plugin_model->delServerAlert($id);
		redirect('plugin/server_alert');
	}	
	
	function change_server_alert_state($id)
	{
		$this->Plugin_model->changeState($id, 'true');
		redirect('plugin/server_alert');
	}
}
