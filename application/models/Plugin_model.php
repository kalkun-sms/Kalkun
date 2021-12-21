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
 * Plugin_model Class
 *
 * Handle all plugin database activity
 *
 * @package		Kalkun
 * @subpackage	Plugin
 * @category	Models
 */
class Plugin_model extends CI_Model {

	function get_plugins()
	{
		$this->db->from('plugins');
		$this->db->order_by('plugin_name', 'ASC');
		return $this->db->get();
	}
}
