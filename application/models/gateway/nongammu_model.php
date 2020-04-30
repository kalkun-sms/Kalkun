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
 * nongammu_model Class
 *
 * Handle all database activity for sending messages
 * for alternative gateways (all non-gammu based).
 * To be used as an ancestor class for all alternative
 * gateways classes. Inherits all other methods from Gammu_model.
 *
 * @package	Kalkun
 * @subpackage	Messages
 * @category	Models
 */
require_once('gammu_model'.EXT);

class nongammu_model extends Gammu_model { 
	
	/**
	 * Constructor
	 *
	 * @access	public
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->model('Kalkun_model');
		log_message('debug',"NonGammu Class Initialized");
	}
	
    // --------------------------------------------------------------------
	
	/**
	 * Send Messages (process SMS,
         * enqueue for later sending (table outbox) or send immediatelly and save to sentitems
         *                
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
	 * url string, WAP url
	 * type string, waplink
	 * validity string
	 * @return	object
	 */	
	function send_messages($data)
	{
	    //default values
	    $data = $this->_default(array('SenderID' => NULL, 'CreatorID' => '', 'validity' => '-1'), $data);

	    // check if wap msg
		if (isset($data['type']) AND $data['type']=='waplink') {
			log_message('error',"Non-gammu alternate gateways DO NOT support WAP link sending!");
 	        return ;
	    } 

	    //check empty message
		if (trim($data['message']) == "") {
			log_message('error',"Cannot send empty message!");
			return;
	    };

	    if (strtotime($data['date'])>time()){ //to be sent in the future
	        $this->enqueue_messages($data);
			return;
	    }

        $gateway = substr(get_class($this),0,-6);
	    log_message('debug',"SMS via gateway \"$gateway\" to ".$data['dest']." length ".strlen($data['message'])." chars");

	    $ret=$this->really_send_messages($data);
	    if(is_string($ret)){
	        log_message('error',"Message failed via gateway \"$gateway\" to ".$data['dest']." Reason: ".$ret);
			$this->save_sent_messages($data,$ret);
			return;
	    };
	    $this->save_sent_messages($data);
	    $this->Kalkun_model->add_sms_used($data['uid']);
	}

    /**
    * really_send_messages
    * A template function to send message "your way"
    * Redefine in descendant Classes
    *
    * @author jbubik
    * @category SMS
    * @param	array $data 
    * @return null/String
    * Return of string value indicates Error sending the message. Otherwise success.
    **/

    function really_send_messages($data)
    {
        return "Error. Method really_send_messages not redefined in Class ".get_class($this)."!!!";
    }

    /**
    * enqueue_messages
    * Save a message to outbox for sending later
    *
    * @author jbubik
    * @category SMS
    * @param	array $data 
    * @return   void
    **/

    function enqueue_messages($tmp_data)
    {
        // remove spaces and dashes if any
		$tmp_data['dest'] = str_replace(" ", "", $tmp_data['dest']);
		$tmp_data['dest'] = str_replace("-", "", $tmp_data['dest']);

		$data = array (
				'InsertIntoDB' => date('Y-m-d H:i:s'),
				'SendingDateTime' => $tmp_data['date'],
				'DestinationNumber' => $tmp_data['dest'],
				'Coding' => ($tmp_data['coding']=='default'?"Default_No_Compression":"Unicode_No_Compression"),
				'Class' => $tmp_data['class'],
				'CreatorID' => $tmp_data['CreatorID'],
				'SenderID' => $tmp_data['SenderID'],
				'Text' => '',
				'TextDecoded' => $tmp_data['message'],
				'RelativeValidity' => $tmp_data['validity'],
				'DeliveryReport' => $tmp_data['delivery_report'],
			'MultiPart' => 'false'
		);
		$this->db->insert('outbox', $data);
		$this->db->insert('user_outbox', array('id_outbox'=>$this->db->insert_id(),
													'id_user'=>$tmp_data['uid']));
		log_message('debug',"Message saved to outbox dest:".$tmp_data['dest']);
    }

