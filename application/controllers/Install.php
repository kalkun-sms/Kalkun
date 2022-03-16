<?php
/**
 * Kalkun
 * An open source web based SMS Management
 *
 * @package		Kalkun
 * @author		Kalkun Dev Team
 * @license		https://spdx.org/licenses/GPL-3.0-or-later.html
 * @link		https://kalkun.sourceforge.io/
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

	public $idiom = 'english';
	private $db_prop = [];
	private $db_engine = '';

	/**
	 * Constructor
	 *
	 * @access	public
	 */
	function __construct()
	{
		parent::__construct();

		// language
		$this->load->helper('i18n');
		$i18n = new MY_Lang();
		if ($this->input->post('idiom') !== NULL)
		{
			$this->idiom = $this->input->post('idiom');
		}
		else
		{
			$this->idiom = $i18n->get_idiom();
		}
		$this->lang->load('kalkun', $this->idiom);

		if ( ! file_exists(FCPATH.'install'))
		{
			show_error(
				nl2br(tr_raw(
					"Installation has been disabled by the administrator.\nTo enable access to it, create a file named {0} in this directory of the server: {1}.\nOtherwise you may log-in at {2}.",
					NULL,
					'<strong>install</strong>',
					'<strong>'.realpath(FCPATH).'</strong>',
					'<a href="'.$this->config->item('base_url').'">'.$this->config->item('base_url').'</a>'
				)),
				403,
				tr('403 Forbidden')
			);
		}

		require_once(APPPATH.'config/database.php');
		$this->db_config = $db[$active_group];

		$this->load->helper('kalkun');
		$this->db_prop = get_database_property($this->db_config['dbdriver']);
		$this->db_engine = $this->db_prop['file'];
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
		$data['idiom'] = $this->idiom;
		$data['language_list'] = $this->lang->kalkun_supported_languages();
		$this->load->helper('form');
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
		$this->load->helper(array('form'));

		$data['main'] = 'main/install/requirement_check';
		$data['idiom'] = $this->idiom;
		$data['database_driver'] = $this->db_config['dbdriver'];
		$data['db_property'] = $this->db_prop;
		$data['sess_save_path'] = $this->config->item('sess_save_path');
		if (is_null($data['sess_save_path']))
		{
			$data['sess_save_path'] = session_save_path();
		}
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
		$this->load->helper(array('form'));
		$this->load->database();
		$data['main'] = 'main/install/database_setup';
		$data['idiom'] = $this->idiom;
		$data['database_driver'] = $this->db->platform();
		$data['db_property'] = $this->db_prop;
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
	function run_install()
	{
		$this->load->helper(array('form'));
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
		$data['idiom'] = $this->idiom;
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
			$error = $this->_execute_kalkun_sql_file('upgrade_kalkun_0.7.sql');
			if ($error !== 0)
			{
				return $error;
			}
		}

		// Update SQL schema to version 0.8
		if ( ! $this->Kalkun_model->has_table_ci_sessions())
		{
			$error = $this->_execute_kalkun_sql_file('upgrade_kalkun_0.8.sql');
			if ($error !== 0)
			{
				return $error;
			}
		}

		// Update b8 table from v2 (of b8 0.5) to v3 schema (of b8 0.7)
		$b8_db_version = NULL;
		if ($this->db->field_exists('count', 'b8_wordlist'))
		{
			$this->db->from('b8_wordlist');
			$this->db->where('token', 'bayes*dbversion');
			$b8_db_version = $this->db->get()->row()->count;
		}
		if ($b8_db_version === '2')
		{
			// Rename old table to b8_wordlist_v2
			if ($this->db->query('ALTER TABLE b8_wordlist RENAME TO b8_wordlist_v2'))
			{
				// Create v3 table
				$this->_execute_kalkun_sql_file('b8_v3.sql');

				// Fill v3 table with values from v2 table
				$this->db->trans_start();

				// 1. Inserting internal variables
				$texts_ham_count = $this->db->query("SELECT count FROM b8_wordlist_v2 WHERE token='bayes*texts.ham'")->row()->count;
				$texts_spam_count = $this->db->query("SELECT count FROM b8_wordlist_v2 WHERE token='bayes*texts.spam'")->row()->count;

				$data = array(
					'token' => 'b8*texts',
					'count_ham' => $texts_ham_count,
					'count_spam' => $texts_spam_count
				);
				$this->db->insert('b8_wordlist', $data);

				// 2. Processing all tokens
				$this->db->from('b8_wordlist_v2');
				$this->db->where('token !=', 'bayes*dbversion');
				$this->db->where('token !=', 'bayes*texts.ham');
				$this->db->where('token !=', 'bayes*texts.spam');
				$query = $this->db->get();

				foreach ($query->result() as $row)
				{
					$parts = explode(' ', $row->count);
					$ham = $parts[0];
					$spam = $parts[1];

					$data = array(
						'token' => $row->token,
						'count_ham' => $ham,
						'count_spam' => $spam
					);
					$this->db->insert('b8_wordlist', $data);
				}

				$this->db->trans_complete();
			}
			else
			{
				return 500; // 500 = error code for failure to rename b8_wordlist
			}
		}

		// Add here equivalent code as above for the future upgrades

		return $error;
	}

	function _install()
	{
		return $this->_execute_kalkun_sql_file('kalkun.sql');
	}

	function _install_pbk_tables()
	{
		return $this->_execute_kalkun_sql_file('pbk_gammu.sql');
	}

	function _add_kalkun_fields_to_pbk_tables()
	{
		return $this->_execute_kalkun_sql_file('pbk_kalkun.sql');
	}

	function _execute_kalkun_sql_file($sqlfile)
	{
		return execute_sql(APPPATH.'sql/'.$this->db_engine.'/'.$sqlfile);
	}
}
