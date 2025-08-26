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

class Sms_credit_test extends KalkunTestCase {

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

		$dbsetup->install_plugin($this, 'sms_credit');
		Pluginss_test::reset_plugins_lib_static_members();

		$output = $this->request('GET', 'plugin/sms_credit');
		$this->assertValidHtml($output);
		$this->markTestIncomplete();
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_packages($db_engine)
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

		$dbsetup->install_plugin($this, 'sms_credit');
		Pluginss_test::reset_plugins_lib_static_members();

		$output = $this->request('GET', 'plugin/sms_credit/packages');
		$this->assertValidHtml($output);
		$this->markTestIncomplete();
	}
}
