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

require_once 'HtmlValidator.php';

class Html4Validator extends HtmlValidator {

	private $TIDY = '/usr/bin/tidy';

	protected $OUTPUT_DIR = '/tmp/kalkun_html4_test_output';
	protected $doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';

	public function run_validator($html)
	{
		$descriptors = array(
			0 => array('pipe', 'r'), // STDIN
			1 => array('pipe', 'w'), // STDOUT
			2 => array('pipe', 'w')   // STDERR
		);

		if ( ! file_exists($this->TIDY))
		{
			$this->testcase->markTestSkipped("tidy not found at {$this->VNU_JAR}");
		}

		$command = escapeshellcmd(
			escapeshellarg($this->TIDY)
				. ' --show-warnings no'
				. ' -errors'
				. ' -q'
		);

		$proc = proc_open($command, $descriptors, $pipes);
		fwrite($pipes[0], $html);
		fclose($pipes[0]);

		$stdout = stream_get_contents($pipes[1]);
		$stderr = stream_get_contents($pipes[2]);

		fclose($pipes[1]);
		fclose($pipes[2]);

		$exitCode = proc_close($proc);

		return $stderr;
	}
}
