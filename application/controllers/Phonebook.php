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
	function __construct()
	{
		parent::__construct();
		$this->load->model('Phonebook_model');
		$this->load->library('Plugins');
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
		$this->load->library('pagination');
		$config = $this->_get_pagination_style();
		$config['per_page'] = $this->Kalkun_model->get_setting()->row('paging');

		if ($type == 'public')
		{
			$config['uri_segment'] = 4;
			$config['base_url'] = site_url().'/phonebook/index/public';
			$config['total_rows'] = $this->Phonebook_model->get_phonebook(array('option' => 'public'))->num_rows();			
			$this->pagination->initialize($config);
						
			$data['title'] = lang('kalkun_public_contact');
			$data['public_contact'] = TRUE;
			$param = array('option' => 'paginate', 'public' => TRUE, 'limit' => $config['per_page'], 'offset' => $this->uri->segment(4,0));
			$data['phonebook'] = $this->Phonebook_model->get_phonebook($param);		
		}
		else
		{
			$config['uri_segment'] = 3;
			$config['base_url'] = site_url().'/phonebook/index/';
			$config['total_rows'] = $this->Phonebook_model->get_phonebook(array('option' => 'all'))->num_rows();
			$this->pagination->initialize($config);
			
			$data['title'] = lang('tni_contacts');
			$data['public_contact'] = FALSE;
			
			if($_POST)
	    	{
				$data['phonebook'] = $this->Phonebook_model->get_phonebook(array('option' => 'search'));
	      		$data['search_string'] = $this->input->post('search_name');
	    	}
			else
			{
				$param = array('option' => 'paginate', 'limit' => $config['per_page'], 'offset' => $this->uri->segment(3,0));
				$data['phonebook'] = $this->Phonebook_model->get_phonebook($param);
			}
		}
		
		$data['main'] = 'main/phonebook/contact/index';
		$data['pbkgroup'] = $this->Phonebook_model->get_phonebook(array('option' => 'group'))->result();
		$this->load->view('main/layout', $data);
	}

	// --------------------------------------------------------------------
	
	/**
	 * Group
	 *
	 * Display list of all group
	 *
	 * @access	public   		 
	 */	
	function group($type = NULL)
	{    
   		
   		$this->load->library('pagination');
   		$config = $this->_get_pagination_style();
   		$config['per_page'] = $this->Kalkun_model->get_setting()->row('paging');
   		
   		if ($type == 'public')
   		{
   			$data['title'] = lang('kalkun_public_group');
   			$data['public_group'] = TRUE;
   			$config['base_url'] = site_url().'/phonebook/group/public';
   			$config['total_rows'] = $this->Phonebook_model->get_phonebook(array('option' => 'group', 'public' => TRUE))->num_rows();
   			$config['uri_segment'] = 4;
   			$this->pagination->initialize($config);
   	
	   		$param = array('option' => 'group_paginate', 'public' => TRUE, 'limit' => $config['per_page'], 'offset' => $this->uri->segment(4,0));
	   		$data['group'] = $this->Phonebook_model->get_phonebook($param);   			
   		}
   		else
   		{
   			$data['title'] = lang('tni_groups');
   			$data['public_group'] = FALSE;
	   		$config['base_url'] = site_url().'/phonebook/group/';
	   		$config['total_rows'] = $this->Phonebook_model->get_phonebook(array('option' => 'group'))->num_rows();
	   		$config['uri_segment'] = 3;
	   		$this->pagination->initialize($config);
	   		
	   		$param = array('option' => 'group_paginate', 'limit' => $config['per_page'], 'offset' => $this->uri->segment(3,0));
	   		$data['group'] = $this->Phonebook_model->get_phonebook($param);
   		}
   		
   		$data['main'] = 'main/phonebook/group/index';
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
		$config = $this->_get_pagination_style();
   		$config['base_url'] = site_url().'/phonebook/group_contacts/'.$group_id;
   		$config['total_rows'] = $this->Phonebook_model->get_phonebook($param)->num_rows();
   		$config['per_page'] = $this->Kalkun_model->get_setting()->row('paging');
   		$config['uri_segment'] = 4;
   		$this->pagination->initialize($config);
   				
   		$param['limit'] = $config['per_page'];
   		$param['offset'] = $this->uri->segment(4,0);
   		$contacts = $this->Phonebook_model->get_phonebook($param);
   		
   		if($contacts->row('is_public') == 'true') $data['public_contact'] = TRUE;
   		else $data['public_contact'] = FALSE;
   		
   		$data['group_id'] = $group_id;
   		$data['main'] = 'main/phonebook/contact/index';	
   	    $data['title'] = $contacts->row('GroupName');
   	    $data['phonebook'] = $contacts;
   		$data['pbkgroup'] = $this->Phonebook_model->get_phonebook(array('option' => 'group'))->result();
   	 	
		$this->load->view('main/layout', $data);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Get Pagination Style
	 *
	 * Get config style for pagination
	 *
	 * @access	private   		 
	 */		
	 function _get_pagination_style()
	 {
   		$config['full_tag_open'] = '<ul>';
   		$config['full_tag_close'] = '</ul>';
   		$config['num_tag_open'] = '<li>';
   		$config['num_tag_close'] = '</li>';	 
   		$config['cur_tag_open'] = '<li><span id="current">';
   		$config['cur_tag_close'] = '</span></li>';	
   		$config['prev_tag_open'] = '<li>';
   		$config['prev_tag_close'] = '</li>';
   		$config['next_tag_open'] = '<li>';
   		$config['next_tag_close'] = '</li>';
   		$config['first_tag_open'] = '<li>';
   		$config['first_tag_close'] = '</li>';
   		$config['last_tag_open'] = '<li>';
   		$config['last_tag_close'] = '</li>';
   		return $config;   			
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
			$pbk['is_public'] = $this->input->post('is_public')? 'true' : 'false';			
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
		$this->load->helper('form');
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
		else if($type=='normal')
		{
			$data['group_id'] = $this->input->post('param1');
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
		$pbk['is_public'] = $this->input->post('is_public')? 'true' : 'false';
		
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
	function get_phonebook($type = NULL)
	{
		$this->load->model('User_model');
		
		$q = $this->input->post('q', TRUE);
		if (isset($q) && strlen($q) > 0)
		{
			$user_id = $this->session->userdata("id_user");
			$param = array('uid' => $user_id, 'query' => $q);
			$query = $this->Phonebook_model->search_phonebook($param)->result_array();
			
			// Add identifier, c for contact, g for group, u for user
			foreach($query as $key => $q)
			{
				$query[$key]['name'] .= ' ('.str_replace('+', '', $q['id']).')';
				$query[$key]['id'] = $q['id'].":c";
			}
			$group = $this->Phonebook_model->search_group($param)->result_array();
			foreach($group as $key => $q)
			{
				$group[$key]['id'] = $q['id'].":g";
			}
			
			// User, currently on inbox only
			$user = array();
			if ($type=='inbox')
			{
				$user = $this->User_model->search_user($q)->result_array();
				foreach($user as $key => $q)
				{
					$user[$key]['id'] = $q['id_user'].":u";
					$user[$key]['name'] = $q['realname'];
				}
			}
			
			// hook for contact get
			$contact = do_action("phonebook.contact.get");
			if (empty($contact))
			{
				$contact = array();
			}
			foreach($contact as $key => $q)
			{
				$contact[$key]['id'] = $q['id'].":c";
			}
			
			$combine = array_merge($query, $group, $user, $contact);
			echo json_encode($combine);
		}
	}
    
}

/* End of file phonebook.php */
/* Location: ./application/controllers/phonebook.php */