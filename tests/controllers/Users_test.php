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

class Users_test extends KalkunTestCase {

	public function setUp() : void
	{
		if (file_exists(FCPATH . 'install'))
		{
			unlink(FCPATH . 'install');
		}
	}

	public static function database_Provider()
	{
		return DBSetup::$db_engines_to_test;
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_index_non_admin($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$this->request->setCallablePreConstructor(
			function () {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '2';
				$_SESSION['level'] = 'user';
				$_SESSION['username'] = 'username';
			}
		);

		$data = $this->request('GET', 'users/index');
		$this->assertRedirect('/', 302);
		$expected = 'Access denied.';
		$CI_instance = & get_instance();
		$flashdata = $CI_instance->session->flashdata('notif');
		$this->assertEquals($expected, $flashdata);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_index_GET($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$this->request->setCallablePreConstructor(
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

		$data = $this->request('GET', 'users/index');
		$expected = '<div id="window_title_left">Users</div>';
		$this->_assertStringContainsString($expected, $data);
		$expected = '>Kalkun SMS<';
		$this->_assertStringContainsString($expected, $data);
		$this->assertValidHtml($data);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_index_GET_no_user_in_db($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$this->request->setCallablePreConstructor(
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
		$this->request->addCallable(
			function ($CI) {
				// Delete user from table to have an empty table.
				$CI->db->where('id_user', '1')->delete('user');
			}
		);

		$data = $this->request('GET', 'users/index');
		$expected = '<div id="window_title_left">Users</div>';
		$this->_assertStringContainsString($expected, $data);
		$expected = '>No users in the database.<';
		$this->_assertStringContainsString($expected, $data);
		$this->assertValidHtml($data);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_index_GET_ajax($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$this->request->setCallablePreConstructor(
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

		$data = $this->ajaxRequest('GET', 'users/index');
		$expected = '<div id="window_title_left">Users</div>';
		$this->assertThat($data, $this->logicalNot($this->stringContains($expected)));
		$expected = '>Kalkun SMS<';
		$this->_assertStringContainsString($expected, $data);
		$this->assertValidHtmlSnippet($data);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_index_POST_search_name_found($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$this->request->setCallablePreConstructor(
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

		// insert user
		$realname = 'User number 1';
		$this->request->addCallable($dbsetup->insert('user', ['realname' => $realname])->closure());

		$data = $this->request('POST', 'users/index', ['search_name' => 'ser NUm']);
		$expected = '<div id="window_title_left">Users</div>';
		$this->_assertStringContainsString($expected, $data);
		$expected = '>'.$realname.'<';
		$this->_assertStringContainsString($expected, $data);
		// Check that kalkun user is not displayed
		$expected = '>Kalkun SMS<';
		$this->assertThat($data, $this->logicalNot($this->stringContains($expected)));
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_index_POST_search_name_nomatch($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$this->request->setCallablePreConstructor(
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

		$data = $this->request('POST', 'users/index', ['search_name' => 'nomatch']);
		$expected = '<div id="window_title_left">Users</div>';
		$this->_assertStringContainsString($expected, $data);
		$expected = '>User not found<';
		$this->_assertStringContainsString($expected, $data);
		// Check that kalkun user is not displayed
		$expected = '>Kalkun SMS<';
		$this->assertThat($data, $this->logicalNot($this->stringContains($expected)));
		$this->assertValidHtml($data);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_add_user($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$this->request->setCallablePreConstructor(
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

		$data = $this->request('GET', 'users/add_user');
		$expected = 'phonebook/add_user_process" id="addUser" method="post"';
		$this->_assertStringContainsString($expected, $data);
		$expected = 'id="realname" value=""';
		$this->_assertStringContainsString($expected, $data);
		//$data = $this->request('GET', 'users/add_user', ['type' => 'normal', 'param1' => '']);
		$this->assertValidHtmlSnippet($data);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_add_user_normal($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$this->request->setCallablePreConstructor(
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

		$data = $this->request('GET', 'users/add_user', ['type' => 'normal', 'param1' => '']);
		$expected = 'phonebook/add_user_process" id="addUser" method="post"';
		$this->_assertStringContainsString($expected, $data);
		$expected = 'id="realname" value=""';
		$this->_assertStringContainsString($expected, $data);
		$this->assertValidHtmlSnippet($data);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_add_user_edit($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$this->request->setCallablePreConstructor(
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

		$data = $this->request('GET', 'users/add_user', ['type' => 'edit', 'param1' => '1']); //param1 is user_id to edit. 1=kalkun
		$expected = 'phonebook/add_user_process" id="addUser" method="post"';
		$this->_assertStringContainsString($expected, $data);
		$expected = 'id="realname" value="Kalkun SMS"';
		$this->_assertStringContainsString($expected, $data);
		$this->assertValidHtmlSnippet($data);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_add_user_process_new_user($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$this->request->setCallablePreConstructor(
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

		$data = $this->request('POST', 'users/add_user_process', [
			'realname' => 'New user from Users_tests',
			'username' => 'new_user',
			'phone_number' => '+33699999988',
			'level' => 'user',
			'password' => 'password_for_new_user',
			//'id_user' => 'kalkun', // Only in case of edit.
		]);
		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($data);
		$data_decoded = json_decode($data, TRUE);
		$this->assertEquals('User added successfully.', $data_decoded['msg']);
		$this->assertEquals('info', $data_decoded['type']);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_add_user_process_edit_user($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$this->request->setCallablePreConstructor(
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

		$data = $this->request('POST', 'users/add_user_process', [
			'realname' => 'Kalkun SMS new realname',
			'username' => 'kalkun_edite', //limited to 12 chars
			'phone_number' => '+33699999988',
			'level' => 'user',
			'password' => 'new_password_for_kalkun',
			'id_user' => '1', // Only in case of edit.
		]);
		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($data);
		$data_decoded = json_decode($data, TRUE);
		$this->assertEquals('User updated successfully.', $data_decoded['msg']);
		$this->assertEquals('info', $data_decoded['type']);

		$user_record = $this->CI->db->where('id_user', '1')->get('user');
		$this->assertEquals('kalkun_edite', $user_record->row()->username);
		$this->assertEquals('user', $user_record->row()->level);
		$this->assertEquals('Kalkun SMS new realname', $user_record->row()->realname);
		$this->assertEquals('+33699999988', $user_record->row()->phone_number);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_add_user_process_edit_user_demomode_forbid_username_change($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$this->request->setCallablePreConstructor(
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

		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = "<?php\n";
		$content .= '$config[\'demo_mode\'] = TRUE;';
		$configFile->write($content);

		$data = $this->request('POST', 'users/add_user_process', [
			'realname' => 'Kalkun SMS new realname',
			'username' => 'kalkun_edite', //limited to 12 chars
			'phone_number' => '+33699999988',
			'level' => 'admin',
			'password' => 'new_password_for_kalkun',
			'id_user' => '1', // Only in case of edit.
		]);

		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($data);
		$data_decoded = json_decode($data, TRUE);
		$this->assertEquals('Modification of username of "kalkun" user forbidden in demo mode. Username was restored.', $data_decoded['msg']);
		$this->assertEquals('error', $data_decoded['type']);

		$user_record = $this->CI->db->where('id_user', '1')->get('user');
		$this->assertEquals('kalkun', $user_record->row()->username);
		$this->assertEquals('admin', $user_record->row()->level);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_add_user_process_edit_user_demomode_forbid_level_change($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$this->request->setCallablePreConstructor(
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

		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = "<?php\n";
		$content .= '$config[\'demo_mode\'] = TRUE;';
		$configFile->write($content);

		$data = $this->request('POST', 'users/add_user_process', [
			'realname' => 'Kalkun SMS new realname',
			'username' => 'kalkun', //limited to 12 chars
			'phone_number' => '+33699999988',
			'level' => 'user',
			'password' => 'new_password_for_kalkun',
			'id_user' => '1', // Only in case of edit.
		]);

		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($data);
		$data_decoded = json_decode($data, TRUE);
		$this->assertEquals('Changing role of "kalkun" user forbidden in demo mode. Role was restored.', $data_decoded['msg']);
		$this->assertEquals('error', $data_decoded['type']);

		$user_record = $this->CI->db->where('id_user', '1')->get('user');
		$this->assertEquals('kalkun', $user_record->row()->username);
		$this->assertEquals('admin', $user_record->row()->level);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_add_user_process_edit_user_demomode_forbid_username_level_change($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$this->request->setCallablePreConstructor(
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

		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = "<?php\n";
		$content .= '$config[\'demo_mode\'] = TRUE;';
		$configFile->write($content);

		$data = $this->request('POST', 'users/add_user_process', [
			'realname' => 'Kalkun SMS new realname',
			'username' => 'kalkun_edite', //limited to 12 chars
			'phone_number' => '+33699999988',
			'level' => 'user',
			'password' => 'new_password_for_kalkun',
			'id_user' => '1', // Only in case of edit.
		]);

		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($data);
		$data_decoded = json_decode($data, TRUE);
		$this->assertEquals('Changing role of "kalkun" user forbidden in demo mode. Role was restored.', $data_decoded['msg']);
		$this->assertEquals('error', $data_decoded['type']);

		$user_record = $this->CI->db->where('id_user', '1')->get('user');
		$this->assertEquals('kalkun', $user_record->row()->username);
		$this->assertEquals('admin', $user_record->row()->level);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_delete_user($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$this->request->setCallablePreConstructor(
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

		// TODO: launch also when there are messages in inbox, outbox & sentitems for that user, and pbk, user_folder, sms_used

		$data = $this->request('POST', 'users/delete_user', ['id_user' => '1']);
		$this->assertEmpty($data);
	}
}
