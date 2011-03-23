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
 * Message_model Class
 *
 * @package		Kalkun
 * @subpackage	Messages
 * @category	Models
 */
class Message_model extends Model { 
	
	var $udh = '';
	
	/**
	 * Constructor
	 *
	 * @access	public
	 */
	function Message_model()
	{
		parent::Model();
	}

	// --------------------------------------------------------------------
	
	/**
	 * Send Messages
	 *
	 * Get messages from inbox, outbox, sentitems
	 *
	 * @access	public   		 
	 * @param	mixed $options 
	 * Option: Values
	 * --------------
	 * dest string, phone number destination
	 * date datetime
	 * message string
	 * coding default, unicode
	 * class -1, 0, 1
	 * delivery_report default, yes, no
	 * uid int
	 * @return	object
	 */	
	function send_messages($data)
	{		
		if($data['dest']!=NULL && $data['date']!=NULL && $data['message']!=NULL)
		{
			// Check message's length	
			$messagelength = strlen($data['message']);
			
			// Check coding
			switch($data['coding'])
			{ 
				case 'default':
					$standar_length = 160;
					$data['coding'] = 'Default_No_Compression';
				break;
				
				case 'unicode':
					$standar_length = 70;
					$data['coding'] = 'Unicode_No_Compression';
				break;
			}
			
			$UDH_length = 7;
			$multipart_length = $standar_length - $UDH_length; 

			// Multipart message
			if($messagelength > $standar_length)
			{
				// generate UDH
				$UDH = "050003";
				$UDH .= strtoupper(dechex(mt_rand(0, 255)));
				$data['UDH'] = $UDH;
						
				// split string
				$tmpmsg = str_split($data['message'], $multipart_length);
				
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
					$this->sendMessageMultipart($outboxid, $tmpmsg[$i], $i, $part, $data['coding'], $data['class'], $UDH);
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
				'Coding' => $tmp_data['coding'],
				'Class' => $tmp_data['class'],
				'TextDecoded' => $tmp_data['message'],
				'DeliveryReport' => $tmp_data['delivery_report']			
				);
					
		if($tmp_data['option']=='multipart')
		{
			$data['MultiPart'] = 'true'; 
			$data['UDH'] = $tmp_data['UDH'].$tmp_data['part'].'01'; 
		}
					
		$this->db->insert('outbox', $data);
		
		$outbox_id = $this->db->insert_id();
		$user = array(
				'id_outbox' => $outbox_id,
				'id_user' => $tmp_data['uid']
				);
		$this->db->insert('user_outbox', $user);		
	}	
	
	private function sendMessageMultipart($outboxid, $message, $pos, $part, $coding, $class, $UDH) 
	{
		$code = $pos+1;
		if($code < 10) $code = '0'.$code;
		
		$data = array (
				'ID' => $outboxid,
				'UDH' => $UDH.$part.''.$code,
				'SequencePosition' => $pos+1,
				'Coding' => $coding,
				'Class' => $class,
				'TextDecoded' => $message,
				);	
		$this->db->insert('outbox_multipart',$data);						
	}	
		
	private function getLastOutboxID() 
	{
		$sql = "select max(ID) as value from outbox";
		return $this->db->query($sql);	
	}	


	// --------------------------------------------------------------------
	
	/**
	 * Get Messages
	 *
	 * Get messages from inbox, outbox, sentitems
	 *
	 * @access	public   		 
	 * @param	mixed $options 
	 * Option: Values
	 * --------------
	 * type inbox, outbox, sentitems
	 * id_folder int
	 * id_message int
	 * number string
	 * processed bool
	 * readed bool
	 * uid int
	 * @return	object
	 */	
	function get_messages($options = array())
	{
		// default values
    	$options = $this->_default(array('type' => 'inbox'), $options);
    
    	// register valid type
		$valid_type = array('inbox', 'outbox', 'sentitems');
		
		// check if it's valid type
		if(!in_array($options['type'], $valid_type)) 
		die('Invalid type request on class '.get_class($this).' function '.__FUNCTION__);		
		
		$user_folder = "user_".$options['type'];
		$this->db->from($options['type']);
		
		// set valid field name
		if($options['type']=='inbox')
		{
			$tmp_number = 'SenderNumber'; 
			$tmp_order = 'ReceivingDateTime';	
			$this->db->where("(UDH = '' OR UDH LIKE '%1')", NULL, FALSE); /* FIXME */				
		}
		else 
		{
			$tmp_number = 'DestinationNumber';
			$tmp_order = 'SendingDateTime';	
			if($options['type']=='sentitems') $this->db->where('SequencePosition', '1');		
		}
						
		// if id message if set
		if(isset($options['id_message'])) $this->db->where('ID', $options['id_message']);
		else
		{
			// if id folder is set, else use default value (inbox = 1, sentitems = 3)
			if(isset($options['id_folder'])) $this->db->where('id_folder', $options['id_folder']);
			else 
			{
				if($options['type']!='outbox') $this->db->where('id_folder', array_search($options['type'], $valid_type)+1);
			}			
		}
		
		// if phone number if set
		if(isset($options['number'])) $this->db->where($tmp_number, $options['number']);

		// if readed if set
		if(isset($options['readed']) && is_bool($options['readed'])) 
		{
			// valid only for inbox
			if($options['type']=='inbox')
			{
				$readed = ($options['readed']) ? 'true' : 'false'; 
				$this->db->where('readed', $readed);	
			}
		}

		// if processed if set
		if(isset($options['processed']) && is_bool($options['processed'])) 
		{
			// valid only for inbox
			if($options['type']=='inbox')
			{
				$processed = ($options['processed']) ? 'true' : 'false'; 
				$this->db->where('Processed', $processed);
			}
		}		
		
		// join user table
		if(isset($options['uid']))
		{
			$this->db->join($user_folder, $user_folder.'.id_'.$options['type'].'='.$options['type'].'.ID');
			$this->db->where($user_folder.'.id_user',$options['uid']);		
		}
		
		// deprecated				
		/*switch($option)
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
		}*/
		
		//if($option=='count' || $option=='by_number_count') return $this->db->get()->row('count');
		//else return $this->db->get();
		$result = $this->db->get();
	
		return $result;
	}
	
	// deprecated
	// get unProcesses inbox 
	/*function getUnprocessed()
	{
		$this->db->where('Processed', 'false');
		return $this->db->get('inbox');
	}*/
	
	// deprecated
	// get unread message from inbox
	/*function getUnread($id_folder=NULL)
	{
		$this->db->select('count(*) as count');
		$this->db->from('inbox');
		$this->db->join('user_inbox', 'inbox.ID=user_inbox.id_inbox');
		$this->db->where('user_inbox.id_user', $this->session->userdata('id_user'));
		$this->db->where('readed', 'false');
		if($id_folder!=NULL) $this->db->where('id_folder', $id_folder);
		else $this->db->where('id_folder', '1');
		return $this->db->get()->row('count');	
	}*/
	
	// deprecated
	/*function getMessagesbyID($type=NULL, $id_message=NULL)
	{
		$this->db->select('TextDecoded');
		if($type=='inbox') $this->db->select('SenderNumber');
		else $this->db->select('DestinationNumber');
		$this->db->from($type);
		if($type=='sentitems') $this->db->where('SequencePosition', '1');
		$this->db->where('ID', $id_message);
		
		return $this->db->get();
	}*/
	
	// --------------------------------------------------------------------
	
	/**
	* _default method combines the options array with a set of defaults 
	* giving the values in the options array priority.
	*
	* @param array $defaults
	* @param array $options
	* @return array
	*/
	function _default($defaults, $options)
	{
	    return array_merge($defaults, $options);
	}	
	

	// --------------------------------------------------------------------
	
	/**
	 * Get Conversation
	 *
	 * Get list of conversation
	 *
	 * @access	public   		 
	 * @param	mixed $options 
	 * Option: Values
	 * --------------
	 * type inbox, outbox, sentitems
	 * id_folder int
	 * limit int
	 * offset int
	 * trash bool
	 *
	 * @return	object
	 */			
	function get_conversation($options = array())
	{
		// default values
    	$options = $this->_default(array('type' => 'inbox'), $options);
    
    	// register valid type
		$valid_type = array('inbox', 'outbox', 'sentitems');
		
		// check if it's valid type
		if(!in_array($options['type'], $valid_type)) 
		die('Invalid type request on class '.get_class($this).' function '.__FUNCTION__);
							
		$user_id = $this->session->userdata('id_user');
		
		// deprecated
		// if($option=='count') $tmp_select = "count(*) as count";
		// else $tmp_select = "*";
		
		// if($id_folder!=NULL) $tmp_id_folder = $id_folder;
		// else $tmp_id_folder = array_search($type, $valid_type)+1;

		// if id folder is set, else use default value (inbox = 1, outbox = 2, sentitems = 3)
		if(isset($options['id_folder'])) $tmp_id_folder = $options['id_folder'];
		else $tmp_id_folder = array_search($options['type'], $valid_type)+1;
		
		if(isset($options['id_folder']) && $options['id_folder']=='5') $tmp_trash='1';
		else $tmp_trash='0';
		
		switch($options['type'])
		{
			case 'inbox':
				$this->db->from('inbox');
				$this->db->select_max('ReceivingDateTime', 'maxdate');
				$this->db->join('user_inbox','user_inbox.id_inbox=inbox.ID');
				$this->db->where('id_user', $user_id);
				$this->db->where('id_folder', $tmp_id_folder);
				$this->db->group_by('SenderNumber');
				
				$sub_sql = $this->db->_compile_select();
				$this->db->_reset_select();
				
				$this->db->from("($sub_sql) as maxresult,inbox");
				$this->db->join('user_inbox','user_inbox.id_inbox=inbox.ID');
				$this->db->where('id_user', $user_id);
				$this->db->where('id_folder', $tmp_id_folder);
				$this->db->where('trash', $tmp_trash);
				
				$engine = $this->db->platform();
				if($engine=='postgre')
				{
					$this->db->where('"ReceivingDateTime"', 'maxresult.maxdate', FALSE);
					//$this->db->select_max('text');
					//$this->db->select_max('inbox.ReceivingDateTime');
					//$this->db->select($tmp_select);
				}
				else
				{
					$this->db->where('ReceivingDateTime', 'maxresult.maxdate', FALSE);
					//$this->db->select($tmp_select);
				}
				//$this->db->group_by('SenderNumber');
				$this->db->order_by('ReceivingDateTime', 'DESC');
			break;
			
			case 'outbox':
				$this->db->from('outbox');
				$this->db->select_max('SendingDateTime', 'maxdate');
				$this->db->join('user_outbox','outbox.ID=user_outbox.id_outbox');
				$this->db->where('id_user', $user_id);
				$this->db->group_by('DestinationNumber');
				
				$sub_sql = $this->db->_compile_select();
				$this->db->_reset_select();
				
				$this->db->from("outbox, ($sub_sql) as maxresult");
				//$this->db->select($tmp_select);
				$this->db->join('user_outbox','outbox.ID=user_outbox.id_outbox');
				$this->db->where('id_user', $user_id);
				$this->db->where('SendingDateTime', 'maxresult.maxdate', FALSE);
				$this->db->group_by('DestinationNumber');
				$this->db->order_by('SendingDateTime', 'DESC');
							
				// deprecated			
				// $sql = "select ".$tmp_select." from outbox, user_outbox, (select max(SendingDateTime) as maxdate from outbox, user_outbox where outbox.ID=user_outbox.id_outbox and user_outbox.id_user='".$user_id."' group by DestinationNumber) as maxresult";
				// $sql.= " where outbox.ID=user_outbox.id_outbox and user_outbox.id_user='".$user_id."' and outbox.SendingDateTime=maxresult.maxdate group by DestinationNumber order by SendingDateTime DESC";
			break;
			
			case 'sentitems':
				$this->db->from('sentitems');
				$this->db->select_max('SendingDateTime', 'maxdate');
				$this->db->join('user_sentitems','sentitems.ID=user_sentitems.id_sentitems');
				$this->db->where('id_user', $user_id);
				$this->db->where('id_folder', $tmp_id_folder);
				$this->db->where('SequencePosition', '1');
				$this->db->group_by('DestinationNumber');
				
				$sub_sql = $this->db->_compile_select();
				$this->db->_reset_select();
				
				$this->db->from("sentitems, ($sub_sql) as maxresult");
				//$this->db->select($tmp_select);
				$this->db->join('user_sentitems','sentitems.ID=user_sentitems.id_sentitems');
				$this->db->where('id_user', $user_id);
				$this->db->where('id_folder', $tmp_id_folder);
				$this->db->where('SequencePosition', '1');
				$this->db->where('trash', $tmp_trash);
				$this->db->where('SendingDateTime', 'maxresult.maxdate', FALSE);
				$this->db->group_by('DestinationNumber');
				$this->db->order_by('SendingDateTime', 'DESC');
				
				// deprecated			
				// $sql = "select ".$tmp_select." from sentitems, user_sentitems, (select max(SendingDateTime) as maxdate from sentitems, user_sentitems where sentitems.ID=user_sentitems.id_sentitems and user_sentitems.id_user='".$user_id."' and id_folder='".$tmp_id_folder."' and SequencePosition='1' group by DestinationNumber) as maxresult";
				// $sql.= " where sentitems.ID=user_sentitems.id_sentitems and user_sentitems.id_user='".$user_id."' and id_folder='".$tmp_id_folder."' and SequencePosition='1' and user_sentitems.trash='".$tmp_trash."' and sentitems.SendingDateTime=maxresult.maxdate group by DestinationNumber order by SendingDateTime DESC";			
			break;
		}
		
		// deprecated
		// if($option=='paginate') $this->db->limit($limit, $offset);
		
		// if($option=='count') return $this->db->get()->num_rows();
		// else return $this->db->get();
		
		if(isset($options['limit']) && isset($options['offset'])) 
		{
			$this->db->limit($options['limit'], $options['offset']);
		}
		return $this->db->get();
	}

	// --------------------------------------------------------------------
	
	/**
	 * Move Messages
	 *
	 * Move messages from a folder to another folder
	 *
	 * @access	public   		 
	 * @param	mixed $options 
	 * Option: Values
	 * --------------
	 * type conversation, single
	 * current_folder int, source folder, use with conversation type
	 * id_folder int, destination folder, use with conversation and single type
	 * number string, use with conversation type
	 * folder inbox, sentitems, use with single type
	 * id_message, use with single type
	 * trash, bool
	 * 
	 * @return	object
	 */		 
	function move_messages($options = array())
	{
		// default values
    	$options = $this->_default(array('trash' => FALSE), $options);
    	$trash = ($options['trash']) ? '1':'0';
    			
		switch($options['type']) 
		{	
			case 'conversation':
			$id_folder = $options['id_folder'];
			$number = $options['number'];
					
			if($options['current_folder']=='') 
			{ 
				$inbox_folder=1;
				$sentitems_folder=3; 
			}
			else
			{
				$inbox_folder=$sentitems_folder=$options['current_folder'];
			}
			
			// deprecated
			// $inbox = $this->db->query("select ID from inbox where id_folder='".$inbox_folder."' and SenderNumber = '".$number."'");
			
			// deprecated
			/*$this->db->select('ID');
			$this->db->where('id_folder', $inbox_folder);
			$this->db->where('SenderNumber', $number);
			$inbox = $this->db->get('inbox');
			
			foreach($inbox->result() as $tmp):
				// deprecated
				// $this->db->query("update inbox set id_folder='".."' where ID='".$tmp->ID."'");
				// $this->db->query("update user_inbox set trash='0' where id_inbox='".$tmp->ID."'");
				
				$this->db->set('id_folder', $id_folder);
				$this->db->where('ID', $tmp->ID);
				$this->db->update('inbox');
				
				$this->db->set('trash', '0');
				$this->db->where('id_inbox', $tmp->ID);
				$this->db->update('user_inbox');
			endforeach;*/
						
			// proccess inbox
			$this->db->set('i.id_folder', $id_folder);
			$this->db->set('ui.trash', $trash);
			$this->db->where('i.id_folder', $inbox_folder);
			$this->db->where('i.SenderNumber', $number);
			$this->db->where('i.ID = ui.id_inbox'); /* FIXME */
			$this->db->update('inbox as i, user_inbox as ui'); /* FIXME */

			// deprecated
			// $sentitems = $this->db->query("select ID from sentitems where id_folder='".$inbox_folder."' and DestinationNumber = '".$number."'");

			// deprecated
			/*$this->db->select('ID');
			$this->db->where('id_folder', $sentitems_folder);
			$this->db->where('DestinationNumber', $number);
			$sentitems = $this->db->get('sentitems');
			
			foreach($sentitems->result() as $tmp):
				// deprecated
				// $this->db->query("update sentitems set id_folder='".$this->input->post('id_folder')."' where ID='".$tmp->ID."'");
				// $this->db->query("update user_sentitems set trash='0' where id_sentitems='".$tmp->ID."'");

				$this->db->set('id_folder', $id_folder);
				$this->db->where('ID', $tmp->ID);
				$this->db->update('sentitems');
				
				$this->db->set('trash', '0');
				$this->db->where('id_sentitems', $tmp->ID);
				$this->db->update('user_sentitems');				
			endforeach;*/
	
			// proccess sentitems
			$this->db->set('si.id_folder', $id_folder);
			$this->db->set('usi.trash', $trash);
			$this->db->where('si.id_folder', $sentitems_folder);
			$this->db->where('si.DestinationNumber', $number);
			$this->db->where('si.ID = usi.id_sentitems'); /* FIXME */
			$this->db->update('sentitems as si, user_sentitems as usi'); /* FIXME */
			break;	
				
			case 'single':
			$folder = $options['folder'];
			$id_folder = $options['id_folder'];
			$id_message = $options['id_message'];			
			$user_folder = "user_".$folder; // add user prefix
			$id_folder_field = "id_".$folder; // add id prefix
			
			// deprecated
			// $this->db->query("update ".$folder." set id_folder='".$this->input->post('id_folder')."' where ID = '".$this->input->post('id_message')."'");
			// $this->db->query("update user_".$folder." set trash='0' where id_".$folder." = '".$this->input->post('id_message')."'");		
			
			/*
			if($folder=='inbox')
			{
				$multipart['type'] = 'inbox';
				$multipart['option'] = 'check';
				$multipart['id_message'] = $id_message;
				$tmp_check = $this->getMultipart($multipart);
				if($tmp_check->row('UDH')!='')
				{
					$multipart['option'] = 'all';
					$multipart['udh'] = substr($tmp_check->row('UDH'),0,8);
					$multipart['phone_number'] = $tmp_check->row('SenderNumber');					
					foreach($this->getMultipart($multipart)->result() as $part):
					$id_message[] = $part->ID;
					endforeach;	
				}					
			}*/
			
			// deprecated
			/*foreach($id_message as $tmp):
				$this->db->set('id_folder', $id_folder);
				$this->db->where('ID', $tmp);
				$this->db->update($options['folder']);
				
				$this->db->set('trash', '0');
				$this->db->where($id_folder_field, $tmp);
				$this->db->update($user_folder);
			endforeach;*/

			foreach($id_message as $tmp):
		 		$this->db->set('a.id_folder', $id_folder);
				$this->db->set('b.trash', $trash);
				$this->db->where('a.ID', $tmp);
				$this->db->where('a.ID = b.'.$id_folder_field); /* FIXME */
				$this->db->update($folder.' as a, '.$user_folder.' as b'); /* FIXME */
			endforeach;			
			break;
		}
	}	

	// --------------------------------------------------------------------
	
	/**
	 * Delete Messages
	 *
	 * Delete messages, permanent or temporary.
	 *
	 * @access	public   		 
	 * @param	mixed $options 
	 * Option: Values
	 * --------------
	 * type conversation, single
	 * option permanent, temporary, outbox
	 * current_folder int, source folder, use with conversation type
	 * number string
	 * source inbox, outbox, sentitems
	 * id int, use with single type
	 * 
	 * @return	object
	 */	
	function delete_messages($options = array())
	{
		$type = $options['type'];
		$source = $options['source'];
		$option = $options['option'];

		if(isset($options['id'])) $tmp_id = $options['id'];		
		if(isset($options['number'])) $number = $options['number'];	
		if(isset($options['current_folder'])) $current_folder = $options['current_folder'];	
		
		$user_source = "user_".$source;
		$id_source = "id_".$source;	
							
		switch($type)
		{
			case 'conversation':
			if(!isset($options['current_folder'])) { $inbox_folder=1; $sentitems_folder=3; }
			else $inbox_folder=$sentitems_folder=$current_folder;			

			switch($option)
			{
				case 'permanent':
				// inbox
				$inbox = "DELETE i, ui
						FROM inbox AS i
						LEFT JOIN user_inbox AS ui ON ui.id_inbox = i.ID
						WHERE i.SenderNumber = '".$number."' AND ui.trash='1'";
				$this->db->query($inbox);
				
				// sentitems
				$sentitems = "DELETE s, us
						FROM sentitems AS s
						LEFT JOIN user_sentitems AS us ON us.id_sentitems = s.ID
						WHERE s.DestinationNumber = '".$number."' AND us.trash='1'";
				$this->db->query($sentitems);
				break;	
				
				case 'temp':
				// deprecated
				// $inbox = $this->db->query("select ID from inbox where id_folder='".$inbox_folder."' and SenderNumber = '".$number."'");
				
				// deprecated
				// same as moveMessage FIXME DRY
				/*$this->db->select('ID');
				$this->db->where('id_folder', $inbox_folder);
				$this->db->where('SenderNumber', $number);
				$inbox = $this->db->get('inbox');
				
				foreach($inbox->result() as $tmp):
					// deprecated
					//$this->db->query("update inbox set id_folder='5' where ID='".$tmp->ID."'");
					//$this->db->query("update user_inbox set trash='1' where id_inbox='".$tmp->ID."'");				
					
					$this->db->set('id_folder', '5');
					$this->db->where('ID', $tmp->ID);
					$this->db->update('inbox');
					
					$this->db->set('trash', '1');
					$this->db->where('id_inbox', $tmp->ID);
					$this->db->update('user_inbox');					
				endforeach;*/
								
				// use move_messages function
				$param['type'] = 'conversation';
				$param['number'] = $number;
				$param['current_folder'] = $options['current_folder'];
				$param['id_folder'] = '5';
				$param['trash'] = TRUE;
				//echo print_r($param);
				$this->move_messages($param);
				echo $this->db->last_query();
				
				// deprecated
				// $sentitems = $this->db->query("select ID from sentitems where id_folder='".$sentitems_folder."' and DestinationNumber = '".$number."'");
								
				// deprecated
				/*$this->db->select('ID');
				$this->db->where('id_folder', $sentitems_folder);
				$this->db->where('DestinationNumber', $number);
				$sentitems = $this->db->get('sentitems');
				
				foreach($sentitems->result() as $tmp):
					// deprecated
					// $this->db->query("update sentitems set id_folder='5' where ID='".$tmp->ID."'");
					// $this->db->query("update user_sentitems set trash='1' where id_sentitems='".$tmp->ID."'");
					
					$this->db->set('id_folder', '5');
					$this->db->where('ID', $tmp->ID);
					$this->db->update('sentitems');
					
					$this->db->set('trash', '1');
					$this->db->where('id_sentitems', $tmp->ID);
					$this->db->update('user_sentitems');
				endforeach;	*/					
				break;
				
				case 'outbox':
				$tmp_sql = $this->get_messages(array('type' => 'outbox', 'number' => $number))->result_array();
				// looping all message
				foreach($tmp_sql as $tmp):
				//check multipart message
				$multipart = array('type' => 'outbox', 'option' => 'check', 'id_message' => $tmp['ID']);
				if($this->getMultipart($multipart)=='true')
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
				foreach($tmp_id as $tmp):
					$this->db->delete("user_".$source, array('id_'.$source => $tmp));
					$this->db->delete($source, array('ID' => $tmp));				
				endforeach;

				// deprecated
				/*if($source=="inbox")
				{
					// check multipart
					$multipart['type'] = 'inbox';
					$multipart['option'] = 'check';
					$multipart['id_message'] = $tmp_id;
					$tmp_check = $this->getMultipart($multipart);
		
					if($tmp_check->row('UDH')!='')
					{
						$multipart['option'] = 'all';
						$multipart['udh'] = substr($tmp_check->row('UDH'),0,8);
						$multipart['phone_number'] = $tmp_check->row('SenderNumber');					
						foreach($this->getMultipart($multipart)->result() as $part):
							$this->db->delete("user_".$source, array('id_'.$source => $part->ID));
							$this->db->delete($source, array('ID' => $part->ID));
						endforeach;	
					}	
				}*/
				break;	
				
				case 'temp':
				// deprecated
				// $this->db->query("update ".$source." set id_folder='5' where ID = '".$tmp_id."'");
				// $this->db->query("update user_".$source." set trash='1' where id_".$source." = '".$tmp_id."'");


				// use move_messages function
				$param['type'] = 'single';
				$param['id_message'] = $tmp_id;
				$param['folder'] = $source;
				$param['id_folder'] = '5';
				$param['trash'] = TRUE;
				$this->move_messages($param);
				
				// deprecated
				/*$this->db->set('id_folder', '5');
				$this->db->where('ID', $tmp_id);
				$this->db->update($source);

				$this->db->set('trash', '1');
				$this->db->where($id_source, $tmp_id);
				$this->db->update($user_source);	

				if($source=="inbox")
				{
					echo "here";
					// check multipart
					$multipart['type'] = 'inbox';
					$multipart['option'] = 'check';
					$multipart['id_message'] = $tmp_id;
					$tmp_check = $this->getMultipart($multipart);
					echo $tmp_check->row('UDH');
					if($tmp_check->row('UDH')!='')
					{
						$multipart['option'] = 'all';
						$multipart['udh'] = substr($tmp_check->row('UDH'),0,8);
						$multipart['phone_number'] = $tmp_check->row('SenderNumber');					
						foreach($this->getMultipart($multipart)->result() as $part):
							$this->db->set('id_folder', '5');
							$this->db->where('ID', $part->ID);
							$this->db->update($source);
			
							$this->db->set('trash', '1');
							$this->db->where($id_source, $part->ID);
							$this->db->update($user_source);
						endforeach;	
					}
				}*/
				break;
				
				case 'outbox':
				//check multipart message
				$multipart = array('type' => 'outbox', 'option' => 'check', 'id_message' => $tmp_id[0]);
				if($this->getMultipart($multipart)=='true')
				$this->db->delete('outbox_multipart', array('ID' => $tmp_id[0]));
				
				$this->db->delete('outbox', array('ID' => $tmp_id[0]));
				break;
			}		
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
		
	function getMultipart($param)
	{	
		switch($param['option'])
		{
			case 'check':
				if($param['type']=='outbox')
				{
					$this->db->select('MultiPart');
					$this->db->where('ID', $param['id_message']);
					return $this->db->get('outbox')->row('MultiPart');					
				}
				else if($param['type']=='inbox')
				{
					$this->db->where('ID', $param['id_message']);
					return $this->db->get('inbox');	
				}
				else if($param['type']=='sentitems')
				{	
					$this->db->select('count(*) as count');
					$this->db->where('ID', $param['id_message']);
					$this->db->where('SequencePosition >', 1);
					return $this->db->get('sentitems')->row('count');
				}
			break;
			
			case 'all':
				if($param['type']=='outbox') 
				{
					$this->db->where('ID', $param['id_message']);
					$this->db->order_by('SequencePosition');
					return $this->db->get('outbox_multipart');
				}
				else if($param['type']=='inbox')
				{
					$this->db->where('SenderNumber', $param['phone_number']);
					$this->db->like('UDH', $param['udh'], 'after');
					$this->db->not_like('UDH', '1', 'before');
					$this->db->order_by('UDH');
					return $this->db->get('inbox');	
				}
				else if($param['type']=='sentitems')
				{
					$this->db->where('ID', $param['id_message']);
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
		foreach($id as $tmp):
			$data = array ('Processed' => 'true');
			$this->db->where('ID', $tmp);		
			$this->db->update('inbox', $data);
		endforeach;
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
