<?php
Class Daemon extends Controller {
				
	function Daemon()
	{	
		// Commented this for allow access from other machine
		// if($_SERVER['REMOTE_ADDR']!='127.0.0.1') exit("Access Denied.");		
						
		parent::controller();
	}

	// ================================================================
	// MESSAGE ROUTINE
	// ================================================================
	
	function message_routine()
	{
		$this->load->model('User_model');
		
		// ===================================================================
		// INBOX
		// ===================================================================
		
		// get unProcessed message
		$message = $this->Message_model->getUnprocessed();
		
		foreach($message->result() as $tmp_message):
		
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
		foreach($users->result() as $tmp_user)
		{
			$tag = "@".$tmp_user->username;
			$msg_word = array();
			$msg_word = explode(" ", $tmp_message->TextDecoded);
			$check = in_array($tag, $msg_word);
						
			// update ownership
			if($check!==false) { $this->Message_model->updateOwner($tmp_message->ID, $tmp_user->id_user); break; }
		}
		
		// if no matched username, set owner to Inbox Master
		if($check===false) $this->Message_model->updateOwner($tmp_message->ID, $this->config->item('inbox_owner_id'));
		
		// simple autoreply
		if($this->config->item('simple_autoreply'))
		{
			$data['coding'] = 'default';
			$data['class'] = '1';
			$data['dest'] = $tmp_message->SenderNumber;
			$data['date'] = date('Y-m-d H:i:s');
			$data['message'] = $this->config->item('simple_autoreply_msg');
			$data['delivery_report'] = 'default';
			$data['uid'] = $this->config->item('simple_autoreply_uid');	
			$this->Message_model->sendMessages($data);	
		}			
		
		// update Processed
		$this->Message_model->updateProcessed($tmp_message->ID);
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
	
	// ================================================================
	// SERVER ALERT
	// ================================================================
	
	function server_alert_engine()
	{
		// check plugin status
		$tmp_stat = $this->Plugin_model->getPluginStatus('server_alert');
		
		if($tmp_stat=='true')
		{
			$tmp_data = $this->Plugin_model->getServerAlert('active');
			foreach($tmp_data->result() as $tmp):
				$fp = fsockopen($tmp->ip_address, $tmp->port_number, $errno, $errstr, 60);
				if(!$fp)
				{
					$data['message'] = $tmp->respond_message."\n\nKalkun Server Alert";
					$data['date'] = date('Y-m-d H:i:s');
					$data['dest'] = $tmp->phone_number;
					$data['delivery_report'] = $this->Kalkun_model->getSetting('delivery_report', 'value')->row('value');
					$data['class'] = '1';
					
					$this->Message_model->sendMessages($data);
					log_message('info', 'Kalkun server alert=> Alert Name: '.$tmp->alert_name.', Dest: '.$tmp->phone_number);
					$this->Plugin_model->changeState($tmp->id_server_alert, 'false');
				} 
			endforeach;
		}
	}
}
?>
