<?php
Class Messages extends MY_Controller {

function Messages()
{
	parent::MY_Controller();
	
	// session check
	if($this->session->userdata('loggedin')==NULL) redirect('login');
}

//=================================================================
// COMPOSE
//=================================================================

function compose()
{
	// register valid type
	$val_type = array('normal', 'reply', 'forward', 'member', 'pbk_contact', 'pbk_groups');
	$type = $this->input->post('type');
	if(!in_array($type, $val_type)) die('Invalid type on compose');
	
	$data['val_type'] = $type;
	if($type=='forward') 
	{
		$source = $this->input->post('param1');
		$id = $this->input->post('param2');
		switch($source)
		{
		case 'inbox':
		$tmp_number = 'SenderNumber';
		$data['message'] = $this->Message_model->getMessagesbyID('inbox', $id)->row('TextDecoded');
		break;
		
		case 'sentitems':
		$tmp_number = 'DestinationNumber';
		$data['message'] = $this->Message_model->getMessagesbyID('sentitems', $id)->row('TextDecoded');
		
		// check multipart
		$tmp_check = $this->Message_model->getMultipart('sentitems', 'check', $id);
		if($tmp_check!=0)
		{
			foreach($this->Message_model->getMultipart('sentitems', 'all', $id)->result() as $part):
			$data['message'] .= $part->TextDecoded;
			endforeach;	
		}
		break;
		}
	
		// get phone number
		$tmp_source = $this->Message_model->getMessagesbyID($source, $id)->row($tmp_number);
		
		// check phonebook name by phone number
		$tmp_check = $this->Phonebook_model->getPhonebook(array('option'=>'bynumber', 'number'=>$tmp_source));
		if($tmp_check->num_rows()!=0) $tmp_source = $tmp_check->row('Name').'  <'.$tmp_source.'>';
		
		$data['message'] = "Original message from: ".$tmp_source."\n\n".$data['message'];		
	}
	else if($type=='reply') $data['dest'] = $this->input->post('param1');
	else if($type=='pbk_contact') $data['dest'] = $this->input->post('param1');
	else if($type=='pbk_groups') $data['dest'] = $this->input->post('param1');
	
	$this->load->view('main/messages/compose', $data);
}

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
		foreach($tmp_dest as $key => $tmp):
		if(trim($tmp)!='') {
			$dest[$key] = $tmp;
		}
		endforeach;
		break;
	
		//Group	
		case 'sendoption2':
		$dest = array();
		foreach($this->Phonebook_model->getPhonebook(array('option' => 'bygroup', 'group_id' => $this->input->post('groupvalue')))->result() as $tmp):
		$dest[] = $tmp->Number;
		endforeach;
		break;
	
		// Input manually
		case 'sendoption3':
		$dest = $this->input->post('manualvalue');
		break;
		
		// Reply
		case 'reply':
		$dest = $this->input->post('reply_value');
		break;
	
		// Member
		case 'member':
		$this->load->model('Member_model');
		$dest = array();
		foreach($this->Member_model->get_member('all')->result() as $tmp):
		$dest[] = $tmp->phone_number;
		endforeach;								
		break;	
		
		// Phonebook group
		case 'pbk_groups':
		$dest = array();
		foreach($this->Phonebook_model->getPhonebook(array('option' => 'bygroup', 'group_id' => $this->input->post('id_pbk')))->result() as $tmp):
		$dest[] = $tmp->Number;
		endforeach;
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
	$data['delivery_report'] = $this->Kalkun_model->getSetting()->row('delivery_report');
	
	// Send the message
	if(is_array($dest)) 
	{
		foreach($dest as $dest):
		$data['dest'] = $dest;				
		for($i=1;$i<=$this->input->post('sms_loop');$i++) 
		{ 
			$this->Message_model->sendMessages($data);
			$this->Kalkun_model->add_sms_used($this->session->userdata('id_user'));
		}
		endforeach;
	}
	else 
	{
		$data['dest'] = $dest;				
		for($i=1; $i<=$this->input->post('sms_loop'); $i++) 
		{ 
			$this->Message_model->sendMessages($data); 
			$this->Kalkun_model->add_sms_used($this->session->userdata('id_user'));
		}
	}
	echo "<div class=\"notif\">Your message has been move to Outbox and ready for delivery.</div>";
	}
}

// contact autocompleter
function getphonebook()
{
	$q = $this->input->post('q', TRUE);
	if (isset($q) && strlen($q) > 0)
	{
		$user_id = $this->session->userdata("id_user");
		$sql = "select Number as id, Name as name from pbk where id_user='".$user_id."' and Name like '%".$q."%' order by Name ";
		$query = $this->Kalkun_model->db->query($sql);
		echo json_encode($query->result());
	}
}	


//=================================================================
// MESSAGE
//=================================================================		

