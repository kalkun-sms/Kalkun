<?php
/**
 * Kalkun
 * An open source web based SMS Manager
 *
 * @copyright 2022-2024 Kalkun dev team
 * @author Kalkun dev team
 * @license <https://spdx.org/licenses/GPL-2.0-or-later.html> GPL-2.0-or-later
 * @link https://kalkun.sourceforge.io/
 */

require_once __DIR__.'/../testutils/ConfigFile.php';
require_once __DIR__.'/../testutils/DBSetup.php';
require_once __DIR__.'/../testutils/KalkunTestCase.php';

use PHPUnit\Framework\Attributes\DataProvider;

class Install_test extends KalkunTestCase {

	public function setUp() : void
	{
		if ( ! file_exists(FCPATH . 'install'))
		{
			file_put_contents(FCPATH . 'install', '');
		}
	}

	public function test_index()
	{
		$output = $this->request('GET', 'install');
		$this->_assertStringContainsString('<h1 style="float: left">Kalkun installation assistant</h1>', $output);
		$this->assertValidHtml($output);
	}

	public function test_index_disabled()
	{
		if (file_exists(FCPATH . 'install'))
		{
			unlink(FCPATH . 'install');
		}

		$output = $this->request('GET', 'install');
		$expected = 'Installation has been disabled by the administrator.';

		$this->assertResponseCode(403);
		$this->_assertStringContainsString($expected, $output);

		$this->assertValidHtmlSnippet($output);
	}

	public function test_config_setup_GET()
	{
		$output = $this->request('GET', 'install/config_setup');
		$this->_assertStringContainsString('<h1>Final configuration steps</h1>', $output);

		$this->assertValidHtml($output);
	}

	public function test_config_setup_POST_remove_install_file()
	{
		$output = $this->request('POST', 'install/config_setup', ['remove_install_file' => 'remove']);
		$this->_assertStringContainsString('<h1>Final configuration steps</h1>', $output);

		$this->assertValidHtml($output);
	}

	public function test_config_setup_POST_install_not_writable()
	{
		$dir = APPPATH . '..';

		// Store original mode to restore it later on.
		$mode = substr(sprintf('%o', fileperms($dir)), -4);
		$modeint = intval(base_convert($mode, 8, 10));

		// Set to read-only
		chmod($dir, 0555);

		$output = $this->request('POST', 'install/config_setup', ['remove_install_file' => 'remove']);
		$this->_assertStringContainsString('You must remove the file manually.', $output);

		// restore original mode.
		chmod($dir, $modeint);
	}

	public function test_requirement_check()
	{
		$output = $this->request('GET', 'install/requirement_check');

		$expected = '<h1>Requirements check</h1>';
		$this->_assertStringContainsString($expected, $output);

		$expected = '<input type="submit" name="submit" value="Next ›"  class="button" />';
		$this->_assertStringContainsString($expected, $output);

		$this->assertValidHtml($output);
	}

	public function test_requirement_check_error()
	{
		$this->request->setCallable(
			function ($CI) {
				$array = ReflectionHelper::getPrivateProperty(
					$CI,
					'db_prop'
				);
				$array['driver'] = 'invalid_value';
				$array['human'] = 'Invalid Database Engine (for testing)';
				ReflectionHelper::setPrivateProperty(
					$CI,
					'db_prop',
					$array
				);
			}
		);

		$output = $this->request('GET', 'install/requirement_check');

		$expected = '<h1>Requirements check</h1>';
		$this->_assertStringContainsString($expected, $output);

		$expected = '<p>Unfortunately, your system does not meet the minimum requirements to run Kalkun. Please update your system to meet the above requirements. Then click on button to check again.</p>';
		$this->_assertStringContainsString($expected, $output);
	}

	public static function database_Provider()
	{
		return DBSetup::$db_engines_to_test;
	}

	/**
	 * @dataProvider database_setup_run_db_setupProvider
	 */
	#[DataProvider('database_setup_run_db_setupProvider')]
	public function test_database_setup_GET($db_engine, $config)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config($config);

		$output = $this->request('GET', 'install/database_setup');
		// TODO: really check the different output depending on the $config.
		$expected = '<input type="submit" name="submit" value="‹ Previous"  class="button" />';
		$this->_assertStringContainsString($expected, $output);

