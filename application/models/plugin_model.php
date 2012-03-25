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
class Plugin_model extends Model {

	/**
	 * Constructor
	 *
	 * @access	public
	 */		
	function Plugin_model()
	{
		parent::Model();
	}
	
	function get_plugins()
	{
		$this->db->from('plugins');
		return $this->db->get();	
	}
	
}

/* End of file plugin_model.php */
/* Location: ./application/models/plugin_model.php */