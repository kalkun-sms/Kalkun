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
	function __construct()
	{
		parent::__construct();
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
		$this->load->helper('kalkun');
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
		$this->load->model('Kalkun_model');
		$this->load->helper(array('form', 'kalkun'));
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
		$this->load->helper('kalkun');
		if($type=='upgrade') $sqlfile = $this->config->item('sql_path').$this->input->post('db_engine')."_upgrade_kalkun.sql";
		else $sqlfile = $sqlfile = $this->config->item('sql_path').$this->input->post('db_engine')."_kalkun.sql";
  
  		$data['error'] = execute_sql($sqlfile);
        
        if($type=='upgrade') $this->_upgrade();
		
        $this->db->empty_table('kalkun'); 
        $this->db->insert('kalkun', array( 'version' => $this->config->item('kalkun_version'))); 
        
		$data['main'] = 'main/install/install_result';
		$this->load->view('main/install/layout', $data);
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
	   // upgrade method from 0.3 to 0.4
	   
	   // Preserve id_folder 1 to 10
	   $id_folder = $this->db->where('id_folder >', '5')->where('id_folder <', '11')->get('user_folders');
	   
	   foreach ($id_folder->result() as $id)
	   {
	   		// Get max ID
	   		$max_id = $this->db->select_max('id_folder', 'max_id')->get('user_folders');
	   		$max_id = $max_id->row('max_id');
			$new_id = $max_id+1;
			
	   		// Update Inbox and Sentitems
	   		$this->db->where('id_folder', $id->id_folder)->update('inbox', array('id_folder' => $new_id));
	   		$this->db->where('id_folder', $id->id_folder)->update('sentitems', array('id_folder' => $new_id));
	   		
	   		// Update user_folders
	   		$this->db->where('id_folder', $id->id_folder)->update('user_folders', array('id_folder' => $new_id));	   		
	   }
	   
	   // Insert spam folder
	   $this->db->insert('user_folders', array('id_folder' => 6, 'name' => 'Spam', 'id_user' => 0));	   
	} 
}

/* End of file install.php */
/* Location: ./application/controllers/install.php */ 