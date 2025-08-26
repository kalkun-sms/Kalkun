<?php
/**
 * Kalkun
 * An open source web based SMS Manager
 *
 * @copyright 2022-2024 Kalkun dev team
 * @author Kalkun dev team
 * @license <https://spdx.org/licenses/GPL-2.0-or-later.html> GPL-2.0-or-later
 * @link https://kalkun.sourceforge.io/
 */

require_once __DIR__.'/../testutils/DBSetup.php';
require_once __DIR__.'/../testutils/GammuSmsdConfigFile.php';
require_once __DIR__.'/../testutils/KalkunTestCase.php';

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

class Gammu_model_test extends KalkunTestCase {

	public function setUp() : void
	{
		$this->resetInstance();
		$this->CI->load->model('gateway/Gammu_model');
		$this->obj = $this->CI->Gammu_model;
	}

	public static function database_Provider()
	{
		return DBSetup::$db_engines_to_test;
	}

	public static function split_multipart_unicode_Provider()
	{
		return [
			['👍🏿 ✌🏿️ @mention 16/ Emjis 😁 💾 😅 💾 🤣 💾 😂 💾 🙂 💾. Lorem 👍🏿ipsum dolor sit ame💾 😂 sum dolor sitsum dolor sitsum dolor sitsum dolor sitm dolor sitsum dolor sitsum dol m dolor sitsum dolor sitsum dol😁 💾 😅 💾. Stop',
				[
					0 => '👍🏿 ✌🏿️ @mention 16/ Emjis 😁 💾 😅 💾 🤣 💾 😂 💾 🙂 💾. Lorem ',
					1 => '👍🏿ipsum dolor sit ame💾 😂 sum dolor sitsum dolor sitsum dolor si',
					2 => 'tsum dolor sitm dolor sitsum dolor sitsum dol m dolor sitsum dolor ',
					3 => 'sitsum dol😁 💾 😅 💾. Stop',
				]],
			['MESSAGE1:   with 00000             AFTER ARROW IT IS CHAR 65 -->123NEXT_MESSAGE', [
				'MESSAGE1:   with 00000             AFTER ARROW IT IS CHAR 65 -->123',
				'NEXT_MESSAGE',
			]],
			['MESSAGE1:                          AFTER ARROW IT IS CHAR 65 -->123MESSAGE2                          AFTER ARROW IT IS CHAR 132 -->123MESSAGE3', [
				'MESSAGE1:                          AFTER ARROW IT IS CHAR 65 -->123',
				'MESSAGE2                          AFTER ARROW IT IS CHAR 132 -->123',
				'MESSAGE3',
			]],
		];
	}

	/**
	 * @dataProvider split_multipart_unicode_Provider
	 */
	#[DataProvider('split_multipart_unicode_Provider')]
	public function test_split_multipart_unicode($input, $expected)
	{
		$output = $this->obj->split_multipart_unicode($input, 67);
		$this->assertEquals(count($expected), count($output));
		for ($i = 0; $i < count($output); $i++)
		{
			$this->assertEquals($expected[$i], $output[$i]);
		}
	}

	public static function utf16_code_units_count_Provider()
	{
		return [
			['👍🏿 ✌🏿️ @mention 16/ Emjis 😁 💾 😅 💾 🤣 💾 😂 💾 🙂 💾. Lorem 👍🏿ipsum dolor sit ame💾 😂 sum dolor sitsum dolor sitsum dolor sitsum dolor sitm dolor sitsum dolor sitsum dol m dolor sitsum dolor sitsum dol😁 💾 😅 💾. Stop', 227],
			['👍🏿', 4],
			['💾', 2],
			['a', 1],
			['', 0],
			['न', 1],
			['म', 1],
			['ते', 2],
			['स्', 2],
			['स्ते', 4],
		];
	}

	/**
	 * @dataProvider utf16_code_units_count_Provider
	 */
	#[DataProvider('utf16_code_units_count_Provider')]
	public function test_utf16_code_units_count($input, $expected)
	{
		$output = $this->obj->utf16_code_units_count($input);
		$this->assertEquals($expected, $output);
	}

	public static function string_split_Provider()
	{
		return [
			['abc', ['a', 'b', 'c']],
			['नम', ['न', 'म']],
			['€£µ$', ['€', '£', 'µ', '$']],
			['éàçß', ['é', 'à', 'ç', 'ß', ]],
			['¬⋅→×', ['¬', '⋅', '→', '×', ]],
			['emo😃.', ['e', 'm', 'o', '😃', '.']],
			//['👍🏿', ['👍🏿']], // Thumbs Up: Dark Skin Tone. This is not supported by mb_*
			//['ते', ['ते']], // This is not supported by mb_*
			//['स्', ['स्']], // This is not supported by mb_*
			//['स्ते', ['स्', 'ते']], // This is not supported by mb_*

			// GSM Default 7-bit special character (count as 2 char)
			['^{}[]~|€\\' . "\f", ['^', '{', '}', '[', ']', '~', '|', '€', '\\', "\f"]],
			// GSM Default 7-bit character (count as 1 char)
			['@Δ 0¡P¿p£_!1AQaq$Φ"2BRbr¥Γ#3CScsèΛ¤4DTdtéΩ%5EUeuùΠ&6FVfvìΨ' . "'" . '7GWgwòΣ(8HXhxÇΘ)9IYiy' . "\n" . 'Ξ*:JZjzØ' . "\x1B" . '+;KÄkäøÆ,<LÖlö' . "\r" . 'æ-=MÑmñÅß.>NÜnüåÉ/?O§oà',
				array('@', 'Δ', ' ', '0', '¡', 'P', '¿', 'p',
					'£', '_', '!', '1', 'A', 'Q', 'a', 'q',
					'$', 'Φ', '"', '2', 'B', 'R', 'b', 'r',
					'¥', 'Γ', '#', '3', 'C', 'S', 'c', 's',
					'è', 'Λ', '¤', '4', 'D', 'T', 'd', 't',
					'é', 'Ω', '%', '5', 'E', 'U', 'e', 'u',
					'ù', 'Π', '&', '6', 'F', 'V', 'f', 'v',
					'ì', 'Ψ', '\'', '7', 'G', 'W', 'g', 'w',
					'ò', 'Σ', '(', '8', 'H', 'X', 'h', 'x',
					'Ç', 'Θ', ')', '9', 'I', 'Y', 'i', 'y',
					"\n", 'Ξ', '*', ':', 'J', 'Z', 'j', 'z',
					'Ø', "\x1B", '+', ';', 'K', 'Ä', 'k', 'ä',
					'ø', 'Æ', ',', '<', 'L', 'Ö', 'l', 'ö',
					"\r", 'æ', '-', '=', 'M', 'Ñ', 'm', 'ñ',
					'Å', 'ß', '.', '>', 'N', 'Ü', 'n', 'ü',
					'å', 'É', '/', '?', 'O', '§', 'o', 'à')],
		];
	}

