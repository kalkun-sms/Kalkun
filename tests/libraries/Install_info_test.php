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

require_once APPPATH . 'libraries/Install_info.php';
require_once __DIR__.'/../testutils/KalkunTestCase.php';

use PHPUnit\Framework\Attributes\DataProvider;

class Install_info_test extends KalkunTestCase {

	private $install_info;

	public function setUp() : void
	{
		parent::setUp();
	}

	public static function get_daemon_path_Provider()
	{
		return [
			['daemon', TRUE, '.bat'],
			['outbox_queue', TRUE, '.bat'],
			['daemon', FALSE, '.sh'],
			['outbox_queue', FALSE, '.sh'],
		];
	}

	/**
	 * @dataProvider get_daemon_path_Provider
	 */
	#[DataProvider('get_daemon_path_Provider')]
	public function test_get_daemon_path($file, $is_windows, $extension)
	{
		// Create a stub for the SomeClass class.
		if (version_compare(self::$phpunit_version, '8.3', '>=') === TRUE)
		{
			$stub = $this->getMockBuilder(Install_info::class)
				->onlyMethods(['is_windows']) // use setMethods with older phpunit
				->getMock();
		}
		else
		{
			$stub = $this->getMockBuilder(Install_info::class)
				->setMethods(['is_windows']) // use setMethods with older phpunit
				->getMock();
		}

		// Configure the stub.
		$stub->method('is_windows')
				->willReturn($is_windows);

		$output = $stub->get_daemon_path($file);
		$this->assertEquals(FCPATH . 'scripts/' . $file . $extension, $output);
	}

	public static function get_daemon_url_Provider()
	{
		return [
			[FCPATH . 'scripts/outbox_queue.php', 'http://localhost/kalkun'],
			[FCPATH . 'scripts/daemon.php', 'http://localhost/kalkun'],
			[FCPATH . 'scripts/daemon.bat', FALSE],
			[FCPATH . 'scripts/daemon.sh', FALSE],
			[FCPATH . 'scripts/missing_file.bat', FALSE],
			[NULL, FALSE],
		];
	}

	/**
	 * @dataProvider get_daemon_url_Provider
	 */
	#[DataProvider('get_daemon_url_Provider')]
	public function test_get_daemon_url($path, $expected)
	{
		$this->install_info = new Install_info();
		$output = $this->install_info->get_daemon_url($path);
		$this->assertEquals($expected, $output);
	}

	public static function get_daemon_var_path_Provider()
	{
		return [
			[FCPATH . 'scripts/outbox_queue.sh', 'DAEMON', '/path/to/kalkun/scripts/outbox_queue.php'],
			[FCPATH . 'scripts/outbox_queue.bat', 'DAEMON', 'C:\kalkun\scripts\outbox_queue.php'],
			[FCPATH . 'scripts/outbox_queue.sh', 'PHP', '/usr/bin/php'],
			[FCPATH . 'scripts/outbox_queue.bat', 'PHP', 'C:\php\php.exe'],
			[FCPATH . 'scripts/daemon.sh', 'DAEMON', '/path/to/kalkun/scripts/daemon.php'],
			[FCPATH . 'scripts/daemon.bat', 'DAEMON', 'C:\kalkun\scripts\daemon.php'],
			[FCPATH . 'scripts/daemon.sh', 'PHP', '/usr/bin/php'],
			[FCPATH . 'scripts/daemon.bat', 'PHP', 'C:\php\php.exe'],
			[FCPATH . 'scripts/daemon.bat', 'INVALID_VAR', FALSE],
			[FCPATH . 'scripts/missing_file.bat', 'PHP', FALSE],
			[NULL, 'PHP', FALSE],
		];
	}

	/**
	 * @dataProvider get_daemon_var_path_Provider
	 */
	#[DataProvider('get_daemon_var_path_Provider')]
	public function test_get_daemon_var_path($file, $var, $expected)
	{
		// Create a stub for the SomeClass class.
		if (version_compare(self::$phpunit_version, '8.3', '>=') === TRUE)
		{
			$stub = $this->getMockBuilder(Install_info::class)
				->onlyMethods(['get_daemon_path']) // use setMethods with older phpunit
				->getMock();
		}
		else
		{
			$stub = $this->getMockBuilder(Install_info::class)
				->setMethods(['get_daemon_path']) // use setMethods with older phpunit
				->getMock();
		}

		// Configure the stub.
		$stub->method('get_daemon_path')
				->willReturn($file);

		$output = $stub->get_daemon_var_path($file, $var);
		$this->assertEquals($expected, $output);
	}

	public static function contains_CI_ENV_Provider()
	{
		return [
			['', 'invalid data', FALSE],
			[NULL, 'invalid data', FALSE],
			['', 'SetEnv CI_ENV development', FALSE],
			['development', 'SetEnv CI_ENV development', TRUE],
			['testing', 'SetEnv CI_ENV testing', TRUE],
			['testing', 'SetEnv CI_ENV development', FALSE],
			['development', ' #SetEnv CI_ENV development', FALSE],
			['development', '# SetEnv CI_ENV development', FALSE],
			['development', ' SetEnv   CI_ENV  development  ', TRUE],
			['', '	 	SetEnv   CI_ENV', TRUE],
		];
	}

	/**
	 * @dataProvider contains_CI_ENV_Provider
	 */
	#[DataProvider('contains_CI_ENV_Provider')]
	public function test_contains_CI_ENV($CI_ENV, $data, $expected)
	{
		// Create a stub for the SomeClass class.
		if (version_compare(self::$phpunit_version, '8.3', '>=') === TRUE)
		{
			$stub = $this->getMockBuilder(Install_info::class)
				->onlyMethods(['get_CI_ENV']) // use setMethods with older phpunit
				->getMock();
		}
		else
		{
			$stub = $this->getMockBuilder(Install_info::class)
				->setMethods(['get_CI_ENV']) // use setMethods with older phpunit
				->getMock();
		}

		// Configure the stub.
		$stub->method('get_CI_ENV')
				->willReturn($CI_ENV);

		$output = $stub->contains_CI_ENV([$data]);
		$this->assertEquals($expected, $output);
	}
}
