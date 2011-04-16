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
        $data['type'] = 'install';
        if($this->config->item('kalkun_upgradeable') && $this->db->table_exists('user'))
        {
        	$data['type'] = 'upgrade';
        }
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
	    
		if($type=='upgrade') $sqlfile = $this->config->item('sql_path').$this->input->post('db_engine')."_upgrade_kalkun.sql";
		else $sqlfile = $sqlfile = $this->config->item('sql_path').$this->input->post('db_engine')."_kalkun.sql";
  
  		$data['error'] = $this->_execute_sql($sqlfile);
        
        if($type=='upgrade') $this->_upgrade();
		
        $this->db->empty_table('kalkun'); 
        $this->db->insert('kalkun', array( 'version' => $this->config->item('kalkun_version'))); 
        
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
 
	function _upgrade() 
	{
	   
       //moving groups to new schema
       $this->db->select('*');
	   $this->db->from('pbk');
       $result = $this->db->get()->result();
       
        for ($i = 0 ; $i< count($result); $i++)
        {
            if($result[$i]->GroupID != '-1') :
                $data = array(
               'id_pbk' => $result[$i]->ID ,
               'id_pbk_groups' => $result[$i]->GroupID,
               'id_user' => $result[$i]->id_user    );
    
                $this->db->insert('user_group', $data); 
                
                $this->db->where('ID', $result[$i]->ID);
                $this->db->update('pbk', array( 'GroupID' => '-1' ));
            endif;
            
            $this->db->update('user', array( 'email_id' => 'you@domain.com' ));
            $this->db->update('user_settings', array( 'email_forward' => 'false' ));
            
        }

	} 
}

/* End of file install.php */
/* Location: ./application/controllers/install.php */ 