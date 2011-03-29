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
 * Plugin Class
 *
 * @package		Kalkun
 * @subpackage	Plugin
 * @category	Controllers
 */
class Plugin extends MY_Controller {

	/**
	 * Constructor
	 *
	 * @access	public
	 */		
	function Plugin()
	{
		parent::MY_Controller();		
		
		// session check
		if($this->session->userdata('loggedin')==NULL) redirect('login');
								
		$this->load->database();						
		$this->lang->load('kalkun', $this->Kalkun_model->get_setting('language', 'value')->row('value'));
	}

	// --------------------------------------------------------------------
	
	/**
	 * Index
	 *
	 * Display list of all plugin
	 *
	 * @access	public   		 
	 */	
	function index() 
	{
		$data['main'] = 'main/plugin/index';
		$this->load->view('main/layout', $data);
	}

	// --------------------------------------------------------------------
	
	/**
	 * Change status
	 *
	 * Enable/Disable status of a plugin
	 *
	 * @access	public   		 
	 */	
	function change_status($name, $state)
	{
		$data = array('plugin_status' => $state);
		$this->db->where('plugin_name', $name);
		$this->db->update('plugin', $data);
		
		redirect('plugin/'.$name);
	}

	// --------------------------------------------------------------------
	
	/**
	 * Blacklist number
	 *
	 * Display blacklist number page
	 *
	 * @access	public   		 
	 */	
	function blacklist_number()
	{
		if($_POST) 
		{	
			if($this->input->post('editid_blacklist_number')) $this->Plugin_model->updateBlacklistNumber();
			else $this->Plugin_model->addBlacklistNumber();
			redirect('plugin/blacklist_number');
		}

		$this->load->library('pagination');
		$config['base_url'] = site_url().'/plugin/blacklist_number';
		$config['total_rows'] = $this->Plugin_model->getBlacklistNumber('count');
		$config['per_page'] = $this->Kalkun_model->get_setting('paging', 'value')->row('value');
		$config['cur_tag_open'] = '<span id="current">';
		$config['cur_tag_close'] = '</span>';		
		$config['uri_segment'] = 3;
		
		$this->pagination->initialize($config);
				
		$data['main'] = 'main/plugin/blacklist_number';
		$data['blacklist'] = $this->Plugin_model->getBlacklistNumber('paginate', $config['per_page'], $this->uri->segment(3,0));
		$data['number'] = $this->uri->segment(3,0)+1;
		$this->load->view('main/layout', $data);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Delete blacklist number
	 *
	 * Remove phone number from blacklist
	 *
	 * @access	public   		 
	 */	
	function delete_blacklist_number($id)
	{
		$this->Plugin_model->delBlacklistNumber($id);
		redirect('plugin/blacklist_number');
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Server alert
	 *
	 * Display Server alert page
	 *
	 * @access	public   		 
	 */		
	function server_alert()
	{
		if($_POST) 
		{	
			if($this->input->post('editid_server_alert')) $this->Plugin_model->updateServerAlert();
			else $this->Plugin_model->addServerAlert();
			redirect('plugin/server_alert');
		}

		$this->load->library('pagination');
		$config['base_url'] = site_url().'/plugin/server_alert';
		$config['total_rows'] = $this->Plugin_model->getServerAlert('count');
		$config['per_page'] = $this->Kalkun_model->get_setting('paging', 'value')->row('value');
		$config['cur_tag_open'] = '<span id="current">';
		$config['cur_tag_close'] = '</span>';		
		$config['uri_segment'] = 3;
		
		$this->pagination->initialize($config);
				
		$data['main'] = 'main/plugin/server_alert';
		$data['alert'] = $this->Plugin_model->getServerAlert('paginate', $config['per_page'], $this->uri->segment(3,0));
		$data['number'] = $this->uri->segment(3,0)+1;
		$this->load->view('main/layout', $data);
	}

	// --------------------------------------------------------------------
	
	/**
	 * Delete server alert
	 *
	 * Remove host from server alert
	 *
	 * @access	public   		 
	 */	
	function delete_server_alert($id)
	{
		$this->Plugin_model->delServerAlert($id);
		redirect('plugin/server_alert');
	}	

	// --------------------------------------------------------------------
	
	/**
	 * Change server alert state
	 *
	 * Enable/disable state of a host
	 *
	 * @access	public   		 
	 */	
	function change_server_alert_state($id)
	{
		$this->Plugin_model->changeState($id, 'true');
		redirect('plugin/server_alert');
	}
}

/* End of file plugin.php */
/* Location: ./application/controllers/plugin.php */ 