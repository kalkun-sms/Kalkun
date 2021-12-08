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
		if ($this->session->userdata('level') !== 'admin')
		{
			$this->session->set_flashdata('notif', lang('pluginss_only_admin_can_manage'));
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
			$data['title'] .= lang('pluginss_installed');
			$data['plugins'] = $this->Plugin_model->get_plugins()->result_array();
		}
		else
		{
			$data['title'] .= lang('pluginss_available');
			$pluginsObj = new Plugins();
			$plugins = get_available_plugin();
			$no = 0;

			if ( ! empty($plugins))
			{
				// do cleanup array key
				foreach ($plugins as $key => $tmp)
				{
					$new_plugin[$no]['plugin_system_name'] = $key;
					foreach ($tmp['plugin_info'] as $key2 => $tmp2)
					{
						$new_plugin[$no][$key2] = $tmp2;
					}
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
		activate_plugin($plugin_name);
		$this->session->set_flashdata('notif', str_replace('%plugin_name%', $plugin_name, lang('pluginss_successfully_installed')));
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
		$this->session->set_flashdata('notif', str_replace('%plugin_name%', $plugin_name, lang('pluginss_successfully_uninstalled')));
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
}
