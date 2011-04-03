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
 * Messages Class
 *
 * @package		Kalkun
 * @subpackage	Messages
 * @category	Controllers
 */
class Messages extends MY_Controller {

	/**
	 * Constructor
	 *
	 * @access	public
	 */
	function Messages()
	{
		parent::MY_Controller();
		
		// session check
		if ($this->session->userdata('loggedin')==NULL) redirect('login');
		
		$param['uid'] = $this->session->userdata('id_user');
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Compose
	 *
	 * Render compose window form
	 *
	 * @access	public   		 
	 */	
	function compose()
	{
		// register valid type
		$val_type = array('normal', 'reply', 'forward', 'member', 'pbk_contact', 'pbk_groups');
		$type = $this->input->post('type');
		if(!in_array($type, $val_type)) die('Invalid type on compose');
		
		$data['val_type'] = $type;
		
		// Forward option
		if ($type=='forward') 
		{
			$source = $this->input->post('param1');
			$id = $this->input->post('param2');
			switch ($source)
			{
				case 'inbox':
				$tmp_number = 'SenderNumber';
				$param['type'] = 'inbox';
				$param['id_message'] = $id;
				$data['message'] = $this->Message_model->get_messages($param)->row('TextDecoded');
				
				// check multipart
				$multipart['type'] = 'inbox';
				$multipart['option'] = 'check';
				$multipart['id_message'] = $id;
				$tmp_check = $this->Message_model->get_multipart($multipart);
				if ($tmp_check->row('UDH')!='')
				{
					$multipart['option'] = 'all';
					$multipart['udh'] = substr($tmp_check->row('UDH'),0,8);
					$multipart['phone_number'] = $tmp_check->row('SenderNumber');					
					foreach($this->Message_model->get_multipart($multipart)->result() as $part):
					$data['message'] .= $part->TextDecoded;
					endforeach;	
				}				
				break;
				
				case 'sentitems':
				$tmp_number = 'DestinationNumber';
				$param = array('type' => 'sentitems', 'id_message' => $id);
				$data['message'] = $this->Message_model->get_messages($param)->row('TextDecoded');
								
				// check multipart
				$multipart['type'] = 'sentitems';
				$multipart['option'] = 'check';
				$multipart['id_message'] = $id;
				$tmp_check = $this->Message_model->get_multipart($multipart);
				if ($tmp_check!=0)
				{
					$multipart['option'] = 'all';
					foreach($this->Message_model->get_multipart($multipart)->result() as $part):
					$data['message'] .= $part->TextDecoded;
					endforeach;	
				}
				break;
			}		
		}
		else if ($type=='reply') $data['dest'] = $this->input->post('param1');
		else if ($type=='pbk_contact') $data['dest'] = $this->input->post('param1');
		else if ($type=='pbk_groups') $data['dest'] = $this->input->post('param1');
		
		$this->load->view('main/messages/compose', $data);
	}

	// --------------------------------------------------------------------
	
	/**
	 * Compose Process
	 *
	 * Process submitted form
	 *
	 * @access	public   		 
	 */		
	function compose_process()
	{
		if($_POST)
		{
		// Select send option
		switch($this->input->post('sendoption')) 
		{
			// Person
			case 'sendoption1':
			$tmp_dest = explode(',', $this->input->post('personvalue'));
			$dest = array();
			foreach ($tmp_dest as $key => $tmp)
			{
				if(trim($tmp)!='') {
					$dest[$key] = $tmp;
				}
			}
			break;
		
			//Group	
			case 'sendoption2':
			$dest = array();
			$param = array('option' => 'bygroup', 'group_id' => $this->input->post('groupvalue'));
			foreach($this->Phonebook_model->get_phonebook($param)->result() as $tmp)
			{
				$dest[] = $tmp->Number;
			}
			break;
		
			// Input manually
			case 'sendoption3':
			$tmp_dest = explode(',', $this->input->post('manualvalue'));
			$dest = array();
			foreach($tmp_dest as $key => $tmp)
			{
				$tmp = trim($tmp); // remove space
				if(trim($tmp)!='') {
					$dest[$key] = $tmp;
				}
			}
			break;
			
			// Reply
			case 'reply':
			$dest = $this->input->post('reply_value');
			break;
		
			// Member
			case 'member':
			$this->load->model('Member_model');
			$dest = array();
			foreach($this->Member_model->get_member('all')->result() as $tmp)
			{
				$dest[] = $tmp->phone_number;
			}								
			break;	
			
			// Phonebook group
			case 'pbk_groups':
			$dest = array();
			$param = array('option' => 'bygroup', 'group_id' => $this->input->post('id_pbk'));
			foreach($this->Phonebook_model->get_phonebook($param)->result() as $tmp)
			{
				$dest[] = $tmp->Number;
			}
			break;					
		}
				
		// Select send date
		switch($this->input->post('senddateoption')) 
		{
			// Now
			case 'option1':
			$date = date('Y-m-d H:i:s');	
			break;
			
			// Date and time 
			case 'option2':
			$date = $this->input->post('datevalue')." ".$this->input->post('hour').":".$this->input->post('minute').":00";
			break;
				
			// Delay
			case 'option3':
			$date = date('Y-m-d H:i:s', mktime(date('H')+$this->input->post('delayhour'), 
					date('i')+$this->input->post('delayminute'), date('s'), date('m'), date('d'), date('Y')));
			break;				
		}
		$data['class'] = ($this->input->post('sms_mode')) ? '0' : '1';
		$data['message'] = $this->input->post('message');
		$data['date'] = $date;
		$data['delivery_report'] = $this->Kalkun_model->get_setting()->row('delivery_report');
		$data['coding'] = ($this->input->post('unicode')=='unicode') ? 'unicode' : 'default';	
		$data['uid'] = $this->session->userdata('id_user');	
				
		// Send the message
		if(is_array($dest)) 
		{
			foreach($dest as $dest):
			$data['dest'] = $dest;				
			for($i=1;$i<=$this->input->post('sms_loop');$i++) 
			{ 
				$this->Message_model->send_messages($data);
				$this->Kalkun_model->add_sms_used($this->session->userdata('id_user'));
			}
			endforeach;
		}
		else 
		{
			$data['dest'] = $dest;				
			for($i=1; $i<=$this->input->post('sms_loop'); $i++) 
			{ 
				$this->Message_model->send_messages($data); 
				$this->Kalkun_model->add_sms_used($this->session->userdata('id_user'));
			}
		}
		echo "<div class=\"notif\">Your message has been move to Outbox and ready for delivery.</div>";
		}
	}
		
	// --------------------------------------------------------------------
	
	/**
	 * Folder
	 *
	 * List messages on folder (inbox, outbox, sentitems, trash)
	 *
	 * @access	public   		 
	 */		
	function folder($type=NULL, $offset=0)
	{		
		// validate url
		$valid_type = array('inbox', 'sentitems', 'outbox');		
		if(!in_array($type, $valid_type)) die('Invalid URL');
		
		$data['folder'] = 'folder';
		$data['type'] = $type;
		$data['offset'] = $offset;
		$data['id_folder'] = '';
		
		// Pagination
		$this->load->library('pagination');
		$config['per_page'] = $this->Kalkun_model->get_setting()->row('paging');
		$config['cur_tag_open'] = '<span id="current">';
		$config['cur_tag_close'] = '</span>';
		
		if(is_ajax())
		{
			$param['type'] = $type;
			$param['limit'] = $config['per_page'];
			$param['offset'] = $offset;
			$data['messages'] = $this->Message_model->get_conversation($param);
			$this->load->view('main/messages/message_list', $data);
		}
		else 
		{
			$config['base_url'] = site_url('messages/folder/'.$type);
			$config['total_rows'] = $this->Message_model->get_conversation(array('type' => $type))->num_rows();	
			$config['uri_segment'] = 4;	
			
			$param['type'] = $type;
			$param['limit'] = $config['per_page'];
			$param['offset'] = $this->uri->segment(4,0);			
			$data['messages'] = $this->Message_model->get_conversation($param);
			$this->pagination->initialize($config); 
		
			$data['main'] = 'main/messages/index';
			$this->load->view('main/layout', $data);			
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * My Folder
	 *
	 * List messages on custom folder (user created folder)
	 *
	 * @access	public   		 
	 */	
	function my_folder($type=NULL, $id_folder=NULL, $offset=0)
	{
		// validate url
		$valid = array('inbox', 'sentitems');		
		if(!in_array($type, $valid)) die('Invalid URL');

		$data['folder'] = 'my_folder';
		$data['type'] = $type;
		$data['offset'] = $offset;
		$data['id_folder'] = $id_folder;
		
		// Pagination
		$this->load->library('pagination');
		$config['per_page'] = $this->Kalkun_model->get_setting()->row('paging');		
		$config['cur_tag_open'] = '<span id="current">';
		$config['cur_tag_close'] = '</span>';
		
		if(is_ajax())
		{
			$param['type'] = $type;
			$param['id_folder'] = $id_folder;
			$param['limit'] = $config['per_page'];
			$param['offset'] = $offset;			
			$data['messages'] = $this->Message_model->get_conversation($param);
			$this->load->view('main/messages/message_list', $data);
		}
		else 
		{			
			$param['type'] = $type;
			$param['id_folder'] = $id_folder;
			$config['base_url'] = site_url('/messages/my_folder/'.$type.'/'.$id_folder);
			$config['total_rows'] = $this->Message_model->get_conversation($param)->num_rows();
			$config['uri_segment'] = 5;			
			$this->pagination->initialize($config); 
			
			$data['main'] = 'main/messages/index';
			$param['type'] = $type;
			$param['id_folder'] = $id_folder;
			$param['limit'] = $config['per_page'];
			$param['offset'] = $this->uri->segment(5,0);				
			$data['messages'] = $this->Message_model->get_conversation($param);
			$this->load->view('main/layout', $data);
		}
	}

	// --------------------------------------------------------------------
	
	/**
	 * Conversation
	 *
	 * List messages on conversation (based on phone number)
	 *
	 * @access	public
	 */		
	function conversation($source=NULL, $type=NULL, $number=NULL, $id_folder=NULL)
	{
		if($source=='folder' && $type!='outbox') 
		{
			$data['main'] = 'main/messages/index';
			$param['type'] = 'inbox';
			$param['number'] = trim($number);
			$inbox = $this->Message_model->get_messages($param)->result_array();	

			// add global date for sorting
			foreach($inbox as $key=>$tmp):
			$inbox[$key]['globaldate'] = $inbox[$key]['ReceivingDateTime'];
			$inbox[$key]['source'] = 'inbox';
			endforeach;
			
			$param['type'] = 'sentitems';
			$param['number'] = trim($number);
			$sentitems = $this->Message_model->get_messages($param)->result_array();	

			// add global date for sorting
			foreach($sentitems as $key=>$tmp):
			$sentitems[$key]['globaldate'] = $sentitems[$key]['SendingDateTime'];
			$sentitems[$key]['source'] = 'sentitems';
			endforeach;
			
			$data['messages'] = $inbox;
			
			// merge inbox and sentitems
			foreach($sentitems as $tmp):
			$data['messages'][] = $tmp;
			endforeach;
			
			// sort data
			$sort_option = $this->Kalkun_model->get_setting()->row('conversation_sort');
			usort($data['messages'], "compare_date_".$sort_option);
			
			$this->load->view('main/layout', $data);	
		}
		else if($source=='folder' && $type=='outbox')
		{
			$data['main'] = 'main/messages/index';
			$param['type'] = 'outbox';
			$param['number'] = trim($number);
			$outbox = $this->Message_model->get_messages($param)->result_array();	
			
			foreach($outbox as $key=>$tmp):
			$outbox[$key]['source'] = 'outbox';
			endforeach;	
			$data['messages'] = $outbox;
			
			$this->load->view('main/layout', $data);							
		}
		else 
		{
			$data['main'] = 'main/messages/index';
			$param['type'] = 'inbox';
			$param['id_folder'] = $id_folder;
			$param['number'] = trim($number);
			$inbox = $this->Message_model->get_messages($param)->result_array();	
			
			// add global date for sorting
			foreach($inbox as $key=>$tmp):
			$inbox[$key]['globaldate'] = $inbox[$key]['ReceivingDateTime'];
			$inbox[$key]['source'] = 'inbox';
			endforeach;

			$param['type'] = 'sentitems';
			$param['id_folder'] = $id_folder;
			$param['number'] = trim($number);			
			$sentitems = $this->Message_model->get_messages($param)->result_array();							

			// add global date for sorting
			foreach($sentitems as $key=>$tmp):
			$sentitems[$key]['globaldate'] = $sentitems[$key]['SendingDateTime'];
			$sentitems[$key]['source'] = 'sentitems';
			endforeach;
			
			$data['messages'] = $inbox;
			
			// merge inbox and sentitems
			foreach($sentitems as $tmp):
			$data['messages'][] = $tmp;
			endforeach;		
			
			// sort data
			$sort_option = $this->Kalkun_model->get_setting()->row('conversation_sort');
			usort($data['messages'], "compare_date_".$sort_option);
			
			$this->load->view('main/layout', $data);				
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Move Message
	 *
	 * Move messages from a folder to another folder
	 *
	 * @access	public
	 */		
	function move_message()
	{
		$param['current_folder'] = '';
		
		if($this->input->post('type')) $param['type'] = $this->input->post('type');
		if($this->input->post('current_folder')) $param['current_folder'] = $this->input->post('current_folder');
		if($this->input->post('number')) $param['number'] = $this->input->post('number');
		if($this->input->post('id_folder')) $param['id_folder'] = $this->input->post('id_folder');
		if($this->input->post('folder')) $param['folder'] = $this->input->post('folder');
		if($this->input->post('id_message')) $param['id_message'][0] = $this->input->post('id_message');
		
		if(isset($param['type']) && $param['type']=='single' && isset($param['folder']) && $param['folder']=='inbox')
		{
			$multipart = array('type' => 'inbox', 'option' => 'check', 'id_message' => $param['id_message'][0]);
			$tmp_check = $this->Message_model->get_multipart($multipart);
			if($tmp_check->row('UDH')!='')
			{
				$multipart = array('option' => 'all', 'udh' => substr($tmp_check->row('UDH'),0,8));	
				$multipart['phone_number'] = $tmp_check->row('SenderNumber');				
				foreach($this->Message_model->get_multipart($multipart)->result() as $part):
				$param['id_message'][] = $part->ID;
				endforeach;	
			}			
		}
		$this->Message_model->move_messages($param);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Delete Message
	 *
	 * Delete messages, permanently or temporarily
	 *
	 * @access	public
	 */		
	function delete_messages($source=NULL)
	{
		if($this->input->post('type')) $param['type'] = $this->input->post('type');
		if($this->input->post('current_folder')) $param['current_folder'] = $this->input->post('current_folder');
		if($this->input->post('id')) $param['id'][0] = $this->input->post('id');
		if(!is_null($source)) $param['source'] = $source;
		if($this->input->post('number')) $param['number'] = $this->input->post('number');
		
		if($param['source']=='outbox') $param['option'] = 'outbox';
		else
		{
			// check trash/permanent delete
			if (isset($param['current_folder']) && $param['current_folder']=='5') $param['option'] = 'permanent';
			else if (!isset($param['current_folder']) && $this->Kalkun_model->get_setting()->row('permanent_delete')=='true') $param['option'] = 'permanent';
			else $param['option'] = 'temp';	
		}
				
		if($param['type']=='single' && $param['source']=='inbox')
		{
			$multipart['type'] = 'inbox';
			$multipart['option'] = 'check';
			$multipart['id_message'] = $param['id'][0];
			$tmp_check = $this->Message_model->get_multipart($multipart);
			if($tmp_check->row('UDH')!='')
			{
				$multipart['option'] = 'all';
				$multipart['udh'] = substr($tmp_check->row('UDH'),0,8);
				$multipart['phone_number'] = $tmp_check->row('SenderNumber');					
				foreach($this->Message_model->get_multipart($multipart)->result() as $part):
				$param['id'][] = $part->ID;
				endforeach;	
			}				
		}
		$this->Message_model->delete_messages($param);
	}

	// --------------------------------------------------------------------
	
	/**
	 * Check Folder Privileges
	 *
	 * Check folder privileges/permission
	 *
	 * @access	private
	 */		
	function _check_folder_privileges($id_folder=NULL) 
	{
		//$this->
	}
}	

/* End of file messages.php */
/* Location: ./application/controllers/messages.php */ 