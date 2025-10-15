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

require_once __DIR__.'/../testutils/DBSetup.php';
require_once __DIR__.'/../testutils/KalkunTestCase.php';

use PHPUnit\Framework\Attributes\DataProvider;

class Pluginss_test extends KalkunTestCase {

	public function setUp() : void
	{
		if (file_exists(FCPATH . 'install'))
		{
			unlink(FCPATH . 'install');
		}

		self::reset_plugins_lib_static_members();
	}

	public static function reset_plugins_lib_static_members()
	{
		// Reset static members in Plugins_lib_kalkun
		// otherwise their value is not correct when we switch db_engine
		Plugins_lib_kalkun::$instance = NULL;
		Plugins_lib_kalkun::$plugins = [];
		Plugins_lib_kalkun::$enabled_plugins = [];
		Plugins_lib_kalkun::$instance = NULL;
		Plugins_lib_kalkun::$actions = [];
		Plugins_lib_kalkun::$current_action = NULL;
		Plugins_lib_kalkun::$run_actions = [];

		$reflection = new \ReflectionProperty('Plugins_lib_kalkun', 'PM');
		if ( ! is_php('8.1'))
		{
			$reflection->setAccessible(TRUE);
		}
		$reflection->setValue(NULL, NULL);

		$reflection = new \ReflectionProperty('Plugins_lib_kalkun', 'plugin_path');
		if ( ! is_php('8.1'))
		{
			$reflection->setAccessible(TRUE);
		}
		$reflection->setValue(NULL, NULL);

		$reflection = new \ReflectionProperty('Plugins_lib_kalkun', 'messages');
		if ( ! is_php('8.1'))
		{
			$reflection->setAccessible(TRUE);
		}
		$reflection->setValue(NULL, NULL);

		/*$reflection = new \ReflectionProperty('Plugins_lib_kalkun', 'CI');
		if ( ! is_php('8.1')) {
			$reflection->setAccessible(TRUE);
		}
		$reflection->setValue(NULL, NULL);*/
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

		$output = $this->request('GET', 'pluginss');
		$this->_assertStringContainsString('<div id="window_title_left">Plugins - Installed</div>', $output);
		$this->_assertStringContainsString('No plugin installed.', $output);
		$this->assertValidHtml($output);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
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
				$_SESSION['username'] = 'user1';
			}
		);

		$output = $this->request('GET', 'pluginss');
		$this->assertRedirect('/', 302);

		$expected = 'Access denied. Only administrators are allowed to manage plugins.';
		$CI_instance = & get_instance();
		$flashdata = $CI_instance->session->flashdata('notif');
		$this->assertEquals($expected, $flashdata);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_index_available($db_engine)
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

		$output = $this->request('GET', 'pluginss/index/available');
		$this->_assertStringContainsString('<div id="window_title_left">Plugins - Available</div>', $output);
		$this->_assertStringContainsString('<h3 style="color: #000">Blacklist Number</h3>', $output);
		$this->assertValidHtml($output);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_index_installed_no_plugins_present($db_engine)
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

		if ( ! file_exists(APPPATH . 'plugins.bak'))
		{
			mkdir(APPPATH . 'plugins.bak');
		}
		foreach (glob(APPPATH . 'plugins/*', GLOB_ONLYDIR) as $dir)
		{
			rename($dir, APPPATH . 'plugins.bak/' . basename($dir));
		}

		try
		{
			$output = $this->request('GET', 'pluginss/index/installed');
			$this->_assertStringContainsString('No plugin installed.', $output);
			$this->assertValidHtml($output);
		}
		catch (Exception $e)
		{
			throw $e;
		}
		finally
		{
			foreach (glob(APPPATH . 'plugins.bak/*', GLOB_ONLYDIR) as $dir)
			{
				rename($dir, APPPATH . 'plugins/' . basename($dir));
			}
			rmdir(APPPATH . 'plugins.bak');
		}
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_available_no_plugins_present($db_engine)
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

		if ( ! file_exists(APPPATH . 'plugins.bak'))
		{
			mkdir(APPPATH . 'plugins.bak');
		}
		foreach (glob(APPPATH . 'plugins/*', GLOB_ONLYDIR) as $dir)
		{
			rename($dir, APPPATH . 'plugins.bak/' . basename($dir));
		}

		try
		{
			$output = $this->request('GET', 'pluginss/index/available');
			$this->_assertStringContainsString('No plugin available.', $output);
			$this->assertValidHtml($output);
		}
		catch (Exception $e)
		{
			throw $e;
		}
		finally
		{
			foreach (glob(APPPATH . 'plugins.bak/*', GLOB_ONLYDIR) as $dir)
			{
				rename($dir, APPPATH . 'plugins/' . basename($dir));
			}
			rmdir(APPPATH . 'plugins.bak');
		}
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_plugin_controller_has_index_FALSE_because_noplugin($db_engine)
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
		$output = $this->request('GET', ['pluginss', '_plugin_controller_has_index', 'noplugin']);
		$this->assertEquals(FALSE, $output);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_plugin_controller_has_index_FALSE_because_nocontroller($db_engine)
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
		$output = $this->request('GET', ['pluginss', '_plugin_controller_has_index', 'external_script']);
		$this->assertEquals(FALSE, $output);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_plugin_controller_has_index_FALSE_because_noindex($db_engine)
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
		$output = $this->request('GET', ['pluginss', '_plugin_controller_has_index', 'jsonrpc']);
		$this->assertEquals(FALSE, $output);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_plugin_controller_has_index_TRUE($db_engine)
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
		$output = Pluginss::_plugin_controller_has_index('blacklist_number');
		$this->assertEquals(TRUE, $output);
	}

	public static function plugin_Provider()
	{
		return DBSetup::prepend_db_engine([
			['blacklist_number', 'Blacklist Number'],
			['external_script', 'External Script'],
			['jsonrpc', 'JSONRPC'],
			['phonebook_ldap', 'Phonebook LDAP'],
			['phonebook_lookup', 'Phonebook Lookup'],
			['rest_api', 'REST API'],
			['server_alert', 'Server Alert'],
			['simple_autoreply', 'Simple Autoreply'],
			['sms_credit', 'SMS Credit'],
			['sms_member', 'SMS Member'],
			['sms_to_email', 'SMS to Email'],
			['sms_to_twitter', 'SMS to Twitter'],
			['sms_to_wordpress', 'SMS to Wordpress'],
			['sms_to_xmpp', 'SMS to XMPP'],
			['soap', 'SOAP'],
			['stop_manager', 'Stop Manager'],
			['welcome', 'Welcome'],
			['whitelist_number', 'Whitelist Number'],
			['xmlrpc', 'XMLRPC'],
		]);
	}

	/**
	 * @dataProvider plugin_Provider
	 */
	#[DataProvider('plugin_Provider')]
	public function test_install($db_engine, $plugin, $plugin_label)
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

		$output = $this->request('GET', 'pluginss/install/' . $plugin);
		$this->assertRedirect('pluginss', 302);

		$output = $this->request('GET', 'pluginss/index/installed');
		$this->_assertStringContainsString('<div id="window_title_left">Plugins - Installed</div>', $output);
		$this->_assertStringContainsString('>'.$plugin_label.'</', $output);

		$output = $this->request('GET', 'pluginss/index/available');
		$this->_assertStringContainsString('<div id="window_title_left">Plugins - Available</div>', $output);
		$this->assertThat($output, $this->logicalNot($this->stringContains('<h3 style="color: #000">'.$plugin_label.'</h3>')));
	}

	/**
	 * @dataProvider plugin_Provider
	 */
	#[DataProvider('plugin_Provider')]
	public function test_install_then_uninstall($db_engine, $plugin, $plugin_label)
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
		$output = $this->request('GET', 'pluginss/install/' . $plugin);
		$this->assertRedirect('pluginss', 302);

		$output = $this->request('GET', 'pluginss/index/installed');
		$this->_assertStringContainsString('<div id="window_title_left">Plugins - Installed</div>', $output);
		$this->_assertStringContainsString('>'.$plugin_label.'</', $output);

		self::reset_plugins_lib_static_members(); //otherwise uninstall wouldn't work because the plugin would be considered as not installed.

		$output = $this->request('GET', 'pluginss/uninstall/' . $plugin);
		$this->assertRedirect('pluginss', 302);

		$output = $this->request('GET', 'pluginss/index/installed');
		$this->_assertStringContainsString('<div id="window_title_left">Plugins - Installed</div>', $output);
		$this->assertThat($output, $this->logicalNot($this->stringContains('>'.$plugin_label.'</')));

		$output = $this->request('GET', 'pluginss/index/available');
		$this->_assertStringContainsString('<div id="window_title_left">Plugins - Available</div>', $output);
		$this->_assertStringContainsString('<h3 style="color: #000">'.$plugin_label.'</h3>', $output);
	}

	/**
	 * @dataProvider plugin_Provider
	 */
	#[DataProvider('plugin_Provider')]
	public function test_uninstall_without_install($db_engine, $plugin, $plugin_label)
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

		$output = $this->request('GET', 'pluginss/uninstall/' . $plugin);
		$this->assertRedirect('pluginss', 302);

		$output = $this->request('GET', 'pluginss/index/installed');
		$this->_assertStringContainsString('<div id="window_title_left">Plugins - Installed</div>', $output);
		$this->assertThat($output, $this->logicalNot($this->stringContains('>'.$plugin_label.'</')));

		$output = $this->request('GET', 'pluginss/index/available');
		$this->_assertStringContainsString('<div id="window_title_left">Plugins - Available</div>', $output);
		$this->_assertStringContainsString('<h3 style="color: #000">'.$plugin_label.'</h3>', $output);
	}

	/**
	 * @dataProvider plugin_Provider
	 */
	#[DataProvider('plugin_Provider')]
	public function test_install_then_remove_dir($db_engine, $plugin, $plugin_label)
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

		$output = $this->request('GET', 'pluginss/install/' . $plugin);
		$this->assertRedirect('pluginss', 302);

		self::reset_plugins_lib_static_members(); //otherwise uninstall wouldn't work because the plugin would be considered as not installed.

		$output = $this->request('GET', 'pluginss/index/installed');
		$this->_assertStringContainsString('<div id="window_title_left">Plugins - Installed</div>', $output);
		$this->_assertStringContainsString('>' . $plugin_label . '</', $output);

		$output = $this->request('GET', 'pluginss/index/available');
		$this->_assertStringContainsString('<div id="window_title_left">Plugins - Available</div>', $output);
		$this->assertThat($output, $this->logicalNot($this->stringContains('<h3 style="color: #000">' . $plugin_label . '</h3>')));

		self::reset_plugins_lib_static_members(); //otherwise the lib static members wouldn't reflect the current state of the plugins on filesystem

		if ( ! file_exists(APPPATH . 'plugins.bak'))
		{
			mkdir(APPPATH . 'plugins.bak');
		}
		rename(APPPATH . 'plugins/' . $plugin, APPPATH . 'plugins.bak/' . $plugin);

		try
		{
			// Check that the plugin is not listed in the installed plugins
			$output = $this->request('GET', 'pluginss/index/installed');
			$this->_assertStringContainsString('No plugin installed.', $output);

			// Check that the plugin is not listed in the available plugins
			$output = $this->request('GET', 'pluginss/index/available');
			$this->assertThat($output, $this->logicalNot($this->stringContains('<h3 style="color: #000">' . $plugin_label . '</h3>')));
		}
		catch (Exception $e)
		{
			throw $e;
		}
		finally
		{
			rename(APPPATH . 'plugins.bak/' . $plugin, APPPATH . 'plugins/' . $plugin);
			rmdir(APPPATH . 'plugins.bak');
		}
	}
}
