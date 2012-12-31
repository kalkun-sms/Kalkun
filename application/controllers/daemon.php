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
	function __construct()
	{	
		// Commented this for allow access from other machine
		// if($_SERVER['REMOTE_ADDR']!='127.0.0.1') exit("Access Denied.");	
		parent::__construct();
		$this->load->library('Plugins');
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
		$this->load->model(array('Kalkun_model', 'Message_model', 'Spam_model'));
		
		// get unProcessed message
		$message = $this->Message_model->get_messages(array('processed' => FALSE));

		foreach($message->result() as $tmp_message)
		{			
			// check for spam
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

            // run user filters
            $this->_run_user_filters($tmp_message, $msg_user);
			
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
    	$this->load->model(array('Message_model', 'User_model', 'Phonebook_model'));
    	
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
		
		// If inbox_routing_use_phonebook is enabled
		if($this->config->item('inbox_routing_use_phonebook'))
		{
			foreach ($users->result() as $tmp_user)
			{
				$param['id_user'] = $tmp_user->id_user;
				$param['number'] = $tmp_message->SenderNumber;
				$param['option'] = 'bynumber';
				$check = $this->Phonebook_model->get_phonebook($param);
				
				if($check->num_rows() != 0)
				{
					$msg_user[] =  $tmp_user->id_user;
				}
			}
			
			if(isset($msg_user))
			{
				$this->Message_model->update_owner($tmp_message->ID, $msg_user); 
			}
		}
		
		// if no matched username, set owner to Inbox Master
		if($check===false OR !isset($msg_user))
		{
			$this->Message_model->update_owner($tmp_message->ID, $this->config->item('inbox_owner_id'));
			$msg_user =  $this->config->item('inbox_owner_id');
		}
		
		return $msg_user;
    }	

    // --------------------------------------------------------------------

    /**
     * Run user filters
     *
     * @access	public	 
     */		
    function _run_user_filters($msg, $users)
    {
        foreach($users as $user)
        {
            $filters = $this->Kalkun_model->get_filters($user);
            foreach($filters->result() as $filter)
            {
                if(!empty($filter->from) AND ($msg->SenderNumber != $filter->from)) continue;
                if(!empty($filter->has_the_words) AND (strstr($msg->TextDecoded, $filter->has_the_words) === FALSE)) continue;
                $this->Message_model->move_messages(array('type' => 'single', 'folder' => 'inbox', 'id_message' => array($msg->ID), 'id_folder' => $filter->id_folder));
            }
        }
    }

	// --------------------------------------------------------------------
	
	/**
	 * Server alert engine
	 *
	 * Scan host port and send SMS alert if the host is down
	 *
	 * @access	public	 
	 */		
	function server_alert_daemon()
	{
		$this->load->model(array('Kalkun_model', 'Message_model'));
	    $this->load->model('server_alert/server_alert_model', 'plugin_model');
	    
		$tmp_data = $this->plugin_model->get('active');
		foreach($tmp_data->result() as $tmp)
		{
			$fp = fsockopen($tmp->ip_address, $tmp->port_number, $errno, $errstr, 60);
			if(!$fp)
			{
				$data['coding'] = 'default';	
				$data['message'] = $tmp->respond_message."\n\nKalkun Server Alert";
				$data['date'] = date('Y-m-d H:i:s');
				$data['dest'] = $tmp->phone_number;
				$data['delivery_report'] = 'default';
				$data['class'] = '1';
				$data['uid'] = '1';
				$this->Message_model->send_messages($data);
				$this->plugin_model->change_state($tmp->id_server_alert, 'false');
			}
		}
	}

}

/* End of file daemon.php */
/* Location: ./application/controllers/daemon.php */ 
