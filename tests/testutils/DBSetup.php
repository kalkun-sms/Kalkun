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
require_once __DIR__.'/../controllers/Pluginss_test.php';

class DBSetup {

	const BASE_MESSAGE_DATE = '2024-02-29 00:00:00';
	private static $id_inbox_count = 0;
	private static $id_outbox_sentitems_count = 0; // ID is incremented in outbox, and in sentitems the ID is the same as the one set in outbox. So the counter is shared for both tables.
	public static $current_setup = []; // array of DBSetup

	const senders = 25;
	const messages_per_sender = 5;
	const nb_of_parts_per_inbox_message = 4;
	const recipients = 25;
	const messages_per_recipient = 5;
	const nb_of_parts_per_outbox_message = 3;

	public static $text_mono_unicode = '<(Mono)  👍🏿 ✌🏿️ XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX >';
	public static $text_mono_gsm = '<(Mono)  ^{}[]~|€\\ XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX >';
	public static $text_multi_unicode = '<(Multi) 👍🏿 ✌🏿️ XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX ><Part2 XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX ><Part3 XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX ><Part4 XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX >';
	public static $text_multi_gsm = '<(Multi) ^{}[]~|€\ XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX ><Part2 XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX ><Part3 XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX >';
	public static $sentitems_status = [
		'SendingOK', 'SendingOKNoReport', 'SendingError', 'DeliveryOK', 'DeliveryFailed', 'DeliveryPending', 'DeliveryUnknown', 'Error'
	];

	private $UDHprefixes = [];

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

