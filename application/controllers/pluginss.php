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
 * Pluginss Class
 *
 * @package		Kalkun
 * @subpackage	Plugin
 * @category	Controllers
 */
class Pluginss extends MY_Controller {

	/**
	 * Constructor
	 *
	 * @access	public
	 */		
	function __construct()
	{
		parent::__construct();

        // Prevent non-admin user
        if($this->session->userdata('level') != 'admin')
        {
            $this->session->set_flashdata('notif', 'Only administrator can manage plugin');
            redirect('/');
        }

		$this->load->library('Plugins');
		$this->load->model('Plugin_model');
	}

	// --------------------------------------------------------------------
	
	/**
	 * Index
	 *
	 * Display list of all plugin
	 *
	 * @access	public   		 
	 */	
	function index($type='installed') 
	{
		$this->load->helper('form');
		$data['main'] = 'main/plugin/index';
		$data['title'] = 'Plugins';
		$data['type'] = $type;
		if($type=='installed')
		{
			$data['title'].= " - Installed";
			$data['plugins'] = $this->Plugin_model->get_plugins()->result_array();	
		}
		else
		{
			$data['title'].= " - Available";
			$plugins = get_available_plugin();
			$no = 0;
			
			// do cleanup array key
			foreach($plugins as $key => $tmp)
			{
				$new_plugin[$no]['plugin_system_name'] = $key;
				foreach($tmp['plugin_info'] as $key2 => $tmp2)
				{
					$new_plugin[$no][$key2] = $tmp2;
				}
				$no++;	
			}
			$installed = $this->Plugin_model->get_plugins()->result_array();
			
			foreach($new_plugin as $key => $tmp)
			{
				foreach($installed as $tmp2)
				{
					if(in_array($tmp['plugin_system_name'],$tmp2))
					{
						unset($new_plugin[$key]);
					}
				}	
			}
			$result = $new_plugin;
			$data['plugins'] = $result;
		}
		$this->load->view('main/layout', $data);
	}

	// --------------------------------------------------------------------
	
	/**
	 * Install
	 *
	 * Install a plugin
	 *
	 * @access	public   		 
	 */	
	function install($plugin_name)
	{
		activate_plugin($plugin_name);
		$this->session->set_flashdata('notif', 'Plugin '.$plugin_name.' successfully installed');
		redirect('pluginss');
	}

	// --------------------------------------------------------------------
	
	/**
	 * Uninstall
	 *
	 * Uninstall a plugin
	 *
	 * @access	public   		 
	 */	
	function uninstall($plugin_name)
	{
		deactivate_plugin($plugin_name);
		$this->session->set_flashdata('notif', 'Plugin '.$plugin_name.' successfully uninstalled');
		redirect('pluginss');		
	}

	// --------------------------------------------------------------------
	
	/**
	 * Activate
	 *
	 * Activated a plugin
	 *
	 * @access	public   		 
	 */		
	function activate($plugin_name)
	{
		$data = array('plugin_status' => 'true');
		$this->db->where('plugin_name', $plugin_name);
		$this->db->update('plugin', $data);			
	}

	// --------------------------------------------------------------------
	
	/**
	 * Deactivate
	 *
	 * Deactivated a plugin
	 *
	 * @access	public   		 
	 */		
	function deactivate($plugin_name)
	{
		$data = array('plugin_status' => 'false');
		$this->db->where('plugin_name', $plugin_name);
		$this->db->update('plugin', $data);
	}	
}

/* End of file plugin.php */
/* Location: ./application/controllers/plugin.php */ 