function folder($type=NULL, $option=NULL, $offset=NULL)
{		
	// validate url
	$valid_type = array('inbox', 'sentitems', 'outbox');		
	if(!in_array($type, $valid_type)) die('Invalid URL');
	
	// Pagination
	$this->load->library('pagination');
	$config['per_page'] = $this->Kalkun_model->getSetting()->row('paging');
	$config['cur_tag_open'] = '<span id="current">';
	$config['cur_tag_close'] = '</span>';
	
	if ($option=='ajax') 
	{
		$data['messages'] = $this->Message_model->getConversation($type, 'paginate', NULL, $config['per_page'], $offset);
		$this->load->view('main/messages/message_list', $data);
	}
	else 
	{
		$config['base_url'] = site_url().'/messages/folder/'.$type;
		$config['total_rows'] = $this->Message_model->getConversation($type, 'count');	
		$config['uri_segment'] = 4;	
		$data['messages'] = $this->Message_model->getConversation($type, 'paginate', NULL, $config['per_page'], $this->uri->segment(4,0));
		$this->pagination->initialize($config); 
	
		$data['main'] = 'main/messages/index';
		$this->load->view('main/layout', $data);			
	}
}

function my_folder($type=NULL, $id_folder=NULL, $option=NULL, $offset=NULL)
{
	// validate url
	$valid = array('inbox', 'sentitems');		
	if(!in_array($type, $valid)) die('Invalid URL');
	
	// Pagination
	$this->load->library('pagination');
	$config['per_page'] = $this->Kalkun_model->getSetting()->row('paging');		
	$config['cur_tag_open'] = '<span id="current">';
	$config['cur_tag_close'] = '</span>';
	
	if ($option=='ajax') 
	{
		$data['messages'] = $this->Message_model->getConversation($type, 'paginate', $id_folder, $config['per_page'], $offset);
		$this->load->view('main/messages/message_list', $data);
	}
	else 
	{			
		$config['base_url'] = site_url().'/messages/my_folder/'.$type.'/'.$id_folder;
		$config['total_rows'] = $this->Message_model->getConversation($type, 'count', $id_folder);
		$config['uri_segment'] = 5;			
		$this->pagination->initialize($config); 
		
		$data['main'] = 'main/messages/index';
		$data['messages'] = $this->Message_model->getConversation($type, 'paginate', $id_folder, $config['per_page'], $this->uri->segment(5,0));
		$this->load->view('main/layout', $data);
	}
}

function conversation($source=NULL, $type=NULL, $number=NULL, $id_folder=NULL)
{
if($source=='folder' && $type!='outbox') 
{
	$data['main'] = 'main/messages/index';
	$inbox = $this->Message_model->getMessages('inbox', 'by_number', NULL, NULL, trim(base64_decode(HexToAscii($number))))->result_array();	
	
	// add global date for sorting
	foreach($inbox as $key=>$tmp):
	$inbox[$key]['globaldate'] = $inbox[$key]['ReceivingDateTime'];
	$inbox[$key]['source'] = 'inbox';
	endforeach;
	
	$sentitems = $this->Message_model->getMessages('sentitems', 'by_number', NULL, NULL, trim(base64_decode(HexToAscii($number))))->result_array();	
	
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
	$sort_option = $this->Kalkun_model->getSetting()->row('conversation_sort');
	usort($data['messages'], "compare_date_".$sort_option);
	
	$this->load->view('main/layout', $data);	
}
else if($source=='folder' && $type=='outbox')
{
	$data['main'] = 'main/messages/index';
	$outbox = $this->Message_model->getMessages('outbox', 'by_number', NULL, NULL, trim(base64_decode(HexToAscii($number))))->result_array();	
	
	foreach($outbox as $key=>$tmp):
	$outbox[$key]['source'] = 'outbox';
	endforeach;	
	$data['messages'] = $outbox;
	
	$this->load->view('main/layout', $data);							
}
else 
{
	$data['main'] = 'main/messages/index';
	$inbox = $this->Message_model->getMessages('inbox', 'by_number', $id_folder, NULL, trim(base64_decode(HexToAscii($number))))->result_array();	
	
	// add global date for sorting
	foreach($inbox as $key=>$tmp):
	$inbox[$key]['globaldate'] = $inbox[$key]['ReceivingDateTime'];
	$inbox[$key]['source'] = 'inbox';
	endforeach;
	
	$sentitems = $this->Message_model->getMessages('sentitems', 'by_number', $id_folder, NULL, trim(base64_decode(HexToAscii($number))))->result_array();							
	
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
	$sort_option = $this->Kalkun_model->getSetting()->row('conversation_sort');
	usort($data['messages'], "compare_date_".$sort_option);
	
	$this->load->view('main/layout', $data);				
}
}

function move_message()
{
$this->Message_model->MoveMessage();
}	


function delete_messages($source=NULL)
{
	if($source=='outbox'): $this->Message_model->delMessages($this->input->post('type'), $source, 'outbox');
	else:
		// check trash delete
		if($this->input->post('current_folder')=='5'): 
		$this->Message_model->delMessages($this->input->post('type'), $source, 'permanent');
		else:	
			// check permanent delete
			if($this->Kalkun_model->getSetting()->row('permanent_delete')=='true'):
				$this->Message_model->delMessages($this->input->post('type'), $source, 'permanent');
			else:	
				$this->Message_model->delMessages($this->input->post('type'), $source, 'temp');
			endif;	
		endif;
	endif;
}

function __check_folder_privileges($id_folder=NULL) 
{
	//$this->
}
}	
?>
