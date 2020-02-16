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
 * Blacklist_number Class
 *
 * @package		Kalkun
 * @subpackage	Plugin
 * @category	Controllers
 */
include_once(APPPATH.'plugins/Plugin_Controller.php');

class Blacklist_number extends Plugin_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->model('Kalkun_model');
		$this->load->model('blacklist_number_model', 'plugin_model');
	}
	
	function index()
	{
		if($_POST) 
		{	
			if($this->input->post('editid_blacklist_number')) $this->plugin_model->update();
			else $this->plugin_model->add();
			redirect('plugin/blacklist_number');
		}

		$this->load->library('pagination');
		$config['base_url'] = site_url('plugin/blacklist_number/index');
		$config['total_rows'] = $this->plugin_model->get('count');
		$config['per_page'] = $this->Kalkun_model->get_setting()->row('paging');
		$config['cur_tag_open'] = '<span id="current">';
		$config['cur_tag_close'] = '</span>';		
		$config['uri_segment'] = 4;
		$this->pagination->initialize($config);
				
		$data['main'] = 'index';
		$data['blacklist'] = $this->plugin_model->get('paginate', $config['per_page'], $this->uri->segment(4,0));
		$data['number'] = $this->uri->segment(4,0)+1;
		$this->load->view('main/layout', $data);
	}
	
	function delete($id)
	{
		$this->plugin_model->delete($id);
		redirect('plugin/blacklist_number');
	}
}
	
/* End of file blacklist_number.php */
/* Location: ./application/plugins/blacklist_number/controllers/blacklist_number.php */
