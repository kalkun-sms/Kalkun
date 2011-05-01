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
 * Daemon Class
 *
 * @package		Kalkun
 * @subpackage	Daemon
 * @category	Controllers
 */
class Daemon extends Controller {

	/**
	 * Constructor
	 *
	 * @access	public
	 */				
	function Daemon()
	{	
		// Commented this for allow access from other machine
		// if($_SERVER['REMOTE_ADDR']!='127.0.0.1') exit("Access Denied.");		
						
		parent::controller();
	}

	// --------------------------------------------------------------------
	
	/**
	 * Message routine
	 *
	 * Process the new/unprocessed incoming sms
	 * Called by shell/batch script on Gammu RunOnReceive directive
	 *
	 * @access	public   		 
	 */
	function message_routine()
	{
		$this->load->model('User_model');
        $this->load->model('Spam_model');
		
		// get unProcessed message
		$message = $this->Message_model->get_messages(array('processed' => FALSE));

		foreach($message->result() as $tmp_message)
		{	
			// sms content
			if($this->config->item('sms_content'))
			{
				$this->_sms_content($tmp_message->TextDecoded, $tmp_message->SenderNumber);
			}	
			
			// check @username tag
			$users = $this->User_model->getUsers(array('option' => 'all'));
			foreach($users->result() as $tmp_user)
			{
				$tag = "@".$tmp_user->username;
				$msg_word = array();
				$msg_word = explode(" ", $tmp_message->TextDecoded);
				$check = in_array($tag, $msg_word);
							
				// update ownership
				if($check!==false) { $this->Message_model->update_owner($tmp_message->ID, $tmp_user->id_user); $msg_user =  $tmp_user->id_user;  break; }
			}
			
			// if no matched username, set owner to Inbox Master
			if($check===false){ $this->Message_model->update_owner($tmp_message->ID, $this->config->item('inbox_owner_id'));  $msg_user =  $this->config->item('inbox_owner_id');  }
			
            //check for spam
            if($this->Spam_model->apply_spam_filter($tmp_message->ID,$tmp_message->TextDecoded))
                continue; ////is spam do not process later part
                       
			// simple autoreply
			if($this->config->item('simple_autoreply'))
			{
				$this->_simple_autoreply($tmp_message->SenderNumber);				
			}	

            // external script
			if($this->config->item('ext_script_state'))
			{
				$this->_external_script($tmp_message->SenderNumber, $tmp_message->TextDecoded, $tmp_message->ID);				
			}					
			
			// update Processed
			$id_message[0] = $tmp_message->ID;
			$multipart = array('type' => 'inbox', 'option' => 'check', 'id_message' => $id_message[0]);
			$tmp_check = $this->Message_model->get_multipart($multipart);
			if($tmp_check->row('UDH')!='')
			{
				$multipart = array('option' => 'all', 'udh' => substr($tmp_check->row('UDH'),0,8));	
				$multipart['phone_number'] = $tmp_check->row('SenderNumber');
				$multipart['type'] = 'inbox';				
				foreach($this->Message_model->get_multipart($multipart)->result() as $part):
				$id_message[] = $part->ID;
				endforeach;	
			}		
            $this->Message_model->update_processed($id_message);
            $this->Kalkun_model->add_sms_used($msg_user,'in');
            
            // sms to email
			$this->_sms2email($tmp_message->TextDecoded, $tmp_message->SenderNumber , $msg_user);				
		 
            
		}	
	}

    


	// --------------------------------------------------------------------
	
	/**
	 * SMS content
	 *
	 * Process the SMS content procedure
	 *
	 * @access	private   		 
	 */
	function _sms_content($message, $number)
	{
		list($code) = explode(" ", $message);
		$reg_code = $this->config->item('sms_content_reg_code');
		$unreg_code = $this->config->item('sms_content_unreg_code');
		if (strtoupper($code)==strtoupper($reg_code))
		{ 
			$this->_register_member($number);
		}
		else if (strtoupper($code)==strtoupper($unreg_code))
		{
			$this->_unregister_member($number);
		}
	}

	// --------------------------------------------------------------------
	
	/**
	 * Register member
	 *
	 * Register member's phone number
	 *
	 * @access	private   		 
	 */
	function _register_member($number)
	{
		$this->load->model('Member_model');
		
		//check if number not registered
		if($this->Member_model->check_member($number)==0)
		$this->Member_model->add_member($number);
	}

	// --------------------------------------------------------------------
	
