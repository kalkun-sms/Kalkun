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
 * Phonebook_model Class
 *
 * Handle all phonebook database activity 
 *
 * @package		Kalkun
 * @subpackage	Phonebook
 * @category	Models
 */
class Phonebook_model extends Model {
	
	/**
	 * Constructor
	 *
	 * @access	public
	 */	
	function Phonebook_model()
	{
		parent::Model();
	}

	// --------------------------------------------------------------------
	
	/**
	 * Get Phonebook
	 *
	 * @access	public   		 
	 * @param	mixed $param
	 * @return	object
	 */	
	function get_phonebook($param)
	{
		switch($param['option']) 
		{
			case 'all':
			$this->db->select('*');
			$this->db->select_as('pbk.ID','id_pbk');
			$this->db->select_as('pbk_groups.Name', 'GroupName');	
			$this->db->from('pbk');
			$this->db->where('pbk.id_user', $this->session->userdata('id_user'));
			$this->db->join('pbk_groups', 'pbk_groups.ID=pbk.GroupID');
			$this->db->order_by('pbk.Name');
			break;	
			
			case 'paginate':
			$this->db->select('*');
			$this->db->select_as('ID', 'id_pbk');	
			$this->db->from('pbk');
			$this->db->where('id_user', $this->session->userdata('id_user'));
			$this->db->order_by('Name');
			$this->db->limit($param['limit'], $param['offset']);
			break;
			
			case 'by_idpbk':
			$this->db->select('*');
			$this->db->select_as('pbk.ID', 'id_pbk');
			$this->db->select_as('pbk.Name', 'Name');
			$this->db->select('pbk_groups.Name', 'GroupName');	
			$this->db->from('pbk');
			$this->db->where('pbk.id_user', $this->session->userdata('id_user'));
			$this->db->join('pbk_groups', 'pbk_groups.ID=pbk.GroupID');
			$this->db->where('pbk.ID', $param['id_pbk']);
			break;
			
			case 'group':
			$this->db->select('*');
			$this->db->select_as('Name','GroupName');
			$this->db->from('pbk_groups');
			$this->db->where('id_user', $this->session->userdata('id_user'));
			$this->db->order_by('Name');
			break;
		
			case 'group_paginate':
			$this->db->select('*');
			$this->db->select_as('Name', 'GroupName');
			$this->db->from('pbk_groups');
			$this->db->where('id_user', $this->session->userdata('id_user'));
			$this->db->order_by('Name');
			$this->db->limit($param['limit'], $param['offset']);
			break;	
			
			case 'groupname':
			$this->db->select_as('Name', 'GroupName');
			$this->db->from('pbk_groups');
			$this->db->where('ID', $param['id']);
			$this->db->where('id_user', $this->session->userdata('id_user'));
			break;
			
			case 'bynumber':
			$this->db->select('*');
			$this->db->select_as('ID', 'id_pbk');	
			$this->db->from('pbk');
			$this->db->where('Number', $param['number']);
			$this->db->where('id_user', $this->session->userdata('id_user'));
			break;
			
			case 'bygroup':
			$this->db->from('pbk');
			$this->db->where('GroupID', $param['group_id']);
			$this->db->where('id_user', $this->session->userdata('id_user'));
			break;
			
			case 'search':
			$this->db->select('*');
			$this->db->select_as('ID', 'id_pbk');	
			$this->db->from('pbk');
			$this->db->like('Name', $this->input->post('search_name'));
			$this->db->where('id_user', $this->session->userdata('id_user'));
			$this->db->order_by('Name');
			break;
		}
		return $this->db->get();	
	}

	// --------------------------------------------------------------------
	
	/**
	 * Search Phonebook
	 *
	 * @access	public   		 
	 * @param	mixed $param
	 * @return	object
	 */		
	function search_phonebook($param)	
	{
		$this->db->from('pbk');
		$this->db->select_as('Number', 'id');
		$this->db->select_as('Name', 'name');
		$this->db->where('id_user', $param['uid']);
		$this->db->like('Name', $param['query']);
		$this->db->order_by('Name');		
		return $this->db->get();
	}

	// --------------------------------------------------------------------
	
	/**
	 * Add Contact
	 *
	 * @access	public   		 
	 * @param	mixed $param
	 * @return
	 */		
	function add_contact($param)
	{
		$this->db->set('Name', $param['Name']);
		$this->db->set('Number', $param['Number']);
		$this->db->set('GroupID', $param['GroupID']);
		$this->db->set('id_user', $param['id_user']);
		
		// edit mode
		if(isset($param['id_pbk'])) 
		{
			$this->db->where('ID', $param['id_pbk']);
			$this->db->update('pbk');
		}
		else $this->db->insert('pbk');
	}

	// --------------------------------------------------------------------
	
	/**
	 * Add Group
	 *
	 * @access	public   		 
	 * @param	mixed $param
	 * @return
	 */		
	function add_group()
	{
		$this->db->set('Name', trim($this->input->post('group_name')));
		$this->db->set('id_user', trim($this->input->post('pbkgroup_id_user')));
			
		// edit mode	
		if($this->input->post('pbkgroup_id'))
		{
			$this->db->where('ID', $this->input->post('pbkgroup_id'));
			$this->db->update('pbk_groups');
		}
		else $this->db->insert('pbk_groups');		
	}	

	// --------------------------------------------------------------------
	
	/**
	 * Delete Contact
	 *
	 * @access	public   		 
	 * @param	number $id_contact
	 * @return
	 */		
	function delete_contact()
	{
		$this->db->delete('pbk', array('ID' => $this->input->post('id'))); 
	}

	// --------------------------------------------------------------------
	
	/**
	 * Delete Group
	 *
	 * @access	public   		 
	 * @param	mixed $id_group
	 * @return
	 */	
	function delete_group()
	{
		$this->db->delete('pbk', array('GroupID' => $this->input->post('id'))); 
		$this->db->delete('pbk_groups', array('ID' => $this->input->post('id'))); 
	}
	
}

/* End of file phonebook_model.php */
/* Location: ./application/models/phonebook_model.php */