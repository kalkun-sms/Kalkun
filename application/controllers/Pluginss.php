<?php
/**
 * Kalkun
 * An open source web based SMS Management
 *
 * @package		Kalkun
 * @author		Kalkun Dev Team
 * @license		https://spdx.org/licenses/GPL-2.0-or-later.html
 * @link		https://kalkun.sourceforge.io/
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
		if ($this->session->userdata('level') !== 'admin')
		{
			$this->session->set_flashdata('notif', tr_raw('Access denied. Only administrators are allowed to manage plugins.'));
			redirect('/');
		}

		$this->load->library('Plugins_lib_kalkun');
		$this->load->model('Plugins_kalkun_model');

		$this->plugins_lib_kalkun->restore_orphaned_plugins();
		$this->plugins_lib_kalkun->update_all_plugin_headers();
	}

	// --------------------------------------------------------------------

	/**
	 * Index
	 *
	 * Display list of all plugin
	 *
	 * @access	public
	 */
	function index($type = 'installed')
	{
		$this->load->helper('form');
		$plugins_lib = $this->plugins_lib_kalkun;
		$data['main'] = 'main/plugin/index';
		$data['title'] = 'Plugins';
		$data['plugins'] = array();
		$data['type'] = $type;
		if ($type === 'installed')
		{
			$data['title'] .= ' - '.tr_raw('Installed', 'Plural');
			foreach ($this->Plugins_kalkun_model->get_plugins() as $key => $plugin)
			{
				if ($plugin->status & $plugins_lib::P_STATUS_MISSING)
				{
					continue;
				}
				if ($plugin->status & $plugins_lib::P_STATUS_ENABLED)
				{
					$data['plugins'][$key] = $plugin;
					$data['plugins'][$key]->controller_has_index = self::_plugin_controller_has_index($plugin->system_name);
				}
			}
		}
		else
		{
			$data['title'] .= ' - '.tr_raw('Available', 'Plural');
			foreach ($this->Plugins_kalkun_model->get_plugins() as $key => $plugin)
			{
				if ($plugin->status & $plugins_lib::P_STATUS_MISSING)
				{
					continue;
				}
				if ( ! ($plugin->status & $plugins_lib::P_STATUS_ENABLED))
				{
					$data['plugins'][$key] = $plugin;
				}
			}
		}
		uasort($data['plugins'], array($this, '_plugins_cmp_plugin_name'));
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
		$this->plugins_lib_kalkun->enable_plugin($plugin_name);
		$this->session->set_flashdata('notif', tr_raw('Plugin {0} installed successfully.', NULL, $plugin_name));
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
		$this->plugins_lib_kalkun->disable_plugin($plugin_name);
		$this->session->set_flashdata('notif', tr_raw('Plugin {0} uninstalled successfully.', NULL, $plugin_name));
		redirect('pluginss');
	}

	// --------------------------------------------------------------------

	/**
	 * Callback function used to order the array of plugins by plugin name
	 */
	function _plugins_cmp_plugin_name($p1, $p2)
	{
		return strcasecmp ($p1->name, $p2->name);
	}

	// --------------------------------------------------------------------

	/**
	 * Check if the controller of the plugin has a 'index()' method
	 */
	static function _plugin_controller_has_index($plugin_system_name)
	{
		$controller_class = ucfirst($plugin_system_name);
		$controller_path = APPPATH . 'plugins/'.$plugin_system_name.'/controllers/'.$controller_class.'.php';

		if ( ! file_exists($controller_path))
		{
			return FALSE;
		}

		require_once $controller_path;
		$rc = new ReflectionClass($controller_class);

		return $rc->hasMethod('index');
	}
}
