<?php
Class Phonebook extends MY_Controller {
	
function Phonebook()
{
	parent::MY_Controller();
}

function index($type = NULL) 
{		
	$data['title'] = 'Contacts';
	$this->load->library('pagination');
	$config['base_url'] = site_url().'/phonebook/index/';
	$config['total_rows'] = $this->Phonebook_model->getPhonebook(array('option' => 'all'))->num_rows();
	$config['per_page'] = $this->Kalkun_model->getSetting()->row('paging');
	$config['cur_tag_open'] = '<span id="current">';
	$config['cur_tag_close'] = '</span>';
	
	if($type == "ajax") $config['uri_segment'] = 4;
	else $config['uri_segment'] = 3;
	$this->pagination->initialize($config);
	$param = array('option' => 'paginate', 'limit' => $config['per_page'], 'offset' => $this->uri->segment(3,0));
	
	if ($type == "ajax"):
	$data['phonebook'] = $this->Phonebook_model->getPhonebook($param);		
	$this->load->view('main/phonebook/contact/pbk_list', $data);
	else:
	$data['main'] = 'main/phonebook/contact/index';	
	$data['pbkgroup'] = $this->Phonebook_model->getPhonebook(array('option' => 'group'))->result();
	if($_POST) $data['phonebook'] = $this->Phonebook_model->getPhonebook(array('option' => 'search'));
	else $data['phonebook'] = $this->Phonebook_model->getPhonebook($param);
	
	$this->load->view('main/layout', $data);
	endif;
}

function group()
{
	$data['title'] = 'Groups';
	$this->load->library('pagination');
	$config['base_url'] = site_url().'/phonebook/group/';
	$config['total_rows'] = $this->Phonebook_model->getPhonebook(array('option' => 'group'))->num_rows();
	$config['per_page'] = $this->Kalkun_model->getSetting()->row('paging');
	$config['cur_tag_open'] = '<span id="current">';
	$config['cur_tag_close'] = '</span>';	
	$config['uri_segment'] = 3;
	$this->pagination->initialize($config);
	$param = array('option' => 'group_paginate', 'limit' => $config['per_page'], 'offset' => $this->uri->segment(3,0));
	
	$data['main'] = 'main/phonebook/group/index';
	$data['group'] = $this->Phonebook_model->getPhonebook($param);
	
	$this->load->view('main/layout', $data);
}


function del_phonebook()
{
	$this->Phonebook_model->delPhonebook();
}

function del_group()
{
	$this->Phonebook_model->delGroup();
}

function add_phonebook_group()
{
	if($_POST) { $this->Phonebook_model->addPhonebookGroup(); redirect('phonebook/group'); }
}

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
		$this->Phonebook_model->addPhonebook($pbk);
		$n++;
	endforeach;
	
	$this->session->set_flashdata('notif', $n.' contacts successfully imported');
	redirect('phonebook');		 
}

function add_contact()
{
	$data['pbkgroup'] = $this->Phonebook_model->getPhonebook(array('option' => 'group'));
	$type = $this->input->post('type');
	
	if($type=='edit')
	{
		$id_pbk = $this->input->post('param1');
	 	$data['contact']=$this->Phonebook_model->getPhonebook(array('option' => 'by_idpbk', 'id_pbk' => $id_pbk));
	}
	else if($type=='message')
	{
		$data['number'] = $this->input->post('param1');
	}
	$this->load->view('main/phonebook/contact/add_contact', $data);	
}

function add_contact_process()
{
	$pbk['Name'] = trim($this->input->post('name'));
	$pbk['Number'] = trim($this->input->post('number'));
	$pbk['GroupID'] = $this->input->post('groupvalue');
	$pbk['id_user'] = $this->input->post('pbk_id_user');
	
	if($this->input->post('editid_pbk'))
	{
		$pbk['id_pbk'] = $this->input->post('editid_pbk');
		$msg = "<div class=\"notif\">Contact has been updated.</div>";
	}
	else
		$msg = "<div class=\"notif\">Contact has been added.</div>";
		
	$this->Phonebook_model->addPhonebook($pbk);
	echo $msg;
}

}
?>