	/**
	 * @dataProvider string_split_Provider
	 */
	#[DataProvider('string_split_Provider')]
	public function test__string_split($input, $expected)
	{
		$output = $this->obj->_string_split($input);
		$this->assertEquals($expected, $output);
	}

	public static function is_special_char_Provider()
	{
		return [
			// GSM Default 7-bit special character (count as 2 char)
			[['^', '{', '}', '[', ']', '~', '|', '€', '\\', "\f"], TRUE],
			// GSM Default 7-bit character (count as 1 char)
			[array('@', 'Δ', ' ', '0', '¡', 'P', '¿', 'p',
				'£', '_', '!', '1', 'A', 'Q', 'a', 'q',
				'$', 'Φ', '"', '2', 'B', 'R', 'b', 'r',
				'¥', 'Γ', '#', '3', 'C', 'S', 'c', 's',
				'è', 'Λ', '¤', '4', 'D', 'T', 'd', 't',
				'é', 'Ω', '%', '5', 'E', 'U', 'e', 'u',
				'ù', 'Π', '&', '6', 'F', 'V', 'f', 'v',
				'ì', 'Ψ', '\'', '7', 'G', 'W', 'g', 'w',
				'ò', 'Σ', '(', '8', 'H', 'X', 'h', 'x',
				'Ç', 'Θ', ')', '9', 'I', 'Y', 'i', 'y',
				"\n", 'Ξ', '*', ':', 'J', 'Z', 'j', 'z',
				'Ø', "\x1B", '+', ';', 'K', 'Ä', 'k', 'ä',
				'ø', 'Æ', ',', '<', 'L', 'Ö', 'l', 'ö',
				"\r", 'æ', '-', '=', 'M', 'Ñ', 'm', 'ñ',
				'Å', 'ß', '.', '>', 'N', 'Ü', 'n', 'ü',
				'å', 'É', '/', '?', 'O', '§', 'o', 'à'), FALSE],
		];
	}

	/**
	 * @dataProvider is_special_char_Provider
	 */
	#[DataProvider('is_special_char_Provider')]
	public function test__is_special_char($input, $expected)
	{
		foreach ($input as $char)
		{
			$output = $this->obj->_is_special_char($char);
			$this->assertEquals($expected, $output);
		}
	}

	public static function get_message_length_Provider()
	{
		return [
			['👍🏿 ✌🏿️ @mention 16/ Emjis 😁 💾 😅 💾 🤣 💾 😂 💾 🙂 💾. Lorem 👍🏿ipsum dolor sit ame💾 😂 sum dolor sitsum dolor sitsum dolor sitsum dolor sitm dolor sitsum dolor sitsum dol m dolor sitsum dolor sitsum dol😁 💾 😅 💾. Stop', 'Unicode_No_Compression', 227],
			['MESSAGE1:                          AFTER ARROW IT IS CHAR 65 -->123NEXT_MESSAGE', 'Default_No_Compression', 79],
			['MESSAGE1:                          AFTER ARROW IT IS CHAR 65 -->123MESSAGE2                          AFTER ARROW IT IS CHAR 132 -->123MESSAGE3', 'Default_No_Compression', 142],
			['message with gsm7 special chars: ^{}[]~|€\\', 'Default_No_Compression', 51],
			['message with gsm7 special chars: ^{}[]~|€\\👍🏿', 'Unicode_No_Compression', 46],
			['message with gsm7 special chars and emojis: 👍🏿', 'Unicode_No_Compression', 48],
			//['message with gsm7 special chars: ^{}[]~|€\\😁', 'Default_No_Compression', 52], // There is an emoji, so this should be unicode, not Default_No_Compression
		];
	}

	/**
	 * @dataProvider get_message_length_Provider
	 */
	#[DataProvider('get_message_length_Provider')]
	public function test__get_message_length($input, $coding, $expected)
	{
		$output = $this->obj->_get_message_length($input, $coding, $expected);
		$this->assertEquals($expected, $output);
	}

	public static function get_message_multipart_Provider()
	{
		return [
			['👍🏿 ✌🏿️ @mention 16/ Emjis 😁 💾 😅 💾 🤣 💾 😂 💾 🙂 💾. Lorem 👍🏿ipsum dolor sit ame💾 😂 sum dolor sitsum dolor sitsum dolor sitsum dolor sitm dolor sitsum dolor sitsum dol m dolor sitsum dolor sitsum dol😁 💾 😅 💾. Stop', 'Unicode_No_Compression',
				[
					0 => '👍🏿 ✌🏿️ @mention 16/ Emjis 😁 💾 😅 💾 🤣 💾 😂 💾 🙂 💾. Lorem ',
					1 => '👍🏿ipsum dolor sit ame💾 😂 sum dolor sitsum dolor sitsum dolor si',
					2 => 'tsum dolor sitm dolor sitsum dolor sitsum dol m dolor sitsum dolor ',
					3 => 'sitsum dol😁 💾 😅 💾. Stop',
				]],
			['MESSAGE1:                          AFTER ARROW IT IS CHAR 65 -->123MESSAGE2                          AFTER ARROW IT IS CHAR 132 -->123MESSAGE3', 'Default_No_Compression',
				[
					'MESSAGE1:                          AFTER ARROW IT IS CHAR 65 -->123MESSAGE2                          AFTER ARROW IT IS CHAR 132 -->123MESSAGE3',
				]],
			['message with gsm7 special chars: ^{}[]~|€\\', 'Default_No_Compression',
				[
					'message with gsm7 special chars: ^{}[]~|€\\']],
			['long message with gsm7 special chars: ^{}[]~|€\\ Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor. Cras elementum ultrices diam. Maecenas ligula massa, varius a, semper congue, euismod non, mi.', 'Default_No_Compression',
				[
					'long message with gsm7 special chars: ^{}[]~|€\\ Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus torto',
					'r, dignissim sit amet, adipiscing nec, ultricies sed, dolor. Cras elementum ultrices diam. Maecenas ligula massa, varius a, semper congue, euismod non, m',
					'i.',
				]],
			// This message has a special character at position 154. It should be put to the 2nd message.
			['long message with gsm7 special chars: ^{}[]~|€\\ Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus torto~^r, dignissim sit amet, adipiscing nec, ultricies sed, dolor. Cras elementum ultrices diam. Maecenas ligula massa, varius a, semper congue, euismod non, mi.', 'Default_No_Compression',
				[
					'long message with gsm7 special chars: ^{}[]~|€\\ Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus torto',
					'~^r, dignissim sit amet, adipiscing nec, ultricies sed, dolor. Cras elementum ultrices diam. Maecenas ligula massa, varius a, semper congue, euismod no',
					'n, mi.',
				]],
			// This message has a special character at position 153. The special character is encoded as 2x7-bit chars, but only one 7bit location is
			// remaining in the message for a character. So it should be placed to the 2nd message.
			['long message with gsm7 special chars: ^{}[]~|€\\ Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tort~^or, dignissim sit amet, adipiscing nec, ultricies sed, dolor. Cras elementum ultrices diam. Maecenas ligula massa, varius a, semper congue, euismod non, mi.', 'Default_No_Compression',
				[
					'long message with gsm7 special chars: ^{}[]~|€\\ Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tort',
					'~^or, dignissim sit amet, adipiscing nec, ultricies sed, dolor. Cras elementum ultrices diam. Maecenas ligula massa, varius a, semper congue, euismod n',
					'on, mi.',
				]],
		];
	}

