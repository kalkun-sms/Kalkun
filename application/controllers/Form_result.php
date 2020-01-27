<?php
class Form_result extends CI_Controller
{
	public function index($ret)
	{
		$this->load->view('main/form_result', array('result' => base64_decode(urldecode($ret))));
	}
}
?>
