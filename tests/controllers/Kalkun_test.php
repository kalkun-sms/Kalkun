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

use Symfony\Component\DomCrawler\Crawler;

class Kalkun_test extends KalkunTestCase {

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
	public function test_add_folder_POST_no_source_url($db_engine)
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

		$data = $this->request('POST', 'kalkun/add_folder', ['folder_name' => 'folder_name_for_add_folder', 'id_user' => '1']);
		$this->assertRedirect('', 302);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_add_folder_POST_with_source_url($db_engine)
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

		$source_url = 'messages/folder/inbox';

		$data = $this->request('POST', 'kalkun/add_folder', ['folder_name' => 'folder_name_for_add_folder', 'id_user' => '1', 'source_url' => $source_url]);
		$this->assertRedirect($source_url, 302);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_delete_filter_ajaxGET($db_engine)
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

		$filter_id = '123456789';

		$data = $this->ajaxRequest('GET', 'kalkun/delete_filter/'.$filter_id);
		$this->assertEmpty($data);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_delete_filter_ajaxGET_none($db_engine)
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

		$data = $this->ajaxRequest('GET', 'kalkun/delete_filter');
		$this->assertEmpty($data);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_delete_folder_GET_none($db_engine)
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

		$data = $this->request('GET', 'kalkun/delete_folder');

		// This isn't supported by ci-phpunit-test, so use the workaround below
		//$this->assertResponseHeader('Refresh', '0;url=http://localhostvendor/bin/index.php/');

		$catched_redirection = $this->CI->output->_status['redirect'];
		$expected = 'Redirect to ' . $this->CI->config->item('base_url') . 'index.php/';
		$this->assertEquals($expected, $catched_redirection);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_delete_folder_GET($db_engine)
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

		$folder_id = '123456789';
		$data = $this->request('GET', 'kalkun/delete_folder/'.$folder_id);

		// This isn't supported by ci-phpunit-test, so use the workaround below
		//$this->assertResponseHeader('Refresh', '0;url=http://localhostvendor/bin/index.php/');

		$catched_redirection = $this->CI->output->_status['redirect'];
		$expected = 'Redirect to ' . $this->CI->config->item('base_url') . 'index.php/';
		$this->assertEquals($expected, $catched_redirection);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_get_csrf_hash($db_engine)
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

		$data = $this->request('GET', 'kalkun/get_csrf_hash');
		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($data);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_get_statistic_GET_none($db_engine)
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

		$data = $this->ajaxRequest('GET', 'kalkun/get_statistic');
		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($data);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_get_statistic_GET_days($db_engine)
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

		$data = $this->ajaxRequest('GET', 'kalkun/get_statistic/days');
		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($data);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_get_statistic_GET_weeks($db_engine)
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

		$data = $this->ajaxRequest('GET', 'kalkun/get_statistic/weeks');
		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($data);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_get_statistic_GET_months($db_engine)
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

		$data = $this->ajaxRequest('GET', 'kalkun/get_statistic/months');
		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($data);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_get_statistic_GET_invalid($db_engine)
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

