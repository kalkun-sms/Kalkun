<?php
Class Phonebook_model extends Model {
	
function Phonebook_model()
{
	parent::Model();
}

function getPhonebook($param)
{
	switch($param['option']) 
	{
	case 'all':
	$this->db->select('*');
	$this->db->select('pbk.ID as id_pbk');
	$this->db->select('pbk_groups.Name as GroupName');	
	$this->db->from('pbk');
	$this->db->where('pbk.id_user', $this->session->userdata('id_user'));
	$this->db->join('pbk_groups', 'pbk_groups.ID=pbk.GroupID');
	$this->db->order_by('pbk.Name');
	break;	
	
	case 'paginate':
	$this->db->select('*');
	$this->db->select('ID as id_pbk');	
	$this->db->from('pbk');
	$this->db->where('id_user', $this->session->userdata('id_user'));
	$this->db->order_by('Name');
	$this->db->limit($param['limit'], $param['offset']);
	break;
	
	case 'by_idpbk':
	$this->db->select('*');
	$this->db->select('pbk.ID as id_pbk');
	$this->db->select('pbk.Name as Name');
	$this->db->select('pbk_groups.Name as GroupName');	
	$this->db->from('pbk');
	$this->db->where('pbk.id_user', $this->session->userdata('id_user'));
	$this->db->join('pbk_groups', 'pbk_groups.ID=pbk.GroupID');
	$this->db->where('pbk.ID', $param['id_pbk']);
	break;
	
	case 'group':
	$this->db->select('*');
	$this->db->select('Name as GroupName');
	$this->db->from('pbk_groups');
	$this->db->where('id_user', $this->session->userdata('id_user'));
	$this->db->order_by('Name');
	break;

	case 'group_paginate':
	$this->db->select('*');
	$this->db->select('Name as GroupName');
	$this->db->from('pbk_groups');
	$this->db->where('id_user', $this->session->userdata('id_user'));
	$this->db->order_by('Name');
	$this->db->limit($param['limit'], $param['offset']);
	break;	
	
	case 'groupname':
	$this->db->select('Name as GroupName');
	$this->db->from('pbk_groups');
	$this->db->where('ID', $param['id']);
	$this->db->where('id_user', $this->session->userdata('id_user'));
	break;
	
	case 'bynumber':
	$this->db->select('*');
	$this->db->select('ID as id_pbk');	
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
	$this->db->select('ID as id_pbk');	
	$this->db->from('pbk');
	$this->db->like('Name', $this->input->post('search_name'));
	$this->db->where('id_user', $this->session->userdata('id_user'));
	$this->db->order_by('Name');
	break;
	}
	return $this->db->get();	
}

function addPhonebook($param)
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

function updatePhonebook()
{
	$data = array (
		'Name' => $this->input->post('name'),
		'Number' => trim($this->input->post('number')),
		'GroupID' => $this->input->post('group',TRUE),
		);
	$this->db->where('ID', $this->input->post('id'));			
	$this->db->update('pbk',$data);
}

function delPhonebook()
{
	$this->db->delete('pbk', array('ID' => $this->input->post('id'))); 
}

function delGroup()
{
	$this->db->delete('pbk', array('GroupID' => $this->input->post('id'))); 
	$this->db->delete('pbk_groups', array('ID' => $this->input->post('id'))); 
}

function addPhonebookGroup()
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
	
}
?>