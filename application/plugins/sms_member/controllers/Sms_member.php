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
 * Member Class
 *
 * @package		Kalkun
 * @subpackage	Plugin
 * @category	Controllers
 */
include_once(APPPATH.'plugins/Plugin_Controller.php');

class SMS_member extends Plugin_Controller {

	/**
	 * Constructor
	 *
	 * @access	public
	 */		
	function __construct()
	{
		parent::__construct();
		$this->load->model('sms_member_model', 'plugin_model');
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Index
	 *
	 * Display list of all member
	 *
	 * @access	public   		 
	 */		
	function index()
	{
		$data['main'] = 'index';
		$data['title'] = 'SMS Member';
		$data['total_member'] = $this->plugin_model->get_member('total')->row('count');
		$data['member'] = $this->plugin_model->get_member('all')->result_array();
		
		$this->load->view('main/layout', $data);
	}
}

/* End of file sms_member.php */
/* Location: ./application/plugins/sms_member/controllers/sms_member.php */