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
 * Install Class
 *
 * @package		Kalkun
 * @subpackage	Install
 * @category	Controllers
 */
class Install extends Controller {
	
	/**
	 * Constructor
	 *
	 * @access	public
	 */
	function Install()
	{
		parent::Controller();
		if(!file_exists('./install')) die("Installation disabled.");
		
		// check if gammu schema already exist
		if(!$this->db->table_exists('gammu')) die("Cannot find gammu database schema.");
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Index
	 *
	 * Display welcome page
	 *
	 * @access	public   		 
	 */	
	function index()
	{
		$data['main'] = 'main/install/welcome';
		$this->load->view('main/install/layout', $data);	
	}

	// --------------------------------------------------------------------
	
	/**
	 * Requirement check
	 *
	 * Requirement check page
	 *
	 * @access	public   		 
	 */		
	function requirement_check()
	{
		$data['main'] = 'main/install/requirement_check';
		$data['database_driver'] = $this->db->platform();
		$this->load->view('main/install/layout', $data);
	}

	// --------------------------------------------------------------------
	
	/**
	 * Database setup
	 *
	 * Database setup page
	 *
	 * @access	public   		 
	 */			
	function database_setup()
	{	
		$data['main'] = 'main/install/database_setup';
		$data['database_driver'] = $this->db->platform();
		$this->load->view('main/install/layout', $data);			
	}

	// --------------------------------------------------------------------
	
	/**
	 * Run install
	 *
	 * Dumping data drom SQL file
	 *
	 * @access	public   		 
	 */			
	function run_install($type=NULL)
	{
		// This method taken from Roundcube installation
		//if($type=='install') $sqlfile = $this->config->item('sql_path')."install.sql";
		//else $sqlfile = $this->config->item('sql_path')."upgrade.sql";
		
		$sqlfile = $this->config->item('sql_path').$this->input->post('db_engine')."_kalkun.sql";
		$data['error'] = $this->_execute_sql($sqlfile);
		
		// cleanup
		//if($type=='upgrade') $this->_upgrade();
		
		$data['main'] = 'main/install/install_result';
		$this->load->view('main/install/layout', $data);
	}

	// --------------------------------------------------------------------
	
	/**
	 * Execute SQL
	 *
	 * Run SQL command from file, line by line
	 *
	 * @access	private   		 
	 */		
	function _execute_sql($sqlfile)
	{
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
				// if contains TRIGGER
				if(preg_match('/CREATE TRIGGER$/', trim($line))) $buff .= ' END;';
				
				$query = $this->Kalkun_model->db->query($buff);
			  	if(!$query) $error++;
			  	$buff = '';
			}
		  }
		}
		return $error;		
	}

	// --------------------------------------------------------------------
	
	/**
	 * Upgrade
	 *
	 * Upgrade installation, database cleanup
	 * Only used if there is change on database structure
	 *
	 * @access	private   		 
	 */		
	 // Currently not used
	 /*
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
	}*/
}

/* End of file install.php */
/* Location: ./application/controllers/install.php */ 