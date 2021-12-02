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
class Install extends CI_Controller {

	/**
	 * Constructor
	 *
	 * @access	public
	 */
	function __construct()
	{
		parent::__construct();
		if ( ! file_exists('./install'))
		{
			show_error('Installation has been disabled by the administrator.<p>
			To enable access to it, create a file named <strong>install</strong>
			in this directory of the server: <strong>'.realpath('.').'</strong>.
			<p>Otherwise you may <a href="..">log-in</a>.', 403, '403 Forbidden');
		}

		require_once(APPPATH.'config/database.php');
		$this->db_config = $db[$active_group];
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
		$data['database_driver'] = $this->db_config['dbdriver'];
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
		$this->load->database();
		$data['main'] = 'main/install/database_setup';
		$data['database_driver'] = $this->db->platform();
		$data['has_smsd_database'] = $this->db->table_exists('gammu') ? TRUE : FALSE;

		// By default we consider Kalkun database schema is not installed
		$detected_db_version = '0';
		$data['type'] = 'install';

		// Now check if it is installed, and which version it is.
		// plugins table appeared in 0.4
		if ($this->Kalkun_model->has_table_plugins())
		{
			$detected_db_version = '0.4';
			$data['type'] = 'upgrade_not_supported';
		}
		// user_forgot_password table appeared in 0.6
		if ($this->Kalkun_model->has_table_user_forgot_password())
		{
			$detected_db_version = '0.6';
			$data['type'] = 'upgrade';
		}
		// user_filters table appeared in 0.7
		if ($this->Kalkun_model->has_table_user_filters())
		{
			$detected_db_version = '0.7';
			$data['type'] = 'upgrade';
		}
		// ci_sessions table appeared in 0.8
		if ($this->Kalkun_model->has_table_ci_sessions())
		{
			$detected_db_version = '0.8';
			$data['type'] = 'up_to_date';
		}

		$data['detected_db_version'] = $detected_db_version;

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
	function run_install($type = NULL)
	{
		$this->load->helper('kalkun');
		$this->load->model('Kalkun_model');
		$this->load->database();

		$data['error'] = 0;

		// Check for phonebook tables
		// they have been dropped in Gammu (schema v16) but we need them for Phonebook feature
		if ( ! $this->Kalkun_model->has_table_pbk())
		{
			$data['error'] += $this->_install_pbk_tables();
		}

		// Add kalkun's specific fields to pbk table.
		if ( ! $this->Kalkun_model->has_table_pbk_with_kalkun_fields())
		{
			$data['error'] += $this->_add_kalkun_fields_to_pbk_tables();
		}


		if ( ! $this->db->table_exists('user'))
		{
			// Install
			$data['error'] += $this->_install('');
		}
		else
		{
			// Upgrade
			$data['error'] += $this->_upgrade();
		}

		// Set current version of kalkun in database
		$ret = $this->db->empty_table('kalkun');
		$this->db->insert('kalkun', array('version' => $this->config->item('kalkun_version')));

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
		$this->load->model('Kalkun_model');
		$error = 0;

		// Update SQL schema to version 0.7
		if ( ! $this->Kalkun_model->has_table_user_filters())
		{
			$error = $this->_execute_kalkun_sql_file('_upgrade_kalkun_0.7.sql');
			if ($error !== 0)
			{
				return $error;
			}
		}

		// Update SQL schema to version 0.8
		if ( ! $this->Kalkun_model->has_table_ci_sessions())
		{
			$error = $this->_execute_kalkun_sql_file('_upgrade_kalkun_0.8.sql');
			if ($error !== 0)
			{
				return $error;
			}
		}

		// Add here equivalent code as above for the future upgrades

		return $error;
	}

	function _install()
	{
		return $this->_execute_kalkun_sql_file('_kalkun.sql');
	}

	function _install_pbk_tables()
	{
		return $this->_execute_kalkun_sql_file('_pbk_gammu.sql');
	}

	function _add_kalkun_fields_to_pbk_tables()
	{
		return $this->_execute_kalkun_sql_file('_pbk_kalkun.sql');
	}

	function _execute_kalkun_sql_file($filename_suffix)
	{
		$this->load->helper('kalkun');
		$sqlfile = $this->input->post('db_engine').$filename_suffix;
		return execute_sql($this->config->item('sql_path').$sqlfile);
	}
}
