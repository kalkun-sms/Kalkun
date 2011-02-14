<?php
class MY_Controller  extends Controller  {

function MY_Controller()
{
	parent::Controller();
	
	// installation mode
	if(file_exists("install")) redirect('install');	

	$this->load->library('session');
	// session check
	if($this->session->userdata('loggedin')==NULL) redirect('login');
	
	// language
	$lang = $this->Kalkun_model->getSetting()->row('language');
	$this->lang->load('kalkun', $lang);
		
	// Message routine
	$this->message_routine();
	
	// Message Plugin
	//$this->run_message_plugin();
}

private function run_message_plugin()
{
	//$this->blacklist_number();
}	

private function message_routine()
{
	$this->load->model('User_model');
	
	// ===================================================================
	// INBOX
	// ===================================================================
	
	// get unProcessed message
	$message = $this->Message_model->getUnprocessed();
	
	foreach($message->result() as $tmp_message):
	
	// user mention, move to user_inbox if contains @username
	// ... php stristr() function
	
	// sms content
	if($this->config->item('sms_content')):
	list($code) = explode(" ", $tmp_message->TextDecoded);
	$reg_code = $this->config->item('sms_content_reg_code');
	$unreg_code = $this->config->item('sms_content_unreg_code');
	if(strtoupper($code)==strtoupper($reg_code)) 
	$this->register_member($tmp_message->SenderNumber);
	else if(strtoupper($code)==strtoupper($unreg_code))
	$this->unregister_member($tmp_message->SenderNumber);
	endif;	
	
	// check @username tag
	$users = $this->User_model->getUsers(array('option' => 'all'));
	//print_r($users);
	foreach($users->result() as $tmp_user)
	{
		$tag = "@".$tmp_user->username;
		$check = strpos($tmp_message->TextDecoded, $tag);
		
		// update ownership
		if($check!==false) { $this->Message_model->updateOwner($tmp_message->ID, $tmp_user->id_user); break; }
	}
	
	// if no matched username
	if($check===false) $this->Message_model->updateOwner($tmp_message->ID, $this->config->item('inbox_owner_id'));
	
	// update Processed
	$this->Message_model->updateProcessed($tmp_message->ID);
	endforeach;
	
	
	// =============================
	// OUTBOX
	// =============================
	
	$outbox = $this->Message_model->getUserOutbox($this->session->userdata("id_user"));
	foreach($outbox->result() as $tmp):
	// if still on outbox, means message not delivered yet
	if($this->Message_model->checkOutbox($tmp->id_outbox)) { }
	// if exist on sentitems then update sentitems ownership, else delete user_outbox
	else if($this->Message_model->checkSentitems($tmp->id_outbox)) 
	{
		$this->Message_model->insertUserSentitems($tmp->id_outbox, $this->session->userdata("id_user"));
		$this->Message_model->deleteUserOutbox($tmp->id_outbox);
	}	
	else $this->Message_model->deleteUserOutbox($tmp->id_outbox);
	endforeach;
}

private function register_member($number)
{
	$this->load->model('Member_model');
	
	//check if number not registered
	if($this->Member_model->check_member($number)==0)
	$this->Member_model->add_member($number);
}

private function unregister_member($number)
{
	$this->load->model('Member_model');
	
	//check if already registered
	if($this->Member_model->check_member($number)==1)
	$this->Member_model->remove_member($number);
}	


private function blacklist_number()
{
	// check plugin status
	$tmp_stat = $this->Plugin_model->getPluginStatus('blacklist_number');
	
	if($tmp_stat=='true')
	{		
	// get Blacklist Number
	$number = $this->Plugin_model->getBlacklistNumber('all');
	
	// get unProcessed message
	$message = $this->Message_model->getMessages('inbox', 'unprocessed');
	
	foreach($message->result() as $tmp_message):
	foreach($number->result() as $tmp_number):
		if($tmp_message->SenderNumber==$tmp_number->phone_number)
		{
			$this->Message_model->delMessages('single', 'inbox', 'permanent', $tmp_message->ID);
			break;
		}
	endforeach;
	
	// update Processed
	$this->Message_model->updateProcessed($tmp_message->ID);
	endforeach;
	}		
}    

}

/* End of file MY_Controller.php */
/* Location: ./system/application/libraries/MY_Controller.php */ 
