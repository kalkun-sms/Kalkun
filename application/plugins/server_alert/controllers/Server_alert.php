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
 * Server_alert Class
 *
 * @package		Kalkun
 * @subpackage	Plugin
 * @category	Controllers
 */
include_once(APPPATH.'plugins/Plugin_Controller.php');

class Server_alert extends Plugin_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('Kalkun_model');
		$this->load->model('server_alert_model', 'plugin_model');
	}	
	
	function index()
	{
		if($_POST) 
		{	
			if($this->input->post('editid_server_alert')) $this->plugin_model->update();
			else $this->plugin_model->add();
			redirect('plugin/server_alert');
		}

		$this->load->library('pagination');
		$config['base_url'] = site_url('plugin/server_alert/index');
		$config['total_rows'] = $this->plugin_model->get('count');
		$config['per_page'] = $this->Kalkun_model->get_setting()->row('paging');
		$config['cur_tag_open'] = '<span id="current">';
		$config['cur_tag_close'] = '</span>';		
		$config['uri_segment'] = 4;
		$this->pagination->initialize($config);
				
		$data['main'] = 'index';
		$data['alert'] = $this->plugin_model->get('paginate', $config['per_page'], $this->uri->segment(4,0));
		$data['number'] = $this->uri->segment(4,0)+1;
		
		$data['time_interval'] = $this->plugin_model->get_time_interval();
		$this->load->view('main/layout', $data);
	}
	
	function delete($id)
	{
		$this->plugin_model->delete($id);
		redirect('plugin/server_alert');
	}	
	
	function change_state($id)
	{
		$this->plugin_model->change_state($id, 'true');
		redirect('plugin/server_alert');
	}
	
	function get_time_interval()
	{
		echo "Total Time Interval : ".$this->plugin_model->get_time_interval()." seconds";	
	}

}
	
/* End of file server_alert.php */
/* Location: ./application/plugins/server_alert/controllers/server_alert.php */
