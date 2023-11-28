<?php
/**
 * Kalkun
 * An open source web based SMS Management
 *
 * @package		Kalkun
 * @author		Kalkun Dev Team
 * @license		https://spdx.org/licenses/GPL-2.0-or-later.html
 * @link		https://kalkun.sourceforge.io/
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

		if ($type === 'public')
		{
			$config['uri_segment'] = 4;
			$config['base_url'] = site_url().'/phonebook/index/public';
			$config['total_rows'] = $this->Phonebook_model->get_phonebook(array('option' => 'paginate', 'public' => TRUE))->num_rows();
			$this->pagination->initialize($config);
			$data['pagination_links'] = $this->pagination->create_links();

			$data['title'] = tr_raw('Public contacts');
			$data['public_contact'] = TRUE;
			$param = array('option' => 'paginate', 'public' => TRUE, 'limit' => $config['per_page'], 'offset' => $this->uri->segment(4, 0));
			$data['phonebook'] = $this->Phonebook_model->get_phonebook($param);
		}
		else
		{
			$config['uri_segment'] = 3;
			$config['base_url'] = site_url().'/phonebook/index/';
			$config['total_rows'] = $this->Phonebook_model->get_phonebook(array('option' => 'paginate'))->num_rows();
			$this->pagination->initialize($config);
			$data['pagination_links'] = $this->pagination->create_links();

			$data['title'] = tr_raw('Contacts');
			$data['public_contact'] = FALSE;

			if ($_POST)
			{
				$data['phonebook'] = $this->Phonebook_model->get_phonebook(array('option' => 'search'));
				$data['search_string'] = $this->input->post('search_name');
			}
			else
			{
				$param = array('option' => 'paginate', 'limit' => $config['per_page'], 'offset' => $this->uri->segment(3, 0));
				$data['phonebook'] = $this->Phonebook_model->get_phonebook($param);
			}
		}

		$data['main'] = 'main/phonebook/contact/index';
		$data['pbkgroup'] = $this->Phonebook_model->get_phonebook(array('option' => 'group'))->result();

		if (is_ajax())
		{
			$this->load->view('main/phonebook/contact/pbk_list', $data);
		}
		else
		{
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
	function group($type = NULL)
	{
		$this->load->library('pagination');
		$config = $this->_get_pagination_style();
		$config['per_page'] = $this->Kalkun_model->get_setting()->row('paging');

		if ($type === 'public')
		{
			$data['title'] = tr_raw('Public groups');
			$data['public_group'] = TRUE;
			$config['base_url'] = site_url().'/phonebook/group/public';
			$config['total_rows'] = $this->Phonebook_model->get_phonebook(array('option' => 'group', 'public' => TRUE))->num_rows();
			$config['uri_segment'] = 4;
			$this->pagination->initialize($config);
			$data['pagination_links'] = $this->pagination->create_links();

			$param = array('option' => 'group_paginate', 'public' => TRUE, 'limit' => $config['per_page'], 'offset' => $this->uri->segment(4, 0));
			$data['group'] = $this->Phonebook_model->get_phonebook($param);
		}
		else
		{
			$data['title'] = tr_raw('Groups');
			$data['public_group'] = FALSE;
			$config['base_url'] = site_url().'/phonebook/group/';
			$config['total_rows'] = $this->Phonebook_model->get_phonebook(array('option' => 'group'))->num_rows();
			$config['uri_segment'] = 3;
			$this->pagination->initialize($config);
			$data['pagination_links'] = $this->pagination->create_links();

			$param = array('option' => 'group_paginate', 'limit' => $config['per_page'], 'offset' => $this->uri->segment(3, 0));
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
		$data['pagination_links'] = $this->pagination->create_links();

		$param['limit'] = $config['per_page'];
		$param['offset'] = $this->uri->segment(4, 0);
		$contacts = $this->Phonebook_model->get_phonebook($param);

		if ($contacts->row('is_public') === 'true')
		{
			$data['public_contact'] = TRUE;
		}
		else
		{
			$data['public_contact'] = FALSE;
		}

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
		if ($_POST)
		{
			$this->Phonebook_model->add_group();
			redirect('phonebook/group');
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
		if ($_POST)
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
		$filePath = $_FILES['csvfile']['tmp_name'];

		//load the CSV document from a file path
		$csv = League\Csv\Reader::createFromPath($filePath, 'r');
		if (method_exists($csv, 'setHeaderOffset'))
		{
			// setHeaderOffset and following methods appeared with CSV League 9.x
			$csv->setHeaderOffset(0);
			$records = $csv->getRecords(); //returns all the CSV records as an Iterator object
		}
		else
		{
			// Case for CSV League 8.x
			$records = $csv->fetchAssoc();
		}

		foreach ($records as $offset => $record)
		{
			$pbk['Name'] = $record['Name'];
			$pbk['Number'] = $record['Number'];
			$pbk['GroupID'] = $this->input->post('importgroupvalue');
			$pbk['id_user'] = $this->input->post('pbk_id_user');
			$pbk['is_public'] = $this->input->post('is_public') ? 'true' : 'false';
			$this->Phonebook_model->add_contact($pbk);
		}

		$this->session->set_flashdata('notif', tr_raw('{0,number,integer} contacts imported successfully.', NULL, count($csv)));
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
		$type = $this->input->get('type');

		switch ($type)
		{
			case 'edit':
				$id_pbk = $this->input->get('param1');
				$data['contact'] = $this->Phonebook_model->get_phonebook(array('option' => 'by_idpbk', 'id_pbk' => $id_pbk));
				break;
			case 'message':
				$data['number'] = $this->input->get('param1');
				break;
			case 'normal':
				$data['group_id'] = $this->input->get('param1');
				break;
			default:
				break;
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
		$this->load->helper('kalkun');
		$pbk['Name'] = trim($this->input->post('name'));
		$this->_phone_number_validation($this->input->post('number'));
		$pbk['Number'] = phone_format_e164($this->input->post('number'));
		//$pbk['GroupID'] = $this->input->post('groupvalue');
		$pbk['Groups'] = $this->input->post('groups');
		$pbk['id_user'] = $this->input->post('pbk_id_user');
		$pbk['is_public'] = $this->input->post('is_public') ? 'true' : 'false';

		if ($this->input->post('editid_pbk'))
		{
			$pbk['id_pbk'] = $this->input->post('editid_pbk');
			$return_msg = [
				'type' => 'info',
				'msg' => tr_raw('Contact updated successfully.'),
			];
		}
		else
		{
			$return_msg = [
				'type' => 'info',
				'msg' => tr_raw('Contact added successfully.'),
			];
		}

		$this->Phonebook_model->add_contact($pbk);

		// Return status
		header('Content-type: application/json');
		echo json_encode($return_msg);
	}

	// --------------------------------------------------------------------

	/**
	 * Check if submitted phone number is valid
	 *
	 * @access	public
	 */
	function _phone_number_validation($phone)
	{
		$this->load->helper('kalkun');
		$result = is_phone_number_valid($phone);

		if ($result !== TRUE)
		{
			show_error($result, 400);
		}
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

		$tagInputResult = [];
		$output_format = $this->input->get('output_format');

		$q = $this->input->get('q');
		if (isset($q) && strlen($q) > 0)
		{
			$user_id = $this->session->userdata('id_user');
			$param = array('uid' => $user_id, 'query' => $q);
			$query = $this->Phonebook_model->search_phonebook($param)->result_array();

			// Add identifier, c for contact, g for group, u for user
			foreach ($query as $key => $val)
			{
				array_push($tagInputResult, array(
					'id' => $val['id'].':c',
					'name' => $val['name'],
					'value' => $val['name'].' ('.$val['id'].')',
				));
				$query[$key]['name'] .= ' ('.$val['id'].')';
				$query[$key]['id'] = $val['id'].':c';
			}
			$group = $this->Phonebook_model->search_group($param)->result_array();
			foreach ($group as $key => $val)
			{
				array_push($tagInputResult, array(
					'id' => $val['id'].':g',
					'name' => $val['name'],
					'value' => $val['name'].' ('.tr_raw('Group').')',
				));
				$group[$key]['id'] = $val['id'].':g';
			}

			// User, currently on inbox only
			$user = array();
			if ($type === 'inbox')
			{
				$user = $this->User_model->search_user($q)->result_array();
				foreach ($user as $key => $val)
				{
					array_push($tagInputResult, array(
						'id' => $val['id_user'].':u',
						'name' => $val['realname'],
						'value' => $val['realname'].' ('.tr_raw('User', 'default').')',
					));
					$user[$key]['id'] = $val['id_user'].':u';
					$user[$key]['name'] = $val['realname'];
				}
			}

			// hook for contact get
			$contact = do_action('phonebook.contact.get');
			if (empty($contact))
			{
				$contact = array();
			}
			foreach ($contact as $key => $val)
			{
				array_push($tagInputResult, array(
					'value' => $val['id'].':c',
					'name' => $val['id'],
					'label' => $val['id'].' (LDAP)',
				));
				$contact[$key]['id'] = $val['id'].':c';
			}

			$combine = array_merge($query, $group, $user, $contact);

			switch ($output_format)
			{
				case 'tagInput':
					header('Content-type: application/json');
					echo json_encode($tagInputResult);
					break;
				default:
					header('Content-type: application/json');
					echo json_encode($combine);
					break;
			}
		}
	}
}
