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
 * @subpackage	Member
 * @category	Controllers
 */
class Member extends MY_Controller {

	/**
	 * Constructor
	 *
	 * @access	public
	 */		
	function Member()
	{
		parent::MY_Controller();			
		
		// session check
		if($this->session->userdata('loggedin')==NULL) redirect('login');
		
		$this->load->database();
		$this->lang->load('kalkun', $this->Kalkun_model->get_setting()->row('language'));
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
		$this->load->model('Member_model');
		$data['main'] = 'main/member/index';
		$data['title'] = 'Member';
		$data['total_member'] = $this->Member_model->get_member('total')->row('count');
		$data['member'] = $this->Member_model->get_member('all')->result_array();
		
		$this->load->view('main/layout', $data);
	}
}

/* End of file member.php */
/* Location: ./application/controllers/member.php */ 