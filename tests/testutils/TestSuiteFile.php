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

class TestSuiteFile {

	private	$id;
	protected $path;
	private $dot_file_path;
	private $remove_dir_when_finished = FALSE;

	public function __construct($path)
	{
		$this->path = $path;
		$this->id = uniqid('', TRUE);
		$this->dot_file_path = dirname($this->path).'/.'.basename($this->path);
	}

	public function __destruct()
	{
		if (file_exists($this->dot_file_path))
		{
			$dot_file_content = file_get_contents($this->dot_file_path);
			if ($dot_file_content === $this->id)
			{
				unlink($this->dot_file_path);
				if (file_exists($this->path))
				{
					unlink($this->path);
				}
			}
		}

		if (file_exists(dirname($this->path).'/.remove_dir_when_finished'))
		{
			unlink (dirname($this->path).'/.remove_dir_when_finished');
			$isDirEmpty = ! (new \FilesystemIterator(dirname($this->path)))->valid();
			if ($isDirEmpty)
			{
				rmdir(dirname($this->path));
			}
			else
			{
				touch(dirname($this->path).'/.remove_dir_when_finished');
			}
		}
	}

	public function write($content)
	{
		if ( ! file_exists(dirname($this->path)))
		{
			mkdir(dirname($this->path));
			touch(dirname($this->path).'/.remove_dir_when_finished');
			//$this->remove_dir_when_finished = TRUE;
		}
		if (file_exists($this->path))
		{
			unlink($this->path);
		}
		if (file_exists($this->dot_file_path))
		{
			unlink($this->dot_file_path);
		}

		file_put_contents($this->dot_file_path, $this->id);
		file_put_contents($this->path, $content);
	}
}
