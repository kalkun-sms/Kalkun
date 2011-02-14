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
	
function run_install($type)
{
	// This method taken from Roundcube installation
	if($type=='install') $sqlfile = $this->config->item('sql_path')."install.sql";
	else $sqlfile = $this->config->item('sql_path')."upgrade.sql";
	
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
	if($type=='upgrade') $this->_upgrade();
	
	$data['main'] = 'main/install/install_result';
	$this->load->view('main/install/layout', $data);
}

function _upgrade() 
{
	// get all inbox ID
	$sql = "select ID from inbox";
	$inbox = $this->Kalkun_model->db->query($sql);
	foreach($inbox->result() as $tmp):
		$data = array('id_inbox' => $tmp->ID, 
					'id_user' => '1');
		$this->Kalkun_model->db->insert('user_inbox', $data);
	endforeach;
	
	// update processed
	$this->Kalkun_model->db->query("update inbox set Processed='true'");
	
	// get all outbox ID
	$sql = "select ID from outbox";
	$inbox = $this->Kalkun_model->db->query($sql);
	foreach($inbox->result() as $tmp):
		$data = array('id_inbox' => $tmp->ID, 
					'id_user' => '1');
		$this->Kalkun_model->db->insert('user_outbox',$data);
	endforeach;	

	// get all sentitems ID
	$sql = "select ID from sentitems";
	$inbox = $this->Kalkun_model->db->query($sql);
	foreach($inbox->result() as $tmp):
		$data = array('id_sentitems' => $tmp->ID, 
					'id_user' => '1');
		$this->Kalkun_model->db->insert('user_sentitems',$data);
	endforeach;		
	
	// get current password
	$sql = "select value from settings where id='2'";
	$pass = $this->Kalkun_model->db->query($sql)->row('value');
	
	$data = array('id_user' => '1',
				'username' => 'kalkun',
				'realname' => 'Kalkun SMS',
				'password' => $pass,
				'phone_number' => '123456',
				'level' => 'admin'	
				);
	$this->Kalkun_model->db->insert('user',$data);
	
	// drop settings table
	$this->Kalkun_model->db->query('DROP TABLE `settings`;');
}
}
?>