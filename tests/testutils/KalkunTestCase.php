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

require_once __DIR__.'/../testutils/Html4Validator.php';
require_once __DIR__.'/../testutils/Html5Validator.php';

class KalkunTestCase extends TestCase {

	protected static $phpunit_version;
	public $request_http_method;
	public $request_url;
	public $request_params;
	public $request_is_ajax = FALSE;
	public static $html_version = NULL;

	public static function setUpBeforeClass() : void
	{
		// \PHPUnit\Runner\Version exists since 6.x, before there was PHPUnit_Runner_Version
		self::$phpunit_version = class_exists('\PHPUnit\Runner\Version') ? \PHPUnit\Runner\Version::id() : PHPUnit_Runner_Version::id();
	}

	public final function _assertStringContainsString($expected, $actual)
	{
		if (version_compare(self::$phpunit_version, '7.5', '>=') === TRUE)
		{
			$this->assertStringContainsString($expected, $actual);
		}
		else
		{
			$this->assertContains($expected, $actual);
		}
	}

	public final function _expectExceptionMessageMatches($regularExpression)
	{
		if (version_compare(self::$phpunit_version, '8.4', '>=') === TRUE)
		{
			$this->expectExceptionMessageMatches($regularExpression);
		}
		else
		{
			$this->expectExceptionMessageRegExp($regularExpression);
		}
	}

	public final function _assertMatchesRegularExpression($pattern, $string)
	{
		if (version_compare(self::$phpunit_version, '9.1', '>=') === TRUE)
		{
			$this->assertMatchesRegularExpression($pattern, $string);
		}
		else
		{
			$this->assertRegExp($pattern, $string);
		}
	}

	public function assertDateEqualsWithDelta($expected, $actual, $delta = 1)
	{
		// When date contains "/" like in DD/MM/YYYY strtotime actually interprets as MM/DD/YYYY
		// However, DD.MM.YYYY is interpreted as DD.MM.YYYY
		// See https://www.php.net/manual/en/datetime.formats.php#datetime.formats.date
		$expected_ts = strtotime(str_replace('/', '.', $expected));
		$actual_ts = strtotime(str_replace('/', '.', $actual));
		if (version_compare(self::$phpunit_version, '7.5', '>=') === TRUE)
		{
			$this->assertEqualsWithDelta(
				$expected_ts,
				$actual_ts,
				$delta,
				'Failed asserting that ' . $actual . ' matches expected ' . $expected . '.'
			);
		}
		else
		{
			$this->assertEquals(
				$expected_ts,
				$actual_ts,
				'Failed asserting that ' . $actual . ' matches expected ' . $expected . '.',
				$delta
			);
		}
	}

	public function crawler_text($crawler)
	{
		// v3.4 doesn't consider arguments, and doesn't normalize spaces.
		// v4.4 triggers an exception if there is no argument. It accepts arguments,
		// although the profile of the function doesn't mention them. Setting the 2nd argument
		// to TRUE will normalize white spaces and avoid the exception.
		$method = new ReflectionMethod('Symfony\Component\DomCrawler\Crawler', 'text');
		$params = $method->getParameters();
		if (count($params) === 0)
		{
			// This is version < 5.0. We apply ourselves the normalization of whitespaces
			return trim(preg_replace('/(?:\s{2,}+|[^\S ])/', ' ', $crawler->text(NULL, TRUE)));
		}
		return $crawler->text();
	}

	public function _nameWithDataSet()
	{
		if (version_compare(self::$phpunit_version, '10.0', '>=') === TRUE)
		{
			return parent::nameWithDataSet();
		}
		else
		{
			return $this->getName(TRUE);
		}
	}

	public function request($http_method, $argv, $params = [])
	{
		$this->request_http_method = $http_method;

		if (is_string($argv))
		{
			$url = ltrim($argv, '/');
		}
		else
		{
			$url = implode('/', $argv);
		}

		$this->request_url = $url;
		$this->request_params = (is_string($params)) ? $params : NULL;

		return parent::request($http_method, $argv, $params);
	}

	public function ajaxRequest($http_method, $argv, $params = [])
	{
		$this->ajax = TRUE;
		return parent::ajaxRequest($http_method, $argv, $params);
	}

	public function assertValidHtml($html)
	{
		if (self::$html_version === NULL)
		{
			self::$html_version = getenv('HTML');
		}
		switch (self::$html_version)
		{
			case '4':
				$validator = new Html4Validator($this);
				break;
			case '5':
				$validator = new Html5Validator($this);
				break;
			case FALSE:
			default:
				return;
		}
		if ($this->request_is_ajax)
		{
			$validator->wrap_snippet();
		}
		$validator->validate($html);
	}

	public function assertValidHtmlSnippet($html)
	{
		if (self::$html_version === NULL)
		{
			self::$html_version = getenv('HTML');
		}
		switch (self::$html_version)
		{
			case '4':
				$validator = new Html4Validator($this);
				break;
			case '5':
				$validator = new Html5Validator($this);
				break;
			case FALSE:
			default:
				return;
		}
		$validator
				->wrap_snippet()
				->validate($html);
	}
}
