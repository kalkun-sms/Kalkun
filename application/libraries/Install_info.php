<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Kalkun
 * An open source web based SMS Manager
 *
 * @copyright 2024 Fab Stz
 * @author Fab Stz <fabstz-it@yahoo.fr>
 * @license <https://spdx.org/licenses/GPL-2.0-or-later.html> GPL-2.0-or-later
 * @link https://kalkun.sourceforge.io/
 */

class Install_info {

	public function get_CI_ENV()
	{
		return isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : '';
	}

	public function get_htaccess_CI_ENV_path()
	{
		$htaccess_paths = [
			FCPATH.'.htaccess',
			'/etc/apache2/conf-available/kalkun.conf',
			'/etc/apache2/apache2.conf'
		];

		foreach ($htaccess_paths as $path)
		{
			if ( ! is_readable($path))
			{
				continue;
			}

			$contents = file($path);

			if ($contents === FALSE)
			{
				continue;
			}

			if ( ! $this->contains_CI_ENV($contents))
			{
				continue;
			}

			return realpath($path);
		}

		return '';
	}

	public function contains_CI_ENV($contents)
	{
		$CI_ENV = $this->get_CI_ENV();
		if ($CI_ENV === '')
		{
			$pattern = '^\s*((?i)SetEnv)\s+CI_ENV$';
		}
		else
		{
			$pattern = '^\s*((?i)SetEnv)\s+CI_ENV\s+' . $CI_ENV . '\s*$';
		}
		$matches = preg_grep('/' . $pattern . '/', $contents);

		if ($matches === FALSE || count($matches) === 0)
		{
			return FALSE;
		}

		return TRUE;
	}

	public function get_daemon_path($file)
	{
		if ($this->is_windows())
		{
			$extension = '.bat';
		}
		else
		{
			$extension = '.sh';
		}

		$daemon_path = [
			FCPATH.'scripts/'.$file.$extension,
			FCPATH.'../scripts/'.$file.$extension
		];

		foreach ($daemon_path as $path)
		{
			if ( ! is_readable($path))
			{
				continue;
			}
			return realpath($path);
		}

		return FALSE;
	}

	public function get_daemon_var_path($file, $var)
	{
		$path = $this->get_daemon_path($file);

		if ( ! $path)
		{
			return FALSE;
		}

		if ( ! is_readable($path))
		{
			return FALSE;
		}

		$contents = file_get_contents($path);

		if ($contents === FALSE)
		{
			return FALSE;
		}

		$pattern = '/\b'.$var.'=(.*)(?:\s#.*)?$/mU';
		$ret = preg_match($pattern, $contents, $matches);

		if ($ret === FALSE || count($matches) === 0)
		{
			return FALSE;
		}

		return trim(trim($matches[1]), '"');
	}


	public function daemon_var_path_exists($path)
	{
		if ($path === FALSE)
		{
			return FALSE;
		}

		if (is_readable($path))
		{
			return TRUE;
		}

		return FALSE;
	}

	public function get_daemon_url($path)
	{
		if ( ! $path)
		{
			return FALSE;
		}

		if ( ! is_readable($path))
		{
			return FALSE;
		}

		$contents = file_get_contents($path);

		if ($contents === FALSE)
		{
			return FALSE;
		}

		$pattern = '/^\s*\$url\s*=\s*[\'"](.*)[\'"].*$/mU';
		$ret = preg_match($pattern, $contents, $matches);

		if ($ret === FALSE || count($matches) === 0)
		{
			return FALSE;
		}

		return trim(trim($matches[1]), '"');
	}

	public function is_windows()
	{
		return strcasecmp(substr(PHP_OS, 0, 3), 'WIN') === 0;
	}
}
