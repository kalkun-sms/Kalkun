<?php
Class Login extends Controller 
{
	function Login()
	{
		parent::controller();
		$this->load->library('session');		
		$this->load->database();
		$this->load->model('Kalkun_model');	
	}

	function index()
	{		
		if($_POST) $this->Kalkun_model->Login();
		$this->load->view('main/login');
	}
	
	function logout()
	{
		$this->session->sess_destroy();
		redirect('login');
	}
}
?>