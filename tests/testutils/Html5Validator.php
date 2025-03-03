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

class Html5Validator extends HtmlValidator {

	private $VNU_JAR = FCPATH . '/utils/vnu.jar';
	private $JAVA_PATH = '/usr/bin/java';
	private static $vnu_type = NULL;

	protected $OUTPUT_DIR = '/tmp/kalkun_html5_test_output';
	protected $doctype = '<!DOCTYPE html>';

	public function run_validator($html)
	{
		if (self::$vnu_type === NULL)
		{
			set_error_handler(function ($errno, $errstr, $errfile, $errline) {
				if (E_WARNING === $errno)
				{
					return;
				}
			});
			$resource = fsockopen('localhost', $port = 8888, $error_code, $error_message, 1);
			restore_error_handler();

			if (is_resource($resource))
			{
				fclose($resource);
				self::$vnu_type = 'server';
			}
			else
			{
				echo "v.Nu HTTP server not running at http://localhost:8888. Validating by command line (which is slower). Consider starting the v.Nu HTTP server.\n";
				self::$vnu_type = 'command_line';
			}
		}
		if (self::$vnu_type === 'server')
		{
			return $this->run_validator_as_http_client($html);
		}
		else
		{
			return $this->run_validator_as_standalone_command_line_client($html);
		}
	}

	private function run_validator_as_standalone_command_line_client($html)
	{
		$descriptors = array(
			0 => array('pipe', 'r'), // STDIN
			1 => array('pipe', 'w'), // STDOUT
			2 => array('pipe', 'w')   // STDERR
		);

		if ( ! file_exists($this->VNU_JAR))
		{
			$this->testcase->markTestSkipped("nu validator (vnu.jar) not found at {$this->VNU_JAR}");
		}

		if ( ! file_exists($this->JAVA_PATH))
		{
			$this->testcase->markTestSkipped("java not found at {$this->JAVA_PATH}");
		}

		$command = escapeshellcmd(
			escapeshellarg($this->JAVA_PATH)
				. ' -jar '
				. escapeshellarg($this->VNU_JAR)
				. ' --errors-only -'
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

	private function run_validator_as_http_client($html)
	{
		$descriptors = array(
			0 => array('pipe', 'r'), // STDIN
			1 => array('pipe', 'w'), // STDOUT
			2 => array('pipe', 'w')   // STDERR
		);

		if ( ! file_exists($this->VNU_JAR))
		{
			$this->testcase->markTestSkipped("v.Nu validator (vnu.jar) not found at {$this->VNU_JAR}");
		}

		if ( ! file_exists($this->JAVA_PATH))
		{
			$this->testcase->markTestSkipped("java not found at {$this->JAVA_PATH}");
		}

		$command = escapeshellcmd(
			escapeshellarg($this->JAVA_PATH)
				. ' -Dnu.validator.client.level=error'
				. ' -Dnu.validator.client.charset=utf-8'
				. ' -cp '
				. escapeshellarg($this->VNU_JAR)
				. ' nu.validator.client.HttpClient'
				. ' -'
		);

		$html = str_replace('<html>', '<html xmlns="http://www.w3.org/1999/xhtml">', $html);
		$proc = proc_open($command, $descriptors, $pipes);
		fwrite($pipes[0], $html);
		fclose($pipes[0]);

		$stdout = stream_get_contents($pipes[1]);
		$stderr = stream_get_contents($pipes[2]);

		fclose($pipes[1]);
		fclose($pipes[2]);

		$exitCode = proc_close($proc);

		return $stdout;
	}
}
