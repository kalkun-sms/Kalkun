<?php
/**
 * Kalkun
 * An open source web based SMS Manager
 *
 * @copyright 2024 Fab Stz
 * @author Fab Stz <fabstz-it@yahoo.fr>
 * @license <https://spdx.org/licenses/GPL-2.0-or-later.html> GPL-2.0-or-later
 * @link https://kalkun.sourceforge.io/
 */

require_once __DIR__.'/../testutils/ConfigFile.php';
require_once __DIR__.'/../testutils/DBSetup.php';
require_once __DIR__.'/../testutils/KalkunTestCase.php';

use PHPUnit\Framework\Attributes\DataProvider;

class Daemon_test extends KalkunTestCase {

	private $restore_error_handler_after_exception = FALSE;

	public function setUp() : void
	{
		if (file_exists(FCPATH . 'install'))
		{
			unlink(FCPATH . 'install');
		}
	}

	public function tearDown() : void
	{
		if ($this->restore_error_handler_after_exception === TRUE)
		{
			// Unsure why two are required.
			// Maybe because we have to remove
			//  1. the error_handler from MY_Hooks::kalkun_set_error_handler()
			//  2. the error_handler from CIPHPUnitTestCase::enableStrictErrorCheck()
			restore_error_handler();
			restore_error_handler();
		}
	}

