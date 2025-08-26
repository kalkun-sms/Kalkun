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

class Phonebook_test extends KalkunTestCase {

	public function setUp() : void
	{
		if (file_exists(FCPATH . 'install'))
		{
			unlink(FCPATH . 'install');
		}
		DBSetup::setup_db_kalkun_testing2($this);
	}

	public function reloadCIwithEngine($db_engine)
	{
		DBSetup::$current_setup[$db_engine]->write_config_file_for_database();
	}

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
		$this->reloadCIwithEngine($db_engine);

		$this->request->setCallablePreConstructor(
			function () {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '2';
				$_SESSION['level'] = 'user';
				$_SESSION['username'] = 'user1';
			}
		);

		$output = $this->request('GET', 'phonebook/index');
		$this->assertValidHtml($output);
		$this->markTestIncomplete();
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_index_search_name($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->request->setCallablePreConstructor(
			function () {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '2';
				$_SESSION['level'] = 'user';
				$_SESSION['username'] = 'user1';
			}
		);

		$output = $this->request('POST', 'phonebook/index', ['search_name' => 'Contact for number 001']);
		$this->assertValidHtml($output);
		$this->markTestIncomplete();
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_index_public($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->request->setCallablePreConstructor(
			function () {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '2';
				$_SESSION['level'] = 'user';
				$_SESSION['username'] = 'user1';
			}
		);

		$output = $this->request('GET', 'phonebook/index/public');
		$this->assertValidHtml($output);
		$this->markTestIncomplete();
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_group($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->request->setCallablePreConstructor(
			function () {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '2';
				$_SESSION['level'] = 'user';
				$_SESSION['username'] = 'user1';
			}
		);

		$output = $this->request('GET', 'phonebook/group');
		$this->assertValidHtml($output);
		$this->markTestIncomplete();
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_group_public($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->request->setCallablePreConstructor(
			function () {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '2';
				$_SESSION['level'] = 'user';
				$_SESSION['username'] = 'user1';
			}
		);

		$output = $this->request('GET', 'phonebook/group/public');
		$this->assertValidHtml($output);
		$this->markTestIncomplete();
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_group_contacts($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->request->setCallablePreConstructor(
			function () {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '2';
				$_SESSION['level'] = 'user';
				$_SESSION['username'] = 'user1';
			}
		);

		$output = $this->request('GET', 'phonebook/group_contacts/1');
		$this->assertValidHtml($output);
		$this->markTestIncomplete();
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_add_contact($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->request->setCallablePreConstructor(
			function () {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '2';
				$_SESSION['level'] = 'user';
				$_SESSION['username'] = 'user1';
			}
		);

		$output = $this->request('GET', 'phonebook/add_contact');
		$this->assertValidHtmlSnippet($output);
		$this->markTestIncomplete();
	}
}
