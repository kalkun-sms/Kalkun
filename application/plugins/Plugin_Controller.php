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
 * Plugin_Controller Class
 *
 * Check all plugin requirement before run
 *
 * @package		Kalkun
 * @subpackage	Plugin
 * @category	Controllers
 */
class Plugin_Controller extends MY_Controller {
		
	var $plugin_name = '';
	var $plugin_version = '';
	var $plugin_author = '';
	var $plugin_view_dir = '';

	function __construct($login=TRUE)
	{
		parent::__construct($login);

        // Prevent non-admin user
        if($login AND $this->session->userdata('level') != 'admin')
        {
            $this->session->set_flashdata('notif', 'Only administrator can manage plugin');
            redirect('/');
        }

		/* Prevent this controller from being called directly */
		if (get_class() == get_class($this))
		{
			redirect(site_url('/'), 'location', 301);
		}
		//$this->load->library('Plugins');
		//$this->load->library('plugins', array('plugins_dir' => 'application/plugins/'));

		// Check if plugin is active
		$CI =& get_instance();
		$check = $CI->db->where('plugin_system_name', strtolower(get_class($this)))->get('plugins');
		if ($check->num_rows()!=1)
		{
			$this->session->set_flashdata('notif', 'Plugin '.strtolower(get_class($this)).' is not installed');
			redirect('pluginss');
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Initialize
	 *
	 * @access	public
	 * @param	array	initialization parameters
	 * @return	void
	 */	
	function initialize($params = array())
	{
		if (count($params) > 0)
		{
			foreach ($params as $key => $val)
			{
				if (isset($this->$key))
				{
					$this->$key = $val;
				}
			}
		}
		
		// Check if all required value already set, otherwise thrown error
		// ...
		
		// Check if plugin already installed
		// ..
		
		// Check if plugin activated
		// ..
		
		// Set plugin view directory
		$this->plugin_view_dir = 'plugin/'.$this->plugin_name.'/';
				
		// if models exist
		if (file_exists(APPPATH.'models/plugin/'.$this->plugin_name.'_model'.EXT))
		{
			$this->load->model('plugin/'.$this->plugin_name.'_model', 'plugin_model');
		}
	}
	
}	

/* End of file Plugin_Controller.php */
/* Location: ./application/plugins/Plugin_Controller.php */
