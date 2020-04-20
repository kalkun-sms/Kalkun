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
 * Blacklist_number_model Class
 *
 * Handle all plugin database activity 
 *
 * @package		Kalkun
 * @subpackage	Plugin
 * @category	Models
 */
class Blacklist_number_model extends CI_Model {
	
	function __construct()
	{
		parent::__construct();
	}
	
	function get($option=NULL, $limit=NULL, $offset=NULL)
	{
		switch($option)
		{
			case 'all':
				return $this->db->get('plugin_blacklist_number');
			break;
			
			case 'paginate':
				return $this->db->get('plugin_blacklist_number', $limit, $offset);		
			break;
			
			case 'count':
				$this->db->select('count(*) as count');
				return $this->db->get('plugin_blacklist_number')->row('count');
			break;
		}
	}
	
	function add()
	{
		$data = array (
				'phone_number' => trim($this->input->post('phone_number',TRUE)),
				'reason' => trim($this->input->post('reason',TRUE)),
					);
		$this->db->insert('plugin_blacklist_number',$data);			
	}

	function update()
	{
		$data = array (
				'phone_number' => trim($this->input->post('editphone_number',TRUE)),
				'reason' => $this->input->post('editreason',TRUE),
					);
		$this->db->where('id_blacklist_number', $this->input->post('editid_blacklist_number',TRUE));			
		$this->db->update('plugin_blacklist_number',$data);
	}	
	
	function delete($id)
	{
		$this->db->delete('plugin_blacklist_number', array('id_blacklist_number' => $id)); 
	}
}

/* End of file blacklist_number_model.php */
/* Location: ./application/plugins/blacklist_number/models/blacklist_number_model.php */