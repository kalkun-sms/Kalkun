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
 * Phonebook Class
 *
 * @package		Kalkun
 * @subpackage	Phonebook
 * @category	Controllers
 */
class Phonebook extends MY_Controller {

	/**
	 * Constructor
	 *
	 * @access	public
	 */		
	function Phonebook()
	{
		parent::MY_Controller();
	}

	// --------------------------------------------------------------------
	
	/**
	 * Index
	 *
	 * Display list of all contact
	 *
	 * @access	public   		 
	 */	
	function index($type = NULL) 
	{		
		$data['title'] = lang('tni_contacts');
		$this->load->library('pagination');
		$config['base_url'] = site_url().'/phonebook/index/';
		$config['total_rows'] = $this->Phonebook_model->get_phonebook(array('option' => 'all'))->num_rows();
		$config['per_page'] = $this->Kalkun_model->get_setting()->row('paging');
		$config['cur_tag_open'] = '<span id="current">';
		$config['cur_tag_close'] = '</span>';
		
		if($type == "ajax") $config['uri_segment'] = 4;
		else $config['uri_segment'] = 3;
		
		$this->pagination->initialize($config);
		$param = array('option' => 'paginate', 'limit' => $config['per_page'], 'offset' => $this->uri->segment(3,0));
		
		if ($type == "ajax")
		{
			$data['phonebook'] = $this->Phonebook_model->get_phonebook($param);		
			$this->load->view('main/phonebook/contact/pbk_list', $data);
		}
		else
		{
			$data['main'] = 'main/phonebook/contact/index';	
			$data['pbkgroup'] = $this->Phonebook_model->get_phonebook(array('option' => 'group'))->result();
			if($_POST)
	    	{
				$data['phonebook'] = $this->Phonebook_model->get_phonebook(array('option' => 'search'));
	      		$data['search_string'] = $this->input->post('search_name');
	    	}
			else $data['phonebook'] = $this->Phonebook_model->get_phonebook($param);
			
			$this->load->view('main/layout', $data);
		}
	}

	// --------------------------------------------------------------------
	
	/**
	 * Group
	 *
	 * Display list of all group
	 *
	 * @access	public   		 
	 */	
	function group()
	{    
   		$data['title'] = 'Groups';
   		$this->load->library('pagination');
   		$config['base_url'] = site_url().'/phonebook/group/';
   		$config['total_rows'] = $this->Phonebook_model->get_phonebook(array('option' => 'group'))->num_rows();
   		$config['per_page'] = $this->Kalkun_model->get_setting()->row('paging');
   		$config['cur_tag_open'] = '<span id="current">';
   		$config['cur_tag_close'] = '</span>';	
   		$config['uri_segment'] = 3;
   		$this->pagination->initialize($config);
   		$param = array('option' => 'group_paginate', 'limit' => $config['per_page'], 'offset' => $this->uri->segment(3,0));
   		
   		$data['main'] = 'main/phonebook/group/index';
   		$data['group'] = $this->Phonebook_model->get_phonebook($param);
   		
   		$this->load->view('main/layout', $data);
	}

	// --------------------------------------------------------------------
	
	/**
	 * Group Contacts
	 *
	 * Display list of contact from specific group
	 *
	 * @access	public   		 
	 */		
	function group_contacts($group_id = NULL)
	{
		$param = array('option' => 'bygroup', 'group_id' => $group_id);
		$this->load->library('pagination');
   		$config['base_url'] = site_url().'/phonebook/group_contacts/'.$group_id;
   		$config['total_rows'] = $this->Phonebook_model->get_phonebook($param)->num_rows();
   		$config['per_page'] = $this->Kalkun_model->get_setting()->row('paging');
   		$config['cur_tag_open'] = '<span id="current">';
   		$config['cur_tag_close'] = '</span>';	
   		$config['uri_segment'] = 4;
   		$this->pagination->initialize($config);
   				
   		$param['limit'] = $config['per_page'];
   		$param['offset'] = $this->uri->segment(4,0);
   		$contacts = $this->Phonebook_model->get_phonebook($param);
   		$data['main'] = 'main/phonebook/contact/index';	
   	    $data['title'] = $contacts->row('GroupName');
   	    $data['phonebook'] = $contacts;
   		$data['pbkgroup'] = $this->Phonebook_model->get_phonebook(array('option' => 'group'))->result();
   	 	
		$this->load->view('main/layout', $data);
	}

