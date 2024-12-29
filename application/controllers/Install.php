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

		require(APPPATH.'config/database.php');
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

		$data['main'] = 'main/install/database_setup';
		$data['idiom'] = $this->idiom;
		$data['db_property'] = $this->db_prop;

		// By default we consider Kalkun database schema is not installed
		$detected_db_version = '0';
		$data['type'] = 'install';

		$data['exception'] = NULL;
		try
		{
			$this->load->database();
		}
		catch (Exception $e)
		{
			$data['exception'] = $e->getMessage();
			$this->load->view('main/install/layout', $data);
			return;
		}

		$data['error'] = 0;

		if ($this->input->post('action') === 'run_db_setup')
		{
			$data['error'] = $this->_run_db_setup();
		}

		$data['database_driver'] = $this->db->platform();
		$data['has_smsd_database'] = $this->db->table_exists('gammu') ? TRUE : FALSE;
		$data['has_table_pbk'] = $this->Kalkun_model->has_table_pbk() ? TRUE : FALSE;
		$data['has_gammu_database'] = $this->db->table_exists('user') ? TRUE : FALSE;

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
			$detected_db_version = '0.8.0';
			$data['type'] = 'upgrade';
		}
		// plugins_table_has_status_column appeared in 0.8.3
		if ($this->Kalkun_model->plugins_table_has_status_column())
		{
			$detected_db_version = '0.8.3';
			$data['type'] = 'up_to_date';
		}

		$data['detected_db_version'] = $detected_db_version;

		$this->load->view('main/install/layout', $data);
	}

	// --------------------------------------------------------------------

	/**
	 * Run configuration setup
	 *
	 * Check remaining configuration steps
	 *
	 * @access	public
	 */
	function config_setup()
	{
		$this->load->helper(array('form'));

		// install file
		if (file_exists(FCPATH.'install') && is_writable(dirname(FCPATH.'install')))
		{
			if ($this->input->post('remove_install_file') === 'remove')
			{
				$rm = unlink(FCPATH.'install');
			}
			$data['needs_manual_install_file_deletion'] = FALSE;
		}
		else
		{
			$data['needs_manual_install_file_deletion'] = TRUE;
		}
		$data['install_realpath'] = realpath(FCPATH.'install');


		// Daemon & Oubox queue scripts
		$data['is_windows'] = $this->_is_windows();
		$data['daemon_path'] = $this->_daemon_get_path('daemon');
		$data['daemon_path_is_executable'] = is_executable($data['daemon_path']);
		$data['daemon_php_path'] = $this->_daemon_get_var_path('daemon', 'PHP');
		$data['daemon_php_path_exists'] = $this->_daemon_var_path_exists($data['daemon_php_path']);
		$data['daemon_daemon_path'] = $this->_daemon_get_var_path('daemon', 'DAEMON');
		$data['daemon_daemon_path_exists'] = $this->_daemon_var_path_exists($data['daemon_daemon_path']);
		$data['daemon_url'] = $this->_daemon_get_url($data['daemon_daemon_path']);
		$data['daemon_url_matches_config'] = (trim($data['daemon_url'], '/') === trim($this->config->item('base_url'), '/')) ? TRUE : FALSE;
		$data['outbox_queue_path'] = $this->_daemon_get_path('outbox_queue');
		$data['outbox_queue_path_is_executable'] = is_executable($data['outbox_queue_path']);
		$data['outbox_queue_php_path'] = $this->_daemon_get_var_path('outbox_queue', 'PHP');
		$data['outbox_queue_php_path_exists'] = $this->_daemon_var_path_exists($data['outbox_queue_php_path']);
		$data['outbox_queue_daemon_path'] = $this->_daemon_get_var_path('outbox_queue', 'DAEMON');
		$data['outbox_queue_daemon_path_exists'] = $this->_daemon_var_path_exists($data['outbox_queue_daemon_path']);
		$data['outbox_queue_url'] = $this->_daemon_get_url($data['outbox_queue_daemon_path']);
		$data['outbox_queue_url_matches_config'] = (trim($data['outbox_queue_url'], '/') === trim($this->config->item('base_url'), '/')) ? TRUE : FALSE;

		// Gammu-smsd

		// Kalkun
		$data['config_gammu_path'] = $this->config->item('gammu_path');
		$data['config_gammu_sms_inject'] = $this->config->item('gammu_sms_inject');
		$data['config_gammu_config'] = $this->config->item('gammu_config');

		// encryption key
		$data['uses_default_encryption_key'] = $this->_uses_default_encryption_key();

		// htaccess for CI_ENV
		$data['htaccess_location'] = $this->_htaccess_CI_ENV_path();
		$data['CI_ENV'] = $this->_get_CI_ENV();

		$data['main'] = 'main/install/config_setup';
		$data['idiom'] = $this->idiom;
		$this->load->view('main/install/layout', $data);
	}

	// --------------------------------------------------------------------

	/**
	 * Run DB setup
	 *
	 * Install/update tables that are specific to Kalkun.
	 *
	 * @access	private
	 */
	function _run_db_setup()
	{
		$error = 0;

		// Check for phonebook tables
		// they have been dropped in Gammu (schema v16) but we need them for Phonebook feature
		if ( ! $this->Kalkun_model->has_table_pbk())
		{
			$error += $this->_install_pbk_tables();
		}

		// Add kalkun's specific fields to pbk table.
		if ( ! $this->Kalkun_model->has_table_pbk_with_kalkun_fields())
		{
			$error += $this->_add_kalkun_fields_to_pbk_tables();
		}


		if ( ! $this->db->table_exists('user'))
		{
			// Install
			$error += $this->_install('');
		}
		else
		{
			// Upgrade
			$error += $this->_upgrade();
		}

		// Set current version of kalkun in database
		$ret = $this->db->empty_table('kalkun');
		$this->db->insert('kalkun', array('version' => $this->config->item('kalkun_version')));

		// Clear data_cache, otherwise, the list of tables in CI3 would not be up to date.
		// for example when checking later on if the pbk table exists.
		$this->db->data_cache = array();

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
		$this->load->model('Kalkun_model');
		$this->load->dbforge();
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
			if ($this->dbforge->rename_table('b8_wordlist', 'b8_wordlist_v2'))
			{
				// Create v3 table
				$this->_execute_kalkun_sql_file('b8_v3.sql');

				// Fill v3 table with values from v2 table
				$this->db->trans_start();

				// 1. Inserting internal variables
				$this->db->select('count');
				$this->db->where('token', 'bayes*texts.ham');
				$texts_ham_count = $this->db->get('b8_wordlist_v2')->row()->count;

				$this->db->select('count');
				$this->db->where('token', 'bayes*texts.spam');
				$texts_spam_count = $this->db->get('b8_wordlist_v2')->row()->count;

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

		// Update SQL schema to version 0.8.3
		if ( ! $this->Kalkun_model->plugins_table_has_status_column())
		{
			$error = $this->_execute_kalkun_sql_file('upgrade_kalkun_0.8.3.sql');
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

	function _uses_default_encryption_key()
	{
		$enc_key = $this->config->item('encryption_key');

		if ($enc_key === hex2bin('F0af18413d1c9e03A6d8d1273160f5Ed'))
		{
			return TRUE;
		}

		if ($enc_key === 'F0af18413d1c9e03A6d8d1273160f5Ed')
		{
			return TRUE;
		}

		return FALSE;
	}

	function _get_CI_ENV()
	{
		return isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : '';
	}

	function _htaccess_CI_ENV_path()
	{
		$htaccess_paths = [
			FCPATH.'.htaccess',
			'/etc/apache2/conf-available/kalkun.conf',
			'/etc/apache2/apache2.conf'
		];

		foreach ($htaccess_paths as $path)
		{
			if ( ! is_readable($path))
			{
				continue;
			}

			$contents = file($path);

			if ($contents === FALSE)
			{
				continue;
			}

			$CI_ENV = $this->_get_CI_ENV();
			if ($CI_ENV === '')
			{
				$pattern = '^\s*((?i)SetEnv)\s+CI_ENV$';
			}
			else
			{
				$pattern = '^\s*((?i)SetEnv)\s+CI_ENV\s+'.$CI_ENV.'\s*$';
			}
			$matches = preg_grep('/'.$pattern.'/', $contents);

			if ($matches === FALSE || count($matches) === 0)
			{
				continue;
			}

			return realpath($path);
		}

		return '';
	}

	function _daemon_get_path($file)
	{
		if ($this->_is_windows())
		{
			$extension = '.bat';
		}
		else
		{
			$extension = '.sh';
		}

		$daemon_path = [
			FCPATH.'scripts/'.$file.$extension,
			FCPATH.'../scripts/'.$file.$extension
		];

		foreach ($daemon_path as $path)
		{
			if ( ! is_readable($path))
			{
				continue;
			}
			return realpath($path);
		}

		return FALSE;
	}

	function _daemon_get_var_path($file, $var)
	{
		$path = $this->_daemon_get_path($file);

		if ( ! $path)
		{
			return FALSE;
		}

		if ( ! is_readable($path))
		{
			return FALSE;
		}

		$contents = file_get_contents($path);

		if ($contents === FALSE)
		{
			return FALSE;
		}

		$pattern = '/\b'.$var.'=(.*)(?:\s#.*)?$/mU';
		$ret = preg_match($pattern, $contents, $matches);

		if ($ret === FALSE || count($matches) === 0)
		{
			return FALSE;
		}

		return trim(trim($matches[1]), '"');
	}


	function _daemon_var_path_exists($path)
	{
		if ($path === FALSE)
		{
			return FALSE;
		}

		if (is_readable($path))
		{
			return TRUE;
		}

		return FALSE;
	}

	function _daemon_get_url($path)
	{
		if ( ! $path)
		{
			return FALSE;
		}

		if ( ! is_readable($path))
		{
			return FALSE;
		}

		$contents = file_get_contents($path);

		if ($contents === FALSE)
		{
			return FALSE;
		}

		$pattern = '/^\s*\$url\s*=\s*[\'"](.*)[\'"].*$/mU';
		$ret = preg_match($pattern, $contents, $matches);

		if ($ret === FALSE || count($matches) === 0)
		{
			return FALSE;
		}

		return trim(trim($matches[1]), '"');
	}

	function _is_windows()
	{
		return strcasecmp(substr(PHP_OS, 0, 3), 'WIN') === 0;
	}
}
