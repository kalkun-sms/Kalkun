<?php
Class Member_model extends Model {
	
	function Member_model()
	{
		parent::Model();
	}
	
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
	
	function add_member($number)
	{
		$data = array('phone_number' => $number,
					'reg_date' => date ('Y-m-d H:i:s')
				);
				
		$this->db->insert('member', $data);
	}

	function remove_member($number)
	{
		$this->db->where('phone_number', $number);		
		$this->db->delete('member');
	}	
	
	function check_member($number)
	{
		$this->db->from('member');
		$this->db->where('phone_number', $number);
		return $this->db->count_all_results();
	}
}
?>