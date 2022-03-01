<?php
/**
 * Kalkun
 * An open source web based SMS Management
 *
 * @package		Kalkun
 * @author		Kalkun Dev Team
 * @license		https://spdx.org/licenses/GPL-3.0-or-later.html
 * @link		http://kalkun.sourceforge.net
 */

// ------------------------------------------------------------------------

/**
 * Plugin_controller Class
 *
 * Check all plugin requirement before run
 *
 * @package		Kalkun
 * @subpackage	Plugin
 * @category	Controllers
 */
class Plugin_controller extends MY_Controller {

	var $plugin_name = '';
	var $plugin_version = '';
	var $plugin_author = '';
	var $plugin_view_dir = '';

	function __construct($login = TRUE)
	{
		parent::__construct($login);

		// Prevent non-admin user
		if ($login && $this->session->userdata('level') !== 'admin')
		{
			$this->session->set_flashdata('notif', tr('Access denied. Only administrators are allowed to manage plugins.'));
			redirect('/');
		}

		$this->plugin_name = strtolower(get_class($this));

		// Prevent this controller from being called directly
		if (get_class() === get_class($this))
		{
			redirect(site_url('/'), 'location', 301);
		}
		//$this->load->library('Plugins');
		//$this->load->library('plugins', array('plugins_dir' => 'application/plugins/'));

		// Check if plugin is active
		$CI = &get_instance();
		$check = $CI->db->where('plugin_system_name', $this->plugin_name)->get('plugins');
		if ($check->num_rows() !== 1)
		{
			$this->session->set_flashdata('notif', tr('Plugin {0} is not installed.', NULL, $this->plugin_name));
			redirect('pluginss');
		}

		// Temporarily add the plugin path to package path to load language, config...
		$this->load->add_package_path(APPPATH.'plugins/'.$this->plugin_name, FALSE);

		// Load translations
		if (file_exists(APPPATH.'plugins/'.$this->plugin_name.'/language/english/'.$this->plugin_name.'_lang.php'))
		{
			$this->lang->load($this->plugin_name);
		}

		// Load plugin config
		// Access config items with: $this->config->config[$plugin_name]->item('config_item');
		if (file_exists(APPPATH.'plugins/'.$this->plugin_name.'/config/'.$this->plugin_name.'.php'))
		{
			$this->load->config($this->plugin_name, TRUE);
		}

		// Remove plugin path from package path now that we finished loading language, config...
		$this->load->remove_package_path(APPPATH.'plugins/'.$this->plugin_name);

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
		if (file_exists(APPPATH.'models/plugin/'.$this->plugin_name.'_model.php'))
		{
			$this->load->model('plugin/'.$this->plugin_name.'_model', $this->plugin_name.'_model');
		}
	}
}