    /**
    * save_sent_massages
    * Save a message to sentitems
    * Optionally delete from outbox ($data['outbox_id']!=null)
    * Optionally mark that sending has failed ($err_desc!="")
    *
    * @author jbubik
    * @category SMS
    * @param	array $data 
    * @return   void
    **/
    function save_sent_messages($tmp_data,$err_desc="")
    {
		$data = array (
			'InsertIntoDB' => date('Y-m-d H:i:s'),
			'SendingDateTime' => $tmp_data['date'],
			'DestinationNumber' => $tmp_data['dest'],
			'Coding' => ($tmp_data['coding']=='default'?"Default_No_Compression":"Unicode_No_Compression"),
			'Class' => $tmp_data['class'],
			'CreatorID' => $tmp_data['CreatorID'],
			'Text' => '',
			'UDH' => '',
			'SenderID' => strval($tmp_data['SenderID']),
			'TextDecoded' => $tmp_data['message'].($err_desc==""?'':' / '.$err_desc),
			'RelativeValidity' => $tmp_data['validity'],
			'Status' => ($err_desc==""?'SendingOK':'SendingError'),
			'SequencePosition' => 1,
			'id_folder' => 3
		);
		$this->db->trans_begin();
		$this->db->from('sentitems');
		$this->db->select('ID');
		$this->db->limit(1);
		$this->db->order_by('ID', 'DESC');
		$lstmsg=$this->db->get();
		if($lstmsg->num_rows() ==0){
			$data['ID']=1;
		}else{
			$data['ID']=1+$lstmsg->row('ID');
		};

		$this->db->insert('sentitems', $data);
		$this->db->insert('user_sentitems', array('id_sentitems'=>$data['ID'],'id_user'=>$tmp_data['uid']));
		if(array_key_exists('id_outbox',$tmp_data)){
		log_message('debug',"Deleting from outbox message ID=".$tmp_data['id_outbox']);
		$this->db->where('ID', $tmp_data['id_outbox']);
			$this->db->delete('outbox');
			$this->db->where('id_outbox', $tmp_data['id_outbox']);
			$this->db->delete('user_outbox');      
		};
		$this->db->trans_commit();
		log_message('debug',"Message saved to sentitems dest:".$tmp_data['dest']);
    }

    // hook function for Alternate Gateways
    // for NON-GAMMU check outbox queue, send and move to sentitems
    function process_outbox_queue()
    {
        $gateway = substr(get_class($this),0,-6);
        log_message('debug',"Processing outbox queue in gateway ".$gateway);
		$this->db->from('outbox');
		$this->db->where('SendingDateTime <=',date('Y-m-d H:i:s'));
		$this->db->order_by('SendingDateTime', 'ASC');
		$res=$this->db->get();
		if($res->num_rows() ==0){
	   		log_message('debug',"Nothing to process in outbox queue.");
	    	return;
		};
		log_message('debug',"Processing ".$res->num_rows()." messages in outbox queue.");
		foreach ($res->result_array() as $row)
		{
			$data = array (
				'date' => $row['SendingDateTime'],
				'dest' => $row['DestinationNumber'],
				'coding' => ($row['Coding']=="Default_No_Compression"?'default':'unicode'),
				'class' => $row['Class'],
				'CreatorID' => $row['CreatorID'],
				'SenderID' => $row['SenderID'],
				'message' => $row['TextDecoded'],
				'validity' => $row['RelativeValidity'],
				'delivery_report' => $row['DeliveryReport'],
			'id_outbox' => $row['ID']
			);
			$this->db->from('user_outbox');
			$this->db->where('id_outbox',$row['ID']);
			$res2=$this->db->get();
			if ($res2->num_rows() !=1){
				log_message('error',"outbox ID=".$row['ID']." not found in user_outbox. Sending as user 1.");
			$data['uid']=1;
			}else{
				$data['uid']=$res2->row('id_user');
			};

			log_message('debug',"SMS via gateway \"$gateway\" to ".$data['dest'].
								" length ".strlen($data['message'])." chars");
			$ret=$this->really_send_messages($data);
			if(is_string($ret)){
				log_message('error',"Message failed via gateway \"$gateway\" to ".$data['dest']." Reason: ".$ret);
				$this->save_sent_messages($data,$ret);
				return;
			};
			$this->save_sent_messages($data);
			$this->Kalkun_model->add_sms_used($data['uid']);
		}
    }


}

/* End of file nongammu_model.php */
/* Location: ./application/models/gateway/nongammu_model.php */
