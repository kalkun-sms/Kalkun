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

require_once __DIR__.'/../testutils/DBSetup.php';
require_once __DIR__.'/../testutils/KalkunTestCase.php';

use PHPUnit\Framework\Attributes\DataProvider;

class Login_test extends KalkunTestCase {

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
	#[DataProvider('database_Provider')]
	public function test_login_GET_form($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$data = $this->request('GET', 'login?l=french');
		$expected = '<title>Kalkun - Se connecter</title>';
		$this->_assertStringContainsString($expected, $data);
		$this->assertValidHtml($data);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_login_POST_success($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$data = $this->request('POST', 'login', ['username' => 'kalkun', 'password' => 'kalkun', 'idiom' => 'english']);
		$this->assertRedirect('kalkun', 302);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_login_POST_failure($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$data = $this->request('POST', 'login', ['username' => 'kalkun', 'password' => 'wrong_password', 'idiom' => 'english']);
		$output = $this->CI->session->flashdata('errorlogin');
		$this->assertEquals('Username or password are incorrect.', $output);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_logout($db_engine)
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

		// Since there is no session open, because phpunit tests are run in CLI,
		// closing the session will throw an exception.
		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('session_destroy(): Trying to destroy uninitialized session');
		// Prevent: "Test code or tested code did not close its own output buffers"
		ob_end_flush();
		ob_get_clean();

		$data = $this->request('POST', 'login/logout');

		// Check that session is closed
		// $this->assertEquals(TRUE, session_status() === PHP_SESSION_NONE);
		// $this->assertRedirect('login', 302);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_forgot_password_GET_form($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$data = $this->request('GET', 'login/forgot_password');
		$expected = '<title>Kalkun - Forgot your password?</title>';
		$this->_assertStringContainsString($expected, $data);
		$this->assertValidHtml($data);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_forgot_password_POST_username($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$data = $this->request('POST', 'login/forgot_password', ['username' => 'kalkun', 'idiom' => 'english']);
		$output = $this->CI->session->flashdata('errorlogin');
		$this->assertRedirect('login/forgot_password?l=english', 302);
		$expected = 'If you are a registered user, a SMS has been sent to you.';
		$this->assertEquals($expected, $output);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_forgot_password_POST_phone($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$data = $this->request('POST', 'login/forgot_password', ['phone' => '+123456', 'idiom' => 'english']);
		$output = $this->CI->session->flashdata('errorlogin');
		$this->assertRedirect('login/forgot_password?l=english', 302);
		$expected = 'If you are a registered user, a SMS has been sent to you.';
		$this->assertEquals($expected, $output);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_password_reset_POST_valid_token_new_password($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$token = 'my_token';
		$this->request->addCallable($dbsetup->insert('user_forgot_password', ['token' => $token])->closure());

		$data = $this->request('POST', 'login/password_reset', ['token' => $token, 'new_password' => 'my_new_password']);
		$this->assertRedirect('login?l=english', 302);

		$output = $this->CI->session->flashdata('errorlogin');
		$expected = 'Password changed successfully.';
		$this->assertEquals($expected, $output);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_password_reset_GET_form_valid_token($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$token = 'my_token';
		$this->request->addCallable($dbsetup->insert('user_forgot_password', ['token' => $token])->closure());

		$data = $this->request('GET', 'login/password_reset/'.$token);
		$expected = '<title>Kalkun - Password reset</title>';
		$this->_assertStringContainsString($expected, $data);
		$this->assertValidHtml($data);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_password_reset_POST_invalid_token_new_password($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$data = $this->request('POST', 'login/password_reset', ['token' => 'invalid_token', 'new_password' => 'my_new_password']);
		$output = $this->CI->session->flashdata('errorlogin');
		$this->assertEquals('Token invalid.', $output);
		$this->assertRedirect('login/forgot_password?l=english', 302);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_password_reset_GET_form_expired_token($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$token = 'my_token';
		$this->request->addCallable($dbsetup->insert('user_forgot_password', [
			'token' => $token,
			'valid_until' => date('Y-m-d H:i:s', mktime(date('H'), date('i') - 30, date('s'), date('m'), date('d'), date('Y'))),
		])->closure());

		$data = $this->request('POST', 'login/password_reset/'.$token);
		$output = $this->CI->session->flashdata('errorlogin');
		$this->assertEquals('Token invalid.', $output);
		$this->assertRedirect('login/forgot_password?l=english', 302);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_password_reset_GET_form_invalid_token($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$data = $this->request('POST', 'login/password_reset/invalid_token');
		$output = $this->CI->session->flashdata('errorlogin');
		$this->assertEquals('Token invalid.', $output);
		$this->assertRedirect('login/forgot_password?l=english', 302);
	}
}
