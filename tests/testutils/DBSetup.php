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

require_once __DIR__.'/ConfigFile.php';
require_once __DIR__.'/DBVars.php';

class DBSetup {

	private $db_name;
	private $user;
	private $password;
	private $engine;
	private $configFile;
	private $pwdFile;
	public static $db_engines_to_test = [
		'PostgreSQL' => ['pgsql'],
		'MySQL' => ['mysql'],
		'SQLite3' => ['sqlite']
	];
	private $records = [];

	public function __construct($params)
	{
		$this->engine = array_key_exists('engine', $params) ? $params['engine'] : NULL;
		$this->db_name = array_key_exists('database', $params) ? $params['database'] : DBVars::DATABASE;
		$this->user = array_key_exists('user', $params) ? $params['user'] : DBVars::USERNAME;
		$this->password = array_key_exists('password', $params) ? $params['password'] : DBVars::PASSWORD;

		switch ($this->get_engine())
		{
			case 'mysql':
				$this->pwdFile = new ConfigFile(APPPATH . 'config/testing/mysql.cnf');
				$this->pwdFile->write('[client]
password=' . $this->password);
				break;
			case 'pgsql':
			case 'sqlite':
			default:
				break;
		}
	}

	public function get_db_path()
	{
		$dir = sys_get_temp_dir().'/'; // With sqlite3, there are issues if the file is put in a subdir of /tmp/
		if ( ! file_exists($dir))
		{
			mkdir ($dir);
		}
		return $dir.$this->db_name.'.sqlite3';
	}

	public function get_db_name()
	{
		return $this->db_name;
	}

	public function get_engine()
	{
		return $this->engine;
	}

	public function get_password()
	{
		return $this->password;
	}

	public function get_user()
	{
		return $this->user;
	}

	private function create_db()
	{
		switch ($this->engine)
		{
			case 'pgsql':
				shell_exec('PGPASSWORD=' . escapeshellarg($this->password)
						. ' createdb'
						. ' -h localhost'
						. ' -U ' . escapeshellarg($this->user)
						. ' -O ' . escapeshellarg($this->user)
						. ' ' . escapeshellarg($this->db_name));
				break;
			case 'mysql':
				shell_exec(
					'mysql'
						. ' --defaults-extra-file=' . APPPATH . 'config/testing/mysql.cnf'
						. ' -u ' . escapeshellarg($this->user)
						//. ' --password=' . escapeshellarg($this->password)
						. ' --execute="create database if not exists ' . $this->db_name . '"'
						. ' --host=localhost'
				);
				break;
			case 'sqlite':
				shell_exec('sqlite3 '.$this->get_db_path().' "VACUUM;"');
				break;
			default:
				//$this->markTestIncomplete();
				break;
		}
	}

	private function drop_db()
	{
		switch ($this->engine)
		{
			case 'pgsql':
				$output = NULL;
				$retval = NULL;
				for ($i = 0; $i < 2; $i++)
				{
					$ret = putenv('PGPASSWORD=' . $this->password);
					if ($ret === FALSE)
					{
						throw new Exception('Could not set environment variable PGPASSWORD');
					}
					putenv('LC_ALL=C');
					exec(
						'dropdb'
							. ' -h localhost'
							. ' -U ' . escapeshellarg($this->user)
							. ' --force'
							. ' --if-exists'
							. ' ' . escapeshellarg($this->db_name),
						$output,
						$retval
					);
					if ($retval === 0)
					{
						break;
					}
					usleep(500000);
				}
				if ($retval !== 0)
				{
					throw new Exception('Could not drop database. (tried ' . ($i + 1) . ' times). Output: ' . implode("\n", $output));
				}
				break;
			case 'mysql':
				shell_exec(
					'mysql'
						. ' --defaults-extra-file=' . APPPATH . 'config/testing/mysql.cnf'
						. ' -u ' . escapeshellarg($this->user)
						//. ' --password=' . escapeshellarg($this->password)
						. ' --execute="DROP DATABASE IF EXISTS ' . $this->db_name . '"'
						. ' --host=localhost'
				);
				break;
			case 'sqlite':
				if (file_exists($this->get_db_path()))
				{
					unlink($this->get_db_path());
				}
				break;
			default:
				//$this->markTestIncomplete();
				break;
		}
	}

	private function run_sql_script($script_path)
	{
		switch ($this->engine)
		{
			case 'pgsql':
				shell_exec(
					'PGPASSWORD=' . $this->password
						. ' psql'
						. ' -h localhost'
						. ' -f ' . escapeshellarg($script_path)
						. ' ' . escapeshellarg($this->db_name)
						. ' ' . escapeshellarg($this->user)
						. ' 2>&1'
				);
				break;
			case 'mysql':
				shell_exec(
					'mysql'
						. ' --defaults-extra-file=' . APPPATH . 'config/testing/mysql.cnf'
						. ' -u ' . escapeshellarg($this->user)
						//. ' --password=' . escapeshellarg($this->password)
						. ' --host=localhost'
						. ' ' . escapeshellarg($this->db_name)
						. ' < ' . escapeshellarg($script_path)
				);
				break;
			case 'sqlite':
				shell_exec('sqlite3 ' . $this->get_db_path() . ' < ' . escapeshellarg($script_path));
				break;
			default:
				$this->markTestIncomplete();
				break;
		}
	}

	public function setup_config($config)
	{
		switch ($config)
		{
			case 'gammu_pbk_kalkun_fresh_install_manual_sql_injection':
				$script_paths[] = TESTPATH . 'testutils/sql-gammu-1.37.4/' . $this->engine . '.sql';
				$script_paths[] = APPPATH . 'sql/' . $this->engine . '/' . 'pbk_kalkun.sql';
				$script_paths[] = APPPATH . 'sql/' . $this->engine . '/' . 'kalkun.sql';
				break;
			case 'gammu_pbk_kalkun_fresh_install_by_installer':
				$script_paths[] = TESTPATH . 'testutils/sql-gammu-1.37.4/' . $this->engine . '.sql';
				break;
			case 'gammu_pbk_kalkun_upgrade_from_0.6':
				$script_paths[] = TESTPATH . 'testutils/sql-gammu-1.37.4/' . $this->engine . '.sql';
				$script_paths[] = TESTPATH . 'testutils/sql-kalkun-v0.6/' . $this->engine . '_kalkun.sql';
				break;
			case 'gammu_pbk_kalkun_upgrade_from_0.7':
				$script_paths[] = TESTPATH . 'testutils/sql-gammu-1.37.4/' . $this->engine . '.sql';
				$script_paths[] = TESTPATH . 'testutils/sql-kalkun-v0.7/' . $this->engine . '_kalkun.sql';
				break;
			case 'gammu_pbk_kalkun_upgrade_from_0.8.0':
				$script_paths[] = TESTPATH . 'testutils/sql-gammu-1.37.4/' . $this->engine . '.sql';
				$script_paths[] = TESTPATH . 'testutils/sql-kalkun-v0.8.0/' . $this->engine . '/kalkun.sql';
				break;
			case 'gammu_pbk_kalkun_upgrade_from_0.8.3':
				$script_paths[] = TESTPATH . 'testutils/sql-gammu-1.37.4/' . $this->engine . '.sql';
				$script_paths[] = TESTPATH . 'testutils/sql-kalkun-v0.8.3/' . $this->engine . '/kalkun.sql';
				break;
			case 'gammu_no_pbk_kalkun_fresh_install_manual_sql_injection':
				$script_paths[] = TESTPATH . 'testutils/sql-gammu-1.42.0/' . $this->engine . '.sql';
				$script_paths[] = APPPATH . 'sql/' . $this->engine . '/' . 'pbk_gammu.sql';
				$script_paths[] = APPPATH . 'sql/' . $this->engine . '/' . 'pbk_kalkun.sql';
				$script_paths[] = APPPATH . 'sql/' . $this->engine . '/' . 'kalkun.sql';
				break;
			case 'gammu_no_pbk_kalkun_fresh_install_by_installer':
				$script_paths[] = TESTPATH . 'testutils/sql-gammu-1.42.0/' . $this->engine . '.sql';
				break;
			// case 'gammu_no_pbk_kalkun_upgrade_from_0.6': // Irrelevant because at the time of kalkun 0.6, gammu provided the pbk table
			// 	break;
			// case 'gammu_no_pbk_kalkun_upgrade_from_0.7': // Irrelevant because at the time of kalkun 0.7, gammu provided the pbk table
			// 	break;
			case 'gammu_no_pbk_kalkun_upgrade_from_0.8.0':
				$script_paths[] = TESTPATH . 'testutils/sql-gammu-1.42.0/' . $this->engine . '.sql';
				$script_paths[] = TESTPATH . 'testutils/sql-kalkun-v0.8.0/' . $this->engine . '/kalkun.sql';
				break;
			case 'gammu_no_pbk_kalkun_upgrade_from_0.8.3':
				$script_paths[] = TESTPATH . 'testutils/sql-gammu-1.42.0/' . $this->engine . '.sql';
				$script_paths[] = TESTPATH . 'testutils/sql-kalkun-v0.8.3/' . $this->engine . '/kalkun.sql';
				break;
			default:
				die;
				break;
		}

		$this->drop_db();
		$this->create_db();
		foreach ($script_paths as $script_path)
		{
			$this->run_sql_script($script_path);
		}
		$this->write_config_file_for_database();
	}

	public function write_config_file_for_database()
	{
		// Create file config/testing/database.php
		switch ($this->get_engine())
		{
			case 'pgsql':
				$content = "<?php
	\$active_group = 'kalkun_postgresql';
	\$db['kalkun_postgresql'] = array(
	'dsn'	=> '',
	'hostname' => 'localhost',
	'username' => '" . $this->get_user() . "',
	'password' => '" . $this->get_password() . "',
	'database' => '" . $this->get_db_name() . "',
	'dbdriver' => 'postgre',
	'dbprefix' => '',
	'pconnect' => FALSE,
	'db_debug' => (ENVIRONMENT !== 'production'),
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8',
	'dbcollat' => '',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => TRUE
);";
				break;
			case 'mysql':
				$content = "<?php
	\$active_group = 'kalkun_mysql';
	\$db['kalkun_mysql'] = array(
	'dsn'	=> '',
	'hostname' => '127.0.0.1',
	'username' => '" . $this->get_user() . "',
	'password' => '" . $this->get_password() . "',
	'database' => '" . $this->get_db_name() . "',
	'dbdriver' => 'mysqli',
	'dbprefix' => '',
	'pconnect' => FALSE,
	'db_debug' => (ENVIRONMENT !== 'production'),
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8mb4',
	'dbcollat' => 'utf8mb4_general_ci',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => TRUE
);";
				break;
			case 'sqlite':
				$content = "<?php
	\$active_group = 'kalkun_sqlite3';
	\$db['kalkun_sqlite3'] = array(
	'dsn'	=> '',
	'hostname' => '',
	'username' => '',
	'password' => '',
	'database' => '" . $this->get_db_path() . "',
	'dbdriver' => 'sqlite3',
	'dbprefix' => '',
	'pconnect' => FALSE,
	'db_debug' => (ENVIRONMENT !== 'production'),
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8',
	'dbcollat' => '',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => TRUE
);";
				break;
			default:
				break;
		}

		$this->configFile = new ConfigFile(APPPATH . 'config/testing/database.php');
		$this->configFile->write($content);
	}

	public static function prepend_db_engine($array = NULL)
	{
		if ( ! isset($array))
		{
			return DBSetup::$db_engines_to_test;
		}

		$result = [];

		foreach (DBSetup::$db_engines_to_test as $db_engine_label => $db_engine)
		{
			foreach ($array as $key => $value)
			{
				if (is_array($value))
				{
					$result[$key . ' (' . $db_engine_label . ')'] = array_merge($db_engine, $value);
				}
				else
				{
					$result[$key . ' (' . $db_engine_label . ')'] = array_merge($db_engine, [$value]);
				}
			}
		}
		return $result;
	}

	public function fill_data($columns, $input, $defaults)
	{
		$data = [];
		foreach ($columns as $key)
		{
			if (array_key_exists($key, $input))
			{
				$data[$key] = $input[$key];
			}
			else
			{
				if (array_key_exists($key, $defaults))
				{
					$data[$key] = $defaults[$key];
				}
			}
		}
		return $data;
	}

	public function get_insert_value($table, $field, $index)
	{
		$i = 0;
		foreach ($this->records as $record)
		{
			if ($record['table'] === $table)
			{
				if ($i === $index)
				{
					return $record['data'][$field];
				}
				$i++;
			}
		}
	}

	public function closure()
	{
		$records = $this->records;
		return function ($CI) use ($records) {
			foreach ($records as $record)
			{
				foreach ($record['data'] as $key => $value)
				{
					$CI->db->set($key, $value);
				}
				$CI->db->insert($record['table']);
			}
		};
	}

	public function execute($CI)
	{
		foreach ($this->records as $record)
		{
			foreach ($record['data'] as $key => $value)
			{
				$CI->db->set($key, $value);
			}
			$CI->db->insert($record['table']);
		}
	}

	public function insert($label, $input = [])
	{
		switch ($label)
		{
			case 'filter':
				$this->insert_filter($input);
				break;
			case 'inbox':
				$this->insert_inbox($input);
				break;
			case 'inbox_multipart':
				$this->insert_inbox_multipart_unicode_emojis($input);
				break;
			case 'outbox':
				$this->insert_outbox($input);
				break;
			case 'pbk':
				$this->insert_pbk($input);
				break;
			case 'pbk_groups':
				$this->insert_pbk_groups($input);
				break;
			case 'plugin_blacklist_number':
				$this->insert_plugin_blacklist_number($input);
				break;
			case 'plugin_remote_access':
				$this->insert_plugin_remote_access($input);
				break;
			case 'plugin_server_alert':
				$this->insert_plugin_server_alert($input);
				break;
			case 'plugin_sms_credit':
				$this->insert_plugin_sms_credit($input);
				break;
			case 'plugin_sms_credit_template':
				$this->insert_plugin_sms_credit_template($input);
				break;
			case 'plugin_sms_member':
				$this->insert_plugin_sms_member($input);
				break;
			case 'plugin_stop_manager':
				$this->insert_plugin_stop_manager($input);
				break;
			case 'sentitems':
				$this->insert_sentitems($input);
				break;
			case 'sentitems_multipart':
				$this->insert_sentitems_multipart($input);
				break;
			case 'sms_used':
				$this->insert_sms_used($input);
				break;
			case 'user':
				$this->insert_user($input);
				break;
			case 'user_folders':
				$this->insert_user_folders($input);
				break;
			case 'user_forgot_password':
				$this->insert_user_forgot_password($input);
				break;
			case 'user_group':
				$this->insert_user_group($input);
				break;
			case 'user_inbox':
				$this->insert_user_inbox($input);
				break;
			case 'user_outbox':
				$this->insert_user_outbox($input);
				break;
			case 'user_sentitems':
				$this->insert_user_sentitems($input);
				break;
			case 'user_templates':
				$this->insert_user_templates($input);
				break;
			default:
				throw new Exception('Unsupported insertion type: '.$label);
		}
		return $this;
	}

	protected function insert_filter($input = [])
	{
		$table = 'user_filters';
		$columns = [
			'from',
			'has_the_words',
			'id_folder',
			'id_user',
		];
		$defaults = [
			'from' => '+33600000000',
			'has_the_words' => 'message',
			'id_folder' => 11,
			'id_user' => 1,
		];

		$this->records[] = [
			'table' => $table,
			'data' => $this->fill_data($columns, $input, $defaults),
		];
	}

	protected function insert_inbox($input = [])
	{
		$table = 'inbox';
		$columns = [
			'UpdatedInDB',
			'ReceivingDateTime',
			'Text',
			'SenderNumber',
			'Coding',
			'UDH',
			'SMSCNumber',
			'Class',
			'TextDecoded',
			//'ID',
			'RecipientID',
			'Processed',
			'Status',
			'id_folder',
			'readed',
		];
		$defaults = [
			//'UpdatedInDB' => '2025-01-09 09:14:06',
			'ReceivingDateTime' => date('Y-m-d').' 03:00:00',
			'Text' => '00760065007200790020006C006F006E00670020006D006500730073006100670065',
			'SenderNumber' => '+33600000000',
			'Coding' => 'Default_No_Compression',
			'UDH' => '',
			'SMSCNumber' => '',
			'Class' => -1,
			'TextDecoded' => 'very long message',
			//'ID' => 1, // Don't set otherwise postgresql, on further insertion without setting ID will complain. See: https://stackoverflow.com/a/24393132
			'RecipientID' => '',
			'Processed' => 'false',
			'Status' => 0,
			//'id_folder' => 1,
			//'readed' => 'false',
		];

		$this->records[] = [
			'table' => $table,
			'data' => $this->fill_data($columns, $input, $defaults),
		];
	}

	protected function insert_inbox_multipart($input = [])
	{
		// Output text is:
		// Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor. Cras elementum ultrices diam. Maecenas ligula massa, varius a, semper congue, euismod non, mi. Proin porttitor, orci nec nonummy molestie, enim est eleifend mi, non fermentum diam nisl sit amet erat. Duis semper. Duis arcu massa, scelerisque vitae, consequat in, pretium a, enim.
		// In the inbox database, the full decoded text is stored in the 1st message only. TextDecoded is empty in the other messages.

		$ID = array_key_exists('ID', $input) ? $input['ID'] : 1;

		$this->insert_inbox(array_merge($input, [
			'Text' => '004C006F00720065006D00200069007000730075006D00200064006F006C006F0072002000730069007400200061006D00650074002C00200063006F006E00730065006300740065007400750072002000610064006900700069007300630069006E006700200065006C00690074002E00200053006500640020006E006F006E002000720069007300750073002E002000530075007300700065006E006400690073007300650020006C0065006300740075007300200074006F00720074006F0072002C0020006400690067006E0069007300730069006D002000730069007400200061006D00650074002C002000610064006900700069007300630069006E00670020006E00650063002C00200075006C00740072006900630069006500730020007300650064002C00200064006F006C',
			'UDH' => '0500037C0301',
			'TextDecoded' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor. Cras elementum ultrices diam. Maecenas ligula massa, varius a, semper congue, euismod non, mi. Proin porttitor, orci nec nonummy molestie, enim est eleifend mi, non fermentum diam nisl sit amet erat. Duis semper. Duis arcu massa, scelerisque vitae, consequat in, pretium a, enim.',
			//'ID' => $ID, // Don't set otherwise postgresql, on further insertion without setting ID will complain. See: https://stackoverflow.com/a/24393132
		]));

		$this->insert_inbox(array_merge($input, [
			'Text' => '006F0072002E0020004300720061007300200065006C0065006D0065006E00740075006D00200075006C0074007200690063006500730020006400690061006D002E0020004D0061006500630065006E006100730020006C006900670075006C00610020006D0061007300730061002C002000760061007200690075007300200061002C002000730065006D00700065007200200063006F006E006700750065002C00200065007500690073006D006F00640020006E006F006E002C0020006D0069002E002000500072006F0069006E00200070006F00720074007400690074006F0072002C0020006F0072006300690020006E006500630020006E006F006E0075006D006D00790020006D006F006C00650073007400690065002C00200065006E0069006D002000650073007400200065',
			'UDH' => '0500037C0302',
			'TextDecoded' => '',
			//'ID' => ++$ID, // Don't set otherwise postgresql, on further insertion without setting ID will complain. See: https://stackoverflow.com/a/24393132
		]));

		$this->insert_inbox(array_merge($input, [
			'Text' => '006C0065006900660065006E00640020006D0069002C0020006E006F006E0020006600650072006D0065006E00740075006D0020006400690061006D0020006E00690073006C002000730069007400200061006D0065007400200065007200610074002E00200044007500690073002000730065006D007000650072002E00200044007500690073002000610072006300750020006D0061007300730061002C0020007300630065006C0065007200690073007100750065002000760069007400610065002C00200063006F006E00730065007100750061007400200069006E002C0020007000720065007400690075006D00200061002C00200065006E0069006D002E',
			'UDH' => '0500037C0303',
			'TextDecoded' => '',
			//'ID' => ++$ID, // Don't set otherwise postgresql, on further insertion without setting ID will complain. See: https://stackoverflow.com/a/24393132
		]));
	}

	protected function insert_inbox_multipart_unicode_emojis($input = [])
	{
		// Output text is:
		// 👍🏿 ✌🏿️ @mention 16/ Emjis 😁 💾 😅 💾 🤣 💾 😂 💾 🙂 💾. Lorem 👍🏿ipsum dolor sit ame💾 😂 sum dolor sitsum dolor sitsum dolor sitsum dolor sitm dolor sitsum dolor sitsum dol m dolor sitsum dolor sitsum dol😁 💾 😅 💾. Stop
		// In the inbox database, the full decoded text is stored in the 1st message only. TextDecoded is empty in the other messages.

		$ID = array_key_exists('ID', $input) ? $input['ID'] : 1;

		$this->insert_inbox(array_merge($input, [
			'Text' => 'D83DDC4DD83CDFFF0020270CD83CDFFFFE0F00200040006D0065006E00740069006F006E002000310036002F00200045006D006A006900730020D83DDE010020D83DDCBE0020D83DDE050020D83DDCBE0020D83EDD230020D83DDCBE0020D83DDE020020D83DDCBE0020D83DDE420020D83DDCBE002E0020004C006F00720065006D0020',
			'UDH' => '050003F60401',
			'TextDecoded' => '👍🏿 ✌🏿️ @mention 16/ Emjis 😁 💾 😅 💾 🤣 💾 😂 💾 🙂 💾. Lorem 👍🏿ipsum dolor sit ame💾 😂 sum dolor sitsum dolor sitsum dolor sitsum dolor sitm dolor sitsum dolor sitsum dol m dolor sitsum dolor sitsum dol😁 💾 😅 💾. Stop',
			'Coding' => 'Unicode_No_Compression',
			//'ID' => $ID, // Don't set otherwise postgresql, on further insertion without setting ID will complain. See: https://stackoverflow.com/a/24393132
		]));

		$this->insert_inbox(array_merge($input, [
			'Text' => 'D83DDC4DD83CDFFF0069007000730075006D00200064006F006C006F0072002000730069007400200061006D0065D83DDCBE0020D83DDE02002000730075006D00200064006F006C006F0072002000730069007400730075006D00200064006F006C006F0072002000730069007400730075006D00200064006F006C006F0072002000730069',
			'UDH' => '050003F60402',
			'TextDecoded' => '',
			'Coding' => 'Unicode_No_Compression',
			//'ID' => ++$ID, // Don't set otherwise postgresql, on further insertion without setting ID will complain. See: https://stackoverflow.com/a/24393132
		]));

		$this->insert_inbox(array_merge($input, [
			'Text' => '007400730075006D00200064006F006C006F00720020007300690074006D00200064006F006C006F0072002000730069007400730075006D00200064006F006C006F0072002000730069007400730075006D00200064006F006C0020006D00200064006F006C006F0072002000730069007400730075006D00200064006F006C006F00720020',
			'UDH' => '050003F60403',
			'TextDecoded' => '',
			'Coding' => 'Unicode_No_Compression',
			//'ID' => ++$ID, // Don't set otherwise postgresql, on further insertion without setting ID will complain. See: https://stackoverflow.com/a/24393132
		]));

		$this->insert_inbox(array_merge($input, [
			'Text' => '00730069007400730075006D00200064006F006CD83DDE010020D83DDCBE0020D83DDE050020D83DDCBE002E002000530074006F0070',
			'UDH' => '050003F60404',
			'TextDecoded' => '',
			'Coding' => 'Unicode_No_Compression',
			//'ID' => ++$ID, // Don't set otherwise postgresql, on further insertion without setting ID will complain. See: https://stackoverflow.com/a/24393132
		]));
	}

	protected function insert_outbox($input = [])
	{
		$table = 'outbox';
		$columns = [
			//'UpdatedInDB',
			//'InsertIntoDB',
			'SendingDateTime',
			'SendBefore',
			'SendAfter',
			'Text',
			'DestinationNumber',
			'Coding',
			'UDH',
			'Class',
			'TextDecoded',
			//'ID',
			'MultiPart',
			'RelativeValidity',
			'SenderID',
			//'SendingTimeOut',
			'DeliveryReport',
			'CreatorID',
			'Retries',
			'Priority',
			'Status',
			'StatusCode',
		];
		$defaults = [
			//'UpdatedInDB' => '2025-01-09 11:21:37',
			//'InsertIntoDB' => '2025-01-09 11:21:37',
			// We need to put a date a bit before now so that the message is
			// in the list of processed outbox messages
			'SendingDateTime' => date('Y-m-d H:i:s', strtotime('-5 minutes')),
			'SendBefore' => '23:59:59',
			'SendAfter' => '00:00:00',
			'Text' => NULL,
			'DestinationNumber' => '+33612345678',
			'Coding' => 'Default_No_Compression',
			'UDH' => NULL,
			'Class' => 1,
			'TextDecoded' => 'outbox message',
			//'ID' => 1, // Don't set otherwise postgresql, on further insertion without setting ID will complain. See: https://stackoverflow.com/a/24393132
			'MultiPart' => FALSE,
			'RelativeValidity' => -1,
			'SenderID' => NULL,
			//'SendingTimeOut' => '2025-01-09 11:21:37',
			'DeliveryReport' => 'default',
			'CreatorID' => 'Kalkun test suite',
			'Retries' => 0,
			'Priority' => 0,
			'Status' => 'Reserved',
			'StatusCode' => -1,
		];

		$this->records[] = [
			'table' => $table,
			'data' => $this->fill_data($columns, $input, $defaults),
		];
	}

	protected function insert_pbk($input = [])
	{
		// Add contact
		$table = 'pbk';
		$columns = [
			//'ID',
			'GroupID',
			'Name',
			'Number',
			'id_user',
			'is_public',
		];
		$defaults = [
			//'ID' => 1, // Don't set otherwise postgresql, on further insertion without setting ID will complain. See: https://stackoverflow.com/a/24393132
			'Name' => 'matching contact',
			'Number' => '+33622222222',
			'id_user' => 2,
			'is_public' => 'false',
		];

		$this->records[] = [
			'table' => $table,
			'data' => $this->fill_data($columns, $input, $defaults),
		];
	}

	protected function insert_pbk_groups($input = [])
	{
		// Add pbk group for 'user having id=2'
		$table = 'pbk_groups';
		$columns = [
			'Name',
			//'ID',
			'id_user',
			'is_public',
		];
		$defaults = [
			'Name' => 'pbk group for user having id=2 (user1)',
			//'ID' => 1, // Don't set otherwise postgresql, on further insertion without setting ID will complain. See: https://stackoverflow.com/a/24393132
			'id_user' => 2,
			'is_public' => 'false',
		];

		$this->records[] = [
			'table' => $table,
			'data' => $this->fill_data($columns, $input, $defaults),
		];
	}

	protected function insert_plugin_blacklist_number($input = [])
	{
		// Add plugin_blacklist_number
		$table = 'plugin_blacklist_number';
		$columns = [
			//'id_blacklist_number',
			'phone_number',
			'reason',
		];
		$defaults = [
			//'id_blacklist_number' => 1,	// Don't set otherwise postgresql, on further insertion without setting ID will complain. See: https://stackoverflow.com/a/24393132
			'phone_number' => '+33622222222',
			'reason' => 'reason',
		];

		$this->records[] = [
			'table' => $table,
			'data' => $this->fill_data($columns, $input, $defaults),
		];
	}

	protected function insert_plugin_remote_access($input = [])
	{
		$table = 'plugin_remote_access';
		$columns = [
			//'id_remote_access',
			'access_name',
			'ip_address',
			'token',
			'status',
		];
		$defaults = [
			//'id_remote_access' => 1,
			'access_name' => 'local',
			'ip_address' => '127.0.0.1',
			'token' => '87bbccd33a008694c25a49c0e03ed8bdca3ea6a5fb353e1e1beba866cc5f0614',
			'status' => 'true',
		];

		$this->records[] = [
			'table' => $table,
			'data' => $this->fill_data($columns, $input, $defaults),
		];
	}

	protected function insert_plugin_server_alert($input = [])
	{
		$table = 'plugin_server_alert';
		$columns = [
			'alert_name',
			'ip_address',
			'port_number',
			'timeout',
			'phone_number',
			'respond_message',
			'release_code',
		];
		$defaults = [
			'alert_name' => 'test_server_alert_localhost_85',
			'ip_address' => 'localhost',
			'port_number' => '85',
			'timeout' => '5',
			'phone_number' => '+123456',
			'respond_message' => 'message from "Server Alert"',
			'release_code' => '', // Not used for now (db requires NOT NULL)
		];

		$this->records[] = [
			'table' => $table,
			'data' => $this->fill_data($columns, $input, $defaults),
		];
	}

	protected function insert_plugin_sms_credit($input = [])
	{
		// Add plugin_sms_credit
		$table = 'plugin_sms_credit';
		$columns = [
			//'id_user_credit',
			'id_user',
			'id_template_credit',
			'valid_start',
			'valid_end',
		];
		$defaults = [
			//'id_user_credit' => 1, // Don't set otherwise postgresql, on further insertion without setting ID will complain. See: https://stackoverflow.com/a/24393132
			'id_user' => '2',
			'id_template_credit' => 1,
			'valid_start' => date('Y-m-d'),
			'valid_end' => date('Y-m-d', strtotime('+ 1 day')),
		];

		$this->records[] = [
			'table' => $table,
			'data' => $this->fill_data($columns, $input, $defaults),
		];
	}

	protected function insert_plugin_sms_credit_template($input = [])
	{
		// Add plugin_sms_credit_template
		$table = 'plugin_sms_credit_template';
		$columns = [
			//'id_credit_template',
			'template_name',
			'sms_numbers',
		];
		$defaults = [
			//'id_credit_template' => 1, // Don't set otherwise postgresql, on further insertion without setting ID will complain. See: https://stackoverflow.com/a/24393132
			'template_name' => 'Package 1',
			'sms_numbers' => 1,
		];

		$this->records[] = [
			'table' => $table,
			'data' => $this->fill_data($columns, $input, $defaults),
		];
	}

	protected function insert_plugin_sms_member($input = [])
	{
		// Add plugin_sms_member
		$table = 'plugin_sms_member';
		$columns = [
			//'id_member',
			'phone_number',
			'reg_date',
		];
		$defaults = [
			//'ID' => 1, // Don't set otherwise postgresql, on further insertion without setting ID will complain. See: https://stackoverflow.com/a/24393132
			'phone_number' => '+33622222222',
			'reg_date' => date('Y-m-d H:i:s'),
		];

		$this->records[] = [
			'table' => $table,
			'data' => $this->fill_data($columns, $input, $defaults),
		];
	}

	protected function insert_plugin_stop_manager($input = [])
	{
		// Add plugin_stop_manager
		$table = 'plugin_stop_manager';
		$columns = [
			//'id_stop_manager',
			'destination_number',
			'stop_type',
			'stop_message',
			'reg_date',
		];
		$defaults = [
			//'id_stop_manager' => 1, // Don't set otherwise postgresql, on further insertion without setting ID will complain. See: https://stackoverflow.com/a/24393132
			'destination_number' => '+33789789789',
			'stop_type' => '',
			'stop_message' => 'stop message received',
			'reg_date' => date('Y-m-d H:i:s'),
		];

		$this->records[] = [
			'table' => $table,
			'data' => $this->fill_data($columns, $input, $defaults),
		];
	}

	protected function insert_sentitems($input = [])
	{
		$table = 'sentitems';
		$columns = [
			//'UpdatedInDB',
			//'InsertIntoDB',
			'SendingDateTime',
			'DeliveryDateTime',
			'Text',
			'DestinationNumber',
			'Coding',
			'UDH',
			'SMSCNumber',
			'Class',
			'TextDecoded',
			'ID',
			'SenderID',
			'SequencePosition',
			'Status',
			'StatusError',
			'TPMR',
			'RelativeValidity',
			'CreatorID',
			'StatusCode',
			'id_folder',
		];
		$defaults = [
			//'UpdatedInDB' => '2025-01-09 11:21:37',
			//'InsertIntoDB' => '2025-01-09 11:21:37',
			// We need to put a date a bit before now so that the message is
			// in the list of processed outbox messages
			'SendingDateTime' => date('Y-m-d H:i:s', strtotime('-5 minutes')),
			'DeliveryDateTime' => date('Y-m-d H:i:s', strtotime('-5 minutes')),
			'Text' => '',
			'DestinationNumber' => '+33612345678',
			'Coding' => 'Default_No_Compression',
			'UDH' => '',
			'SMSCNumber' => '+33600000000',
			'Class' => -1,
			'TextDecoded' => 'sentitem message',
			'ID' => 1,  // This field is autoincrement in pgsql, but not mysql neither sqlite
			'SenderID' => '',
			'SequencePosition' => 1,
			'Status' => 'SendingOKNoReport',
			'StatusError' => -1,
			'TPMR' => 0,
			'RelativeValidity' => -1,
			'CreatorID' => 'Kalkun test suite',
			'StatusCode' => -1,
			'id_folder' => 3,
		];

		$this->records[] = [
			'table' => $table,
			'data' => $this->fill_data($columns, $input, $defaults),
		];
	}

	protected function insert_sentitems_multipart($input = [])
	{
		$ID = array_key_exists('ID', $input) ? $input['ID'] : 1;

		$this->insert_sentitems(array_merge($input, [
			'Text' => '006C006F006E00670020006D00650073007300610067006500200077006900740068002000670073006D00370020007300700065006300690061006C002000630068006100720073003A0020005E007B007D005B005D007E007C20AC005C0020004C006F00720065006D00200069007000730075006D00200064006F006C006F0072002000730069007400200061006D00650074002C00200063006F006E00730065006300740065007400750072002000610064006900700069007300630069006E006700200065006C00690074002E00200053006500640020006E006F006E002000720069007300750073002E002000530075007300700065006E006400690073007300650020006C0065006300740075007300200074006F00720074006F',
			'UDH' => '0500031A0301',
			'TextDecoded' => 'long message with gsm7 special chars: ^{}[]~|€\ Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus torto',
			//'ID' => $ID, // Don't set otherwise postgresql, on further insertion without setting ID will complain. See: https://stackoverflow.com/a/24393132
			'SequencePosition' => 1,
		]));

		$this->insert_sentitems(array_merge($input, [
			'Text' => '0072002C0020006400690067006E0069007300730069006D002000730069007400200061006D00650074002C002000610064006900700069007300630069006E00670020006E00650063002C00200075006C00740072006900630069006500730020007300650064002C00200064006F006C006F0072002E0020004300720061007300200065006C0065006D0065006E00740075006D00200075006C0074007200690063006500730020006400690061006D002E0020004D0061006500630065006E006100730020006C006900670075006C00610020006D0061007300730061002C002000760061007200690075007300200061002C002000730065006D00700065007200200063006F006E006700750065002C00200065007500690073006D006F00640020006E006F006E002C0020006D',
			'UDH' => '0500031A0302',
			'TextDecoded' => 'r, dignissim sit amet, adipiscing nec, ultricies sed, dolor. Cras elementum ultrices diam. Maecenas ligula massa, varius a, semper congue, euismod non, m',
			//'ID' => $ID, // Don't set otherwise postgresql, on further insertion without setting ID will complain. See: https://stackoverflow.com/a/24393132
			'SequencePosition' => 2,
		]));

		$this->insert_sentitems(array_merge($input, [
			'Text' => '0069002E',
			'UDH' => '0500031A0303',
			'TextDecoded' => 'i.',
			//'ID' => $ID, // Don't set otherwise postgresql, on further insertion without setting ID will complain. See: https://stackoverflow.com/a/24393132
			'SequencePosition' => 3,
		]));
	}

	protected function insert_sms_used($input = [])
	{
		$table = 'sms_used';
		$columns = [
			// 'id_sms_used',
			'sms_date',
			'id_user',
			'out_sms_count',
			'in_sms_count',
		];
		$defaults = [
			//'id_sms_used' => 1,
			'sms_date' => date('Y-m-d'),
			'id_user' => 2,
			'out_sms_count' => 10,
			'in_sms_count' => 15,
		];

		$this->records[] = [
			'table' => $table,
			'data' => $this->fill_data($columns, $input, $defaults),
		];
	}

	protected function insert_user($input = [])
	{
		// user
		$table = 'user';
		$columns = [
			//'id_user',
			'realname',
			'username',
			'phone_number',
			'level',
			'password',
		];
		$defaults = [
			//'id_user' => 2,
			'realname' => 'User number 1',
			'username' => 'user1',
			'phone_number' => '+33611111111',
			'level' => 'user',
			'password' => password_hash('password', PASSWORD_BCRYPT),
		];

		$this->records[] = [
			'table' => $table,
			'data' => $this->fill_data($columns, $input, $defaults),
		];

		// user_settings
		$table = 'user_settings';
		$columns = [
			'id_user',
			'theme',
			'signature',
			'permanent_delete',
			'paging',
			'bg_image',
			'delivery_report',
			'language',
			'conversation_sort',
			'country_code',
		];
		$defaults = [
			'id_user' => 2,
			'theme' => 'blue',
			'signature' => 'false;',
			'permanent_delete' => 'false',
			'paging' => '20',
			'bg_image' => 'true;background.jpg',
			'delivery_report' => 'default',
			'language' => 'english',
			'conversation_sort' => 'asc',
			'country_code' => 'US',
		];

		$this->records[] = [
			'table' => $table,
			'data' => $this->fill_data($columns, $input, $defaults),
		];
	}

	protected function insert_user_folders($input = [])
	{
		$table = 'user_folders';
		$columns = [
			//'id_folder',
			'name',
			'id_user',
		];
		$defaults = [
			//'id_folder' => 11,
			'name' => 'my_folder_1',
			'id_user' => '1',
		];

		$this->records[] = [
			'table' => $table,
			'data' => $this->fill_data($columns, $input, $defaults),
		];
	}

	protected function insert_user_forgot_password($input = [])
	{
		$table = 'user_forgot_password';
		$columns = [
			'id_user',
			'token',
			'valid_until',
		];
		$defaults = [
			'id_user' => 1,
			'token' => 'my_token',
			'valid_until' => date('Y-m-d H:i:s', mktime(date('H'), date('i') + 30, date('s'), date('m'), date('d'), date('Y'))),
		];

		$this->records[] = [
			'table' => $table,
			'data' => $this->fill_data($columns, $input, $defaults),
		];
	}

	protected function insert_user_group($input = [])
	{
		// Add user group for 'user having id=2'
		$table = 'user_group';
		$columns = [
			'id_group',
			'id_pbk',
			'id_pbk_groups',
			'id_user',
		];
		$defaults = [
			'id_group' => 1,
			'id_pbk' => 1,
			'id_pbk_groups' => 1,
			'id_user' => 2,
		];

		$this->records[] = [
			'table' => $table,
			'data' => $this->fill_data($columns, $input, $defaults),
		];
	}

	protected function insert_user_inbox($input = [])
	{
		$table = 'user_inbox';
		$columns = [
			'id_user',
			'id_inbox',
			'trash',
		];
		$defaults = [
			'id_user' => 1,
			'id_inbox' => 1,
			'trash' => 0,
		];

		$this->records[] = [
			'table' => $table,
			'data' => $this->fill_data($columns, $input, $defaults),
		];
	}

	protected function insert_user_outbox($input = [])
	{
		$table = 'user_outbox';
		$columns = [
			'id_user',
			'id_outbox',
		];
		$defaults = [
			'id_user' => 1,
			'id_outbox' => 1,
		];

		$this->records[] = [
			'table' => $table,
			'data' => $this->fill_data($columns, $input, $defaults),
		];
	}

	protected function insert_user_sentitems($input = [])
	{
		$table = 'user_sentitems';
		$columns = [
			'id_user',
			'id_sentitems',
			'trash',
		];
		$defaults = [
			'id_user' => 1,
			'id_sentitems' => 1,
			'trash' => 0,
		];

		$this->records[] = [
			'table' => $table,
			'data' => $this->fill_data($columns, $input, $defaults),
		];
	}

	protected function insert_user_templates($input = [])
	{
		$table = 'user_templates';
		$columns = [
			//'id_template',
			'id_user',
			'Name',
			'Message',
		];
		$defaults = [
			//'id_template' => 1,
			'id_user' => 1,
			'Name' => 'template label',
			'Message' => 'Content of the message template.',
		];

		$this->records[] = [
			'table' => $table,
			'data' => $this->fill_data($columns, $input, $defaults),
		];
	}
}