	// --------------------------------------------------------------------
	
	/**
	 * Delete contact
	 *
	 * Delete a contact
	 *
	 * @access	public   		 
	 */		
	function delete_contact()
	{
		$this->Phonebook_model->delete_contact();
	}

	// --------------------------------------------------------------------
	
	/**
	 * Delete group
	 *
	 * Delete a group and all contact on that group
	 *
	 * @access	public   		 
	 */		
	function delete_group()
	{
		$this->Phonebook_model->delete_group();
	}

	// --------------------------------------------------------------------
	
	/**
	 * Add group
	 *
	 * Add a group
	 *
	 * @access	public   		 
	 */		
	function add_group()
	{
		if($_POST) 
		{ 
			$this->Phonebook_model->add_group(); redirect('phonebook/group'); 
		}
	}

	// --------------------------------------------------------------------
  
  	/**
	 * Add/Remove Group from Contact
	 *
	 * Add a group
	 *
	 * @access	public   		 
	 */		
	function update_contact_group()
	{
		if($_POST) 
		{ 
			$this->Phonebook_model->multi_attach_group();  
		}
	}

	// --------------------------------------------------------------------
	
	/**
	 * Import contact
	 *
	 * Add contact from CSV file
	 * The CSV file must contain Name and Number as table header
	 *
	 * @access	public   		 
	 */		
	function import_phonebook()
	{
		$this->load->library('csvreader');
		$filePath = $_FILES["csvfile"]["tmp_name"];
		$csvData = $this->csvreader->parse_file($filePath, true);	
		
		$n=0;
		foreach($csvData as $field):
			$pbk['Name'] = $field["Name"];
			$pbk['Number'] = $field["Number"];
			$pbk['GroupID'] = $this->input->post('importgroupvalue');
			$pbk['id_user'] = $this->input->post('pbk_id_user');			
			$this->Phonebook_model->add_contact($pbk);
			$n++;
		endforeach;
		
		$this->session->set_flashdata('notif', $n.' contacts successfully imported');
		redirect('phonebook');		 
	}

	// --------------------------------------------------------------------
	
	/**
	 * Add contact
	 *
	 * Display add/update contact form
	 *
	 * @access	public   		 
	 */		
	function add_contact()
	{
		$data['pbkgroup'] = $this->Phonebook_model->get_phonebook(array('option' => 'group'));
		$type = $this->input->post('type');
		
		if ($type=='edit')
		{
			$id_pbk = $this->input->post('param1');
		 	$data['contact']=$this->Phonebook_model->get_phonebook(array('option' => 'by_idpbk', 'id_pbk' => $id_pbk));
		}
		else if ($type=='message')
		{
			$data['number'] = $this->input->post('param1');
		}
		$this->load->view('main/phonebook/contact/add_contact', $data);	
	}

	// --------------------------------------------------------------------
	
	/**
	 * Add contact process
	 *
	 * Process the submitted add/update contact form
	 *
	 * @access	public   		 
	 */		
	function add_contact_process()
	{
		$pbk['Name'] = trim($this->input->post('name'));
		$pbk['Number'] = trim($this->input->post('number'));
		//$pbk['GroupID'] = $this->input->post('groupvalue');
        $pbk['Groups'] = $this->input->post('groups');
		$pbk['id_user'] = $this->input->post('pbk_id_user');
		
		if($this->input->post('editid_pbk'))
		{
			$pbk['id_pbk'] = $this->input->post('editid_pbk');
			$msg = "<div class=\"notif\">Contact has been updated.</div>";
		}
		else
			$msg = "<div class=\"notif\">Contact has been added.</div>";
			
		$this->Phonebook_model->add_contact($pbk);
		echo $msg;
	}

	// --------------------------------------------------------------------
	
	/**
	 * Get phonebook
	 *
	 * Search contact by name
	 * Used by compose window as autocompleter
	 *
	 * @access	public   		 
	 */	
	function get_phonebook()
	{
		$q = $this->input->post('q', TRUE);
		if (isset($q) && strlen($q) > 0)
		{
			$user_id = $this->session->userdata("id_user");
			$param = array('uid' => $user_id, 'query' => $q);
			$query = $this->Phonebook_model->search_phonebook($param);
			echo json_encode($query->result());
		}
	}
    
}

/* End of file phonebook.php */
/* Location: ./application/controllers/phonebook.php */