	public static function database_Provider()
	{
		return DBSetup::$db_engines_to_test;
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_message_routine($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$this->request->addCallable($dbsetup->insert('inbox')->closure());

		$data = $this->request('GET', 'daemon/message_routine');
		$this->assertEmpty($data);
		//$this->assertEquals('', $data); // This one is to display the message

		$result = $this->CI->db
				->where('ID', '1')
				->get('inbox');
		$this->assertEquals(TRUE, db_boolean_to_php_bool($db_engine, $result->row()->Processed));
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_message_routine_multipart($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$this->request->addCallable($dbsetup->insert('inbox_multipart')->closure());

		$data = $this->request('GET', 'daemon/message_routine');
		$this->assertEmpty($data);
		//$this->assertEquals('', $data); // This one is to display the message

		$result = $this->CI->db
				->where('ID', '1')
				->get('inbox');
		$this->assertEquals(TRUE, db_boolean_to_php_bool($db_engine, $result->row()->Processed));
		$result = $this->CI->db
				->where('ID', '2')
				->get('inbox');
		$this->assertEquals(TRUE, db_boolean_to_php_bool($db_engine, $result->row()->Processed));
		$result = $this->CI->db
				->where('ID', '3')
				->get('inbox');
		$this->assertEquals(TRUE, db_boolean_to_php_bool($db_engine, $result->row()->Processed));
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_message_routine_spam($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$this->request->addCallable(
			function ($CI) {
				$CI->load->model('Spam_model');
				// The spam level of the message is expected to be 0.5,
				// so ratingcutoff to 0 permits to detect message as spam.
				$CI->Spam_model->ratingcutoff = 0;
			}
		);

		$this->request->addCallable($dbsetup->insert('inbox')->closure());

		$data = $this->request('GET', 'daemon/message_routine');
		$this->assertEmpty($data);
		//$this->assertEquals('', $data); // This one is to display the message

		$result = $this->CI->db
				->where('ID', '1')
				->get('inbox');
		$this->assertEquals(6, $result->row()->id_folder);

		$word = '<(Mono)...>'; // Any word that is only once in the textmessage
		$result = $this->CI->db
				->where('token', $word)
				->get('b8_wordlist');
		$this->assertEquals(1, $result->row()->count_spam);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_message_routine_hook_before_ownership_break($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		// Don't use MonkeyPatch because on newer version of phpparser (>=4.6)
		// lines are incorrect in code coverage.
		//MonkeyPatch::patchFunction(
		//	'do_action_kalkun',
		//	function ($tag, $args = NULL) {
		//		if ($tag === 'message.incoming.before')
		//		{
		//			return 'break';
		//		}
		//	},
		//	'Daemon::message_routine'
		//);

		$plugin_suffix = '_' . __FUNCTION__;
		$configFile = new ConfigFile(APPPATH . 'plugins/tester'.$plugin_suffix.'/tester'.$plugin_suffix.'.php');
		$content = '<?php';
		$content .= '
require_once (APPPATH . \'plugins/Plugin_helper.php\');
Plugin_helper::autoloader();
class Tester'.$plugin_suffix.'_plugin extends CI3_plugin_system {
	use plugin_trait;
	public function __construct()
	{
		parent::__construct();
		add_filter(\'message.incoming.before\', array($this, \'return_break\'), 1);
	}
	function return_break($sms)
	{
		return \'break\';
	}
}
';
		$configFile->write($content);

		$dbsetup->install_plugin($this, 'tester'.$plugin_suffix);

		$this->request->addCallable($dbsetup->insert('inbox')->closure());

		$data = $this->request('GET', 'daemon/message_routine');
		$this->assertEmpty($data);
		//$this->assertEquals('', $data); // This one is to display the message

		$result = $this->CI->db
				->where('ID', '1')
				->get('inbox');
		$this->assertEquals(FALSE, db_boolean_to_php_bool($db_engine, $result->row()->Processed));

		Pluginss_test::reset_plugins_lib_static_members();
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_message_routine_hook_after_ownership_break($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		// Don't use MonkeyPatch because on newer version of phpparser (>=4.6)
		// lines are incorrect in code coverage.
		//MonkeyPatch::patchFunction(
		//	'do_action_kalkun',
		//	function ($tag, $args = NULL) {
		//		if ($tag === 'message.incoming.after')
		//		{
		//			return 'break';
		//		}
		//	},
		//	'Daemon::message_routine'
		//);

		$plugin_suffix = '_' . __FUNCTION__;
		$configFile = new ConfigFile(APPPATH . 'plugins/tester'.$plugin_suffix.'/tester'.$plugin_suffix.'.php');
		$content = '<?php';
		$content .= '
require_once (APPPATH . \'plugins/Plugin_helper.php\');
Plugin_helper::autoloader();
class Tester'.$plugin_suffix.'_plugin extends CI3_plugin_system {
	use plugin_trait;
	public function __construct()
	{
		parent::__construct();
		add_filter(\'message.incoming.after\', array($this, \'return_break\'), 1);
	}
	function return_break($sms)
	{
		return \'break\';
	}
}
';
		$configFile->write($content);

		$dbsetup->install_plugin($this, 'tester'.$plugin_suffix);

		$this->request->addCallable($dbsetup->insert('inbox')->closure());

		$data = $this->request('GET', 'daemon/message_routine');
		$this->assertEmpty($data);
		//$this->assertEquals('', $data); // This one is to display the message

		$result = $this->CI->db
				->where('ID', '1')
				->get('inbox');
		$this->assertEquals(FALSE, db_boolean_to_php_bool($db_engine, $result->row()->Processed));

		Pluginss_test::reset_plugins_lib_static_members();
	}

	public static function gateway_engine_Provider()
	{
		return DBSetup::prepend_db_engine([
			[['engine' => 'Clickatell', 'url' => 'http://localhost/_wrong_path', 'username' => 'username', 'password' => 'password', 'api_id' => 'api_id']],
			[['engine' => 'Connekt', 'url' => 'http://localhost/_wrong_path', 'api_id' => 'api_id']],
			[['engine' => 'Gammu']],
			[['engine' => 'Kannel', 'url' => 'http://localhost/_wrong_path', 'username' => 'username', 'password' => 'password']],
			[['engine' => 'Nowsms', 'url' => 'http://localhost/_wrong_path', 'username' => 'username', 'password' => 'password']],
			[['engine' => 'Ozeking', 'url' => 'http://localhost/_wrong_path', 'username' => 'username', 'password' => 'password']],
			[['engine' => 'Panacea', 'url' => 'http://localhost/_wrong_path', 'username' => 'username', 'password' => 'password']],
			[['engine' => 'Tmobilecz', 'tmobileczauth' => array(
				1 => array('user' => 'admins login', 'pass' => 'his_password', 'hist' => TRUE, 'eml' => ''),
				2 => array('user' => '2nd users login', 'pass' => 'her_password', 'hist' => TRUE, 'eml' => ''),
				'default' => array('user' => 'all others', 'pass' => 'their_password', 'hist' => TRUE, 'eml' => '')
			)]],
			[['engine' => 'Way2sms', 'username' => 'username', 'password' => 'password']],
		]);
	}

	/**
	 * @dataProvider gateway_engine_Provider
	 * If this test fails, check that the timezone is set correctly in the configuration
	 * of php. The reason might be that the DB returns no message in outbox because of
	 * the filter on the outbox table  "'SendingDateTime <=" See Nongammu_model::process_outbox_queue
	 */
	#[DataProvider('gateway_engine_Provider')]
	public function test_outbox_routine($db_engine, $gateway)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = '<?php';
		$content .= '
$config[\'gateway\'] = '.var_export($gateway, TRUE).';
';
		$configFile->write($content);

		$this->request->addCallable($dbsetup->insert('outbox')->closure());

		if (in_array($gateway['engine'], ['Clickatell', 'Kannel', 'Ozeking', 'Panacea', 'Nowsms'], TRUE))
		{
			// Since we provide invalid gateway parameter (URL...), file_get_contents will throw an exception.
			$this->expectException(WarningException::class);
			$this->_expectExceptionMessageMatches('/file_get_contents.*Failed to open stream:/i');

			// Prevent: "Test code or tested code did not remove its own error handlers" (since PHPUnit 11)
			// As suggested in https://github.com/laravel/framework/issues/49502#issuecomment-2222592953
			$this->restore_error_handler_after_exception = TRUE;

			// Prevent: "Test code or tested code did not close its own output buffers"
			ob_end_flush();
			ob_get_clean();
		}
		$this->request->enableHooks();
		$data = $this->request('GET', 'daemon/outbox_routine');
		$this->assertEmpty($data);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_server_alert_daemon_trigger_alert($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		// Install+enable Server Alert Plugin
		$dbsetup->install_plugin($this, 'server_alert');

		// Add server alert to the plugin
		$this->request->addCallable($dbsetup->insert('plugin_server_alert')->closure());

		// Call the server_alert_daemon
		$this->request->enableHooks();
		$data = $this->request('GET', 'daemon/server_alert_daemon');
		$this->assertEmpty($data);

		$result = $this->CI->db
				->where('alert_name', 'test_server_alert_localhost_85')
				->get('plugin_server_alert');
		$this->assertEquals('false', $result->row()->status);

		Pluginss_test::reset_plugins_lib_static_members();
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_run_user_filters($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$message_text = 'test message for test_run_user_filters';
		$this->request->addCallable($dbsetup->insert('inbox', ['TextDecoded' => $message_text], FALSE)
						->insert('user_folders')
						->insert('filter')
						->closure());

		$data = $this->request('GET', 'daemon/message_routine');
		$this->assertEmpty($data);

		$result = $this->CI->db
				->where('TextDecoded', $message_text)
				->get('inbox');
		$this->assertEquals(11, $result->row()->id_folder);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_run_user_filters_no_matching_filter($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$message_text = 'test message for test_run_user_filters_no_matching_filter';
		$this->request->addCallable($dbsetup->insert('inbox', ['TextDecoded' => $message_text], FALSE)
						->insert('user_folders')
						->insert('filter', [
							'from' => '+33611111111',
							'has_the_words' => 'word_not_there',
						])
						->closure());

		$data = $this->request('GET', 'daemon/message_routine');
		$this->assertEmpty($data);

		$result = $this->CI->db
				->where('TextDecoded', $message_text)
				->get('inbox');
		$this->assertEquals(1, $result->row()->id_folder);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_run_user_filters_no_matching_filter2($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$message_text = 'test message for test_run_user_filters_no_matching_filter2';
		$this->request->addCallable($dbsetup->insert('inbox', ['TextDecoded' => $message_text], FALSE)
						->insert('user_folders')
						->insert('filter', [
							'from' => '+33600000000',
							'has_the_words' => 'word_not_there',
						])
						->closure());

		$data = $this->request('GET', 'daemon/message_routine');
		$this->assertEmpty($data);

		$result = $this->CI->db
				->where('TextDecoded', $message_text)
				->get('inbox');
		$this->assertEquals(1, $result->row()->id_folder);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_set_ownership_for_user_by_message_text($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$username = 'user1';
		$user_ID = 2;
		$user_phone = '+33611111111';
		$inbox_msg_id = 1;
		$message_text = 'message for @'.$username;
		$message_sender_number = '+123458'; // Different from user number
		$this->request->addCallable($dbsetup
						// insert user
						->insert('user', ['username' => $username, 'id_user' => $user_ID, 'phone_number' => $user_phone])
						// insert message containing text @user1
						->insert('inbox', [
							'TextDecoded' => $message_text,
							'SenderNumber' => $message_sender_number,
							'ID' => $inbox_msg_id,
						], FALSE)
						->closure());

		$data = $this->request('GET', 'daemon/message_routine');
		$this->assertEmpty($data);

		// Check that message owner is set to 'user'
		$result = $this->CI->db
				->where('TextDecoded', $message_text)
				->get('inbox');
		$result_user_inbox = $this->CI->db
				->where('id_inbox', $inbox_msg_id)
				->get('user_inbox');
		$this->assertEquals($user_ID, $result_user_inbox->row()->id_user);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_set_ownership_for_user_by_phonenumber($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$username = 'user1';
		$user_ID = 2;
		$user_phone = '+33611111111';
		$inbox_msg_id = 1;
		$message_text = 'message with matching number';
		$message_sender_number = '+33611111111'; // Same as user number
		$this->request->addCallable($dbsetup
						// insert user
						->insert('user', ['username' => $username, 'id_user' => $user_ID, 'phone_number' => $user_phone])
						->insert('inbox', [
							'TextDecoded' => $message_text,
							'SenderNumber' => $message_sender_number,
							'ID' => $inbox_msg_id,
						], FALSE)
						->closure());

		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = '<?php';
		$content .= '
$config[\'inbox_routing_user_phonenumber\'] = TRUE;
';
		$configFile->write($content);

		$data = $this->request('GET', 'daemon/message_routine');
		$this->assertEmpty($data);

		// Check that message owner is set to 'user'
		$result = $this->CI->db
				->where('TextDecoded', $message_text)
				->get('inbox');
		$result_user_inbox = $this->CI->db
				->where('id_inbox', $inbox_msg_id)
				->get('user_inbox');
		$this->assertEquals($user_ID, $result_user_inbox->row()->id_user);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_set_ownership_for_user_by_user_phonebook($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$username = 'user1';
		$user_ID = 2;
		$user_phone = '+33611111111';
		$inbox_msg_id = 1;
		$message_text = 'message with matching pbk number';
		$message_sender_number = '+33622222222'; // Same as contact in pbk
		$pbk_contact_number = $message_sender_number;
		$this->request->addCallable($dbsetup
						// insert user
						->insert('user', ['username' => $username, 'id_user' => $user_ID, 'phone_number' => $user_phone])
						// insert message where SenderNumber is in the phonebook of the user
						->insert('inbox', [
							'SenderNumber' => $message_sender_number,
							'TextDecoded' => $message_text,
							'ID' => $inbox_msg_id,
						], FALSE)
						->insert('pbk_groups')
						// insert contact to phonebook where contactNumber is SenderNumber of the message
						->insert('pbk', ['Number' => $pbk_contact_number])
						->closure());

		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = '<?php';
		$content .= '
$config[\'inbox_routing_use_phonebook\'] = TRUE;
';
		$configFile->write($content);

		$data = $this->request('GET', 'daemon/message_routine');
		$this->assertEmpty($data);

		// Check that message owner is set to 'user'
		$result = $this->CI->db
				->where('TextDecoded', $message_text)
				->get('inbox');
		$result_user_inbox = $this->CI->db
				->where('id_inbox', $inbox_msg_id)
				->get('user_inbox');
		$this->assertEquals($user_ID, $result_user_inbox->row()->id_user);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_set_ownership_no_username_match_custom_inbox_owner($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$username = 'user1';
		$user_ID = 2;
		$user_phone = '+33611111111';
		$inbox_msg_id = 1;
		$message_text = 'message without username match';
		$message_sender_number = $user_phone; // Same as contact in pbk
		$pbk_contact_number = $message_sender_number;
		$inbox_owner_id = 7;
		$this->request->addCallable($dbsetup
						// insert user
						->insert('user', ['username' => $username, 'id_user' => $user_ID, 'phone_number' => $user_phone])
						// insert message where SenderNumber is in the phonebook of the user
						->insert('inbox', [
							'SenderNumber' => $message_sender_number,
							'TextDecoded' => $message_text,
							'ID' => $inbox_msg_id,
						], FALSE)
						->insert('pbk_groups')
						// insert contact to phonebook where contactNumber is SenderNumber of the message
						->insert('pbk', ['Number' => $pbk_contact_number])
						->closure());

		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = '<?php';
		$content .= '
$config[\'inbox_owner_id\'] = array(\''.$inbox_owner_id.'\');
$config[\'inbox_routing_use_phonebook\'] = FALSE;
$config[\'inbox_routing_user_phonenumber\'] = FALSE;
';
		$configFile->write($content);

		$data = $this->request('GET', 'daemon/message_routine');
		$this->assertEmpty($data);

		// Check that message owner is set to 'user'
		$result = $this->CI->db
				->where('TextDecoded', 'message without username match')
				->get('inbox');
		$result_user_inbox = $this->CI->db
				->where('id_inbox', $inbox_msg_id)
				->get('user_inbox');
		$this->assertEquals($inbox_owner_id, $result_user_inbox->row()->id_user);
	}
}