	/**
	 * Unregister member
	 *
	 * Unregister member's phone number
	 *
	 * @access	private   		 
	 */	
	function _unregister_member($number)
	{
		$this->load->model('Member_model');
		
		//check if already registered
		if($this->Member_model->check_member($number)==1)
		$this->Member_model->remove_member($number);
	}	

	// --------------------------------------------------------------------
	
	/**
	 * Simple autoreply
	 *
	 * Send autoreply message
	 *
	 * @access	private   		 
	 */
	function _simple_autoreply($phone_number)
	{
		$data['coding'] = 'default';
		$data['class'] = '1';
		$data['dest'] = $phone_number;
		$data['date'] = date('Y-m-d H:i:s');
		$data['message'] = $this->config->item('simple_autoreply_msg');
		$data['delivery_report'] = 'default';
		$data['uid'] = $this->config->item('simple_autoreply_uid');	
		$this->Message_model->send_messages($data);				
	} 

	// --------------------------------------------------------------------
	
	/**
	 * External script
	 *
	 * Execute external script if condition match
	 *
	 * @access	private   		 
	 */	
	function _external_script($phone=NULL, $content=NULL, $id=NULL)
	{
		$shell_path = $this->config->item('ext_script_path');
				
		// Load all rules	
		foreach($this->config->item('ext_script') as $rule)
		{
			$script_name = $rule['name'];
			$value=$parameter="";
			
			// evaluate rule key
			switch($rule['key'])
			{
				case 'sender':
					$value = $phone;
				break;
				
				case 'content':
					$value = $content;
				break;
			}
			
			// evaluate rule type
			switch($rule['type'])
			{
				case 'match':
					$is_valid = $this->_is_match($rule['value'], $value);
				break;
				
				case 'contain':
					$is_valid = $this->_is_contain($rule['value'], $value);
				break;
			}
			
			// if we got valid rules
			if ($is_valid)
			{
				// build extra parameters
				if (!empty($rule['parameter']))
				{
					$valid_param = array('phone','content','id');
					$param = explode("|", $rule['parameter']);
					
					foreach ($param as $tmp)
					{
						if (in_array($tmp, $valid_param))
						{
							$parameter.=" ".${$tmp};
						}
					}
				}
				
				// execute it
				echo $shell_path." ".$script_name." ".$parameter;
			}
		}
		
	}	 
	/**
     *  function  _sms2email
     * 
     *  Function for sms to email feature
     *  
     *  @access	private
     **/ 
    function _sms2email($message , $from, $msg_user)
    {
        $this->load->library('email');
        $this->load->model('kalkun_model');
        $this->load->model('phonebook_model');
        $active  = $this->Kalkun_model->get_setting($msg_user)->row('email_forward');
        if($active != 'true') return;         
        $this->email->initialize($this->config);
        $mail_to = $this->Kalkun_model->get_setting($msg_user)->row('email_id');            
        $qry = $this->Phonebook_model->get_phonebook(array('option'=>'bynumber','number'=>$from , 'id_user' =>$msg_user));
		if($qry->num_rows()!=0) $from = $qry->row('Name');
        $this->email->from($this->config->item('mail_from'), $from);
        $this->email->to($mail_to); 
        $this->email->subject('Kalkun New SMS');
        $this->email->message($message."\n\n". "- ".$from);	
        $this->email->send();

    }
    
	function _is_match($subject, $matched)
	{
		if ($subject===$matched) return TRUE;
		else return FALSE;
	}
	
	function _is_contain($subject, $matched)
	{
		if (!strstr($matched, $subject)) return FALSE;
		else return TRUE;
	}	
	
	// --------------------------------------------------------------------
	
	/**
	 * Server alert engine
	 *
	 * Scan host port and send SMS alert if the host is down
	 *
	 * @access	public   		 
	 */		
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
					$data['delivery_report'] = $this->Kalkun_model->get_setting('delivery_report', 'value')->row('value');
					$data['class'] = '1';
					
					$this->Message_model->sendMessages($data);
					log_message('info', 'Kalkun server alert=> Alert Name: '.$tmp->alert_name.', Dest: '.$tmp->phone_number);
					$this->Plugin_model->changeState($tmp->id_server_alert, 'false');
				} 
			endforeach;
		}
	}

	// --------------------------------------------------------------------
	
	/**
	 * Blacklist number
	 *
	 * Force delete SMS if coming from blacklist phone number
	 *
	 * @access	private   		 
	 */	
	function _blacklist_number()
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
		$this->Message_model->update_processed($tmp_message->ID);
		endforeach;
		}		
	}	
}

/* End of file daemon.php */
/* Location: ./application/controllers/daemon.php */ 