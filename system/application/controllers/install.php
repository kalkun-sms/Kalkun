<?php
class Install extends Controller {
	
//=================================================================
// KALKUN INSTALLATION
//=================================================================	

function Install()
{
	parent::Controller();
	if(!file_exists('./install')) die("Installation disabled.");
}

function index()
{
	$data['main'] = 'main/install/welcome';
	$this->load->view('main/install/layout', $data);	
}

function requirement_check()
{
	$data['main'] = 'main/install/requirement_check';
	$this->load->view('main/install/layout', $data);
}
	
function database_setup()
{	
	$data['main'] = 'main/install/database_setup';
	$this->load->view('main/install/layout', $data);			
}
	
function run_install()
{
	// This method taken from Roundcube installation
	$sqlfile = $this->config->item('sql_path')."install.sql";
	$error=0;
	if ($lines = @file($sqlfile, FILE_SKIP_EMPTY_LINES)) 
	{
	  $buff = '';
	  foreach ($lines as $i => $line)
	  {
		if (preg_match('/^--/', $line)) continue;
		$buff .= $line . "\n";
		if (preg_match('/;$/', trim($line)))
		{
		  $query = $this->Kalkun_model->db->query($buff);
		  if(!$query) $error++;
		  $buff = '';
		}
	  }
	}
	$data['error'] = $error;
	$data['main'] = 'main/install/install_result';
	$this->load->view('main/install/layout', $data);
}
}
?>