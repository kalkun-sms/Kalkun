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
require_once __DIR__.'/../controllers/Pluginss_test.php';

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RequiresPhpunit;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;

class Jsonrpc_test extends KalkunTestCase {

	public function setUp() : void
	{
		if (file_exists(FCPATH . 'install'))
		{
			unlink(FCPATH . 'install');
		}
		//DBSetup::setup_db_kalkun_testing2($this);
	}

	/*public function reloadCIwithEngine($db_engine)
	{
		DBSetup::$current_setup[$db_engine]->write_config_file_for_database();
	}*/

	public static function database_Provider()
	{
		return DBSetup::$db_engines_to_test;
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_send_sms_client($db_engine)
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

		$dbsetup->install_plugin($this, 'jsonrpc');
		Pluginss_test::reset_plugins_lib_static_members();

		// On CI or other systems, the http server may not be up
		// in that case, the call will return 'Failed to open stream: Connection refused' (with lowercase Failed for PHP < 8.0)
		// otherwise, if the server is up, it shoud return a 404 error.
		set_error_handler(function () {
		}, 0);
		$fp = @file_get_contents(base_url());
		// For example: file_get_contents(http://localhost/vendor/bin/index.php/plugin/jsonrpc/send_sms): Failed to open stream: Connection refused
		restore_error_handler();

		if ($error = error_get_last())
		{
			$error_message = error_get_last()['message'];
			$message = implode(': ', array_slice(explode(': ', $error_message), 1));
		}

		if (isset($message) && strtolower($message) === strtolower('Failed to open stream: Connection refused'))
		{
			$this->expectException(ErrorException::class);
			$this->_expectExceptionMessageMatches('/'.$message.'/');
		}
		else
		{
			if (class_exists('Datto\JsonRpc\Http\Exceptions\HttpException'))
			{
				$this->expectException(Datto\JsonRpc\Http\Exceptions\HttpException::class);
			}
			else
			{
				// This is for datto/json-rpc-http v4
				$this->expectException(Datto\JsonRpc\Http\HttpException::class);
			}
			$this->expectExceptionMessage('404 Not Found');
		}
		// Prevent: "Test code or tested code did not close its own output buffers"
		ob_end_flush();
		ob_get_clean();

		$output = $this->request('GET', 'plugin/jsonrpc/send_sms_client');

		$this->markTestIncomplete();
	}

	/**
	 * @dataProvider database_Provider
	 *
	 * This test would require json-rpc-http to read from php://input
	 */
	/*#[DataProvider('database_Provider')]
		public function test_send_sms_empty_data($db_engine)
		{
		}
	*/

	/**
	 * @dataProvider database_Provider
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @requires PHPUnit >= 6.0.0
	 *
	 * With PHP unit 5.7.27 on PHP 5.6 test errors out like this
	 * exception 'ErrorException' with message 'unserialize(): Error at offset 0 of 75 bytes' in /home/runner/work/Kalkun/Kalkun/vendor/phpunit/phpunit/src/Util/PHP.php:290
	 * Passing the same data to unserialize() with recent PHP (8.x) also triggers the issue. So the problem
	 * may be somewhere in phpunit in the management of the data.
	 */
	#[DataProvider('database_Provider')]
	#[RunInSeparateProcess]
	#[PreserveGlobalState(FALSE)]
	#[RequiresPhpunit('>= 6.0.0')]
	public function test_send_sms_empty_data($db_engine)
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

		$dbsetup->install_plugin($this, 'jsonrpc');
		Pluginss_test::reset_plugins_lib_static_members();

		$this->request->setHeader('Content-Type', 'application/json');
		//$json = '{"jsonrpc":"2.0","id":551,"method":"sms.send_sms","params":{"phoneNumber":"+123456","message":"Testing JSONRPC"}}';
		$output = $this->request('POST', 'plugin/jsonrpc/send_sms');

		//$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($output);

		$data_decoded = json_decode($output, TRUE);
		// {"jsonrpc":"2.0","id":null,"error":{"code":-32700,"message":"Parse error"}}
		$this->assertEquals('2.0', $data_decoded['jsonrpc']);
		$this->assertEquals(['code' => -32700, 'message' => 'Parse error'], $data_decoded['error']);
		$this->assertEquals(NULL, $data_decoded['id']);
	}
}
