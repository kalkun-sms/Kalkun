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
}