	/**
	 * @dataProvider get_message_multipart_Provider
	 */
	#[DataProvider('get_message_multipart_Provider')]
	public function test__get_message_multipart($input, $coding, $expected)
	{
		$output = $this->obj->_get_message_multipart($input, $coding, 67);
		for ($i = 0; $i < count($output); $i++)
		{
			$this->assertEquals($expected[$i], $output[$i]);
		}
	}

	public static function _send_wap_link_Provider()
	{
		return DBSetup::prepend_db_engine(
			[
				'successful' => [
					[
						'date' => date('Y-m-d H:i:s', strtotime('+ 5 hours')),
						'dest' => '23456', // Should resolve to +123456 for user 1 with region US
						'url' => 'https://www.github.com',
						'type' => 'waplink',
						'message' => 'unicode text with emoji 😁!\'', // if multipart, only the 1st part of the message
						'validity' => '23',
						'delivery_report' => 'no',
						'uid' => '5',
					],
					'expected' => 'Message queued.'],
				'successful with NULL service' => [
					[
						'date' => date('Y-m-d H:i:s', strtotime('+ 5 hours')),
						'dest' => '23456', // Should resolve to +123456 for user 1 with region US
						'url' => 'https://www.github.com',
						'type' => 'waplink',
						'message' => 'unicode text with emoji 😁!\'', // if multipart, only the 1st part of the message
						'NULL_SERVICE' => TRUE,
					],
					'expected' => 'Message queued.'],
				'failing, wrong type' => [
					[
						'date' => date('Y-m-d H:i:s', strtotime('+ 5 hours')),
						'dest' => '23456', // Should resolve to +123456 for user 1 with region US
						'url' => 'https://www.github.com',
						'type' => 'waplink0',
						'message' => 'unicode text with emoji 😁!\'', // if multipart, only the 1st part of the message
					],
					'expected' => 'Parameter invalid.'],
			]
		);
	}

	/**
	 * @dataProvider _send_wap_link_Provider
	 * @group gammu-smsd-inject
	 */
	#[DataProvider('_send_wap_link_Provider')]
	#[Group('gammu-smsd-inject')]
	public function test__send_wap_link($db_engine, $data, $expected)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		// Create a gammu-smsd config file
		if (isset($data['NULL_SERVICE']))
		{
			// config gammu-smsd with NULL service
			$configFile2 = new GammuSmsdConfigFile(APPPATH . 'config/testing/gammu-config', NULL);
			$configFile2->write($configFile2->get_content());
		}
		else
		{
			$configFile3 = new GammuSmsdConfigFile(APPPATH . 'config/testing/gammu-config', $dbsetup);
			$configFile3->write($configFile3->get_content());
		}

		// Set correct configuration of kalkun for gammu-smsd-inject
		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = '<?php '
					. '$config[\'gammu_config\'] = \'' . APPPATH . 'config/testing/gammu-config\';'
					. '$config[\'gammu_sms_inject\'] = \'/usr/bin/gammu-smsd-inject\';';
		$configFile->write($content);
		$this->resetInstance();

		if ( ! is_readable($this->CI->config->item('gammu_config')))
		{
			$this->markTestSkipped('gammu-smsd config file "' . $this->CI->config->item('gammu_config') . '" is not readable. Skipping test.');
		}

		// Load session (the function uses add_sms_used() which needs id_user)
		$this->CI->load->library('session');
		$_SESSION['loggedin'] = 'TRUE';
		$_SESSION['id_user'] = '1';
		$_SESSION['level'] = 'admin';
		$_SESSION['username'] = 'kalkun';

		$this->CI->load->database();
		$this->CI->load->model('Kalkun_model');
		// language for usage of tr_raw() & tr() in Gammu_model.php
		$this->CI->load->helper('i18n');
		$this->CI->lang->load('kalkun', 'english');
		$this->CI->lang->load('date', 'english');

		$function_result = $this->obj->_send_wap_link($data);

		$this->assertEquals($expected, $function_result['status']);

		if (isset($data['NULL_SERVICE']))
		{
			return;
		}

