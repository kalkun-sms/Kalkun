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
 * Handle all messages database activity 
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
		
		// Set mb encoding
		mb_internal_encoding($this->config->item('charset'));
	}

	// --------------------------------------------------------------------
    
    function _send_wap_link($data)
    {
        if($data['dest']!=NULL && $data['date']!=NULL && $data['url']!=NULL && $data['message']!=NULL && $data['type']=='waplink')
		{
		    $cmd = '"'.$this->config->item('gammu_sms_inject').'"' ." -c " .'"'.$this->config->item('gammu_config').'"'. " WAPINDICATOR " . $data['dest']. " \"". $data['url']. "\"  \"".$data['message']."\" ";
            $ret = exec ($cmd);
            preg_match("/with ID ([\d]+)/i",$ret,$matches);
            $insert_id = $matches[1];
            if(empty($insert_id))  die( "<div class=\"notif\"><font color='red'>Could Not Send Message. Make Sure Gammu Path is correctly set.</font></div>");
            $this->db->update('outbox', array('SendingDateTime' => $data['date']), "ID = $insert_id" );
            $this->Kalkun_model->add_sms_used($this->session->userdata('id_user'));	
		}
		else 
		{
			echo 'Parameter invalid';	
		}
    }
    
    
    // --------------------------------------------------------------------
	
	/**
	 * Send Messages
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
		// default values
    	$data = $this->_default(array('SenderID' => NULL, 'CreatorID' => '', 'validity' => '-1'), $data);
    	
        // check if wap msg
        if(isset($data['type']) AND $data['type']=='waplink') { $this->_send_wap_link($data); return ;} 
		
        if($data['dest']!=NULL && $data['date']!=NULL && $data['message']!=NULL)
		{	
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

			// Check message's length
			$messagelength = $this->_get_message_length($data['message'], $data['coding']);			

			// Multipart message
			if($messagelength > $standar_length)
			{
				$UDH_length = 7;
				$multipart_length = $standar_length - $UDH_length; 
			
				// generate UDH
				$UDH = "050003";
				$hex = dechex(mt_rand(0, 255));
                $hex = str_pad($hex, 2, "0", STR_PAD_LEFT);
                $UDH .= strtoupper($hex);
				$data['UDH'] = $UDH;
						
				// split string
				$tmpmsg = $this->_get_message_multipart($data['message'], $data['coding'], $multipart_length);			
				
				// count part message
				$part = count($tmpmsg);
				if($part < 10) $part = '0'.$part;
				
				// insert first part to outbox and get last outbox ID
				$data['option'] = 'multipart';
				$data['message'] = $tmpmsg[0];
				$data['part'] = $part;
				$outboxid = $this->_send_message_route($data);
                $this->Kalkun_model->add_sms_used($data['uid']);	// FIXME
				
				// insert the rest part to Outbox Multipart
				for($i=1; $i<count($tmpmsg); $i++) 
				{
				    $this->_send_message_multipart($outboxid, $tmpmsg[$i], $i, $part, $data['coding'], $data['class'], $UDH);
                    $this->Kalkun_model->add_sms_used($data['uid']);		
                }
			}
			else 
			{
				$data['option'] = 'single';
				$this->_send_message_route($data);
				$this->Kalkun_model->add_sms_used($data['uid']); // FIXME		
			}	
		}
		else 
		{
			echo 'Parameter invalid';	
		}
	}

	// --------------------------------------------------------------------
	
	/**
	 * Send Message Route
	 *
	 * @access	private   		 
	 * @param	mixed
	 * 
	 * @return
	 */		
	function _send_message_route($tmp_data)
	{
        // remove spaces and dashes if any
        $tmp_data['dest'] = str_replace(" ", "", $tmp_data['dest']);
        $tmp_data['dest'] = str_replace("-", "", $tmp_data['dest']);

		$data = array (
				'InsertIntoDB' => date('Y-m-d H:i:s'),
				'SendingDateTime' => $tmp_data['date'],
				'DestinationNumber' => $tmp_data['dest'],
				'Coding' => $tmp_data['coding'],
				'Class' => $tmp_data['class'],
				'CreatorID' => $tmp_data['CreatorID'],
				'SenderID' => $tmp_data['SenderID'],
				'TextDecoded' => $tmp_data['message'],
                'RelativeValidity' => $tmp_data['validity'],
				'DeliveryReport' => $tmp_data['delivery_report']			
				);
					
		if($tmp_data['option']=='multipart')
		{
			$data['MultiPart'] = 'true'; 
			$data['UDH'] = $tmp_data['UDH'].$tmp_data['part'].'01'; 
		}
					
		$this->db->insert('outbox', $data);
		
		$last_outbox_id = $this->db->insert_id();
		$user = array(
				'id_outbox' => $last_outbox_id,
				'id_user' => $tmp_data['uid']
				);
		$this->db->insert('user_outbox', $user);
		
		if($tmp_data['option']=='multipart') return $last_outbox_id;
	}	

	// --------------------------------------------------------------------
	
	/**
	 * Send Message Multipart
	 *
	 * @access	private   		 
	 * @param	mixed
	 * 
	 * @return
	 */		
	function _send_message_multipart($outboxid, $message, $pos, $part, $coding, $class, $UDH) 
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

	// --------------------------------------------------------------------
	function search_messages($options = array())
	{
        if(!isset($options['number'])) if(!isset($options['search_string']))  die("No String to Search For");
		
		// Inbox
		$options['type'] = 'inbox';
		$user_folder = "user_".$options['type'];
		$this->db->from('inbox');
        $tmp_number = 'SenderNumber'; 
		$tmp_order = 'ReceivingDateTime';
		$udh_where = "(".$this->_protect_identifiers("UDH")." = '' OR ".$this->_protect_identifiers("UDH")." LIKE '%1')";
		$this->db->where($udh_where, NULL, FALSE);				
		
        if(isset($options['search_string'])) $this->db->like('TextDecoded', $options['search_string']);
		
		// if phone number is set
		if(isset($options['number'])) $this->db->like($tmp_number, $options['number']);
		
		// date range
        if(isset($options['date_from']) AND isset($options['date_to']))
        {
        	$this->db->where($tmp_order.' >', $options['date_from']);
        	$this->db->where($tmp_order.' <', $options['date_to']);
        }
        
        // custom folder
        if(isset($options['id_folder']))
        {
        	if ($options['id_folder']!='all') $this->db->where('id_folder', $options['id_folder']);
        }
        
        //remove already trashed
		if(!isset($options['trash'])) $this->db->where('id_folder !=', '5');
        
        // join user table
		if(isset($options['uid']))
		{
			$this->db->join($user_folder, $user_folder.'.id_'.$options['type'].'='.$options['type'].'.ID');
			$this->db->where($user_folder.'.id_user',$options['uid']);	
		}
        
        $result = $this->db->get();
        
        $inbox = $result->result_array();
        
        // add global date for sorting
		foreach($inbox as $key=>$tmp):
		$inbox[$key]['globaldate'] = $inbox[$key]['ReceivingDateTime'];
		$inbox[$key]['source'] = 'inbox';
		endforeach;
          
        // Sentitems              
		$options['type'] = 'sentitems';
		$user_folder = "user_".$options['type'];
        $this->db->from('sentitems');
		$tmp_number = 'DestinationNumber';
		$tmp_order = 'SendingDateTime';	
        $this->db->where('SequencePosition', '1');		
			
		if(isset($options['search_string'])) $this->db->like('TextDecoded', $options['search_string']);
		
		// if phone number is set
		if(isset($options['number'])) $this->db->like($tmp_number, $options['number']);

		// date range
        if(isset($options['date_from']) AND isset($options['date_to']))
        {
        	$this->db->where($tmp_order.' >', $options['date_from']);
        	$this->db->where($tmp_order.' <', $options['date_to']);
        }		

        // custom folder
        if(isset($options['id_folder']))
        {
        	if ($options['id_folder']!='all') $this->db->where('id_folder', $options['id_folder']);
        }        
        
        // sending status
        if(isset($options['status']))
        {
        	switch(strtolower($options['status']))
        	{
        		case 'delivered':
        			$this->db->where_in('Status', array('SendingOK', 'SendingOKNoReport', 'DeliveryOK', 'DeliveryPending'));
        		break;
        		
        		case 'failed':
        			$this->db->where_in('Status', array('SendingError', 'DeliveryFailed', 'DeliveryUnknown', 'Error'));     		
        		break;
        		
        		default:
        			// nothing to do
        		break;
        	}	
        }
        
         //remove already trashed
		if(!isset($options['trash'])) $this->db->where('id_folder !=', '5');
        
        // join user table
		if(isset($options['uid']))
		{
			$this->db->join($user_folder, $user_folder.'.id_'.$options['type'].'='.$options['type'].'.ID');
			$this->db->where($user_folder.'.id_user',$options['uid']);	
		}
        
        $result = $this->db->get();
        $sentitems = $result->result_array();
         
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
    
        $return_data= array();
        $return_data['total_rows'] = count($data['messages']);
        $return_data['messages'] = array();
       
        //paginate
        if(isset($options['offset'] ) && isset($options['limit'])) 
        {
          for($i = $options['offset'] ; $i  <  min( ($options['offset']+ $options['limit']), $return_data['total_rows']) ;  $i++)
          {
             $return_data['messages'][] = $data['messages'][$i];
          }
        }
        else 
        {
           $return_data['messages'] = $data['messages'];
        }
    	
        return  (object) $return_data;
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

		// if phone number is set
		if(isset($options['number']) AND $options['number']!='sending_error') $arr_number = $this->Phonebook_model->convert_phonenumber(array('number' => $options['number']));

		$user_folder = "user_".$options['type'];
		$this->db->from($options['type']);
		
		// set valid field name
		if($options['type']=='inbox')
		{
			$tmp_number = 'SenderNumber'; 
			$tmp_order = 'ReceivingDateTime';
			
			$udh_where = "(".$this->_protect_identifiers("UDH")." = '' OR ".$this->_protect_identifiers("UDH")." LIKE '%1')";
			$this->db->where($udh_where, NULL, FALSE);				
		}
		else 
		{
			$tmp_number = 'DestinationNumber';
			$tmp_order = 'SendingDateTime';	
			if($options['type']=='sentitems') $this->db->where('SequencePosition', '1');		
		}
						
		// if id message is set
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
         
        //if search string is set
		if(isset($options['search_string'])) $this->db->like('TextDecoded', $options['search_string']);
		
		// if phone number is set
		if(isset($options['number']) AND $options['number']!='sending_error') $this->db->where_in($tmp_number, $arr_number);
		
		// sentitems only error
		if($options['type']=='sentitems' AND isset($options['number']) AND $options['number']=='sending_error') $this->db->where('Status', 'SendingError');
		
		// if readed is set
		if(isset($options['readed']) && is_bool($options['readed'])) 
		{
			// valid only for inbox
			if($options['type']=='inbox')
			{
				$readed = ($options['readed']) ? 'true' : 'false'; 
				$this->db->where('readed', $readed);	
			}
		}

		// if processed is set
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
			
			// if trash is set
			if(isset($options['trash']) && is_bool($options['trash'])) $this->db->where($user_folder.'.trash', $options['trash']);	
		}
    
		if(isset($options['order_by'])) $this->db->order_by($options['order_by'], isset($options['order_by_type'])?$options['order_by_type']:'DESC');

		if(isset($options['limit']) && isset($options['offset'])) 
		{
			$this->db->limit($options['limit'], $options['offset']);
		}		
		
		$result = $this->db->get();
		return $result;
	}

	// --------------------------------------------------------------------
	
	/**
	* _protect_identifiers
	*
	* Ugly hack to add backticks to database field
	*
	* @param string $identifier
	* @return string
	*/
	function _protect_identifiers($identifier=NULL)
	{
		$this->load->helper('kalkun');
		$escape_char;
		$escaped_identifer="";
		
		// get database engine
		$db_engine = $this->db->platform();
		$escape_char = get_database_property($db_engine);
		$escape_char = $escape_char['escape_char'];
		
		$sub = explode(".", $identifier);
		$sub_count = count($sub);

		foreach($sub as $key => $tmp)
		{
			$escaped_identifer.=$escape_char.$tmp.$escape_char;
			
			// if this is not the last
			if($key!=$sub_count-1)
			{
				$escaped_identifer.=".";
			}
		}
		
	    return $escaped_identifer;
	}

	// --------------------------------------------------------------------
	
	/**
	* _get_message_length
	*
	* Get message length
	* Watch out for special character ^ { } \ [ ] ~ | € (only for non unicode coding)
	* 
	* @param string $message, $coding
	* @return string
	*/		
	function _get_message_length($message=NULL, $coding=NULL)
	{
		$msg_length = 0;
		if($coding=="Default_No_Compression")
		{			
			$msg_char = $this->_string_split($message);
			foreach($msg_char as $char)
			{
				if($this->_is_special_char($char))
				{
					$msg_length += 2;
				}
				else
				{
					$msg_length += 1;
				}
				
			}
			return $msg_length;
		}
		else
		{
			return mb_strlen($message);
		}
	}

	function _is_special_char($char)
	{
		// GSM Default 7-bit special character (count as 2 char)
		$special_char = array('^','{','}','[',']','~','|','€','\\');
		
		// GSM Default 7-bit character (count as 1 char)
		$default = array('@', '£', '$', '¥', 'è', 'é', 'ù', 'ì', 'ò', 'Ç', 'Ø', 'ø', 'Å', 'å', 'Δ', '_', 'Φ', 'Γ', 'Λ',
					'Ω', 'Π', 'Ψ', 'Σ', 'Θ', 'Ξ', 'Æ', 'æ', 'É', '!', '"', '#', '¤', '%', '&', '\'', '(',')', '*', '+', 
					',', '-', '.', '/', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', ':', ';', '<', '=', '>', '?', 
					'¡', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 
					'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'Ñ', '§', '¿', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 
					'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'ä', 'ñ', 'à');
		
		if(in_array($char, $special_char))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}	

	function _string_split($string, $length=1)
	{
		$len = mb_strlen($string);
		$result = array();
		for($i=0;$i<$len;$i+=$length)
		{
			$char = mb_substr($string, $i, $length);
			array_push($result, $char);
	    }
	    return $result;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Get Modem List
     *
     * Get Modem List based on criteria
     *
     * @access  public           
     * @param   $criteria $order
     * 
     * @return  string  
     */ 
    function get_modem_list($criteria='time', $order='asc')
    {
    	$valid_criteria = array('time', 'count');
    	$valid_order = array('asc', 'desc');
    	if (!in_array($criteria, $valid_criteria) OR !in_array($order, $valid_order)) 
    	{
    		return;	
    	}
    	
    	// set valid field based on criteria
    	if ($criteria=='time')
    	{
    		$criteria_field = 'UpdatedInDB';
    	}
    	else
    	{
    		$criteria_field = 'Sent';	
    	}
    	
		$this->db->from('phones');
		$this->db->select('ID');
		$this->db->where('Send', 'yes');
		$this->db->order_by($criteria_field, $order);
		return $this->db->get();
    }

	// --------------------------------------------------------------------
	
	/**
	* _get_message_multipart
	*
	* Get message multipart
	* Split message into multipart
	*
	* @param string $message, $coding
	* @return string
	*/		
	function _get_message_multipart($message=NULL, $coding=NULL, $multipart_length=NULL)
	{
		if($coding=='Default_No_Compression')
		{
			$char = $this->_string_split($message);
			$string = "";
			$left = 153;
			$char_taken = 0;
			$msg = array();
			
			while (list($key, $val) = each($char))
			{
				if($left>0)
				{
					if($this->_is_special_char($val))
					{
						if($left>1)
						{
							$string .= $val;
							$left -= 2;
						}
						else
						{
							$left = 0;
							prev($char);
							$char_taken--;
						}
					}
					else
					{
						$string .= $val;
						$left -= 1;
					}
				}
				$char_taken++;
				
				if($left==0 OR $char_taken==mb_strlen($message))
				{
					$msg[] = $string;
					$string = "";
					$left = 153;
				}
			}
		}
		else
		{
			$msg = $this->_string_split($message, $multipart_length);
		}
		return $msg;
	}
		
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

		// if id folder is set, else use default value (inbox = 1, outbox = 2, sentitems = 3)
		if(isset($options['id_folder'])) $tmp_id_folder = $options['id_folder'];
		else $tmp_id_folder = array_search($options['type'], $valid_type)+1;
		
		if(isset($options['id_folder']) && $options['id_folder']=='5') $tmp_trash='1';
		else $tmp_trash='0';
		
		switch($options['type'])
		{
			case 'inbox':
				$this->db->from('inbox');
				//$this->db->select_max($this->_protect_identifiers('ReceivingDateTime'), $this->_protect_identifiers('maxdate'), FALSE);
                $this->db->select_max($this->_protect_identifiers('ID'), $this->_protect_identifiers('maxID'), FALSE);
				$this->db->join('user_inbox','user_inbox.id_inbox=inbox.ID');
				$this->db->where('id_user', $user_id);
				$this->db->where('id_folder', $tmp_id_folder);
				$this->db->group_by('SenderNumber');
				
				$sub_sql = $this->db->_compile_select();
				$this->db->_reset_select();
				
				$this->db->distinct();
				//$this->db->from("($sub_sql) as ".$this->_protect_identifiers('maxresult').",inbox");
                $this->db->from("($sub_sql) as ".$this->_protect_identifiers('maxresult').",inbox");
				$this->db->join('user_inbox','user_inbox.id_inbox=inbox.ID');
				$this->db->where('id_user', $user_id);
				$this->db->where('id_folder', $tmp_id_folder);
				$this->db->where('trash', $tmp_trash);
				
				//$this->db->where($this->_protect_identifiers('ReceivingDateTime'), $this->_protect_identifiers('maxresult.maxdate'), FALSE);
                $this->db->where($this->_protect_identifiers('ID'), $this->_protect_identifiers('maxresult.maxID'), FALSE);
				//$this->db->group_by('SenderNumber');
				$this->db->order_by('ReceivingDateTime', 'DESC');
			break;
			
			case 'outbox':
				$this->db->from('outbox');
				$this->db->select_max($this->_protect_identifiers('SendingDateTime'), $this->_protect_identifiers('maxdate'), FALSE);
				$this->db->join('user_outbox','outbox.ID=user_outbox.id_outbox');
				$this->db->where('id_user', $user_id);
				$this->db->group_by('DestinationNumber');
				
				$sub_sql = $this->db->_compile_select();
				$this->db->_reset_select();
				
				$this->db->distinct();
				$this->db->from("($sub_sql) as ".$this->_protect_identifiers('maxresult').",outbox");
				$this->db->join('user_outbox','outbox.ID=user_outbox.id_outbox');
				$this->db->where('id_user', $user_id);
				$this->db->where($this->_protect_identifiers('SendingDateTime'), $this->_protect_identifiers('maxresult.maxdate'), FALSE);
				//$this->db->group_by('DestinationNumber');
				$this->db->order_by('SendingDateTime', 'DESC');
			break;
			
			case 'sentitems':
				$this->db->from('sentitems');
				$this->db->select_max($this->_protect_identifiers('SendingDateTime'), $this->_protect_identifiers('maxdate'), FALSE);
				$this->db->join('user_sentitems','sentitems.ID=user_sentitems.id_sentitems');
				$this->db->where('id_user', $user_id);
				$this->db->where('id_folder', $tmp_id_folder);
				$this->db->where('SequencePosition', '1');
				$this->db->group_by('DestinationNumber');
				
				$sub_sql = $this->db->_compile_select();
				$this->db->_reset_select();
				
				$this->db->distinct();
				$this->db->from("($sub_sql) as ".$this->_protect_identifiers('maxresult').",sentitems");
				$this->db->join('user_sentitems','sentitems.ID=user_sentitems.id_sentitems');
				$this->db->where('id_user', $user_id);
				$this->db->where('id_folder', $tmp_id_folder);
				$this->db->where('SequencePosition', '1');
				$this->db->where('trash', $tmp_trash);
				$this->db->where($this->_protect_identifiers('SendingDateTime'), $this->_protect_identifiers('maxresult.maxdate'), FALSE);
				//$this->db->group_by('DestinationNumber');
				$this->db->order_by('SendingDateTime', 'DESC');			
			break;
		}
				
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
			
			// start transcation		
			$this->db->trans_start();
							
			// proccess inbox
			$this->db->select('ID');
			$this->db->from('inbox');
			$this->db->where('id_folder', $inbox_folder);
			$this->db->where('SenderNumber', $number);
			$this->db->join('user_inbox', 'user_inbox.id_inbox = inbox.ID');
			$res = $this->db->get();
			if ($res->num_rows() > 0)
			{
				foreach ($res->result_array() as $row)
				{
					$ids[] = $row['ID'];
				}
				$this->db->set('id_folder', $id_folder);
				$this->db->where_in('ID', $ids);		
				$this->db->update('inbox');
				$this->db->set('trash', $trash);
				$this->db->where_in('id_inbox', $ids);
				$this->db->update('user_inbox');
			}

			// proccess sentitems
			$this->db->select('ID');
			$this->db->from('sentitems');
			$this->db->where('id_folder', $sentitems_folder);
			$this->db->where('DestinationNumber', $number);
			$this->db->join('user_sentitems', 'user_sentitems.id_sentitems = sentitems.ID');
			$res = $this->db->get();
			if ($res->num_rows() > 0)
			{
				foreach($res->result_array() as $row)
				{
					$ids[] = $row['ID'];
				}
				$this->db->set('id_folder', $id_folder);
				$this->db->where_in('ID', $ids);		
				$this->db->update('sentitems');
				$this->db->set('trash', $trash);
				$this->db->where_in('id_sentitems', $ids);
				$this->db->update('user_sentitems');
			}

			$this->db->trans_complete();
			break;
				
			case 'single':
			$folder = $options['folder'];
			$id_folder = $options['id_folder'];
			$id_message = $options['id_message'];			
			$user_folder = "user_".$folder; // add user prefix
			$id_folder_field = "id_".$folder; // add id prefix

			foreach($id_message as $tmp):
		 		$this->db->set('a.id_folder', $id_folder);
				$this->db->set('b.trash', $trash);
				$this->db->where('a.ID', $tmp);
				$update_id_folder_field = 'b.'.$id_folder_field;
				$this->db->where($this->_protect_identifiers('a.ID'), $this->_protect_identifiers($update_id_folder_field), FALSE);
				$update_folder = $this->_protect_identifiers($folder);
				$update_folder_alias = $this->_protect_identifiers('a');
				$update_user_folder = $this->_protect_identifiers($user_folder);
				$update_user_folder_alias = $this->_protect_identifiers('b');				
				$this->db->update($update_folder.' as '.$update_folder_alias.', '.$update_user_folder.' as '.$update_user_folder_alias);
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
		$user_id = $this->session->userdata('id_user');
		
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

			$trash = FALSE;
			switch($option)
			{
				case 'permanent':
				
				// if it's coming from trash
				if(isset($current_folder) && $current_folder=='5') $trash = TRUE;	
				
				// get inbox
				$param = array('id_folder' => $inbox_folder, 'number' => $number, 'trash' => $trash, 'uid' => $user_id);
				$inbox = $this->get_messages($param);
				
				foreach($inbox->result() as $tmp)
				{
					// start transcation    
					$this->db->trans_start();
					$this->db->where('ID', $tmp->id_inbox);
					$this->db->delete('inbox');
		
					$this->db->where('id_inbox', $tmp->id_inbox);
					$this->db->delete('user_inbox');	
					$this->db->trans_complete();				
				}
				
				// deprecated
				// inbox
				/*$inbox = "DELETE i, ui
						FROM inbox AS i
						LEFT JOIN user_inbox AS ui ON ui.id_inbox = i.ID
						WHERE i.SenderNumber = '".$number."' AND ui.trash='1'";
				$this->db->query($inbox);*/
				
				// get sentitems
				$param = array('id_folder' => $sentitems_folder, 'type' => 'sentitems', 'number' => $number, 'trash' => $trash, 'uid' => $user_id);
				$sentitems = $this->get_messages($param);
				
				foreach($sentitems->result() as $tmp)
				{
					// start transcation    
					$this->db->trans_start();
					$this->db->where('ID', $tmp->id_sentitems);
					$this->db->delete('sentitems');
		
					$this->db->where('id_sentitems', $tmp->id_sentitems);
					$this->db->delete('user_sentitems');	
					$this->db->trans_complete();								
				}				
				
				// sentitems
				/*$sentitems = "DELETE s, us
						FROM sentitems AS s
						LEFT JOIN user_sentitems AS us ON us.id_sentitems = s.ID
						WHERE s.DestinationNumber = '".$number."' AND us.trash='1'";
				$this->db->query($sentitems);*/
				break;	
				
				case 'temp':	
				// use move_messages function
				$param['type'] = 'conversation';
				$param['number'] = $number;
				$param['current_folder'] = $options['current_folder'];
				$param['id_folder'] = '5';
				$param['trash'] = TRUE;
				$this->move_messages($param);			
				break;
				
				case 'outbox':
				$tmp_sql = $this->get_messages(array('type' => 'outbox', 'number' => $number))->result_array();
				// looping all message
				foreach($tmp_sql as $tmp):
				//check multipart message
				$multipart = array('type' => 'outbox', 'option' => 'check', 'id_message' => $tmp['ID']);
				
				// start transcation    
				$this->db->trans_start();
				
				if($this->get_multipart($multipart)=='true')
				$this->db->delete('outbox_multipart', array('ID' => $tmp['ID']));
				
				$this->db->delete('outbox', array('ID' => $tmp['ID']));
				$this->db->trans_complete();				
				endforeach;		
				break;
			}
			break;		
			
			case 'single':
			switch($option)
			{				
				case 'permanent':
				foreach($tmp_id as $tmp):
					// start transcation    
					$this->db->trans_start();
					$this->db->delete("user_".$source, array('id_'.$source => $tmp));
					$this->db->delete($source, array('ID' => $tmp));	
					$this->db->trans_complete();
				endforeach;
				break;	
				
				case 'temp':
				// use move_messages function
				$param['type'] = 'single';
				$param['id_message'] = $tmp_id;
				$param['folder'] = $source;
				$param['id_folder'] = '5';
				$param['trash'] = TRUE;
				$this->move_messages($param);
				break;
				
				case 'outbox':
				//check multipart message
				$multipart = array('type' => 'outbox', 'option' => 'check', 'id_message' => $tmp_id[0]);
				
				// start transcation    
				$this->db->trans_start();
				if($this->get_multipart($multipart)=='true')
				$this->db->delete('outbox_multipart', array('ID' => $tmp_id[0]));
				
				$this->db->delete('outbox', array('ID' => $tmp_id[0]));
				$this->db->trans_complete();
				break;
			}		
			break;		
		}		
	}	
	
	// --------------------------------------------------------------------
	
	/**
	 * Get Multipart
	 *
	 * Get and check for multipart messages
	 *
	 * @access	public   		 
	 * @param	mixed
	 * 
	 * @return object
	 */	
	function get_multipart($param)
	{	
		// default values
    	$param = $this->_default(array('type' => 'inbox'), $param);
    	
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
					$this->db->where('ID', $param['id_message']);
					$this->db->where('SequencePosition >', 1);
					return $this->db->get('sentitems')->num_rows();
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

	// --------------------------------------------------------------------
	
	/**
	 * Update readed inbox
	 *
	 * @access	public   		 
	 * @param	mixed
	 * 
	 * @return
	 */	
	function update_read($id)
	{
		$data = array ('readed' => 'true');
		$this->db->where('ID', $id);		
		$this->db->update('inbox', $data);
	}
			
	// --------------------------------------------------------------------
	
	/**
	 * Utility function used by the daemon and base controller
	 *
	 * @access	public   		 
	 * @param	mixed
	 * 
	 * @return
	 */		
	function insert_user_sentitems($id_message, $user_id)
	{
		$this->db->set('id_user', $user_id);
		$this->db->set('id_sentitems', $id_message);	
		$this->db->insert('user_sentitems');
	}

	function get_user_outbox($user_id)
	{
		$this->db->where('id_user', $user_id);
		return $this->db->get("user_outbox");
	}		

	function delete_user_outbox($id_message)
	{
		$this->db->where('id_outbox', $id_message);
		$this->db->delete("user_outbox");
	}	

	// Update processed inbox
	function update_processed($id)
	{
		foreach($id as $tmp):
			$data = array ('Processed' => 'true');
			$this->db->where('ID', $tmp);		
			$this->db->update('inbox', $data);
		endforeach;
	}

	// Update ownership
	function update_owner($msg_id, $user_id)
	{
		if (!is_array($user_id))
		{
			$user_id = (array) $user_id;
		}
		
		$count = count($user_id);
		for ($i=0; $i<$count; $i++)
		{
			$this->db->where('id_user', $user_id[$i]);
			$this->db->where('id_inbox', $msg_id);
			$exist = $this->db->count_all_results('user_inbox');
			if ($exist == 0)
			{
				$data = array ('id_user' => $user_id[$i], 'id_inbox' => $msg_id);
				$this->db->insert('user_inbox', $data);
			}
			
			// not the last element
			if ($i != $count-1)
			{
				// copy message
				$msg_id = $this->copy_message($msg_id);
			}
		}
	}	
	
	// --------------------------------------------------------------------
	
	/**
	 * Copy message
	 *
	 * @access	public   		 
	 * @param	integer
	 * 
	 * @return integer
	 */	
	function copy_message($msg_id)
	{
		$data = $this->db->get_where('inbox', array('ID' => $msg_id));
		$data = $data->row();
		unset($data->ID);
		$data->Processed = 'true';
		$data->readed = 'false';
		if (!empty($data->UDH))
		{
			// Generate new UDH
			$newUDH = '';
			for ($j=1; $j<=4; $j++)
			{
				$newUDH .= strtoupper(dechex(mt_rand(0, 255)));
			}
			$data->UDH = $newUDH.substr($data->UDH, -4);
		}
		$this->db->insert('inbox', $data);
		$new_msg_id = $this->db->insert_id();
		
		// check multipart
		$multipart = array('type' => 'inbox', 'option' => 'check', 'id_message' => $msg_id);
		$tmp_check = $this->get_multipart($multipart);
		
		if ($tmp_check->row('UDH')!='')
		{
			$multipart = array('option' => 'all', 'udh' => substr($tmp_check->row('UDH'),0,8));	
			$multipart['phone_number'] = $tmp_check->row('SenderNumber');
			$multipart['type'] = 'inbox';				
			foreach($this->get_multipart($multipart)->result() as $part)
			{
				unset($part->ID);
				$part->Processed = 'true';
				$data->readed = 'false';
				$part->UDH = $newUDH.substr($part->UDH, -4);
				$this->db->insert('inbox', $part);
			}
		}
		return $new_msg_id;
	}
    
    //Save Canned Response
    function canned_response($name,$message, $action)
    {
        if($action == 'list')
        {
        	return $this->db->get_where('user_templates', array( 'id_user'=> $this->session->userdata('id_user') ));
        }
        else if($action == 'get')
        {
            $message = $this->db->get_where('user_templates', array('Name' => $name), 1, 0)->row('Message');
            echo $message;
        }
        else if($action == 'save')
        {
            $record = array('Name'=>$name, 'Message'=>$message, 'id_user'=> $this->session->userdata('id_user'));
             
            $query = $this->db->get_where('user_templates', array('Name'=> $name, 'id_user'=> $this->session->userdata('id_user')), 1, 0);
            if ($query->num_rows() == 0) {
              // A record does not exist, insert one.
              $query = $this->db->insert('user_templates', $record);
            } else {
              // A record does exist, update it.
              $query = $this->db->update('user_templates', $record, array('id_template'=>$query->row('id_template')));
            }
        }
        else if($action == 'delete')
        {
            $this->db->delete('user_templates', array('Name' => $name));
        }
        else
            die("Invalid Option");
    }
}

/* End of file messages_model.php */
/* Location: ./application/models/messages_model.php */
