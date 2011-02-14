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

function add_phonebook()
{
	if($_POST) $this->Phonebook_model->addPhonebook();
	else redirect('phonebook');		
}

function del_phonebook()
{
	$this->Phonebook_model->delPhonebook();
}

function del_group()
{
	$this->Phonebook_model->delGroup();
}

function update_phonebook()
{
	$this->Phonebook_model->updatePhonebook();
}

function add_phonebook_group()
{
	if($_POST) { $this->Phonebook_model->addPhonebookGroup(); redirect('phonebook/group'); }
}

function import_phonebook()
{
	if($_POST) { 
          $this->load->library('csvreader');
          $filePath = 'http://localhost:8888/kalkun/worked/temp/test.csv';
          $csvData = $this->csvreader->parse_file($filePath);
		
		echo "<pre>";
		print_r($csvData);
		echo "</pre>";
		
		//$this->Kalkun_model->importPhonebook(); 
		//redirect('phonebook'); 
	}
	else redirect('phonebook');	
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
	$this->Phonebook_model->addPhonebook();
	if($this->input->post('editid_pbk')) echo "<div class=\"notif\">Contact has been updated.</div>";
	else echo "<div class=\"notif\">Contact has been added.</div>";
}

}
?>