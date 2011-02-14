<?php
Class Member extends MY_Controller {
	
	function Member()
	{
		parent::MY_Controller();			
		
		// session check
		if($this->session->userdata('loggedin')==NULL) redirect('login');
		
		$this->load->database();
		$this->lang->load('kalkun', $this->Kalkun_model->getSetting()->row('language'));
	}
	
	
	function index()
	{
		$this->load->model('Member_model');
		$data['main'] = 'main/member/index';
		$data['title'] = 'Member';
		$data['total_member'] = $this->Member_model->get_member('total')->row('count');
		$data['member'] = $this->Member_model->get_member('all')->result_array();
		
		$this->load->view('main/layout', $data);
	}
}
?>