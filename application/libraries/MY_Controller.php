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
 * MY_Controller Class
 *
 * Base controller
 *
 * @package		Kalkun
 * @subpackage	Base
 * @category	Controllers
 */
class MY_Controller  extends Controller  {
	
	/**
	 * Constructor
	 *
	 * @access	public
	 */
	function __construct($login=TRUE)
	{
		parent::__construct();
		
		// installation mode
		if(file_exists("install")) redirect('install');	
		
		$this->load->library('session');
		
		if($login)
		{
			// session check
			if($this->session->userdata('loggedin')==NULL) redirect('login');
			
			$this->load->model('Kalkun_model');
			
			// language
			$this->load->helper('language');
			$lang = $this->Kalkun_model->get_setting()->row('language');
			$this->lang->load('kalkun', $lang);
				
			// Message routine
			$this->_message_routine();
		}
	}
		
	function _message_routine()
	{
		$this->load->model('User_model');
		$this->load->model('Message_model');
		$uid = $this->session->userdata("id_user");
		
		$outbox = $this->Message_model->get_user_outbox($uid);
		foreach ($outbox->result() as $tmp)
		{
			$id_message = $tmp->id_outbox;
			
			// if still on outbox, means message not delivered yet
			if ($this->Message_model->get_messages(array('id_message' => $id_message, 'type' => 'outbox'))->num_rows()>0)
			{ 
				// do nothing
			}
			// if exist on sentitems then update sentitems ownership, else delete user_outbox
			else if ($this->Message_model->get_messages(array('id_message' => $id_message, 'type' => 'sentitems'))->num_rows()>0) 
			{
				$this->Message_model->insert_user_sentitems($id_message, $uid);
				$this->Message_model->delete_user_outbox($id_message);
			}
			else
			{
				$this->Message_model->delete_user_outbox($id_message);
			}
		}
	}
}

/* End of file MY_Controller.php */
/* Location: ./application/libraries/MY_Controller.php */ 
