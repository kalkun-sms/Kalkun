<?php
class Info_message extends CI_Controller
{
	public function index($ret)
	{
		$this->load->view('main/info_message', array('result' => base64_decode(urldecode($ret))));
	}
}
?>
