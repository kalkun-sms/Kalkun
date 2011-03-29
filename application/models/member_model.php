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
class Member_model extends Model {

	/**
	 * Constructor
	 *
	 * @access	public
	 */		
	function Member_model()
	{
		parent::Model();
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
		
		$this->db->from('member');	
		return $this->db->get();
	}

	// --------------------------------------------------------------------
	
	/**
	 * Add Member
	 *
	 * @access	public   		 
	 * @param	string $number phone number
	 * @return
	 */	
	function add_member($number)
	{
		$data = array('phone_number' => $number, 'reg_date' => date ('Y-m-d H:i:s'));
		$this->db->insert('member', $data);
	}

	// --------------------------------------------------------------------
	
	/**
	 * Remove Member
	 *
	 * @access	public   		 
	 * @param	string $number phone number
	 * @return
	 */	
	function remove_member($number)
	{
		$this->db->where('phone_number', $number);		
		$this->db->delete('member');
	}	

	// --------------------------------------------------------------------
	
	/**
	 * Check Member
	 *
	 * @access	public	 
	 * @param	string $number phone number
	 * @return number
	 */	
	function check_member($number)
	{
		$this->db->from('member');
		$this->db->where('phone_number', $number);
		return $this->db->count_all_results();
	}
}

/* End of file member_model.php */
/* Location: ./application/models/member_model.php */