		$data = $this->ajaxRequest('GET', 'kalkun/get_statistic/invalid');
		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($data);
		// Should return same value as 'days'
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_get_statistic_GET_days_nonadmin($db_engine)
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
				$_SESSION['level'] = 'user';
				$_SESSION['username'] = 'kalkun';
			}
		);

		$data = $this->ajaxRequest('GET', 'kalkun/get_statistic/days');
		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($data);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_index($db_engine)
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

		$output = $this->request('GET', '/');
		$crawler = new Crawler($output);
		$this->assertEquals('Kalkun / Dashboard', $this->crawler_text($crawler->filter('title')));

		$this->assertValidHtml($output);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_index_outgoing_disabled($db_engine)
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
		$configFile->write('<?php $config[\'disable_outgoing\'] = TRUE;');

		$data = $this->request('GET', '/');
		$expected = '<div class="warning">Outgoing SMS disabled. Contact system administrator.</div>';
		$this->_assertStringContainsString($expected, $data);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_notification($db_engine)
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

		$data = $this->request('GET', 'kalkun/notification');
		$expected = '<!-- Values are filled in javascript -->';
		$this->_assertStringContainsString($expected, $data);
		$this->assertValidHtmlSnippet($data);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_notification_ajax($db_engine)
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

		$data = $this->ajaxRequest('GET', 'kalkun/notification');
		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($data);
		$data_decoded = json_decode($data, TRUE);
		$this->assertTrue(array_key_exists('signal', $data_decoded));
		$this->assertTrue(array_key_exists('battery', $data_decoded));
		$this->assertTrue(array_key_exists('status', $data_decoded));
	}



	public static function phone_number_validation_Provider()
	{
		return DBSetup::prepend_db_engine([
			'get_valid_number' => ['GET', '+33612345678', 'FR', TRUE],
			'get_invalid_number' => ['GET', '0612345678', '', FALSE],
			'post_valid_number' => ['POST', '+33612345678', 'FR', TRUE],
			'post_invalid_number' => ['POST', '0612345678', '', FALSE],
		]);
	}

	/**
	 * @dataProvider phone_number_validation_Provider
	 */
	public function test_phone_number_validation($db_engine, $method, $phone, $region, $expected)
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

		$data = $this->request($method, 'kalkun/phone_number_validation', ['phone' => $phone, 'region' => $region]);
		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($data);
		$data_decoded = json_decode($data);
		if ($expected === TRUE)
		{
			$this->assertEquals('true', $data_decoded);
		}
		else
		{
			$this->assertNotEquals('true', $data_decoded);
		}
	}

	public static function phone_number_validation_multiple_Provider()
	{
		return DBSetup::prepend_db_engine([
			'get_valid_number' => ['GET', '+33612345678, +33623456789', 'FR', TRUE],
			'get_valid_number2' => ['GET', '+33612345678, 0623456789', 'FR', TRUE],
			'get_invalid_number' => ['GET', '0612345678, 0623456789', '', FALSE],
			'post_valid_number' => ['POST', '+33612345678, +33623456789', 'FR', TRUE],
			'post_valid_number2' => ['POST', '+33612345678, 0623456789', 'FR', TRUE],
			'post_invalid_number' => ['POST', '0612345678, 0623456789', '', FALSE],
		]);
	}

	/**
	 * @dataProvider phone_number_validation_multiple_Provider
	 */
	public function test_phone_number_validation_multiple($db_engine, $method, $phone, $region, $expected)
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

		$data = $this->request($method, 'kalkun/phone_number_validation_multiple', ['phone' => $phone, 'region' => $region]);
		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($data);
		$data_decoded = json_decode($data);
		if ($expected === TRUE)
		{
			$this->assertEquals('true', $data_decoded);
		}
		else
		{
			$this->assertNotEquals('true', $data_decoded);
		}
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_rename_folder_POST($db_engine)
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

		$source_url = 'messages/folder/inbox';

		$data = $this->request('POST', 'kalkun/rename_folder', ['edit_folder_name' => 'folder_name_for_edit_folder', 'id_folder' => '1', 'source_url' => $source_url]);
		$this->assertRedirect($source_url, 302);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_settings_invalid($db_engine)
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

		$data = $this->request('GET', 'settings/invalid');
		$this->assertResponseCode(404);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_settings_none($db_engine)
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

		$data = $this->request('GET', 'settings');
		$this->assertResponseCode(404);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_settings_general($db_engine)
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

		$data = $this->request('GET', 'settings/general');
		$expected = '<td>Country calling code</td>';
		$this->_assertStringContainsString($expected, $data);
		$this->assertValidHtml($data);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_settings_personal($db_engine)
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

		$data = $this->request('GET', 'settings/personal');
		$expected = '<td>Username</td>';
		$this->_assertStringContainsString($expected, $data);
		$this->assertValidHtml($data);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_settings_appearance($db_engine)
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

		$data = $this->request('GET', 'settings/appearance');
		$expected = '<td>Background image</td>';
		$this->_assertStringContainsString($expected, $data);
		$this->assertValidHtml($data);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_settings_password($db_engine)
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

		$data = $this->request('GET', 'settings/password');
		$expected = '<td>New password</td>';
		$this->_assertStringContainsString($expected, $data);
		$this->assertValidHtml($data);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_settings_save_general($db_engine)
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

		$post_data = [
			'option' => 'general',
			'language' => 'english',
			'paging' => '20',
			'permanent_delete' => 'false',
			'delivery_report' => 'default',
			'conversation_sort' => 'asc',
			'dial_code' => 'FR',
		];

		$data = $this->request('POST', 'settings/save', $post_data);
		$this->assertRedirect('settings/general', 302);
		$expected = 'Settings saved successfully.';
		$flashdata = $this->CI->session->flashdata('notif');
		$this->assertEquals($expected, $flashdata);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_settings_save_personal($db_engine)
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

		$post_data = [
			'option' => 'personal',
			'realname' => 'Kalkun SMS',
			'username' => 'kalkun',
			'phone_number' => '+123456',
			'signatureoption' => 'false',
			'signature' => "--\n\nPut your signature here",
		];

		$data = $this->request('POST', 'settings/save', $post_data);
		$this->assertRedirect('settings/personal', 302);
		$expected = 'Settings saved successfully.';
		$flashdata = $this->CI->session->flashdata('notif');
		$this->assertEquals($expected, $flashdata);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_settings_save_personal_change_kalkun_username($db_engine)
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

		$post_data = [
			'option' => 'personal',
			'realname' => 'Kalkun SMS',
			'username' => 'kalkunNew',
			'phone_number' => '+123456',
			'signatureoption' => 'false',
			'signature' => "--\n\nPut your signature here",
		];

		$data = $this->request('POST', 'settings/save', $post_data);
		$this->assertRedirect('settings/personal', 302);
		$expected = 'Settings saved successfully.';
		$flashdata = $this->CI->session->flashdata('notif');
		$this->assertEquals($expected, $flashdata);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_settings_save_personal_change_kalkun_username_demo_mode($db_engine)
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
		$configFile->write('<?php $config[\'demo_mode\'] = TRUE;');

		$post_data = [
			'option' => 'personal',
			'realname' => 'Kalkun SMS',
			'username' => 'kalkunNew',
			'phone_number' => '+123456',
			'signatureoption' => 'false',
			'signature' => "--\n\nPut your signature here",
		];

		$data = $this->request('POST', 'settings/save', $post_data);
		$this->assertRedirect('settings/personal', 302);
		$expected = 'Settings saved successfully (except username for kalkun user which can\'t be changed in demo mode)';
		$flashdata = $this->CI->session->flashdata('notif');
		$this->assertEquals($expected, $flashdata);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_settings_save_personal_change_to_existing_username($db_engine)
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
				$_SESSION['username'] = 'notKalkun';
			}
		);

		$post_data = [
			'option' => 'personal',
			'realname' => 'Kalkun SMS',
			'username' => 'kalkun',
			'phone_number' => '+123456',
			'signatureoption' => 'false',
			'signature' => "--\n\nPut your signature here",
		];

		$data = $this->request('POST', 'settings/save', $post_data);
		$this->assertRedirect('settings/personal', 302);
		$expected = 'Username already taken';
		$flashdata = $this->CI->session->flashdata('notif');
		$this->assertEquals($expected, $flashdata);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_settings_save_appearance($db_engine)
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

		$post_data = [
			'option' => 'appearance',
			'theme' => 'green',
			'bg_image_option' => 'true',
		];

		$data = $this->request('POST', 'settings/save', $post_data);
		$this->assertRedirect('settings/appearance', 302);
		$expected = 'Settings saved successfully.';
		$flashdata = $this->CI->session->flashdata('notif');
		$this->assertEquals($expected, $flashdata);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_settings_save_password($db_engine)
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

		$post_data = [
			'option' => 'password',
			'current_password' => 'kalkun',
			'new_password' => 'kalkun_new_password',
			'confirm_password' => 'kalkun_new_password',
		];

		$data = $this->request('POST', 'settings/save', $post_data);
		$this->assertRedirect('settings/password', 302);
		$expected = 'Settings saved successfully.';
		$flashdata = $this->CI->session->flashdata('notif');
		$this->assertEquals($expected, $flashdata);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_settings_save_password_wrong_current_password($db_engine)
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

		$post_data = [
			'option' => 'password',
			'current_password' => 'kalkun_wrong',
			'new_password' => 'kalkun_new_password',
			'confirm_password' => 'kalkun_new_password',
		];

		$data = $this->request('POST', 'settings/save', $post_data);
		$this->assertRedirect('settings/password', 302);
		$expected = 'Wrong password';
		$flashdata = $this->CI->session->flashdata('notif');
		$this->assertEquals($expected, $flashdata);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_settings_save_password_demo_mode($db_engine)
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
		$configFile->write('<?php $config[\'demo_mode\'] = TRUE;');

		$post_data = [
			'option' => 'password',
			'current_password' => 'kalkun',
			'new_password' => 'kalkun_new_password',
			'confirm_password' => 'kalkun_new_password',
		];

		$data = $this->request('POST', 'settings/save', $post_data);
		$this->assertRedirect('settings/password', 302);
		$expected = 'Password modification forbidden in demo mode.';
		$flashdata = $this->CI->session->flashdata('notif');
		$this->assertEquals($expected, $flashdata);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_settings_save_filters_insert($db_engine)
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

		$post_data = [
			'option' => 'filters',
			'id_filter' => '',
			'from' => '+123456',
			'has_the_words' => 'keyword',
			'id_folder' => '1',
			'id_user' => '1',
		];

		$data = $this->request('POST', 'settings/save', $post_data);
		$this->assertRedirect('settings/filters', 302);
		$expected = 'Settings saved successfully.';
		$flashdata = $this->CI->session->flashdata('notif');
		$this->assertEquals($expected, $flashdata);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_settings_save_filters_update($db_engine)
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

		$this->request->addCallable($dbsetup
						->insert('user_folders')
						->insert('filter', [
							'from' => '+12345600',
							'has_the_words' => 'keyword will be changed',
						])->closure());

		$post_data = [
			'option' => 'filters',
			'id_filter' => '1',
			'from' => '+123456',
			'has_the_words' => 'keyword',
			'id_folder' => '11',
			'id_user' => '1',
		];

		$data = $this->request('POST', 'settings/save', $post_data);
		$this->assertRedirect('settings/filters', 302);
		$expected = 'Settings saved successfully.';
		$flashdata = $this->CI->session->flashdata('notif');
		$this->assertEquals($expected, $flashdata);

		$result = $this->CI->db
				->where('id_filter', '1')
				->get('user_filters');
		$this->assertEquals('+123456', $result->row()->from);
		$this->assertEquals('keyword', $result->row()->has_the_words);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_settings_filters($db_engine)
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

		$data = $this->request('GET', 'settings/filters');
		$expected = '<a href="javascript:void(0);" id="addnewfilter">Create a new filter</a>';
		$this->_assertStringContainsString($expected, $data);
		$this->assertValidHtml($data);
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_unread_count($db_engine)
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

		$data = $this->request('GET', 'kalkun/unread_count');
		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($data);
		$data_decoded = json_decode($data, TRUE);
		$this->assertTrue(array_key_exists('in', $data_decoded));
		$this->assertTrue(array_key_exists('draft', $data_decoded));
		$this->assertTrue(array_key_exists('spam', $data_decoded));
	}
}
