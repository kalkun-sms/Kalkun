<?php
/**
 * Part of ci-phpunit-test
 *
 * @author     Kenji Suzuki <https://github.com/kenjis>
 * @license    MIT License
 * @copyright  2015 Kenji Suzuki
 * @link       https://github.com/kenjis/ci-phpunit-test
 */

class Install_test extends TestCase
{
	private $realAssertStringContainsString;

	public function setUp() : void
	{
		// Using assertContains() with string haystacks is deprecated and will not be supported in PHPUnit 9
		// Refactor your test to use assertStringContainsString() or assertStringContainsStringIgnoringCase() instead.
		$this->realAssertStringContainsString = method_exists($this, 'assertStringContainsString')
			? 'assertStringContainsString'
			: 'assertContains';
	}

	public function test_index()
	{
		$output = $this->request('GET', 'install');
		call_user_func_array(array($this, $this->realAssertStringContainsString), array('<title>Kalkun &rsaquo; Installation</title>', $output));
	}

	public function test_method_404()
	{
		$this->request('GET', 'welcome/method_not_exist');
		$this->assertResponseCode(404);
	}

	public function test_APPPATH()
	{
		$actual = realpath(APPPATH);
		$expected = realpath(__DIR__ . '/../..');
		$this->assertEquals(
			$expected,
			$actual,
			'Your APPPATH seems to be wrong. Check your $application_folder in tests/Bootstrap.php'
		);
	}
}
