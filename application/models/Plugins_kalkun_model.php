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

require_once('Plugins_model.php');
/**
 * Plugins_kalkun_model Class
 *
 * Handle all plugin database activity
 *
 * @package		Kalkun
 * @subpackage	Plugin
 * @category	Models
 */
class Plugins_kalkun_model extends Plugins_model {

	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Insert Plugin
	 *
	 * Insert the plugin information in the database.
	 *
	 * @param   str     $plugin     The system_name of the plugin
	 * @param   array   $settings   New settings for plugin
	 * @access  public
	 * @since   0.1.0
	 * @return  bool
	 */
	public function insert_plugin($plugin, array $settings)
	{
		return static::$db->where('system_name', $plugin)->insert('plugins', $settings);
	}

	// ------------------------------------------------------------------------

	/**
	 * Get Plugins
	 *
	 * Get all plugins, return an array of the plugins from the database, with the system_name
	 * as the keys
	 * The override here is so that it returns FALSE only if the result is not an array.
	 * ie. if the resultset is emtpy (ie. an empty array), it will not return FALSE but the empty array.
	 *
	 * @access public
	 * @since   0.1.0
	 * @return array|bool
	 */
	public function get_plugins()
	{
		$query = static::$db->get('plugins');

		if ( ! is_array($result = $query->result()))
		{
			log_message('error', 'Error retrieving plugins from database');

			return FALSE;
		}

		$return = array();

		foreach ($result as $r)
		{
			if ( ! empty($r->data))
			{
				$r->data = unserialize($r->data);
			}

			$return[$r->system_name] = $r;
		}

		return $return;
	}
}
