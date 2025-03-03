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

class HtmlValidator {

	protected $testcase;
	protected $wrap_snippet = FALSE;

	public function __construct($testcase)
	{
		$this->testcase = $testcase;
	}

	public function wrap_snippet()
	{
		$this->wrap_snippet = TRUE;
		return $this;
	}

	public function store_input($html)
	{
		$filename = $this->get_filename($this->testcase->request_url);
		$dir = dirname($filename);
		if ( ! file_exists($dir))
		{
			mkdir($dir, 0777, TRUE);
		}
		file_put_contents($filename, $html);

		return $filename;
	}

	public function store_output($output)
	{
		$dir = dirname($this->testcase->request_url);
		if ( ! file_exists($this->OUTPUT_DIR.'/'.$dir))
		{
			mkdir($this->OUTPUT_DIR.'/'.$dir, 0777, TRUE);
		}
		$filename = $this->get_filename($this->testcase->request_url) . '.txt';
		file_put_contents($filename, $output);

		return $filename;
	}

	public function get_filename()
	{
		$query = ($this->testcase->request_params !== NULL) ? '=' . $this->testcase->request_params : '';
		$testname = '_' . $this->testcase->_nameWithDataSet();
		$filename = $this->OUTPUT_DIR
				. '/'
				. $this->testcase->request_url
				. $query
				. $testname
				. '.html';
		return $filename;
	}

	public function validate($html)
	{
		if ($this->wrap_snippet === TRUE)
		{
			$html = $this->doctype . '<html><head><title>dummy</title></head><body>' . $html . '</body></html>';
		}

		$filename = $this->get_filename($this->testcase->request_url);
		// If file exists and hash is same as $html, skip.
		if (file_exists($filename) && file_exists($filename.'.txt'))
		{
			if (filesize($filename.'.txt') === 0 && sha1_file($filename) === sha1($html))
			{
				return;
			}
		}

		$this->store_input($html);
		$result = $this->run_validator($html);
		$this->store_output($result);
		$this->testcase->assertEmpty($result, $result . 'HTML is at: ' . $filename);
	}
}
