<?php
/**
 * Kalkun
 * An open source web based SMS Management
 *
 * @package		Kalkun
 * @author		Kalkun Dev Team
 * @license		https://spdx.org/licenses/GPL-2.0-or-later.html
 * @link		https://kalkun.sourceforge.io/
 */

require_once __DIR__.'/TestSuiteFile.php';

class GammuSmsdConfigFile extends TestSuiteFile {

	private $config = [];

	public function __construct($path, $dbsetup)
	{
		parent::__construct($path);

		$this->addItem('gammu', 'model', 'dummy');
		$this->addItem('gammu', 'connection', 'none');
		$this->addItem('gammu', 'device', dirname($path) . '/gammu-dummy');
		if ( ! file_exists(dirname($path) . '/gammu-dummy'))
		{
			mkdir(dirname($path) . '/gammu-dummy', 0777);
			touch(dirname($path) . '/gammu-dummy'.'/.remove_dir_when_finished');
		}

		if ($dbsetup !== NULL)
		{
			$this->addItem('smsd', 'service', 'SQL');
			$this->addItem('smsd', 'user', $dbsetup->get_user());
			$this->addItem('smsd', 'password', $dbsetup->get_password());
			$this->addItem('smsd', 'host', 'localhost');
			switch ($dbsetup->get_engine())
		{
			case 'mysql':
				$this->addItem('smsd', 'driver', 'native_mysql');
				$this->addItem('smsd', 'database', $dbsetup->get_db_name());
				break;
			case 'pgsql':
				$this->addItem('smsd', 'driver', 'native_pgsql');
				$this->addItem('smsd', 'database', $dbsetup->get_db_name());
				break;
			case 'sqlite':
				$this->addItem('smsd', 'driver', 'sqlite3');
				$this->addItem('smsd', 'database', basename($dbsetup->get_db_path()));
				$this->addItem('smsd', 'DBDir', dirname($dbsetup->get_db_path()));
				break;
			default:
				break;
		}
		}
		else
		{
			$this->addItem('smsd', 'service', 'NULL');
		}
	}

	public function __destruct()
	{
		if (file_exists(dirname($this->path) . '/gammu-dummy'.'/.remove_dir_when_finished'))
		{
			unlink (dirname($this->path) . '/gammu-dummy'.'/.remove_dir_when_finished');
			$isDirEmpty = ! (new \FilesystemIterator(dirname($this->path) . '/gammu-dummy'))->valid();
			if ($isDirEmpty)
			{
				rmdir(dirname($this->path) . '/gammu-dummy');
			}
			else
			{
				touch(dirname($this->path) . '/gammu-dummy'.'/.remove_dir_when_finished');
			}
		}

		parent::__destruct();
	}

	public function addItem($section, $key, $value)
	{
		$this->config[$section][$key] = $value;
	}

	public function get_content()
	{
		ksort($this->config);
		$content = '';
		foreach (array_keys($this->config) as $section)
		{
			$content .= '[' . $section . ']' . "\n";
			foreach ($this->config[$section] as $key => $val)
			{
				$content .= $key . ' = ' . $val . "\n";
			}
		}
		return $content;
	}
}
