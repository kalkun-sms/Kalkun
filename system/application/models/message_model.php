<?php
Class Message_model extends Model {
	
	function Message_model()
	{
		parent::Model();
	}
	
	//	name: 		sendMessages
	//	@param:		type ('inbox', 'sentitems','outbox')
	//				option ('all', 'unread', 'by_number', 'by_id', 'count', 'unread_count')
	//				id_folder int
	//				id_message int
	//				number int
	//	@return: 	array/object
	//
	//  note: by_id option not work, use getMessagesbyID instead
	//=================================================================	

	function sendMessages($data)
	{
		if($data['dest']!=NULL && $data['date']!=NULL && $data['message']!=NULL)
		{
			// Check message's length	
			$messagelength = strlen($data['message']);

			// Multipart message
			if($messagelength > 160)
			{
				// split string
				$tmpmsg = str_split($data['message'], 153);
				
				// count part message
				$part = count($tmpmsg);
				if($part < 10) $part = '0'.$part;
				
				// insert first part to outbox
				$data['option'] = 'multipart';
				$data['message'] = $tmpmsg[0];
				$data['part'] = $part;
				$this->sendMessagesRoute($data);
				
				// get last outbox ID
				$outboxid = $this->getLastOutboxID()->row('value');
				
				// insert the rest part to Outbox Multipart
				for($i=1; $i<count($tmpmsg); $i++) 
					$this->sendMessageMultipart($outboxid, $tmpmsg[$i], $i, $part);
			}		
			else 
			{
				$data['option'] = 'single';
				$this->sendMessagesRoute($data);		
			}	
		}
		else 
		{
			echo 'Parameter invalid';	
		}
	}
	
	private function sendMessagesRoute($tmp_data)
	{
		$data = array (
				'InsertIntoDB' => date('Y-m-d H:i:s'),
				'SendingDateTime' => $tmp_data['date'],
				'DestinationNumber' => $tmp_data['dest'],
				'Coding' => 'Default_No_Compression',
				'Class' => $tmp_data['class'],
				'TextDecoded' => $tmp_data['message'],
				'DeliveryReport' => $tmp_data['delivery_report']			
				);
					
		if($tmp_data['option']=='multipart')
		{
			$data['MultiPart'] = 'true'; 
			$data['UDH'] = '050003D3'.$tmp_data['part'].'01'; 
		}
					
		$this->db->insert('outbox', $data);
		
		$outbox_id = $this->db->insert_id();
		$user = array(
				'id_outbox' => $outbox_id,
				'id_user' => $this->session->userdata('id_user')
				);
		$this->db->insert('user_outbox', $user);		
	}	
	
	private function sendMessageMultipart($outboxid, $message, $pos, $part) 
	{
		$code = $pos+1;
		if($code < 10) $code = '0'.$code;
		
		$data = array (
				'ID' => $outboxid,
				'UDH' => '050003D3'.$part.''.$code,
				'SequencePosition' => $pos+1,
				'Coding' => 'Default_No_Compression',
				'Class' => $this->input->post('sms_mode'),
				'TextDecoded' => $message,
				);	
		$this->db->insert('outbox_multipart',$data);						
	}	
		
	private function getLastOutboxID() 
	{
		$sql = "select max(ID) as value from outbox";
		return $this->db->query($sql);	
	}	
	
	
	//=================================================================
	//	name: 		getMessages
	//	@param:		type ('inbox', 'sentitems','outbox')
	//				option ('all', 'unread', 'by_number', 'by_id', 'count', 'unread_count')
	//				id_folder int
	//				id_message int
	//				number int
	//	@return: 	array/object
	//
	//  note: by_id option not work, use getMessagesbyID instead
	//=================================================================	
	
	function getMessages($type=NULL, $option=NULL, $id_folder=NULL, $id_message=NULL, $number=NULL)
	{
		$valid_type = array('inbox', 'outbox', 'sentitems');
		$valid_option = array('by_number', 'by_number_count', 'by_id', 'count', 'unprocessed');
		
		if(!in_array($type, $valid_type) || !in_array($option, $valid_option)) 
		die('<b>Invalid type/option request on Message_model->getMessages function.</b>');
		
		$user_folder = "user_".$type;
		$user_id = $this->session->userdata('id_user');
		
		if($type=='inbox')
		{
			$tmp_number = 'SenderNumber'; 
			$tmp_order = 'ReceivingDateTime';					
		}
		else 
		{
			$tmp_number = 'DestinationNumber';
			$tmp_order = 'SendingDateTime';			
		}
		
		if($option=='count' || $option=='by_number_count') $tmp_select = 'count(*) as count';
		else $tmp_select = '*';
		
		$this->db->select($tmp_select);
		$this->db->from($type);
		
		if($id_folder!=NULL) $this->db->where('id_folder', $id_folder);
		else 
		{
			if($type!='outbox') $this->db->where('id_folder', array_search($type, $valid_type)+1);
			if($type=='sentitems') $this->db->where('SequencePosition', '1');
		}
							
		switch($option)
		{
			case 'by_id':
				$this->db->where('ID', $id_message);
			break;
			
			case 'unprocessed':
				$this->db->where('Processed', 'false');
			break;
				
			case 'by_number':
			case 'by_number_count':
				$this->db->where($tmp_number, $number);
				$this->db->order_by($tmp_order, 'ASC');
			break;
		}
		
		if($option!='unprocessed') {
			$this->db->join($user_folder, $user_folder.'.id_'.$type.'='.$type.'.ID');
			$this->db->where($user_folder.'.id_user',$user_id);
		}
		
		if($option=='count' || $option=='by_number_count') return $this->db->get()->row('count');
		else return $this->db->get();
	}
	
	// get unProcesses inbox 
	function getUnprocessed()
	{
		$this->db->where('Processed', 'false');
		return $this->db->get('inbox');
	}
	
	
	// get unread message from inbox
	function getUnread($id_folder=NULL)
	{
		$this->db->select('count(*) as count');
		$this->db->from('inbox');
		$this->db->join('user_inbox', 'inbox.ID=user_inbox.id_inbox');
		$this->db->where('user_inbox.id_user', $this->session->userdata('id_user'));
		$this->db->where('readed', 'false');
		if($id_folder!=NULL) $this->db->where('id_folder', $id_folder);
		else $this->db->where('id_folder', '1');
		return $this->db->get()->row('count');	
	}
	
	
	function getMessagesbyID($type=NULL, $id_message=NULL)
	{
		$this->db->select('TextDecoded');
		if($type=='inbox') $this->db->select('SenderNumber');
		else $this->db->select('DestinationNumber');
		$this->db->from($type);
		if($type=='sentitems') $this->db->where('SequencePosition', '1');
		$this->db->where('ID', $id_message);
		
		return $this->db->get();
	}
	

	//=================================================================
	//	name: 		getConversation
	//	@param:		type ('inbox', 'sentitems','outbox')
	//				option ('all', 'paginate')
	//				id_folder int
	//				limit int
	//				offset int
	//	@return: 	array/object
	//=================================================================	
		
	function getConversation($type=NULL, $option=NULL, $id_folder=NULL, $limit=NULL, $offset=NULL, $trash=NULL)
	{
		$valid_type = array('inbox', 'outbox', 'sentitems');
		$valid_option = array('all', 'paginate', 'count');
		
		if(!in_array($type, $valid_type) || !in_array($option, $valid_option)) 
		die('<b>Invalid type/option request on Message_model->getConversation function.</b>');
			
		$user_id = $this->session->userdata('id_user');
		if($option=='count') $tmp_select = "count(*) as count";
		else $tmp_select = "*";
		
		if($id_folder!=NULL) $tmp_id_folder = $id_folder;
		else $tmp_id_folder = array_search($type, $valid_type)+1;
		
		if($id_folder=='5') $tmp_trash='1';
		else $tmp_trash='0';
		
		switch($type)
		{
			case 'inbox':
				$sql = "select ".$tmp_select." from inbox, user_inbox, (select max(ReceivingDateTime) as maxdate from inbox,user_inbox where id_folder='".$tmp_id_folder."' and inbox.ID=user_inbox.id_inbox and user_inbox.id_user='".$user_id."' group by SenderNumber) as maxresult";
				$sql.= " where inbox.ID=user_inbox.id_inbox and user_inbox.id_user='".$user_id."' and id_folder='".$tmp_id_folder."' and user_inbox.trash='".$tmp_trash."' and inbox.ReceivingDateTime=maxresult.maxdate group by SenderNumber order by ReceivingDateTime DESC";
			break;
			
			case 'outbox':
				$sql = "select ".$tmp_select." from outbox, user_outbox, (select max(SendingDateTime) as maxdate from outbox, user_outbox where outbox.ID=user_outbox.id_outbox and user_outbox.id_user='".$user_id."' group by DestinationNumber) as maxresult";
				$sql.= " where outbox.ID=user_outbox.id_outbox and user_outbox.id_user='".$user_id."' and outbox.SendingDateTime=maxresult.maxdate group by DestinationNumber order by SendingDateTime DESC";
			break;
			
			case 'sentitems':
				$sql = "select ".$tmp_select." from sentitems, user_sentitems, (select max(SendingDateTime) as maxdate from sentitems, user_sentitems where sentitems.ID=user_sentitems.id_sentitems and user_sentitems.id_user='".$user_id."' and id_folder='".$tmp_id_folder."' and SequencePosition='1' group by DestinationNumber) as maxresult";
				$sql.= " where sentitems.ID=user_sentitems.id_sentitems and user_sentitems.id_user='".$user_id."' and id_folder='".$tmp_id_folder."' and SequencePosition='1' and user_sentitems.trash='".$tmp_trash."' and sentitems.SendingDateTime=maxresult.maxdate group by DestinationNumber order by SendingDateTime DESC";			
			break;
		}
		
		if($option=='paginate') $sql.= " limit ".$limit." offset ".$offset."";
		
		if($option=='count') return $this->db->query($sql)->num_rows();
		else return $this->db->query($sql);
	}
	


	//=================================================================
	//	name: 		delMessages
	//	@param:		type ('conversation', 'single')
	//				source ('inbox', 'outbox', 'sentitems')
	//				option ('permanent', 'temp', 'outbox')
	//				id int
	//=================================================================	

	function delMessages($type, $source, $option, $id=NULL)
	{
		if($this->input->post('id')) $tmp_id = $this->input->post('id');
		else $tmp_id = $id;		
		
		switch($type)
		{
			case 'conversation':
			$number = $this->input->post('number');
			if($this->input->post('current_folder')=='') { $inbox_folder=1; $sentitems_folder=3; }
			else $inbox_folder=$sentitems_folder=$this->input->post('current_folder');			

			switch($option)
			{
				case 'permanent':
				// inbox
				$inbox = "DELETE i, ui
						FROM inbox AS i
						LEFT JOIN user_inbox AS ui ON ui.id_inbox = i.ID
						WHERE i.SenderNumber = '".$number."'";
				$this->db->query($inbox);
				
				// sentitems
				$sentitems = "DELETE s, us
						FROM sentitems AS s
						LEFT JOIN user_sentitems AS us ON us.id_sentitems = s.ID
						WHERE s.DestinationNumber = '".$number."'";
				$this->db->query($sentitems);
				break;	
				
				case 'temp':
				$inbox = $this->db->query("select ID from inbox where id_folder='".$inbox_folder."' and SenderNumber = '".$number."'");
				foreach($inbox->result() as $tmp):
					$this->db->query("update inbox set id_folder='5' where ID='".$tmp->ID."'");
					$this->db->query("update user_inbox set trash='1' where id_inbox='".$tmp->ID."'");
				endforeach;
				
				$sentitems = $this->db->query("select ID from sentitems where id_folder='".$sentitems_folder."' and DestinationNumber = '".$number."'");
				foreach($sentitems->result() as $tmp):
					$this->db->query("update sentitems set id_folder='5' where ID='".$tmp->ID."'");
					$this->db->query("update user_sentitems set trash='1' where id_sentitems='".$tmp->ID."'");
				endforeach;						
				break;
				
				case 'outbox':
				$tmp_sql = $this->getMessages('outbox', 'by_number', NULL, NULL, $this->input->post('number'))->result_array();
				// looping all message
				foreach($tmp_sql as $tmp):
				//check multipart message
				if($this->getMultipart('outbox', 'check', $tmp['ID'])=='true')
				$this->db->delete('outbox_multipart', array('ID' => $tmp['ID']));
				
				$this->db->delete('outbox', array('ID' => $tmp['ID']));
				endforeach;		
				break;
			}
			break;		
			
			case 'single':
			switch($option)
			{				
				case 'permanent':
				$this->db->delete("user_".$source, array('id_'.$source => $tmp_id));
				$this->db->delete($source, array('ID' => $tmp_id));
				break;	
				
				case 'temp':
				$this->db->query("update ".$source." set id_folder='5' where ID = '".$tmp_id."'");
				$this->db->query("update user_".$source." set trash='1' where id_".$source." = '".$tmp_id."'");
				break;
				
				case 'outbox':
				//check multipart message
				if($this->getMultipart('outbox', 'check', $tmp_id)=='true')
				$this->db->delete('outbox_multipart', array('ID' => $tmp_id));
				
				$this->db->delete('outbox', array('ID' => $tmp_id));
				break;
			}		
			break;		
		}		
	}	


	//=================================================================
	//	name: 		MoveMessage
	//	@param:		type ('conversation', 'single')
	//				current_folder int
	//				id_folder int
	//=================================================================	

	function MoveMessage()
	{
		switch($this->input->post('type')) 
		{
			case 'conversation':
			if($this->input->post('current_folder')=='') { $inbox_folder=1; $sentitems_folder=3; }
			else $inbox_folder=$sentitems_folder=$this->input->post('current_folder');
			$number = $this->input->post('number');

			$inbox = $this->db->query("select ID from inbox where id_folder='".$inbox_folder."' and SenderNumber = '".$number."'");
			foreach($inbox->result() as $tmp):
				$this->db->query("update inbox set id_folder='".$this->input->post('id_folder')."' where ID='".$tmp->ID."'");
				$this->db->query("update user_inbox set trash='0' where id_inbox='".$tmp->ID."'");
			endforeach;
			
			$sentitems = $this->db->query("select ID from sentitems where id_folder='".$inbox_folder."' and DestinationNumber = '".$number."'");
			foreach($sentitems->result() as $tmp):
				$this->db->query("update sentitems set id_folder='".$this->input->post('id_folder')."' where ID='".$tmp->ID."'");
				$this->db->query("update user_sentitems set trash='0' where id_sentitems='".$tmp->ID."'");
			endforeach;
			break;	
				
			case 'single':
			$folder = $this->input->post('folder');
			$this->db->query("update ".$folder." set id_folder='".$this->input->post('id_folder')."' where ID = '".$this->input->post('id_message')."'");
			$this->db->query("update user_".$folder." set trash='0' where id_".$folder." = '".$this->input->post('id_message')."'");		
			break;
		}
	}	
	

	//=================================================================
	//	name: 		getMultipart
	//	@param:		type ('outbox', 'sentitems')
	//				option ('check', 'all')
	//				ID int
	//  note: inbox multipart not supported yet.
	//=================================================================	
		
	function getMultipart($type=NULL, $option=NULL, $id_message=NULL)
	{
		switch($option)
		{
			case 'check':
				if($type=='outbox')
				{
					$this->db->select('MultiPart');
					$this->db->where('ID', $id_message);
					return $this->db->get('outbox')->row('MultiPart');					
				}
				else 
				{	
					$this->db->select('count(*) as count');
					$this->db->where('ID', $id_message);
					$this->db->where('SequencePosition >', 1);
					return $this->db->get('sentitems')->row('count');
				}
			break;
			
			case 'all':
				if($type=='outbox') 
				{
					$this->db->where('ID', $id_message);
					$this->db->order_by('SequencePosition');
					return $this->db->get('outbox_multipart');
				}
				else 
				{
					$this->db->where('ID', $id_message);
					$this->db->where('SequencePosition >', 1);	
					$this->db->order_by('SequencePosition');
					return $this->db->get('sentitems');				
				}
			break;
		}			
	}
	
	// Update readed inbox
	function updateRead($id)
	{
		$data = array ('readed' => 'true');
		$this->db->where('ID', $id);		
		$this->db->update('inbox', $data);
	}	
	
	// Update processed inbox
	function updateProcessed($id)
	{
		$data = array ('Processed' => 'true');
		$this->db->where('ID', $id);		
		$this->db->update('inbox', $data);
	}		
	
	// Update ownership
	function updateOwner($msg_id, $user_id)
	{
		$data = array ('id_user' => $user_id,
						'id_inbox' => $msg_id
						);
		$this->db->insert('user_inbox', $data);			
	}
	
	
	
	// User folder ownership management	
	
	// getUserOutbox
	function getUserOutbox($user_id)
	{
		$this->db->where("id_user", $user_id);
		return $this->db->get("user_outbox");
	}

	// getUserInbox
	function getUserInbox($user_id)
	{
		$this->db->where("id_user", $user_id);
		return $this->db->get("user_inbox");
	}	

	// getUserSentitems
	function getUserSentitems($user_id)
	{
		$this->db->where("id_user", $user_id);
		return $this->db->get("user_sentitems");
	}	
	
	function deleteUserOutbox($ID)
	{
		$this->db->where('id_outbox', $ID);
		$this->db->delete("user_outbox");
	}

	// checkOutbox
	function checkOutbox($ID)
	{
		$this->db->where("ID", $ID);
		if($this->db->count_all_results("outbox")>0) return TRUE;
		else return FALSE;
	}	
	
	// checkSentitems
	function checkSentitems($ID)
	{
		$this->db->where("ID", $ID);
		if($this->db->count_all_results("sentitems")>0) return TRUE;
		else return FALSE;		
	}
	
	function insertUserSentitems($ID, $user_id)
	{
		$this->db->set('id_user', $user_id);
		$this->db->set('id_sentitems', $ID);	
		$this->db->insert('user_sentitems');
	}
}
?>
