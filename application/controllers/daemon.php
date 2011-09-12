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
		$this->load->library('Plugins');
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
		// get unProcessed message
		$message = $this->Message_model->get_messages(array('processed' => FALSE));

		foreach($message->result() as $tmp_message)
		{			
			// check for spam
			$this->load->model('Spam_model');
            if($this->Spam_model->apply_spam_filter($tmp_message->ID,$tmp_message->TextDecoded))
            {
                continue; ////is spam do not process later part
            }
            
			// hook for incoming message (before ownership)
			$status = do_action("message.incoming.before", $tmp_message);

            // message deleted, do not process later part
            if(isset($status) AND $status=='break')
            {
            	continue;
            }

            // set message's ownership
			$msg_user = $this->_set_ownership($tmp_message);
			$this->Kalkun_model->add_sms_used($msg_user,'in');
			
            // hook for incoming message (after ownership)
            $tmp_message->msg_user = $msg_user;
			$status = do_action("message.incoming.after", $tmp_message);
			
			// message deleted, do not process later part
			if(isset($status) AND $status=='break')
			{
				continue;
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
		}
	}

	// --------------------------------------------------------------------
	
	/**
	 * Set Ownership
	 *
	 * Set ownership for incoming message
	 *
	 * @access	private	 
	 */
    function _set_ownership($tmp_message)
    {
    	$this->load->model('User_model');
    	
		// check @username tag
		$users = $this->User_model->getUsers(array('option' => 'all'));
		foreach ($users->result() as $tmp_user)
		{
			$tag = "@".$tmp_user->username;
			$msg_word = array();
			$msg_word = explode(" ", $tmp_message->TextDecoded);
			$check = in_array($tag, $msg_word);
						
			// update ownership
			if($check!==false)
			{
				$this->Message_model->update_owner($tmp_message->ID, $tmp_user->id_user); 
				$msg_user =  $tmp_user->id_user;
				break;
			}
		}
		
		// if no matched username, set owner to Inbox Master
		if($check===false)
		{
			$this->Message_model->update_owner($tmp_message->ID, $this->config->item('inbox_owner_id'));
			$msg_user =  $this->config->item('inbox_owner_id');
		}
		
		return $msg_user;
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
}

/* End of file daemon.php */
/* Location: ./application/controllers/daemon.php */ 