		// Reset counter between each instance of DBSetup.
		self::$id_inbox_count = 0;
		self::$id_outbox_sentitems_count = 0;
		if (isset(self::$current_setup[$this->engine])
				&& self::$current_setup[$this->engine]->get_db_name() === 'kalkun_testing2'
				&& $this->get_db_name() === 'kalkun_testing2')
		{
			self::$current_setup[$this->engine] = NULL;
		}
	}

	public static function text_replace_placeholder($replacement, $text, $part_id = 0)
	{
		if (strlen($replacement) === 0)
		{
			return $text;
		}

		$text_parts = preg_split('/(?<=>)/', $text);
		array_pop($text_parts);

		$part_done = implode('', array_slice($text_parts, 0, $part_id));
		$current_part = $text_parts[$part_id];
		$placeholder_len = substr_count($current_part, 'X');
		$start_pos_of_X = strpos($current_part, ' X');
		if ($start_pos_of_X === FALSE)
		{
			// End of placeholder reached.
			return $text;
		}

		$start_pos_of_X += strlen($part_done) + 1;
		$replacement_for_part = substr($replacement, 0, $placeholder_len);
		$replacement_remaining = substr($replacement, $placeholder_len);

		$new_text = substr_replace($text, $replacement_for_part, $start_pos_of_X, strlen($replacement_for_part));
		return self::text_replace_placeholder($replacement_remaining, $new_text, $part_id + 1);
	}

	public static function get_text_multi_unicode()
	{
		return self::$text_multi_unicode;
	}

	public static function get_text_multi_gsm()
	{
		return self::$text_multi_gsm;
	}

	public static function get_multi_parts($text)
	{
		$text_multi_parts = preg_split('/(?<=>)/', $text);
		if ($text_multi_parts[count($text_multi_parts) - 1] === '')
		{
			array_pop($text_multi_parts);
		}
		return $text_multi_parts;
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

	public static function setup_db_kalkun_testing2($testcase)
	{
		foreach (DBSetup::$db_engines_to_test as $db_engine)
		{
			$engine = $db_engine[0];
			if (isset(DBSetup::$current_setup[$engine])
					&& DBSetup::$current_setup[$engine] instanceof DBSetup
					&& DBSetup::$current_setup[$engine]->get_db_name() === 'kalkun_testing2')
			{
				continue;
			}
			echo 'Filling DB "kalkun_testing2" for ' . $engine . "\n";

			DBSetup::$current_setup[$engine] = new DBSetup([
				'engine' => $engine,
				'database' => 'kalkun_testing2',
			]);
			DBSetup::$current_setup[$engine]->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

			DBSetup::$current_setup[$engine]->setup_db_content();

			$testcase->resetInstance();
			$reflection = new \ReflectionProperty('KalkunTestCase', 'CI');
			$reflection->setAccessible(TRUE);
			$CI = $reflection->getValue($testcase);
			$CI->load->database();
			DBSetup::$current_setup[$engine]->execute($CI);
		}
	}

	public function setup_db_content()
	{
		$users = 8;
		for ($i = 1; $i <= $users; $i++)
		{
			$this->insert('user', [
				'id_user' => $i + 1,
				'realname' => 'User number ' . $i,
				'username' => 'user' . $i,
				'phone_number' => '+336' . (100000000 - $i),
				'level' => 'user',
				'password' => password_hash('password', PASSWORD_BCRYPT),
			]);
		}

		// Messages for user 1 (single messages, processed)
		$this->setup_db_content_messages([
			'id_user' => 2,
			'is_processed' => TRUE,
			'is_multipart' => FALSE,
			'id_folder' => NULL,
			'sender_count' => NULL,
			'messages_per_sender' => NULL,
			'recipient_count' => NULL,
			'messages_per_recipient' => NULL,
		]);
		// ID messages offset: inbox: 125 outbox: 125 sentitems: 250

		$this->setup_db_content_messages([
			'id_user' => 2,
			'is_processed' => TRUE,
			'is_multipart' => FALSE,
			'id_folder' => 5,
			'sender_count' => NULL,
			'messages_per_sender' => NULL,
			'recipient_count' => NULL,
			'messages_per_recipient' => NULL,
		]);
		// ID messages offset: inbox: 250 outbox: 125 sentitems: 375

		// Add some contacts to phonebook
		$this->insert('pbk', [
			'Name' => 'Contact for number 001...',
			'Number' => '+33600000001',
		]);
		$this->insert('pbk', [
			'Name' => 'Contact for number 003...',
			'Number' => '+33600000003',
		]);

		// Messages for user 2 (single messages, not processed)
		$this->setup_db_content_messages([
			'id_user' => 3,
			'is_processed' => FALSE,
			'is_multipart' => FALSE,
			'id_folder' => NULL,
			'sender_count' => NULL,
			'messages_per_sender' => NULL,
			'recipient_count' => NULL,
			'messages_per_recipient' => NULL,
		]);
		// ID messages offset: inbox: 375 outbox: 500 sentitems: 625

		// Messages for user 3 (multipart messages, processed)
		$this->setup_db_content_messages([
			'id_user' => 4,
			'is_processed' => TRUE,
			'is_multipart' => TRUE,
			'id_folder' => NULL,
			'sender_count' => NULL,
			'messages_per_sender' => NULL,
			'recipient_count' => NULL,
			'messages_per_recipient' => NULL,
		]);
		// ID messages offset: 875 (+4*125) outbox: 750 sentitems: 875

		// Messages for user 4 (multipart messages, not processed)
		$this->setup_db_content_messages([
			'id_user' => 5,
			'is_processed' => FALSE,
			'is_multipart' => TRUE,
			'id_folder' => NULL,
			'sender_count' => NULL,
			'messages_per_sender' => NULL,
			'recipient_count' => NULL,
			'messages_per_recipient' => NULL,
		]);
		// ID messages offset: 1375 (+4*125) outbox: 1000 (+125) sentitems: 1125 (+125)

		$this->insert('user_folders', ['id_user' => 2, 'name' => 'user1, folder1 (id:11)']);
		$this->insert('user_folders', ['id_user' => 2, 'name' => 'user1, folder2 (id:12)']);
		// Messages for user 1 (single messages, processed, folder 11)
		$this->setup_db_content_messages([
			'id_user' => 2,
			'is_processed' => TRUE,
			'is_multipart' => FALSE,
			'id_folder' => 11,
			'sender_count' => NULL,
			'messages_per_sender' => NULL,
			'recipient_count' => NULL,
			'messages_per_recipient' => NULL,
		]);
		// ID messages offset: 1500 (+125) outbox: 1125 (+0) sentitems: 1250 (+125)

		// User 5 (single message, contact not in pbk, inbox read)
		$this->setup_db_content_messages([
			'id_user' => 6,
			'is_processed' => TRUE,
			'is_multipart' => TRUE,
			'id_folder' => NULL,
			'sender_count' => 1,
			'messages_per_sender' => 1,
			'recipient_count' => 1,
			'messages_per_recipient' => 1,
			'is_read' => TRUE,
		]);
		// ID messages offset: 1504 (+4*1) outbox: 1251 (+1) sentitems: 1252 (+1)

		// User 6 (single message, contact in pbk, inbox unread)
		$this->setup_db_content_messages([
			'id_user' => 7,
			'is_processed' => TRUE,
			'is_multipart' => TRUE,
			'id_folder' => NULL,
			'sender_count' => 1,
			'messages_per_sender' => 1,
			'recipient_count' => 1,
			'messages_per_recipient' => 1,
			'is_read' => FALSE,
		]);
		// ID messages offset: 1508 (+4*1) outbox: 1253 (+1) sentitems: 1254 (+1)
		// Add some contacts to phonebook
		$this->insert('pbk', [
			'id_user' => 7,
			'Name' => 'Contact for number 001...',
			'Number' => '+33600000001',
		]);

		$this->insert('user_folders', ['id_user' => 3, 'name' => 'user2, folder1 (id:13)']);
		$this->insert('user_folders', ['id_user' => 3, 'name' => 'user2, folder2 (id:14)']);
		// Messages for user2 (single messages, processed, folder 11).
		// We really want id_folder 11 even if this folder already belongs to user1
		// This is to check count of messages in the folder when logged in as user1
		$this->setup_db_content_messages([
			'id_user' => 3,
			'is_processed' => TRUE,
			'is_multipart' => FALSE,
			'id_folder' => 11, // 11 is not a mistake
			'sender_count' => 1,
			'messages_per_sender' => 22,
			'recipient_count' => 1,
			'messages_per_recipient' => 22,
		]);
		// ID messages offset: 1530 (+22) outbox: 1254 (+0) sentitems: 1276 (+22)

		$this->insert('user_folders', ['id_user' => 4, 'name' => 'user3, folder1 (id:15)']);
		$this->insert('user_folders', ['id_user' => 4, 'name' => 'user3, folder2 (id:16)']);
		// Messages for user 3 (multipart messages, processed)
		$this->setup_db_content_messages([
			'id_user' => 4,
			'is_processed' => TRUE,
			'is_multipart' => TRUE,
			'id_folder' => 15,
			'sender_count' => NULL,
			'messages_per_sender' => NULL,
			'recipient_count' => NULL,
			'messages_per_recipient' => NULL,
		]);
		// ID messages offset: 2030 (+4*125) outbox: 1276 (+0) sentitems: 1401 (+125)
		// Messages for user 3 (multipart messages, processed)
		$this->setup_db_content_messages([
			'id_user' => 4,
			'is_processed' => TRUE,
			'is_multipart' => TRUE,
			'id_folder' => 6,
			'sender_count' => NULL,
			'messages_per_sender' => NULL,
			'recipient_count' => NULL,
			'messages_per_recipient' => NULL,
		]);
		// ID messages offset: 2530 (+4*125) outbox: 1401 (+0) sentitems: 1401 (+0)
		$this->insert('user_folders', ['id_user' => 8, 'name' => 'user7, folder1 (id:17)']);
		// User 7 (single message, contact not in pbk, inbox read)
		$this->setup_db_content_messages([
			'id_user' => 8,
			'is_processed' => TRUE,
			'is_multipart' => TRUE,
			'id_folder' => 17,
			'sender_count' => 1,
			'messages_per_sender' => 1,
			'recipient_count' => 1,
			'messages_per_recipient' => 1,
			'is_read' => TRUE,
		]);
		// ID messages offset: 2534 (+4*1) outbox: 1401 (+0) sentitems: 1402 (+1)
		$this->insert('user_folders', ['id_user' => 9, 'name' => 'user8, folder1 (id:18)']);
		// User 8 (single message, contact in pbk, inbox unread)
		$this->setup_db_content_messages([
			'id_user' => 9,
			'is_processed' => TRUE,
			'is_multipart' => TRUE,
			'id_folder' => 18,
			'sender_count' => 1,
			'messages_per_sender' => 1,
			'recipient_count' => 1,
			'messages_per_recipient' => 1,
			'is_read' => FALSE,
		]);
		// ID messages offset: 2538 (+4*1) outbox: 1402 (+0) sentitems: 1403 (+1)
		// Add some contacts to phonebook
		$this->insert('pbk', [
			'id_user' => 9,
			'Name' => 'Contact for number 001...',
			'Number' => '+33600000001',
		]);
	}

	public function setup_db_content_messages($params)
	{
		$processed = ($params['is_processed'] === TRUE) ? 'true' : 'false';
		$multipart_suffix = ($params['is_multipart'] === TRUE) ? '_multipart' : '';
		switch ($params['id_folder'])
		{
			case NULL:
				$folder_text = '';
				$trash = 0;
				break;
			case 5:
				$folder_text = '/Trash';
				$trash = 1;
				break;
			case 6:
				$folder_text = '/Spam';
				$trash = 0;
				break;
			default:
				$folder_text = '/#' . $params['id_folder'];
				$trash = 0;
				break;
		}
		$sender_count = (isset($params['sender_count'])) ? $params['sender_count'] : DBSetup::senders;
		$messages_per_sender = (isset($params['messages_per_sender'])) ? $params['messages_per_sender'] : DBSetup::messages_per_sender;
		$recipient_count = (isset($params['recipient_count'])) ? $params['recipient_count'] : DBSetup::recipients;
		$messages_per_recipient = (isset($params['messages_per_recipient'])) ? $params['messages_per_recipient'] : DBSetup::messages_per_recipient;
		$readed = (isset($params['is_read']) && $params['is_read']) ? 'true' : 'false';

		// Insert inbox
		$i = 1;
		for ($sender = 1; $sender <= $sender_count; $sender++)
		{
			for ($i; $i <= $messages_per_sender * $sender; $i++)
			{
				$ID = self::$id_inbox_count + 1;
				$msg_number = ($i - 1) % $messages_per_sender + 1;
				$this->insert(
					'inbox' . $multipart_suffix,
					['ReceivingDateTime' => date('Y-m-d H:i:s', strtotime(self::BASE_MESSAGE_DATE . ' -' . $i . ' minutes')),
						'TextDecoded' => 'User ' . $params['id_user'] . '. Message ' . $msg_number . '. Sender ' . $sender . '. Folder Inbox' . $folder_text,
						'ID' => $ID,
						'id_folder' => ($params['id_folder'] === NULL) ? 1 : $params['id_folder'],
						'id_user' => $params['id_user'],
						'SenderNumber' => '+336' . sprintf('%08d', $sender),
						'Processed' => $processed,
						'readed' => $readed,
					]
				);
				if ($params['is_processed'])
				{
					$this->insert(
						'user_inbox',
						['id_user' => $params['id_user'],
							'id_inbox' => $ID, // same as ID in inbox
							'trash' => $trash,
						]
					);
				}
			}
		}

		// Insert outbox
		if ($params['id_folder'] === NULL)
		{
			$i = 1;
			for ($recipient = 1; $recipient <= $recipient_count; $recipient++)
			{
				for ($i; $i <= $messages_per_recipient * $recipient; $i++)
				{
					$msg_number = ($i - 1) % $messages_per_recipient + 1;
					$this->insert(
						'outbox' . $multipart_suffix,
						['SendingDateTime' => date('Y-m-d H:i:s', strtotime(self::BASE_MESSAGE_DATE . ' -' . $i . ' minutes')),
							'TextDecoded' => 'User ' . $params['id_user'] . '. Message ' . $msg_number . '. Recipient ' . $recipient . '. Folder Outbox' . $folder_text,
							'ID' => self::$id_outbox_sentitems_count + 1,
							'DestinationNumber' => '+336' . sprintf('%08d', $recipient),
						]
					);
					if ($params['is_processed'])
					{
						$this->insert(
							'user_outbox',
							['id_user' => $params['id_user'],
								'id_outbox' => self::$id_outbox_sentitems_count, // same as ID in outbox
							]
						);
					}
				}
			}
		}

		// Insert sentitems
		if ( ! (isset($params['id_folder']) && $params['id_folder'] === 6))
		{
			$i = 1;
			for ($recipient = 1; $recipient <= $recipient_count; $recipient++)
			{
				for ($i; $i <= $messages_per_recipient * $recipient; $i++)
				{
					$msg_number = ($i - 1) % $messages_per_recipient + 1;
					$this->insert(
						'sentitems' . $multipart_suffix,
						['SendingDateTime' => date('Y-m-d H:i:s', strtotime(self::BASE_MESSAGE_DATE . ' -' . $i . ' minutes')),
							'TextDecoded' => 'User ' . $params['id_user'] . '. Message ' . $msg_number . '. Recipient ' . $recipient . '. Folder Sentitems' . $folder_text,
							'ID' => self::$id_outbox_sentitems_count + 1,
							'id_folder' => ($params['id_folder'] === NULL) ? 3 : $params['id_folder'],
							'id_user' => $params['id_user'],
							'DestinationNumber' => '+336' . sprintf('%08d', $recipient),
							'Status' => self::$sentitems_status[($msg_number - 1) % count(self::$sentitems_status)],
						]
					);
					if ($params['is_processed'])
					{
						$this->insert(
							'user_sentitems',
							['id_user' => $params['id_user'],
								'id_sentitems' => self::$id_outbox_sentitems_count, // same as ID in sentitems
								'trash' => $trash,
							]
						);
					}
				}
			}
		}
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
		$db_engine = $this->get_engine();
		$this->records = [];
		return function ($CI) use ($records, $db_engine) {
			foreach ($records as $record)
			{
				if ( ! isset($record['statement']) || $record['statement'] === 'insert')
				{
					foreach ($record['data'] as $key => $value)
					{
						$CI->db->set($key, $value);
					}
					$CI->db->insert($record['table']);
				}
				if (isset($record['statement']) && $record['statement'] === 'update')
				{
					foreach ($record['where'] as $key => $value)
					{
						if (strpos($value, '%') === FALSE)
						{
							$CI->db->where($key, $value);
						}
						else
						{
							$CI->db->like($key, str_replace('%', '', $value));
						}
					}
					foreach ($record['set'] as $key => $value)
					{
						$CI->db->set($key, $value);
					}
					$CI->db->update($record['table']);
				}
			}
			if ($db_engine === 'pgsql')
			{
				// refresh sequence (required for postgresql because we manually set the ID column)
				// otherwise, on further insertion without setting ID it will complain. See: https://stackoverflow.com/a/24393132
				//$CI->db->query('ALTER SEQUENCE "outbox_ID_seq" RESTART WITH ' . ($id_outbox_sentitems_count+1) . ';');
				$CI->db->query('select setval(\'"outbox_ID_seq"\'::regclass, (select max("ID") from "outbox"))');
				$CI->db->query('select setval(\'"inbox_ID_seq"\'::regclass, (select max("ID") from "inbox"))');
				$CI->db->query('select setval(\'"sentitems_ID_seq"\'::regclass, (select max("ID") from "sentitems"))');
			}
		};
	}

	public function execute($CI)
	{
		foreach ($this->records as $record)
		{
			if ( ! isset($record['statement']) || $record['statement'] === 'insert')
			{
				foreach ($record['data'] as $key => $value)
				{
					$CI->db->set($key, $value);
				}
				$CI->db->insert($record['table']);
			}
			if (isset($record['statement']) && $record['statement'] === 'update')
			{
				foreach ($record['where'] as $key => $value)
				{
					if (strpos($value, '%') === FALSE)
					{
						$CI->db->where($key, $value);
					}
					else
					{
						$CI->db->like($key, str_replace('%', '', $value));
					}
				}
				foreach ($record['set'] as $key => $value)
				{
					$CI->db->set($key, $value);
				}
				$CI->db->update($record['table']);
			}
		}
		if ($this->get_engine() === 'pgsql')
		{
			// refresh sequence (required for postgresql because we manually set the ID column)
			// otherwise, on further insertion without setting ID it will complain. See: https://stackoverflow.com/a/24393132
			//$CI->db->query('ALTER SEQUENCE "outbox_ID_seq" RESTART WITH ' . (self::$id_outbox_sentitems_count+1) . ';');
			$CI->db->query('select setval(\'"outbox_ID_seq"\'::regclass, (select max("ID") from "outbox"))');
			$CI->db->query('select setval(\'"inbox_ID_seq"\'::regclass, (select max("ID") from "inbox"))');
			$CI->db->query('select setval(\'"sentitems_ID_seq"\'::regclass, (select max("ID") from "inbox"))');
		}
		$this->records = [];
	}

	public function get_udh_prefix($source, $number)
	{
		if (isset($this->UDHprefixes[$source][$number]) && count($this->UDHprefixes[$source][$number]) === 256)
		{
			echo 'Reached max possible UDH prefixes for ' . $source . ' number ' . $number;
			return '050003' . sprintf('%02X', random_int(0, 255));
		}

		while (TRUE)
		{
			$UDHprefix = '050003' . sprintf('%02X', random_int(0, 255));
			if ( ! isset($this->UDHprefixes[$source][$number]) || ! in_array($UDHprefix, $this->UDHprefixes[$source][$number]))
			{
				$this->UDHprefixes[$source][$number][] = $UDHprefix;
				return $UDHprefix;
			}
		}
	}

	public function install_plugin($testcase, $plugin)
	{
		// Reset static members in Plugins_lib_kalkun
		// otherwise their value is not correct when we switch db_engine
		Pluginss_test::reset_plugins_lib_static_members();

		// We need to have a session open to install plugins
		$testcase->request->setCallablePreConstructor(
			function () {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '1';
				$_SESSION['level'] = 'admin';
				$_SESSION['username'] = 'kalkun';
			}
		);

		$testcase->request('GET', 'pluginss/install/' . $plugin);
		// Remove all callables that were added.
		$testcase->request->setCallable(function () {
		});
		$_SESSION = [];
		if (session_status() === PHP_SESSION_ACTIVE && is_cli() === FALSE)
		{
			session_destroy();
		}
		$testcase->resetInstance();
	}

	public function insert($label, $input = [], $use_standard_text = TRUE)
	{
		switch ($label)
		{
			case 'filter':
				$this->insert_filter($input);
				break;
			case 'inbox':
				$this->insert_inbox($input, $use_standard_text);
				break;
			case 'inbox_multipart':
				$this->insert_inbox_multipart_unicode_emojis($input);
				break;
			case 'outbox':
				$this->insert_outbox($input, $use_standard_text);
				break;
			case 'outbox_multipart':
				$this->insert_outbox_multipart($input);
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
				$this->insert_sentitems($input, $use_standard_text);
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

	protected function insert_inbox($input = [], $use_standard_text = TRUE)
	{
		self::$id_inbox_count++;

		if (isset($input['TextDecoded']) && $use_standard_text === TRUE)
		{
			$input['TextDecoded'] = DBSetup::text_replace_placeholder($input['TextDecoded'], DBSetup::$text_mono_gsm);
		}

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
			'ID',
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
			//'TextDecoded' => 'very long message',
			'TextDecoded' => self::$text_mono_gsm,
			'ID' => self::$id_inbox_count,
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

		$ID = array_key_exists('ID', $input) ? $input['ID'] : self::$id_inbox_count + 1;

		if (isset($input['TextDecoded']))
		{
			$text = DBSetup::text_replace_placeholder($input['TextDecoded'], self::$text_multi_unicode);
		}
		else
		{
			$text = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor. Cras elementum ultrices diam. Maecenas ligula massa, varius a, semper congue, euismod non, mi. Proin porttitor, orci nec nonummy molestie, enim est eleifend mi, non fermentum diam nisl sit amet erat. Duis semper. Duis arcu massa, scelerisque vitae, consequat in, pretium a, enim.';
		}

		$UDHprefix = $this->get_udh_prefix('inbox', (isset($input['SenderNumber']) ? $input['SenderNumber'] : '+33600000000'));

		$this->insert_inbox(array_merge($input, [
			'Text' => '004C006F00720065006D00200069007000730075006D00200064006F006C006F0072002000730069007400200061006D00650074002C00200063006F006E00730065006300740065007400750072002000610064006900700069007300630069006E006700200065006C00690074002E00200053006500640020006E006F006E002000720069007300750073002E002000530075007300700065006E006400690073007300650020006C0065006300740075007300200074006F00720074006F0072002C0020006400690067006E0069007300730069006D002000730069007400200061006D00650074002C002000610064006900700069007300630069006E00670020006E00650063002C00200075006C00740072006900630069006500730020007300650064002C00200064006F006C',
			'UDH' => $UDHprefix . '0301',
			'TextDecoded' => $text,
			'ID' => $ID,
		]), FALSE);

		$this->insert_inbox(array_merge($input, [
			'Text' => '006F0072002E0020004300720061007300200065006C0065006D0065006E00740075006D00200075006C0074007200690063006500730020006400690061006D002E0020004D0061006500630065006E006100730020006C006900670075006C00610020006D0061007300730061002C002000760061007200690075007300200061002C002000730065006D00700065007200200063006F006E006700750065002C00200065007500690073006D006F00640020006E006F006E002C0020006D0069002E002000500072006F0069006E00200070006F00720074007400690074006F0072002C0020006F0072006300690020006E006500630020006E006F006E0075006D006D00790020006D006F006C00650073007400690065002C00200065006E0069006D002000650073007400200065',
			'UDH' => $UDHprefix . '0302',
			'TextDecoded' => '',
			'ID' => ++$ID,
		]), FALSE);

		$this->insert_inbox(array_merge($input, [
			'Text' => '006C0065006900660065006E00640020006D0069002C0020006E006F006E0020006600650072006D0065006E00740075006D0020006400690061006D0020006E00690073006C002000730069007400200061006D0065007400200065007200610074002E00200044007500690073002000730065006D007000650072002E00200044007500690073002000610072006300750020006D0061007300730061002C0020007300630065006C0065007200690073007100750065002000760069007400610065002C00200063006F006E00730065007100750061007400200069006E002C0020007000720065007400690075006D00200061002C00200065006E0069006D002E',
			'UDH' => $UDHprefix . '0303',
			'TextDecoded' => '',
			'ID' => ++$ID,
		]), FALSE);
	}

	protected function insert_inbox_multipart_unicode_emojis($input = [])
	{
		// Output text is:
		// 👍🏿 ✌🏿️ @mention 16/ Emjis 😁 💾 😅 💾 🤣 💾 😂 💾 🙂 💾. Lorem 👍🏿ipsum dolor sit ame💾 😂 sum dolor sitsum dolor sitsum dolor sitsum dolor sitm dolor sitsum dolor sitsum dol m dolor sitsum dolor sitsum dol😁 💾 😅 💾. Stop
		// In the inbox database, the full decoded text is stored in the 1st message only. TextDecoded is empty in the other messages.

		$ID = array_key_exists('ID', $input) ? $input['ID'] : self::$id_inbox_count + 1;

		if (isset($input['TextDecoded']))
		{
			$text = DBSetup::text_replace_placeholder($input['TextDecoded'], self::$text_multi_unicode);
		}
		else
		{
			$text = '👍🏿 ✌🏿️ @mention 16/ Emjis 😁 💾 😅 💾 🤣 💾 😂 💾 🙂 💾. Lorem 👍🏿ipsum dolor sit ame💾 😂 sum dolor sitsum dolor sitsum dolor sitsum dolor sitm dolor sitsum dolor sitsum dol m dolor sitsum dolor sitsum dol😁 💾 😅 💾. Stop';
		}

		$UDHprefix = $this->get_udh_prefix('inbox', (isset($input['SenderNumber']) ? $input['SenderNumber'] : '+33600000000'));

		$this->insert_inbox(array_merge($input, [
			'Text' => 'D83DDC4DD83CDFFF0020270CD83CDFFFFE0F00200040006D0065006E00740069006F006E002000310036002F00200045006D006A006900730020D83DDE010020D83DDCBE0020D83DDE050020D83DDCBE0020D83EDD230020D83DDCBE0020D83DDE020020D83DDCBE0020D83DDE420020D83DDCBE002E0020004C006F00720065006D0020',
			'UDH' => $UDHprefix . '0401',
			'TextDecoded' => $text,
			'Coding' => 'Unicode_No_Compression',
			'ID' => $ID,
		]), FALSE);

		$this->insert_inbox(array_merge($input, [
			'Text' => 'D83DDC4DD83CDFFF0069007000730075006D00200064006F006C006F0072002000730069007400200061006D0065D83DDCBE0020D83DDE02002000730075006D00200064006F006C006F0072002000730069007400730075006D00200064006F006C006F0072002000730069007400730075006D00200064006F006C006F0072002000730069',
			'UDH' => $UDHprefix . '0402',
			'TextDecoded' => '',
			'Coding' => 'Unicode_No_Compression',
			'ID' => ++$ID,
		]), FALSE);

		$this->insert_inbox(array_merge($input, [
			'Text' => '007400730075006D00200064006F006C006F00720020007300690074006D00200064006F006C006F0072002000730069007400730075006D00200064006F006C006F0072002000730069007400730075006D00200064006F006C0020006D00200064006F006C006F0072002000730069007400730075006D00200064006F006C006F00720020',
			'UDH' => $UDHprefix . '0403',
			'TextDecoded' => '',
			'Coding' => 'Unicode_No_Compression',
			'ID' => ++$ID,
		]), FALSE);

		$this->insert_inbox(array_merge($input, [
			'Text' => '00730069007400730075006D00200064006F006CD83DDE010020D83DDCBE0020D83DDE050020D83DDCBE002E002000530074006F0070',
			'UDH' => $UDHprefix . '0404',
			'TextDecoded' => '',
			'Coding' => 'Unicode_No_Compression',
			'ID' => ++$ID,
		]), FALSE);
	}

	protected function insert_outbox($input = [], $use_standard_text = TRUE)
	{
		if (isset($input['TextDecoded']) && $use_standard_text === TRUE)
		{
			$input['TextDecoded'] = DBSetup::text_replace_placeholder($input['TextDecoded'], DBSetup::$text_mono_gsm);
		}

		self::$id_outbox_sentitems_count++;
		$table = 'outbox';
		$columns = [
			//'UpdatedInDB',
			'InsertIntoDB',
			'SendingDateTime',
			'SendBefore',
			'SendAfter',
			'Text',
			'DestinationNumber',
			'Coding',
			'UDH',
			'Class',
			'TextDecoded',
			'ID',
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
			// Gammu's default in SQLite3 is datetime('now'), so without localtime. So it will
			// be stored as GMT. This is unfortunate as mysql & postgresql store dates in localtime.
			// Moreover, updating SQLite3 default value for the columns is not supported.
			// So we MUST set a value so that it stores the local time.
			'InsertIntoDB' => date('Y-m-d H:i:s'),
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
			'ID' => self::$id_outbox_sentitems_count,
			'MultiPart' => 'false',
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

	protected function insert_outbox_multipart($input = [])
	{
		$ID = array_key_exists('ID', $input) ? $input['ID'] : self::$id_outbox_sentitems_count + 1;

		if (isset($input['TextDecoded']))
		{
			$text = DBSetup::text_replace_placeholder($input['TextDecoded'], self::$text_multi_gsm);
		}
		else
		{
			$text = self::$text_multi_gsm;
		}

		$parts = DBSetup::get_multi_parts($text);

		$UDHprefix = $this->get_udh_prefix('sentitems', (isset($input['DestinationNumber']) ? $input['DestinationNumber'] : '+33612345678'));

		$this->insert_outbox(array_merge($input, [
			'Text' => '006C006F006E00670020006D00650073007300610067006500200077006900740068002000670073006D00370020007300700065006300690061006C002000630068006100720073003A0020005E007B007D005B005D007E007C20AC005C0020004C006F00720065006D00200069007000730075006D00200064006F006C006F0072002000730069007400200061006D00650074002C00200063006F006E00730065006300740065007400750072002000610064006900700069007300630069006E006700200065006C00690074002E00200053006500640020006E006F006E002000720069007300750073002E002000530075007300700065006E006400690073007300650020006C0065006300740075007300200074006F00720074006F',
			'UDH' => $UDHprefix . '0301',
			'TextDecoded' => $parts[0],
			'ID' => $ID,
			'MultiPart' => 'true',
			//'SequencePosition' => 1,
		]), FALSE);

		$this->insert_outbox_multipart_internal(array_merge($input, [
			'Text' => '0072002C0020006400690067006E0069007300730069006D002000730069007400200061006D00650074002C002000610064006900700069007300630069006E00670020006E00650063002C00200075006C00740072006900630069006500730020007300650064002C00200064006F006C006F0072002E0020004300720061007300200065006C0065006D0065006E00740075006D00200075006C0074007200690063006500730020006400690061006D002E0020004D0061006500630065006E006100730020006C006900670075006C00610020006D0061007300730061002C002000760061007200690075007300200061002C002000730065006D00700065007200200063006F006E006700750065002C00200065007500690073006D006F00640020006E006F006E002C0020006D',
			'UDH' => $UDHprefix . '0302',
			'TextDecoded' => $parts[1],
			'ID' => $ID,
			'SequencePosition' => 2,
		]), FALSE);

		$this->insert_outbox_multipart_internal(array_merge($input, [
			'Text' => '0069002E',
			'UDH' => $UDHprefix . '0303',
			'TextDecoded' => $parts[2],
			'ID' => $ID,
			'SequencePosition' => 3,
		]), FALSE);
	}

	protected function insert_outbox_multipart_internal($input = [])
	{
		$table = 'outbox_multipart';
		$columns = [
			'Text',
			'Coding',
			'UDH',
			'Class',
			'TextDecoded',
			'ID',
			'SequencePosition',
			'Status',
			'StatusCode',
		];
		$defaults = [
			'Text' => NULL,
			'Coding' => 'Default_No_Compression',
			'UDH' => NULL,
			'Class' => 1,
			'TextDecoded' => 'outbox message',
			'ID' => self::$id_outbox_sentitems_count,  // This field is autoincrement in pgsql, but not mysql neither sqlite
			'SequencePosition' => FALSE,
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

	protected function insert_sentitems($input = [], $use_standard_text = TRUE)
	{
		if (isset($input['TextDecoded']) && $use_standard_text === TRUE)
		{
			$input['TextDecoded'] = DBSetup::text_replace_placeholder($input['TextDecoded'], DBSetup::$text_mono_gsm);
		}

		if ( ! isset($input['SequencePosition']) || $input['SequencePosition'] === 1)
		{
			self::$id_outbox_sentitems_count++;
		}
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
			'ID' => self::$id_outbox_sentitems_count,  // This field is autoincrement in pgsql, but not mysql neither sqlite
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
		$ID = array_key_exists('ID', $input) ? $input['ID'] : self::$id_outbox_sentitems_count + 1;
		if (isset($input['TextDecoded']))
		{
			$text = DBSetup::text_replace_placeholder($input['TextDecoded'], self::$text_multi_gsm);
		}
		else
		{
			$text = self::$text_multi_gsm;
		}

		$parts = DBSetup::get_multi_parts($text);

		$UDHprefix = $this->get_udh_prefix('sentitems', (isset($input['DestinationNumber']) ? $input['DestinationNumber'] : '+33612345678'));

		$this->insert_sentitems(array_merge($input, [
			'Text' => '006C006F006E00670020006D00650073007300610067006500200077006900740068002000670073006D00370020007300700065006300690061006C002000630068006100720073003A0020005E007B007D005B005D007E007C20AC005C0020004C006F00720065006D00200069007000730075006D00200064006F006C006F0072002000730069007400200061006D00650074002C00200063006F006E00730065006300740065007400750072002000610064006900700069007300630069006E006700200065006C00690074002E00200053006500640020006E006F006E002000720069007300750073002E002000530075007300700065006E006400690073007300650020006C0065006300740075007300200074006F00720074006F',
			'UDH' => $UDHprefix . '0301',
			'TextDecoded' => $parts[0],
			'ID' => $ID,
			'SequencePosition' => 1,
		]), FALSE);

		$this->insert_sentitems(array_merge($input, [
			'Text' => '0072002C0020006400690067006E0069007300730069006D002000730069007400200061006D00650074002C002000610064006900700069007300630069006E00670020006E00650063002C00200075006C00740072006900630069006500730020007300650064002C00200064006F006C006F0072002E0020004300720061007300200065006C0065006D0065006E00740075006D00200075006C0074007200690063006500730020006400690061006D002E0020004D0061006500630065006E006100730020006C006900670075006C00610020006D0061007300730061002C002000760061007200690075007300200061002C002000730065006D00700065007200200063006F006E006700750065002C00200065007500690073006D006F00640020006E006F006E002C0020006D',
			'UDH' => $UDHprefix . '0302',
			'TextDecoded' => $parts[1],
			'ID' => $ID,
			'SequencePosition' => 2,
		]), FALSE);

		$this->insert_sentitems(array_merge($input, [
			'Text' => '0069002E',
			'UDH' => $UDHprefix . '0303',
			'TextDecoded' => $parts[2],
			'ID' => $ID,
			'SequencePosition' => 3,
		]), FALSE);
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

	public function update($table, $where = [], $set = [])
	{
		$this->records[] = [
			'table' => $table,
			'statement' => 'update',
			'where' => $where,
			'set' => $set,
		];
		return $this;
	}
}