		$this->assertValidHtml($output);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_database_setup_GET_with_db_exception($db_engine)
	{
		$db = 'kalkun_testing_missing_db';

		$dbsetup = new DBSetup([
			'database' => $db,
			'engine' => $db_engine
		]);
		$dbsetup->write_config_file_for_database();

		if ($db_engine === 'sqlite')
		{
			$this->markTestIncomplete('FIXME: haven\'t found yet how to catch error opening sqlite file.');

			$dir = dirname($this->db($db_engine, $db));

			// Store original mode to restore it later on.
			$mode = substr(sprintf('%o', fileperms($dir)), -4);
			$modeint = intval(base_convert($mode, 8, 10));

			// Set dir to read-only & non-executable
			chmod($dir, 0444);
		}

		$output = $this->request('GET', 'install/database_setup');

		if ($db_engine === 'sqlite')
		{
			// restore original mode.
			chmod($dir, $modeint);

			$this->assertResponseCode(500);
		}
		else
		{
			$expected = '<p class="red">There was a problem when trying to load the database.</p>';
			$this->_assertStringContainsString($expected, $output);
		}
	}

	public static function database_setup_run_db_setupProvider()
	{
		return DBSetup::prepend_db_engine([
			'gammu with pbk, fresh kalkun' => ['gammu_pbk_kalkun_fresh_install_by_installer'],
			'gammu with pbk, update kalkun 0.6' => ['gammu_pbk_kalkun_upgrade_from_0.6'],
			'gammu with pbk, update kalkun 0.7' => ['gammu_pbk_kalkun_upgrade_from_0.7'],
			'gammu with pbk, update kalkun 0.8.0' => ['gammu_pbk_kalkun_upgrade_from_0.8.0'],
			'gammu with pbk, update kalkun 0.8.3' => ['gammu_pbk_kalkun_upgrade_from_0.8.3'],
			'gammu without pbk, fresh kalkun' => ['gammu_no_pbk_kalkun_fresh_install_by_installer'],
			'gammu without pbk, update kalkun 0.8.0' => ['gammu_no_pbk_kalkun_upgrade_from_0.8.0'],
			'gammu without pbk, update kalkun 0.8.3' => ['gammu_no_pbk_kalkun_upgrade_from_0.8.3'],
		]);
	}

	/**
	 * @dataProvider database_setup_run_db_setupProvider
	 *
	 * for sqlite and other DB, this error might be displayed:
	 *	Parse error near line 29: table user_settings has 10 columns but 9 values were supplied
	 * This was fixed in 0.8.1 commit 04ff138ef2f83b538dd56b9ae40914227ac8806c
	 *
	 * This test requires to remove the "static" from CI3's DB_driver.php, line "static $preg_ec = array();"
	 * Otherwise, escaping for the DB query would fail in some cases (when switching DB engine).
	 */
	#[DataProvider('database_setup_run_db_setupProvider')]
	public function test_database_setup_POST_run_db_setup($db_engine, $config)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine
		]);
		$dbsetup->setup_config($config);

		$output = $this->request('POST', 'install/database_setup', ['action' => 'run_db_setup']);
		$expected = '<input type="submit" name="submit" value="Continue ›"  class="button" />';
		$this->_assertStringContainsString($expected, $output);
	}

	public static function uses_default_encryption_keyProvider()
	{
		return [
			'default_enc_key 1' => ["hex2bin('F0af18413d1c9e03A6d8d1273160f5Ed')", TRUE],
			'default_enc_key 2' => ["'F0af18413d1c9e03A6d8d1273160f5Ed'", TRUE],
			'non default_enc_key 1' => ["'nonDefaultKey'", FALSE],
		];
	}

	/**
	 * @dataProvider uses_default_encryption_keyProvider
	 */
	#[DataProvider('uses_default_encryption_keyProvider')]
	public function test_uses_default_encryption_key($enc_key, $expected)
	{
		$configFile = new ConfigFile(APPPATH . 'config/testing/config.php');
		$content = '<?php $config[\'encryption_key\'] = ' . $enc_key . ';';
		$configFile->write($content);

		$this->resetInstance();
		$this->install = new Install;
		$output = $this->install->_uses_default_encryption_key();
		$this->assertEquals($expected, $output);
	}

	public function test_method_404()
	{
		$this->request('GET', 'welcome/method_not_exist');
		$this->assertResponseCode(404);
	}

	public function test_APPPATH()
	{
		$actual = realpath(APPPATH);
		$expected = realpath(__DIR__ . '/../../application');
		$this->assertEquals(
			$expected,
			$actual,
			'Your APPPATH seems to be wrong. Check your $application_folder in tests/Bootstrap.php'
		);
	}
}
