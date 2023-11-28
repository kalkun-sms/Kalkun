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
	function index($type = 'installed')
	{
		$this->load->helper('form');
		$data['main'] = 'main/plugin/index';
		$data['title'] = 'Plugins';
		$data['plugins'] = array();
		$data['type'] = $type;
		if ($type === 'installed')
		{
			$data['title'] .= ' - '.tr_raw('Installed', 'Plural');
			$data['plugins'] = $this->Plugin_model->get_plugins()->result_array();
			foreach ($data['plugins'] as $key => $plugin)
			{
				$data['plugins'][$key]['plugin_controller_has_index'] = $this->_plugin_controller_has_index($plugin['plugin_system_name']);
			}
		}
		else
		{
			$data['title'] .= ' - '.tr_raw('Available', 'Plural');
			$plugins = $this->plugins->print_plugins();
			$no = 0;

			if ( ! empty($plugins))
			{
				// do cleanup array key
				foreach ($plugins as $key => $tmp)
				{
					$this->plugins->get_plugin_headers($key);
					$new_plugin[$no] = array_merge (
						array ('plugin_system_name' => $key),
						$this->plugins->plugin_info($key)
					);
					$no++;
				}
				$installed = $this->Plugin_model->get_plugins()->result_array();

				foreach ($new_plugin as $key => $tmp)
				{
					foreach ($installed as $tmp2)
					{
						if (in_array($tmp['plugin_system_name'], $tmp2))
						{
							unset($new_plugin[$key]);
						}
					}
				}
				$result = $new_plugin;
				uasort($result, array($this, '_plugins_cmp_plugin_name'));
				$data['plugins'] = $result;
			}
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
		$this->plugins->activate_plugin($plugin_name);
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
		$this->plugins->deactivate_plugin($plugin_name);
		$this->session->set_flashdata('notif', tr_raw('Plugin {0} uninstalled successfully.', NULL, $plugin_name));
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

	// --------------------------------------------------------------------

	/**
	 * Callback function used to order the array of plugins by plugin name
	 */
	function _plugins_cmp_plugin_name($p1, $p2)
	{
		return strcasecmp ($p1['plugin_name'], $p2['plugin_name']);
	}

	// --------------------------------------------------------------------

	/**
	 * Check if the controller of the plugin has a 'index()' method
	 */
	function _plugin_controller_has_index($plugin_system_name)
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
