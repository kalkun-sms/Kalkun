<?php
/**
 * Kalkun
 * An open source web based SMS Manager
 *
 * @copyright 2025 Fab Stz
 * @author Fab Stz <fabstz-it@yahoo.fr>
 * @license <https://spdx.org/licenses/GPL-2.0-or-later.html> GPL-2.0-or-later
 * @link https://kalkun.sourceforge.io/
 */

require_once __DIR__.'/../testutils/ConfigFile.php';
require_once __DIR__.'/../testutils/DBSetup.php';
require_once __DIR__.'/../testutils/KalkunTestCase.php';

use PHPUnit\Framework\Attributes\DataProvider;

class Messages_test extends KalkunTestCase {

	public function setUp() : void
	{
		if (file_exists(FCPATH . 'install'))
		{
			unlink(FCPATH . 'install');
		}
	}

	public static function database_Provider()
	{
		return DBSetup::$db_engines_to_test;
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_report_spam_POST_spam($db_engine)
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

		$this->request->addCallable($dbsetup->insert('inbox')->closure());

		$data = $this->request('POST', 'messages/report_spam/spam', ['id_message' => '1']);
		$this->assertEmpty($data);

		$result = $this->CI->db
				->where('ID', '1')
				->get('inbox');
		$this->assertEquals(6, $result->row()->id_folder);

		$word = '<(Mono)...>'; // Any word that is only once in the textmessage
		$result = $this->CI->db
				->where('token', $word)
				->get('b8_wordlist');
		$this->assertEquals(1, $result->row()->count_spam);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_report_spam_POST_missing_id($db_engine)
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

		$this->request->addCallable($dbsetup->insert('inbox')->closure());

		$data = $this->request('POST', 'messages/report_spam/spam', ['not_id_message' => '1']);
		$this->assertResponseCode(400);
		$this->assertEquals('Something is wrong with the request.', $data);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_report_spam_POST_jam($db_engine)
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

		$this->request->addCallable($dbsetup->insert('inbox')->closure());

		$data = $this->request('POST', 'messages/report_spam/jam', ['id_message' => '1']);
		$this->assertResponseCode(400);
		$this->assertEquals('Invalid Type', $data);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_report_spam_POST_ham($db_engine)
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

		// Insert message to spam folder
		$this->request->addCallable($dbsetup->insert('inbox', ['id_folder' => 6])->closure());

		$data = $this->request('POST', 'messages/report_spam/ham', ['id_message' => '1']);
		$this->assertEmpty($data);

		$result = $this->CI->db
				->where('ID', '1')
				->get('inbox');
		$this->assertEquals(1, $result->row()->id_folder);

		$word = '<(Mono)...>'; // Any word that is only once in the textmessage
		$result = $this->CI->db
				->where('token', $word)
				->get('b8_wordlist');
		$this->assertEquals(1, $result->row()->count_ham);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_report_spam_POST_spam_then_ham($db_engine)
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

		$this->request->addCallable($dbsetup->insert('inbox')->closure());

		$data = $this->request('POST', 'messages/report_spam/spam', ['id_message' => '1']);

		$result = $this->CI->db
				->where('ID', '1')
				->get('inbox');
		$this->assertEquals(6, $result->row()->id_folder);

		// Reset callables
		$this->request->setCallable(function() {
		});
		$data = $this->request('POST', 'messages/report_spam/ham', ['id_message' => '1']);

		$this->assertEmpty($data);

		$result = $this->CI->db
				->where('ID', '1')
				->get('inbox');
		$this->assertEquals(1, $result->row()->id_folder);

		$word = '<(Mono)...>'; // Any word that is only once in the textmessage
		$result = $this->CI->db
				->where('token', $word)
				->get('b8_wordlist');
		$this->assertEquals(1, $result->row()->count_ham);
		$result = $this->CI->db
				->where('token', $word)
				->get('b8_wordlist');
		$this->assertEquals(1, $result->row()->count_spam);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_query_GET($db_engine)
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

		$data = $this->request('GET', 'messages/query');
		$this->assertResponseCode(400);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_query_POST_basic($db_engine)
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

		$data = $this->request('POST', 'messages/query', ['search_sms' => ' long message ']);
		$this->assertRedirect('messages/search/basic/'.rawurlencode('long message'));
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_query_POST_advanced_filled($db_engine)
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

		$search_data = [
			'a_search_query' => 'tricky %20 + message',
			'a_search_from_to' => '+33612345678',
			'a_search_date_from' => date('Y-m-d', strtotime('-5 days')),
			'a_search_date_to' => date('Y-m-d'),
			'a_search_sentitems_status' => 'all',
			'a_search_on' => 'all',
			'a_search_paging' => '10',
		];

		$data = $this->request('POST', 'messages/query', array_merge(['a_search_trigger' => '1'], $search_data));

		$url = '';
		foreach ($search_data as $param)
		{
			$url .= '/' . rawurlencode($param);
		}
		$this->assertRedirect('messages/search/advanced'.$url);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_query_POST_advanced_empty($db_engine)
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

		$data = $this->request('POST', 'messages/query', [
			'a_search_trigger' => '1',
			'a_search_query' => '',
			'a_search_from_to' => '',
			//'a_search_date_from' => '', // Let voluntarily away to verify that missing parameter has same effet as empty
			'a_search_date_to' => '',
			'a_search_sentitems_status' => '',
			'a_search_on' => '',
			'a_search_paging' => '',
		]);

		$this->assertRedirect('messages/search/advanced/_/_/_/_/_/_/_');
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_search_basic($db_engine)
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

		$message_text = ' tricky MessAGE %20 + oth&er _ $ stran!ge ` chars " \'er\'';
		$this->request->addCallable($dbsetup->insert('inbox', ['TextDecoded' => $message_text], FALSE)
				->insert('user_inbox') // There must be a record in user_inbox for the message
				->closure());

		$data = $this->request('GET', 'messages/search/basic/'.rawurlencode($message_text));
		$expected = htmlentities($message_text, ENT_QUOTES) . '		</div>';
		$this->_assertStringContainsString($expected, $data);
		$this->_assertStringContainsString('class="messagelist conversation messagelist_conversation"', $data);

		$this->assertValidHtml($data);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_search_basic_multipart($db_engine)
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

		// Text that is in the 2nd part of the multipart message
		$message_text = 'dol😁 💾 😅 💾. St';
		$this->request->addCallable($dbsetup->insert('inbox_multipart')
				->insert('user_inbox') // There must be a record in user_inbox for the message
				->closure());

		$data = $this->request('GET', 'messages/search/basic/'.rawurlencode($message_text));
		// Search for the text at the beginning of the message (because kalkun displays only the beginning)
		$expected = htmlentities('👍🏿 ✌🏿️ @mention 16/ Emjis', ENT_QUOTES);
		$this->_assertStringContainsString($expected, $data);

		$this->assertValidHtml($data);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_search_advanced($db_engine)
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

		$message_text = ' tricky MessAGE %20 + oth&er _ $ strange ` chars " \'er\'';
		$this->request->addCallable($dbsetup->insert('inbox', ['TextDecoded' => $message_text, 'SenderNumber' => '+33611122233'], FALSE)
				->insert('user_inbox') // There must be a record in user_inbox for the message
				->closure());

		$data = $this->request('GET', 'messages/search/advanced/'
				. rawurlencode($message_text).'/'
				. rawurlencode('+33611122233').'/'
				. date('Y-m-d').'/'
				. date('Y-m-d').'/'
				. 'all/'
				. '_/'
				. '_');
		$expected = htmlentities($message_text, ENT_QUOTES) . '		</div>';
		$this->_assertStringContainsString($expected, $data);

		$this->assertValidHtml($data);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_search_no_result($db_engine)
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

		$message_text = ' tricky MessAGE %20 + oth&er _ $ strange ` chars " \'er\'';
		$this->request->addCallable($dbsetup->insert('inbox', ['TextDecoded' => $message_text, 'SenderNumber' => '+33611122233'], FALSE)
				->insert('user_inbox') // There must be a record in user_inbox for the message
				->closure());

		$data = $this->request('GET', 'messages/search/advanced/'
				. rawurlencode($message_text).'/'
				. rawurlencode('+33610122233').'/' // Number doesn't match
				. date('Y-m-d').'/'
				. date('Y-m-d').'/'
				. 'all/'
				. '_/'
				. '_');
		$expected = 'No result.';
		$this->_assertStringContainsString($expected, $data);

		$this->assertValidHtml($data);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_move_message($db_engine)
	{
		$this->markTestIncomplete();
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_delete_messages($db_engine)
	{
		$this->markTestIncomplete();
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_delete_all($db_engine)
	{
		$this->markTestIncomplete();
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_compose_invalid($db_engine)
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

		$data = [
			'type' => 'unknown',
		];
		$output = $this->request('GET', 'messages/compose', $data);
		$this->assertResponseCode(400);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_compose_normal($db_engine)
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

		$signature = "\n" . 'It\'s me.';
		$dbsetup->insert('user', ['signature' => 'true;'.$signature]);

		$this->request->addCallable($dbsetup->closure());

		$data = [
			'type' => 'normal',
		];
		$output = $this->request('GET', 'messages/compose', $data);

		$expected = 'type="radio" id="sendoption1" name="sendoption" value="sendoption1"';
		$this->_assertStringContainsString($expected, $output);

		// Check that the disabled kalkun_config-dependant form input elements are not there
		$not_expected = [
			'value="ncpr" id="ncpr" name="ncpr"',
			'*Ads is active',
			'id="message" name="message">' . "\n\n\n" . htmlentities($signature, ENT_QUOTES) .'</textarea>',
		];
		foreach ($not_expected as $value)
		{
			$this->assertThat($value, $this->logicalNot($this->stringContains($output)));
		}

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_compose_normal_sms_bomber($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = '<?php '
					. '$config[\'sms_bomber\'] = \'TRUE\';';
		$configFile->write($content);

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

		$dbsetup->insert('user');
		$this->request->addCallable($dbsetup->closure());

		$data = [
			'type' => 'normal',
		];
		$output = $this->request('GET', 'messages/compose', $data);

		$expected = 'name="sms_loop" id="sms_loop" value="1"';
		$this->_assertStringContainsString($expected, $output);

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_compose_normal_with_signature($db_engine)
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

		$signature = "\n" . 'It\'s me.';
		$dbsetup->insert('user', ['signature' => 'true;'.$signature]);
		$this->request->addCallable($dbsetup->closure());

		$data = [
			'type' => 'normal',
		];
		$output = $this->request('GET', 'messages/compose', $data);

		$expected = 'id="message" name="message">' . "\n\n\n" . htmlentities($signature, ENT_QUOTES) .'</textarea>';
		$this->_assertStringContainsString($expected, $output);

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_compose_normal_ncpr($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = '<?php '
					. '$config[\'ncpr\'] = \'TRUE\';';
		$configFile->write($content);

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

		$dbsetup->insert('user');
		$this->request->addCallable($dbsetup->closure());

		$data = [
			'type' => 'normal',
		];
		$output = $this->request('GET', 'messages/compose', $data);

		$expected = 'value="ncpr" id="ncpr" name="ncpr"';
		$this->_assertStringContainsString($expected, $output);

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_compose_normal_sms_advertise($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = '<?php '
					. '$config[\'sms_advertise\'] = \'TRUE\';'
					. '$config[\'sms_advertise_message\'] = \'This is ads message\';';
		$configFile->write($content);

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

		$dbsetup->insert('user');
		$this->request->addCallable($dbsetup->closure());

		$data = [
			'type' => 'normal',
		];
		$output = $this->request('GET', 'messages/compose', $data);

		$expected = '*Ads is active';
		$this->_assertStringContainsString($expected, $output);

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_compose_reply($db_engine)
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

		$signature = "\n" . 'It\'s me.';
		$dbsetup->insert('user', ['signature' => 'true;'.$signature]);

		$this->request->addCallable($dbsetup->closure());

		$phone = '+33688855511';
		$data = [
			'type' => 'reply',
			'dest' => $phone,
		];
		$output = $this->request('GET', 'messages/compose', $data);

		$expected = '<input type="hidden" name="sendoption" value="reply"';
		$this->_assertStringContainsString($expected, $output);

		$expected2 = '<input type="hidden" name="reply_value" value="'.htmlentities($phone, ENT_QUOTES).'"';
		$this->_assertStringContainsString($expected2, $output);

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_compose_forward_inbox($db_engine)
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

		$dbsetup->insert('user')
				->insert('inbox_multipart');
		$message_content = $dbsetup->get_insert_value('inbox', 'TextDecoded', 0);

		$this->request->addCallable($dbsetup->closure());

		$source = 'inbox';
		$id = '1';
		$data = [
			'type' => 'forward',
			'source' => $source,
			'id' => $id,
		];
		$output = $this->request('GET', 'messages/compose', $data);

		$expected = [
			'Forward to',
			'<input type="hidden" name="msg_id" value="'.htmlentities($id, ENT_QUOTES).'"',
			'id="message" name="message">' . "\n" . htmlentities($message_content, ENT_QUOTES),
		];
		foreach ($expected as $expected_item)
		{
			$this->_assertStringContainsString($expected_item, $output);
		}

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_compose_forward_sentitems($db_engine)
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

		$dbsetup->insert('user')
				->insert('sentitems_multipart');
		$message_content = DBSetup::$text_multi_gsm;

		$this->request->addCallable($dbsetup->closure());

		$source = 'sentitems';
		$id = '1';
		$data = [
			'type' => 'forward',
			'source' => $source,
			'id' => $id,
		];
		$output = $this->request('GET', 'messages/compose', $data);

		$expected = [
			'Forward to',
			'id="message" name="message">' . "\n" . htmlentities($message_content, ENT_QUOTES),
		];
		foreach ($expected as $expected_item)
		{
			$this->_assertStringContainsString($expected_item, $output);
		}

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_compose_member($db_engine)
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

		$dbsetup->insert('user');

		$this->request->addCallable($dbsetup->closure());

		$data = [
			'type' => 'member',
		];
		$output = $this->request('GET', 'messages/compose', $data);

		$expected = '<td>Member<input type="hidden" name="sendoption" value="member"></td>';
		$this->_assertStringContainsString($expected, $output);

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_compose_pbk_contact($db_engine)
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

		$signature = "\n" . 'It\'s me.';
		$dbsetup->insert('user', ['signature' => 'true;'.$signature]);

		$this->request->addCallable($dbsetup->closure());

		$phone = '+33688855511';
		$data = [
			'type' => 'pbk_contact',
			'dest' => $phone,
		];
		$output = $this->request('GET', 'messages/compose', $data);

		$expected = '<input type="hidden" name="sendoption" value="reply"';
		$this->_assertStringContainsString($expected, $output);

		$expected2 = '<input type="hidden" name="reply_value" value="'.htmlentities($phone, ENT_QUOTES).'"';
		$this->_assertStringContainsString($expected2, $output);

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_compose_pbk_groups($db_engine)
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

		$signature = "\n" . 'It\'s me.';
		$dbsetup->insert('user', ['signature' => 'true;'.$signature]);
		$dbsetup->insert('pbk');
		$group_name = 'Group<Name"';
		$dbsetup->insert('pbk_groups', ['Name' => $group_name]);

		$this->request->addCallable($dbsetup->closure());

		$group_id = '1';
		$data = [
			'type' => 'pbk_groups',
			'dest' => $group_id,
		];
		$output = $this->request('GET', 'messages/compose', $data);

		$expected = '<input type="hidden" name="sendoption" value="pbk_groups"';
		$this->_assertStringContainsString($expected, $output);

		$expected2 = '<input type="hidden" name="id_pbk" value="'.htmlentities($group_id, ENT_QUOTES).'"';
		$this->_assertStringContainsString($expected2, $output);

		$expected3 = htmlentities($group_name, ENT_QUOTES);
		$this->_assertStringContainsString($expected3, $output);

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_compose_all_contacts($db_engine)
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

		$dbsetup->insert('user');

		$this->request->addCallable($dbsetup->closure());

		$data = [
			'type' => 'all_contacts',
		];
		$output = $this->request('GET', 'messages/compose', $data);

		$expected = '<input type="hidden" name="sendoption" value="all_contacts">';
		$this->_assertStringContainsString($expected, $output);

		$this->assertValidHtmlSnippet($output);
	}


	/**
	 * @dataProvider database_Provider
	 *
	 */
	/*#[DataProvider('database_Provider')]
	public function test_compose_resend_inbox($db_engine)
	{
		// This is not possible in the GUI. Resend is only possible on 'sentitems' messages.
	}*/

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_compose_resend_sentitems($db_engine)
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

		$dbsetup->insert('user')
				->insert('sentitems_multipart');
		$message_content = DBSetup::$text_multi_gsm;

		$this->request->addCallable($dbsetup->closure());

		$source = 'sentitems';
		$id = '1';
		$data = [
			'type' => 'resend',
			'source' => $source,
			'id' => $id,
		];
		$output = $this->request('GET', 'messages/compose', $data);
		$expected = [
			'<input type="hidden" name="sendoption" value="resend"',
			'<input type="hidden" name="resend_value" value="'.htmlentities('+33612345678', ENT_QUOTES).'"',
			'<input type="hidden" name="orig_msg_id" value="'.$id.'"',
			'id="message" name="message">' . "\n" . htmlentities($message_content, ENT_QUOTES),
			'<input type="checkbox" id="resend_delete_original" name="resend_delete_original"',
		];
		foreach ($expected as $expected_item)
		{
			$this->_assertStringContainsString($expected_item, $output);
		}

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_compose_prefill($db_engine)
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

		$dbsetup->insert('user');

		$this->request->addCallable($dbsetup->closure());

		$phone = '+33688855511';
		$message = 'the message for prefill';
		$data = [
			'type' => 'prefill',
			'phone' => $phone,
			'message' => $message,
		];
		$output = $this->request('GET', 'messages/compose', $data);

		$expected = [
			'<input type="radio" id="sendoption3" name="sendoption" value="sendoption3" style="border: none;"  checked="checked"',
			'<input type="radio" id="sendoption1" name="sendoption" value="sendoption1" class="left_aligned" style="border: none;" >',
			'name="manualvalue" id="manualvalue"  value="' . htmlentities($phone, ENT_QUOTES) . '"',
			'id="message" name="message">' . "\n" . htmlentities($message, ENT_QUOTES),
		];
		foreach ($expected as $expected_item)
		{
			$this->_assertStringContainsString($expected_item, $output);
		}

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_canned_response_list_no_result($db_engine)
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

		// Add a contact that is in another group
		//$dbsetup->insert();

		$this->request->addCallable($dbsetup->closure());

		$output = $this->request('POST', 'messages/canned_response/list');
		$expected = 'There are no canned responses. Continue to save your present message as canned response.';
		$this->_assertStringContainsString($expected, $output);

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_canned_response_list($db_engine)
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

		// Add a template
		$name = 'template label';
		$dbsetup->insert('user_templates', ['id_user' => 2, 'Name' => $name]);

		$this->request->addCallable($dbsetup->closure());

		$output = $this->request('POST', 'messages/canned_response/list');

		$expected = '<strong>'.htmlentities($name, ENT_QUOTES).'</strong>';
		$this->_assertStringContainsString($expected, $output);

		$not_expected = 'There are no canned responses. Continue to save your present message as canned response.';
		$this->assertThat($not_expected, $this->logicalNot($this->stringContains($output)));

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_canned_response_get($db_engine)
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

		// Add a template
		$name = 'template label';
		$message = '👍🏿 ✌🏿️ @mention 16';
		$dbsetup->insert('user_templates', ['id_user' => 2, 'Name' => $name, 'Message' => $message]);

		$this->request->addCallable($dbsetup->closure());

		$output = $this->request('POST', 'messages/canned_response/get', ['name' => $name]);
		$this->assertEquals($message, $output);

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_canned_response_save_insert($db_engine)
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

		// Add a template
		$name = 'template label';
		$message = '👍🏿 ✌🏿️ @mention 16';

		$output = $this->request('POST', 'messages/canned_response/save', ['name' => $name, 'message' => $message]);
		$this->assertEmpty($output);

		$result = $this->CI->db->where('id_user', 2)
				->where('Name', $name)
				->where('Message', $message)
				->get('user_templates');
		$this->assertEquals(1, $result->num_rows());
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_canned_response_save_update($db_engine)
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

		// Add a template
		$name = 'template label';
		$message = '👍🏿 ✌🏿️ @mention 16';
		$dbsetup->insert('user_templates', ['id_user' => 2, 'Name' => $name, 'Message' => $message]);

		$this->request->addCallable($dbsetup->closure());

		$new_message = '@mention 16 👍🏿 ✌🏿️';
		$output = $this->request('POST', 'messages/canned_response/save', ['name' => $name, 'message' => $new_message]);
		$this->assertEmpty($output);

		$result = $this->CI->db->where('id_user', 2)
				->where('Name', $name)
				->where('Message', $message)
				->get('user_templates');
		$this->assertEquals(0, $result->num_rows());

		$result2 = $this->CI->db->where('id_user', 2)
				->where('Name', $name)
				->where('Message', $new_message)
				->get('user_templates');
		$this->assertEquals(1, $result2->num_rows());
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_canned_response_delete($db_engine)
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

		// Add a template
		$name = 'template label';
		$message = '👍🏿 ✌🏿️ @mention 16';
		$dbsetup->insert('user_templates', ['id_user' => 2, 'Name' => $name, 'Message' => $message]);

		$this->request->addCallable($dbsetup->closure());

		$output = $this->request('POST', 'messages/canned_response/delete', ['name' => $name]);
		$this->assertEmpty($output);

		$result = $this->CI->db->get('user_templates');
		$this->assertEquals(0, $result->num_rows());
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_canned_response_invalid($db_engine)
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

		// Add a template
		$name = 'template label';
		$message = '👍🏿 ✌🏿️ @mention 16';
		$dbsetup->insert('user_templates', ['id_user' => 2, 'Name' => $name, 'Message' => $message]);

		$this->request->addCallable($dbsetup->closure());

		$output = $this->request('POST', 'messages/canned_response/invalid', ['name' => $name]);
		$this->assertResponseCode(400);
	}
}