		// Additional check if the test case results in insertion into the database.
		if ($expected === 'Message queued.')
		{
			$result = $this->CI->db->get('outbox');
			// If gammu-smsd is not properly configured so that the message is inserted
			// to the corresponding database the query will not return any result
			//if ($result->row() === NULL)
			//{
			//	$this->markTestIncomplete('gammu-smsd may not be properly configured to connect to a database.');
			//}
			//else
			//{
			$this->assertEquals($data['date'], $result->row()->SendingDateTime);
			$this->assertEquals($data['validity'], $result->row()->RelativeValidity);
			$this->assertEquals($data['delivery_report'], $result->row()->DeliveryReport);
			$result_user_outbox = $this->CI->db->get('user_outbox');
			$this->assertEquals(1, $result_user_outbox->row()->id_outbox);
			$this->assertEquals(5, $result_user_outbox->row()->id_user);
			//}
		}
	}

	public static function _send_wap_link_failing_Provider()
	{
		return DBSetup::prepend_db_engine([
			'failing, wrong config' => [[
				'date' => date('Y-m-d H:i:s'),
				'dest' => '23456', // Should resolve to +123456 for user 1 with region US
				'url' => 'https://www.github.com',
				'type' => 'waplink',
				'message' => 'unicode text with emoji 😁!\'',
			]],
		]);
	}

	/**
	 * @dataProvider _send_wap_link_failing_Provider
	 * @group gammu-smsd-inject
	 */
	#[DataProvider('_send_wap_link_failing_Provider')]
	#[Group('gammu-smsd-inject')]
	public function test__send_wap_link_failing_check_log($db_engine, $data)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		// Create a failing config
		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = '<?php $config[\'gammu_sms_inject\'] = \'/wrong/path\';';
		$configFile->write($content);
		$this->resetInstance();

		$this->CI->load->helper('i18n');
		$this->CI->lang->load('kalkun', 'english');
		$this->CI->lang->load('date', 'english');

		try
		{
			$this->obj->_send_wap_link($data);
		}
		catch (CIPHPUnitTestShowErrorException $e)
		{
			// This exception will be thrown by ci-phpunit-test but we skip it
			// to check the content of the logs.
		}
		$expected = 'Failure to inject message into Gammu with gammu-smsd-inject.';
		$this->assertTrue(CIPHPUnitTestLogger::didLog('error', $expected));
	}

	/**
	 * @dataProvider _send_wap_link_failing_Provider
	 * @group gammu-smsd-inject
	 */
	#[DataProvider('_send_wap_link_failing_Provider')]
	#[Group('gammu-smsd-inject')]
	public function test__send_wap_link_failing_check_exception_thrown($db_engine, $data)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		// Create a failing config
		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = '<?php $config[\'gammu_sms_inject\'] = \'/wrong/path\';';
		$configFile->write($content);
		$this->resetInstance();

		$this->CI->load->helper('i18n');
		$this->CI->lang->load('kalkun', 'english');
		$this->CI->lang->load('date', 'english');

		$this->expectException(CIPHPUnitTestShowErrorException::class);
		$expected = 'Failure to inject message into Gammu with gammu-smsd-inject. See kalkun logs.';
		$this->expectExceptionMessage($expected);
		$this->obj->_send_wap_link($data);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_send_messages_waplink($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		// Create a gammu-smsd config file
		$configFile3 = new GammuSmsdConfigFile(APPPATH . 'config/testing/gammu-config', $dbsetup);
		$configFile3->write($configFile3->get_content());

		// Set correct configuration of kalkun for gammu-smsd-inject
		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = '<?php '
					. '$config[\'gammu_config\'] = \'' . APPPATH . 'config/testing/gammu-config\';'
					. '$config[\'gammu_sms_inject\'] = \'/usr/bin/gammu-smsd-inject\';';
		$configFile->write($content);
		$this->resetInstance();

		if ( ! is_readable($this->CI->config->item('gammu_config')))
		{
			$this->markTestSkipped('gammu-smsd config file "' . $this->CI->config->item('gammu_config') . '" is not readable. Skipping test.');
		}

		// Load session (the function uses add_sms_used() which needs id_user)
		$this->CI->load->library('session');
		$_SESSION['loggedin'] = 'TRUE';
		$_SESSION['id_user'] = '1';
		$_SESSION['level'] = 'admin';
		$_SESSION['username'] = 'kalkun';

		$this->CI->load->database();
		$this->CI->load->model('Kalkun_model');
		// language for usage of tr_raw() & tr() in Gammu_model.php
		$this->CI->load->helper('i18n');
		$this->CI->lang->load('kalkun', 'english');
		$this->CI->lang->load('date', 'english');

		$data = [
			'date' => date('Y-m-d H:i:s', strtotime('+ 5 hours')),
			'dest' => '23456', // Should resolve to +123456 for user 1 with region US
			'url' => 'https://www.github.com',
			'type' => 'waplink',
			'message' => 'unicode text with emoji 😁!\'',
			'delivery_report' => 'default',
			'uid' => '1',
		];
		$function_result = $this->obj->send_messages($data);

		$expected = 'Message queued.';
		$this->assertEquals($expected, $function_result['status']);

		$result = $this->CI->db->get('outbox');
		$this->assertEquals($data['date'], $result->row()->SendingDateTime);
		$this->assertEquals('8bit', $result->row()->Coding);
	}

	public static function send_messages_failing_because_empty_Provider()
	{
		return DBSetup::prepend_db_engine([
			'failing, no date' => [[
				'date' => '',
				'dest' => '23456', // Should resolve to +123456 for user 1 with region US
				'message' => 'unicode text with emoji 😁!\'',
			]],
			'failing, no dest' => [[
				'date' => date('Y-m-d H:i:s'),
				'dest' => '',
				'message' => 'unicode text with emoji 😁!\'',
			]],
			'failing, no message' => [[
				'date' => date('Y-m-d H:i:s'),
				'dest' => '23456',
				'message' => '',
			]],

		]);
	}
	/**
	 * @dataProvider send_messages_failing_because_empty_Provider
	 */
	#[DataProvider('send_messages_failing_because_empty_Provider')]
	public function test_send_messages_failing_because_empty($db_engine, $data)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$this->expectException(CIPHPUnitTestShowErrorException::class);
		$expected = 'Parameter invalid.';
		$this->expectExceptionMessage($expected);
		$this->obj->send_messages($data);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_send_messages_single_unicode($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		// Load session (the function uses add_sms_used() which needs id_user)
		$this->CI->load->library('session');
		$_SESSION['loggedin'] = 'TRUE';
		$_SESSION['id_user'] = '1';
		$_SESSION['level'] = 'admin';
		$_SESSION['username'] = 'kalkun';

		$this->CI->load->database();
		$this->CI->load->model('Kalkun_model');

		$data = [
			'date' => date('Y-m-d H:i:s'),
			'dest' => '23456', // Should resolve to +123456 for user 1 with region US
			'message' => 'Unicode msg with Emoji 😁 & specials ^{}\[]~|€ and exactly 70 chars...',
			'class' => '1',
			'delivery_report' => 'default',
			'uid' => '2',
			'type' => 'normal', // normal, flash or waplink
		];

		$function_result = $this->obj->send_messages($data);

		$expected = 'Message queued.';
		$this->assertEquals($expected, $function_result['status']);


		$result = $this->CI->db->get('outbox');
		$this->assertEquals($data['date'], $result->row()->SendingDateTime);
		$this->assertEquals('', $result->row()->Text);
		$this->assertEquals('+123456', $result->row()->DestinationNumber);
		$this->assertEquals('Unicode_No_Compression', $result->row()->Coding);
		$this->assertEquals('', $result->row()->UDH);
		$this->assertEquals(1, $result->row()->Class);
		$this->assertEquals($data['message'], $result->row()->TextDecoded);
		$this->assertEquals(1, $result->row()->ID);
		$this->assertEquals(FALSE, db_boolean_to_php_bool($db_engine, $result->row()->MultiPart));
		$this->assertEquals('-1', $result->row()->RelativeValidity);
		$this->assertEquals(NULL, $result->row()->SenderID);
		$this->assertEquals($data['delivery_report'], $result->row()->DeliveryReport);
		$this->assertEquals('🦃 Kalkun '.$this->CI->config->item('kalkun_version'), $result->row()->CreatorID);

		$result_multipart = $this->CI->db->get('outbox_multipart');
		$this->assertEquals(0, $result_multipart->num_rows());

		$result_smsused = $this->CI->db->get('sms_used');
		$this->assertEquals(1, $result_smsused->num_rows());
		$row = $result_smsused->row();
		$this->assertEquals(date('Y-m-d'), $row->sms_date);
		$this->assertEquals($data['uid'], $row->id_user);
		$this->assertEquals(1, $row->out_sms_count);
		$this->assertEquals(0, $row->in_sms_count);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_send_messages_single_gsm7bit($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		// Load session (the function uses add_sms_used() which needs id_user)
		$this->CI->load->library('session');
		$_SESSION['loggedin'] = 'TRUE';
		$_SESSION['id_user'] = '1';
		$_SESSION['level'] = 'admin';
		$_SESSION['username'] = 'kalkun';

		$this->CI->load->database();
		$this->CI->load->model('Kalkun_model');

		$data = [
			'date' => date('Y-m-d H:i:s'),
			'dest' => '23456', // Should resolve to +123456 for user 1 with region US
			'message' => 'Non unicode text with specials ^{}\[]~|€| and exactly 160 chars --------------------------------------------------------------------------------------',
			'class' => '1',
			'delivery_report' => 'default',
			'uid' => '2',
			'type' => 'normal', // normal, flash or waplink
		];

		$function_result = $this->obj->send_messages($data);

		$expected = 'Message queued.';
		$this->assertEquals($expected, $function_result['status']);

		$result = $this->CI->db->get('outbox');
		$this->assertEquals($data['date'], $result->row()->SendingDateTime);
		$this->assertEquals('', $result->row()->Text);
		$this->assertEquals('+123456', $result->row()->DestinationNumber);
		$this->assertEquals('Default_No_Compression', $result->row()->Coding);
		$this->assertEquals('', $result->row()->UDH);
		$this->assertEquals(1, $result->row()->Class);
		$this->assertEquals($data['message'], $result->row()->TextDecoded);
		$this->assertEquals(1, $result->row()->ID);
		$this->assertEquals(FALSE, db_boolean_to_php_bool($db_engine, $result->row()->MultiPart));
		$this->assertEquals('-1', $result->row()->RelativeValidity);
		$this->assertEquals(NULL, $result->row()->SenderID);
		$this->assertEquals($data['delivery_report'], $result->row()->DeliveryReport);
		$this->assertEquals('🦃 Kalkun '.$this->CI->config->item('kalkun_version'), $result->row()->CreatorID);

		$result_multipart = $this->CI->db->get('outbox_multipart');
		$this->assertEquals(0, $result_multipart->num_rows());

		$result_smsused = $this->CI->db->get('sms_used');
		$this->assertEquals(1, $result_smsused->num_rows());
		$row = $result_smsused->row();
		$this->assertEquals(date('Y-m-d'), $row->sms_date);
		$this->assertEquals($data['uid'], $row->id_user);
		$this->assertEquals(1, $row->out_sms_count);
		$this->assertEquals(0, $row->in_sms_count);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_send_messages_multipart_unicode($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		// Load session (the function uses add_sms_used() which needs id_user)
		$this->CI->load->library('session');
		$_SESSION['loggedin'] = 'TRUE';
		$_SESSION['id_user'] = '1';
		$_SESSION['level'] = 'admin';
		$_SESSION['username'] = 'kalkun';

		$this->CI->load->database();
		$this->CI->load->model('Kalkun_model');

		$parts = 18;
		$message = '';
		$message_part_template = '<START%02d Multipart Unicode msg 👍🏿 😁 & specials ^{}\[]~|€ -- END>';
		for ($i = 1; $i <= $parts; $i++)
		{
			$message .= sprintf($message_part_template, $i);
		}
		$data = [
			'date' => date('Y-m-d H:i:s'),
			'dest' => '23456', // Should resolve to +123456 for user 1 with region US
			'message' => $message,
			'class' => '1',
			'delivery_report' => 'no',
			'uid' => '2',
			'type' => 'normal', // normal, flash or waplink
		];

		$function_result = $this->obj->send_messages($data);

		$expected = 'Message queued.';
		$this->assertEquals($expected, $function_result['status']);

		$result = $this->CI->db->get('outbox');
		$row = $result->row();
		$this->assertEquals($data['date'], $row->SendingDateTime);
		$this->assertEquals('', $row->Text);
		$this->assertEquals('+123456', $row->DestinationNumber);
		$this->assertEquals('Unicode_No_Compression', $row->Coding);
		$this->_assertMatchesRegularExpression('/050003..'.sprintf('%02X', $parts).'01/', $row->UDH);
		$this->assertEquals(1, $row->Class);
		$expected_message = sprintf($message_part_template, 1);
		$this->assertEquals($expected_message, $row->TextDecoded);
		$this->assertEquals(1, $row->ID);
		$this->assertEquals(TRUE, db_boolean_to_php_bool($db_engine, $row->MultiPart));
		$this->assertEquals('-1', $row->RelativeValidity);
		$this->assertEquals(NULL, $row->SenderID);
		$this->assertEquals($data['delivery_report'], $row->DeliveryReport);
		$this->assertEquals('🦃 Kalkun '.$this->CI->config->item('kalkun_version'), $row->CreatorID);

		$result_multipart = $this->CI->db->get('outbox_multipart');
		$this->assertEquals(($parts - 1), $result_multipart->num_rows());
		$row = $result_multipart->row();
		for ($i = 2; $i <= $parts; $i++)
		{
			$this->assertEquals('', $row->Text);
			$this->assertEquals('Unicode_No_Compression', $row->Coding);
			$this->_assertMatchesRegularExpression('/050003..'.sprintf('%02X', $parts).sprintf('%02X', $i).'/', $row->UDH);
			$this->assertEquals(1, $row->Class);
			$expected_message2 = sprintf($message_part_template, $i);
			$this->assertEquals($expected_message2, $row->TextDecoded);
			$this->assertEquals(1, $row->ID);
			$this->assertEquals($i, $row->SequencePosition);
			$row = $result_multipart->next_row();
		}

		$result_smsused = $this->CI->db->get('sms_used');
		$this->assertEquals(1, $result_smsused->num_rows());
		$row = $result_smsused->row();
		$this->assertEquals(date('Y-m-d'), $row->sms_date);
		$this->assertEquals($data['uid'], $row->id_user);
		$this->assertEquals($parts, $row->out_sms_count);
		$this->assertEquals(0, $row->in_sms_count);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_send_messages_multipart_gsm7bit($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		// Load session (the function uses add_sms_used() which needs id_user)
		$this->CI->load->library('session');
		$_SESSION['loggedin'] = 'TRUE';
		$_SESSION['id_user'] = '1';
		$_SESSION['level'] = 'admin';
		$_SESSION['username'] = 'kalkun';

		$this->CI->load->database();
		$this->CI->load->model('Kalkun_model');

		$parts = 18;
		$message = '';
		$message_part_template = '<START%02d Multipart gsm default charset with specials ^{}\[]~|€ ---------------------------------------------------------------------------- END>';
		for ($i = 1; $i <= $parts; $i++)
		{
			$message .= sprintf($message_part_template, $i);
		}
		$data = [
			'date' => date('Y-m-d H:i:s'),
			'dest' => '23456', // Should resolve to +123456 for user 1 with region US
			'message' => $message,
			'class' => '1',
			'delivery_report' => 'yes',
			'uid' => '2',
			'type' => 'normal', // normal, flash or waplink
		];

		$function_result = $this->obj->send_messages($data);

		$expected = 'Message queued.';
		$this->assertEquals($expected, $function_result['status']);

		$result = $this->CI->db->get('outbox');
		$row = $result->row();
		$this->assertEquals($data['date'], $row->SendingDateTime);
		$this->assertEquals('', $row->Text);
		$this->assertEquals('+123456', $row->DestinationNumber);
		$this->assertEquals('Default_No_Compression', $row->Coding);
		$this->_assertMatchesRegularExpression('/050003..'.sprintf('%02X', $parts).'01/', $row->UDH);
		$this->assertEquals(1, $row->Class);
		$expected_message = sprintf($message_part_template, 1);
		$this->assertEquals($expected_message, $row->TextDecoded);
		$this->assertEquals(1, $row->ID);
		$this->assertEquals(TRUE, db_boolean_to_php_bool($db_engine, $row->MultiPart));
		$this->assertEquals('-1', $row->RelativeValidity);
		$this->assertEquals(NULL, $row->SenderID);
		$this->assertEquals($data['delivery_report'], $row->DeliveryReport);
		$this->assertEquals('🦃 Kalkun '.$this->CI->config->item('kalkun_version'), $row->CreatorID);

		$result_multipart = $this->CI->db->get('outbox_multipart');
		$this->assertEquals(($parts - 1), $result_multipart->num_rows());
		$row = $result_multipart->row();
		for ($i = 2; $i <= $parts; $i++)
		{
			$this->assertEquals('', $row->Text);
			$this->assertEquals('Default_No_Compression', $row->Coding);
			$this->_assertMatchesRegularExpression('/050003..'.sprintf('%02X', $parts).sprintf('%02X', $i).'/', $row->UDH);
			$this->assertEquals(1, $row->Class);
			$expected_message2 = sprintf($message_part_template, $i);
			$this->assertEquals($expected_message2, $row->TextDecoded);
			$this->assertEquals(1, $row->ID);
			$this->assertEquals($i, $row->SequencePosition);
			$row = $result_multipart->next_row();
		}

		$result_smsused = $this->CI->db->get('sms_used');
		$this->assertEquals(1, $result_smsused->num_rows());
		$row = $result_smsused->row();
		$this->assertEquals(date('Y-m-d'), $row->sms_date);
		$this->assertEquals($data['uid'], $row->id_user);
		$this->assertEquals($parts, $row->out_sms_count);
		$this->assertEquals(0, $row->in_sms_count);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_send_messages_flash($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		// Load session (the function uses add_sms_used() which needs id_user)
		$this->CI->load->library('session');
		$_SESSION['loggedin'] = 'TRUE';
		$_SESSION['id_user'] = '1';
		$_SESSION['level'] = 'admin';
		$_SESSION['username'] = 'kalkun';

		$this->CI->load->database();
		$this->CI->load->model('Kalkun_model');

		$data = [
			'date' => date('Y-m-d H:i:s'),
			'dest' => '23456', // Should resolve to +123456 for user 1 with region US
			'message' => 'Non unicode text with specials ^{}\[]~|€| and exactly 160 chars --------------------------------------------------------------------------------------',
			'class' => '0',
			'delivery_report' => 'default',
			'uid' => '2',
			'type' => 'flash', // normal, flash or waplink
		];

		$function_result = $this->obj->send_messages($data);

		$expected = 'Message queued.';
		$this->assertEquals($expected, $function_result['status']);

		$result = $this->CI->db->get('outbox');
		$this->assertEquals($data['date'], $result->row()->SendingDateTime);
		$this->assertEquals('', $result->row()->Text);
		$this->assertEquals('+123456', $result->row()->DestinationNumber);
		$this->assertEquals('Default_No_Compression', $result->row()->Coding);
		$this->assertEquals('', $result->row()->UDH);
		$this->assertEquals(0, $result->row()->Class);
		$this->assertEquals($data['message'], $result->row()->TextDecoded);
		$this->assertEquals(1, $result->row()->ID);
		$this->assertEquals(FALSE, db_boolean_to_php_bool($db_engine, $result->row()->MultiPart));
		$this->assertEquals('-1', $result->row()->RelativeValidity);
		$this->assertEquals(NULL, $result->row()->SenderID);
		$this->assertEquals($data['delivery_report'], $result->row()->DeliveryReport);
		$this->assertEquals('🦃 Kalkun '.$this->CI->config->item('kalkun_version'), $result->row()->CreatorID);

		$result_multipart = $this->CI->db->get('outbox_multipart');
		$this->assertEquals(0, $result_multipart->num_rows());

		$result_smsused = $this->CI->db->get('sms_used');
		$this->assertEquals(1, $result_smsused->num_rows());
		$row = $result_smsused->row();
		$this->assertEquals(date('Y-m-d'), $row->sms_date);
		$this->assertEquals($data['uid'], $row->id_user);
		$this->assertEquals(1, $row->out_sms_count);
		$this->assertEquals(0, $row->in_sms_count);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_send_messages_override_defaults($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		// Load session (the function uses add_sms_used() which needs id_user)
		$this->CI->load->library('session');
		$_SESSION['loggedin'] = 'TRUE';
		$_SESSION['id_user'] = '1';
		$_SESSION['level'] = 'admin';
		$_SESSION['username'] = 'kalkun';

		$this->CI->load->database();
		$this->CI->load->model('Kalkun_model');

		$data = [
			'date' => date('Y-m-d H:i:s'),
			'dest' => '23456', // Should resolve to +123456 for user 1 with region US
			'message' => 'Non unicode text with specials ^{}\[]~|€| and exactly 160 chars --------------------------------------------------------------------------------------',
			'class' => '1',
			'delivery_report' => 'default',
			'uid' => '2',
			'type' => 'normal', // normal, flash or waplink
			'SenderID' => '+33600000111',
			'CreatorID' => 'custom creator',
			'validity' => '5',
		];

		$function_result = $this->obj->send_messages($data);

		$expected = 'Message queued.';
		$this->assertEquals($expected, $function_result['status']);

		$result = $this->CI->db->get('outbox');
		$this->assertEquals($data['date'], $result->row()->SendingDateTime);
		$this->assertEquals('', $result->row()->Text);
		$this->assertEquals('+123456', $result->row()->DestinationNumber);
		$this->assertEquals('Default_No_Compression', $result->row()->Coding);
		$this->assertEquals('', $result->row()->UDH);
		$this->assertEquals(1, $result->row()->Class);
		$this->assertEquals($data['message'], $result->row()->TextDecoded);
		$this->assertEquals(1, $result->row()->ID);
		$this->assertEquals(FALSE, db_boolean_to_php_bool($db_engine, $result->row()->MultiPart));
		$this->assertEquals($data['validity'], $result->row()->RelativeValidity);
		$this->assertEquals($data['SenderID'], $result->row()->SenderID);
		$this->assertEquals($data['delivery_report'], $result->row()->DeliveryReport);
		$this->assertEquals('🦃 Kalkun '.$this->CI->config->item('kalkun_version'), $result->row()->CreatorID);

		$result_multipart = $this->CI->db->get('outbox_multipart');
		$this->assertEquals(0, $result_multipart->num_rows());

		$result_smsused = $this->CI->db->get('sms_used');
		$this->assertEquals(1, $result_smsused->num_rows());
		$row = $result_smsused->row();
		$this->assertEquals(date('Y-m-d'), $row->sms_date);
		$this->assertEquals($data['uid'], $row->id_user);
		$this->assertEquals(1, $row->out_sms_count);
		$this->assertEquals(0, $row->in_sms_count);
	}

	public static function _send_message_route_Provider()
	{
		return DBSetup::prepend_db_engine([
			'single, with default delivery report' => [['date' => date('Y-m-d H:i:s'),
				'dest' => '23456', // Should resolve to +123456 for user 1 with region US
				'message' => 'default_text', // if multipart, only the 1st part of the message
				'class' => '0',
				'CreatorID' => 'Kalkun_test_creator',
				'SenderID' => '+33698658754',
				'validity' => '-1', // -1 (default), 0 (5min), 1 (10min), ..., 255 (maximum)
				'delivery_report' => 'default', // default, yes, no, NOT NULL
				'option' => 'single', // single, multipart
				'UDH' => '', // set if multipart
				'part' => '', // Count of parts, set if multipart
				'uid' => '2', ]],
			'multipart, with delivery report' => [['date' => date('Y-m-d H:i:s'),
				'dest' => '+33612345678',
				'message' => 'unicode text with emoji 😁!\'', // if multipart, only the 1st part of the message
				'class' => '1',
				'CreatorID' => 'Kalkun_test_creator',
				'SenderID' => '+33698658744',
				'validity' => '5', // -1 (default), 0 (5min), 1 (10min), ..., 255 (maximum)
				'delivery_report' => 'yes', // default, yes, no, NOT NULL
				'option' => 'multipart', // single, multipart
				'UDH' => '050003AC', // set if multipart
				'part' => '5', // Count of parts, set if multipart
				'uid' => '2', ]],
			'multipart, without delivery report' => [['date' => date('Y-m-d H:i:s'),
				'dest' => '+33612345678',
				'message' => 'unicode text with emoji 😁!\'', // if multipart, only the 1st part of the message
				'class' => '1',
				'CreatorID' => 'Kalkun_test_creator',
				'SenderID' => '+33698658744',
				'validity' => '5', // -1 (default), 0 (5min), 1 (10min), ..., 255 (maximum)
				'delivery_report' => 'no', // default, yes, no, NOT NULL
				'option' => 'multipart', // single, multipart
				'UDH' => '050003AC', // set if multipart
				'part' => '25', // Count of parts, set if multipart
				'uid' => '2', ]],
		]);
	}

	/**
	 * @dataProvider _send_message_route_Provider
	 */
	#[DataProvider('_send_message_route_Provider')]
	public function test__send_message_route($db_engine, $tmp_data)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		// Load session (the function uses phone_format_e164() needs session to get the country_code)
		$this->CI->load->library('session');
		$_SESSION['loggedin'] = 'TRUE';
		$_SESSION['id_user'] = '1';
		$_SESSION['level'] = 'admin';
		$_SESSION['username'] = 'kalkun';

		$this->CI->load->database();
		$this->CI->load->helper('kalkun');
		$this->CI->load->config('kalkun_settings');

		$function_result = $this->obj->_send_message_route($tmp_data);

		$output_outbox = $this->CI->db->where('ID', 1)->get('outbox');
		$output_user_outbox = $this->CI->db->where('id_outbox', 1)->get('user_outbox');

		$this->assertEquals($tmp_data['date'], $output_outbox->row()->SendingDateTime);
		$this->assertEquals(phone_format_e164($tmp_data['dest']), $output_outbox->row()->DestinationNumber);
		$this->assertEquals(get_gammu_coding($tmp_data['message']), $output_outbox->row()->Coding);
		$this->assertEquals($tmp_data['class'], $output_outbox->row()->Class);
		$this->assertEquals('🦃 Kalkun ' . $this->CI->config->item('kalkun_version'), $output_outbox->row()->CreatorID);
		$this->assertEquals($tmp_data['SenderID'], $output_outbox->row()->SenderID);
		$this->assertEquals($tmp_data['message'], $output_outbox->row()->TextDecoded);
		$this->assertEquals($tmp_data['validity'], $output_outbox->row()->RelativeValidity);
		$this->assertEquals($tmp_data['delivery_report'], $output_outbox->row()->DeliveryReport);
		$this->assertEquals($tmp_data['message'], $output_outbox->row()->TextDecoded);

		if ($tmp_data['option'] === 'multipart')
		{
			$this->assertEquals(TRUE, db_boolean_to_php_bool($db_engine, $output_outbox->row()->MultiPart));
			$this->assertEquals($tmp_data['UDH'] . (sprintf('%02X', $tmp_data['part'])) . '01', $output_outbox->row()->UDH);
			$this->assertEquals(1, $function_result);
		}
		else
		{
			$this->assertEquals(FALSE, db_boolean_to_php_bool($db_engine, $output_outbox->row()->MultiPart));
			$this->assertEquals('', $output_outbox->row()->UDH);
		}

		$this->assertEquals(1, $output_user_outbox->row()->id_outbox);
		$this->assertEquals(2, $output_user_outbox->row()->id_user);
	}

	public static function _send_message_multipart_Provider()
	{
		return DBSetup::prepend_db_engine([
			['outboxid' => 5,
				'message' => 'message text',
				'pos' => 1,
				'part' => '02',
				'coding' => 'Default_No_Compression',
				'class' => '0',
				'UDH' => '050003AC'],
			['outboxid' => 6,
				'message' => 'message text',
				'pos' => 3,
				'part' => '03',
				'coding' => 'Unicode_No_Compression',
				'class' => '1',
				'UDH' => '050003AC'],
			['outboxid' => 6,
				'message' => 'message text',
				'pos' => 14, // So that we can check that we have a hex value of 0F in UDH (pos+1 = 15 = 0xF)
				'part' => '25', // So that we can check that we have a hex value of 19 in UDH
				'coding' => 'Default_Compression',
				'class' => '127',
				'UDH' => '050003AC'],
			['outboxid' => 6,
				'message' => 'message text',
				'pos' => 3,
				'part' => '03',
				'coding' => 'Unicode_Compression',
				'class' => '0',
				'UDH' => '050003AC'],
			['outboxid' => 6,
				'message' => 'message text',
				'pos' => 3,
				'part' => '03',
				'coding' => '8bit',
				'class' => '-1',
				'UDH' => '050003AC']]);
	}

	/**
	 * @dataProvider _send_message_multipart_Provider
	 */
	#[DataProvider('_send_message_multipart_Provider')]
	public function test__send_message_multipart($db_engine, $outboxid, $message, $pos, $part, $coding, $class, $UDH)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$this->CI->load->database();

		$this->obj->_send_message_multipart($outboxid, $message, $pos, $part, $coding, $class, $UDH);

		$output = $this->CI->db->where('ID', $outboxid)->get('outbox_multipart');

		$this->assertEquals($outboxid, $output->row()->ID);
		$this->assertEquals($UDH . (sprintf('%02X', $part)) . (sprintf('%02X', ($pos + 1))), $output->row()->UDH);
		$this->assertEquals(($pos + 1), $output->row()->SequencePosition);
		$this->assertEquals($coding, $output->row()->Coding);
		$this->assertEquals(($class === NULL) ? -1 : $class, $output->row()->Class);
		$this->assertEquals($message, $output->row()->TextDecoded);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_search_messages($db_engine)
	{
		$this->markTestIncomplete();
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_get_messages($db_engine)
	{
		$this->markTestIncomplete();
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_get_modem_list($db_engine)
	{
		$this->markTestIncomplete();
	}

	public function test__default()
	{
		$array1 = ['key1' => 'value1', 'key2' => 'value2'];
		$array2 = ['key1' => 'value3'];

		$expected = ['key1' => 'value3', 'key2' => 'value2'];

		$output = $this->obj->_default($array1, $array2, $expected);
		$this->assertEquals($expected, $output);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_get_conversation($db_engine)
	{
		$this->markTestIncomplete();
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_move_messages($db_engine)
	{
		$this->markTestIncomplete();
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_delete_messages($db_engine)
	{
		$this->markTestIncomplete();
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_get_multipart($db_engine)
	{
		$this->markTestIncomplete();
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_update_read($db_engine)
	{
		$this->markTestIncomplete();
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_insert_user_sentitems($db_engine)
	{
		$this->markTestIncomplete();
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_get_user_outbox($db_engine)
	{
		$this->markTestIncomplete();
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_delete_user_outbox($db_engine)
	{
		$this->markTestIncomplete();
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_update_processed($db_engine)
	{
		$this->markTestIncomplete();
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_update_owner($db_engine)
	{
		$this->markTestIncomplete();
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_copy_message($db_engine)
	{
		$this->markTestIncomplete();
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_canned_response($db_engine)
	{
		$this->markTestIncomplete();
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_process_outbox_queue($db_engine)
	{
		$this->markTestIncomplete();
	}
}
