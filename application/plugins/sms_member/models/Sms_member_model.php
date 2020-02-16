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
 * Member_model Class
 *
 * Handle all member database activity 
 *
 * @package		Kalkun
 * @subpackage	Member
 * @category	Models
 */
class SMS_member_model extends CI_Model {

	/**
	 * Constructor
	 *
	 * @access	public
	 */		
	function __construct()
	{
		parent::__construct();
	}

	// --------------------------------------------------------------------
	
	/**
	 * Get Member
	 *
	 * @access	public   		 
	 * @param	mixed
	 * @return	object
	 */		
	function get_member($option)
	{
		switch($option)
		{
			case 'all':
				$this->db->select('*');
			break;
			
			case 'total':
				$this->db->select('count(*) as count');
		}
		
		$this->db->from('plugin_sms_member');	
		return $this->db->get();
	}

	/**
	 * Add Member
	 *
	 * @access	public   		 
	 * @param	string $number
	 * @return	void
	 */		
	function add_member($number)
	{
		$data = array('phone_number' => $number, 'reg_date' => date ('Y-m-d H:i:s'));
		$this->db->insert('plugin_sms_member', $data);
	}

	/**
	 * Remove Member
	 *
	 * @access	public   		 
	 * @param	string $number
	 * @return	void
	 */	
	function remove_member($number)
	{
		$this->db->where('phone_number', $number);		
		$this->db->delete('plugin_sms_member');			
	}

	/**
	 * Check/ Count Member
	 *
	 * @access	public   		 
	 * @param	string $number
	 * @return	integer
	 */	
	function check_member($number)
	{
		$this->db->from('plugin_sms_member');
		$this->db->where('phone_number', $number);
		return $this->db->count_all_results();    		
	}
}

/* End of file sms_member_model.php */
/* Location: ./application/plugins/sms_member/models/sms_member_model.php */