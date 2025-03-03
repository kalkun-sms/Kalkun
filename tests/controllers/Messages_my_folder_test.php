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

use Symfony\Component\DomCrawler\Crawler;

class Messages_my_folder_test extends KalkunTestCase {

	public function setUp() : void
	{
		if (file_exists(FCPATH . 'install'))
		{
			unlink(FCPATH . 'install');
		}
		DBSetup::setup_db_kalkun_testing2($this);
	}

	public function reloadCIwithEngine($db_engine)
	{
		DBSetup::$current_setup[$db_engine]->write_config_file_for_database();
	}

	public static function database_Provider()
	{
		return DBSetup::$db_engines_to_test;
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_my_folder_ajaxGET_invalid($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

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

		$id_folder = 11;
		$output = $this->ajaxRequest('GET', 'messages/my_folder/invalid/' . $id_folder);
		$this->assertResponseCode(400);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_my_folder_ajaxGET_inbox_no_message_in_folder($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->request->setCallablePreConstructor(
			function () {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '3';
				$_SESSION['level'] = 'user';
				$_SESSION['username'] = 'user2';
			}
		);

		$id_folder = 13;
		$output = $this->ajaxRequest('GET', 'messages/my_folder/inbox/' . $id_folder);
		$crawler = new Crawler($output);
		$this->assertEquals('There is no message in this folder.', $this->crawler_text($crawler->filter('p.no_content > i ')));

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_my_folder_ajaxGET_inbox_trash_no_message_in_folder($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->request->setCallablePreConstructor(
			function () {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '3';
				$_SESSION['level'] = 'user';
				$_SESSION['username'] = 'user2';
			}
		);

		$id_folder = 5;
		$output = $this->ajaxRequest('GET', 'messages/my_folder/inbox/' . $id_folder);
		$crawler = new Crawler($output);
		$this->assertEquals('There is no message in Trash.', $this->crawler_text($crawler->filter('p.no_content > i ')));

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_my_folder_ajaxGET_inbox_byConversation_single_unread_contactInPbk($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->request->setCallablePreConstructor(
			function () {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '9';
				$_SESSION['level'] = 'user';
				$_SESSION['username'] = 'user8';
			}
		);

		// Set conversation grouping
		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = '<?php '
					. '$config[\'conversation_grouping\'] = TRUE;';
		$configFile->write($content);
		$this->resetInstance();

		$db = &DBSetup::$current_setup[$db_engine];

		$id_user = 9;
		$db->update('user_settings', ['id_user' => $id_user], ['conversation_sort' => 'asc']);
		$per_page = 20;
		$db->update('user_settings', ['id_user' => $id_user], ['paging' => $per_page]);
		$db->update('inbox', ['TextDecoded' => '%User '.$id_user.'%'], ['readed' => 'false']);

		$this->request->addCallable($db->closure());

		$id_folder = 18;
		$output = $this->ajaxRequest('GET', 'messages/my_folder/inbox/' . $id_folder);
		$crawler = new Crawler($output);

		$this->assertEquals(1, $crawler->filter('div.messagelist.unreaded')->count());
		$message_text = DBSetup::text_replace_placeholder('User 9. Message 1. Sender 1. Folder Inbox/#' . $id_folder, DBSetup::$text_multi_unicode);
		$this->assertEquals($message_text, $crawler->filter('div.messagelist.unreaded')->attr('title'));
		$this->assertEquals('+33600000001', $crawler->filter('input.select_conversation.nicecheckbox')->attr('id'));
		$this->assertEquals('+33600000001', $crawler->filter('input.select_conversation.nicecheckbox')->attr('value'));
		$expected = 'document.location.href=\''.site_url().'/messages/conversation/my_folder/inbox/%2B33600000001/' . $id_folder . '\'';
		$this->assertEquals($expected, $crawler->filter('span.message_toggle')->attr('onclick'));
		$expected = $this->CI->config->item('img_path') . 'arrow_left.gif';
		$this->assertEquals($expected, $crawler->filter('div.message_header img')->attr('src'));
		$expected = '/Contact for number 001\.\.\.\s*\(1\)\s*/';
		$this->_assertMatchesRegularExpression($expected, $this->crawler_text($crawler->filter('div.message_header > span > span')));
		$expected = mb_substr($message_text, 0, 20);
		$this->_assertStringContainsString($expected, $this->crawler_text($crawler->filter('div.message_header > span > span.message_preview')));

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_my_folder_ajaxGET_inbox_byConversation_single_read_contactNotInPbk($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->request->setCallablePreConstructor(
			function () {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '8';
				$_SESSION['level'] = 'user';
				$_SESSION['username'] = 'user7';
			}
		);

		// Set conversation grouping
		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = '<?php '
					. '$config[\'conversation_grouping\'] = TRUE;';
		$configFile->write($content);
		$this->resetInstance();

		$id_folder = 17;
		$output = $this->ajaxRequest('GET', 'messages/my_folder/inbox/' . $id_folder);
		$crawler = new Crawler($output);

		$this->assertEquals(1, $crawler->filter('div.messagelist')->count());
		$message_text = DBSetup::text_replace_placeholder('User 8. Message 1. Sender 1. Folder Inbox/#' . $id_folder, DBSetup::$text_multi_unicode);
		$this->assertEquals($message_text, $crawler->filter('div.messagelist')->attr('title'));
		$this->assertEquals('messagelist', trim($crawler->filter('div.messagelist')->attr('class')));
		$this->assertEquals('+33600000001', $crawler->filter('input.select_conversation.nicecheckbox')->attr('id'));
		$this->assertEquals('+33600000001', $crawler->filter('input.select_conversation.nicecheckbox')->attr('value'));
		$expected = 'document.location.href=\''.site_url().'/messages/conversation/my_folder/inbox/%2B33600000001/' . $id_folder .'\'';
		$this->assertEquals($expected, $crawler->filter('span.message_toggle')->attr('onclick'));
		$expected = $this->CI->config->item('img_path') . 'arrow_left.gif';
		$this->assertEquals($expected, $crawler->filter('div.message_header img')->attr('src'));
		$expected = '/\+33 6 00 00 00 01\\s*\(1\)\s*/';
		$this->_assertMatchesRegularExpression($expected, $this->crawler_text($crawler->filter('div.message_header > span > span')));
		$expected = mb_substr($message_text, 0, 20);
		$this->_assertStringContainsString($expected, $this->crawler_text($crawler->filter('div.message_header > span > span.message_preview')));

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_my_folder_ajaxGET_inbox_notByConversation_single_unread_contactInPbk($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->request->setCallablePreConstructor(
			function () {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '9';
				$_SESSION['level'] = 'user';
				$_SESSION['username'] = 'user8';
			}
		);

		// Set conversation grouping
		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = '<?php '
					. '$config[\'conversation_grouping\'] = FALSE;';
		$configFile->write($content);
		$this->resetInstance();

		$db = &DBSetup::$current_setup[$db_engine];

		$id_user = 9;
		$db->update('user_settings', ['id_user' => $id_user], ['conversation_sort' => 'asc']);
		$per_page = 20;
		$db->update('user_settings', ['id_user' => $id_user], ['paging' => $per_page]);
		$db->update('inbox', ['TextDecoded' => '%User '.$id_user.'%'], ['readed' => 'false']);

		$this->request->addCallable($db->closure());

		$id_folder = 18;
		$output = $this->ajaxRequest('GET', 'messages/my_folder/inbox/' . $id_folder);
		$crawler = new Crawler($output);

		$this->assertEquals(1, $crawler->filter('div.messagelist.unreaded')->count());
		$message_text = DBSetup::text_replace_placeholder('User 9. Message 1. Sender 1. Folder Inbox/#' . $id_folder, DBSetup::$text_multi_unicode);
		$this->assertEquals($message_text, $crawler->filter('div.messagelist.unreaded')->attr('title'));
		$id_offset = 2534;
		$this->assertEquals($id_offset + 1, $crawler->filter('input.select_conversation.nicecheckbox')->attr('id'));
		$this->assertEquals($id_offset + 1, $crawler->filter('input.select_conversation.nicecheckbox')->attr('value'));
		$expected = 'document.location.href=\''.site_url().'/messages/conversation/my_folder/inbox/%2B33600000001/' . $id_folder .'\'';
		$this->assertEquals($expected, $crawler->filter('span.message_toggle')->attr('onclick'));
		$expected = $this->CI->config->item('img_path') . 'arrow_left.gif';
		$this->assertEquals($expected, $crawler->filter('div.message_header img')->attr('src'));
		$expected = '/Contact for number 001\.\.\.\s*\(1\)\s*/';
		$this->_assertMatchesRegularExpression($expected, $this->crawler_text($crawler->filter('div.message_header > span > span')));
		$expected = mb_substr($message_text, 0, 20);
		$this->_assertStringContainsString($expected, $this->crawler_text($crawler->filter('div.message_header > span > span.message_preview')));

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_my_folder_ajaxGET_inbox_notByConversation_single_read_contactNotInPbk($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->request->setCallablePreConstructor(
			function () {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '8';
				$_SESSION['level'] = 'user';
				$_SESSION['username'] = 'user7';
			}
		);

		// Set conversation grouping
		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = '<?php '
					. '$config[\'conversation_grouping\'] = FALSE;';
		$configFile->write($content);
		$this->resetInstance();

		$id_folder = 17;
		$output = $this->ajaxRequest('GET', 'messages/my_folder/inbox/' . $id_folder);
		$crawler = new Crawler($output);

		$this->assertEquals(1, $crawler->filter('div.messagelist')->count());
		$message_text = DBSetup::text_replace_placeholder('User 8. Message 1. Sender 1. Folder Inbox/#' . $id_folder, DBSetup::$text_multi_unicode);
		$this->assertEquals($message_text, $crawler->filter('div.messagelist')->attr('title'));
		$this->assertEquals('messagelist', trim($crawler->filter('div.messagelist')->attr('class')));
		$id_offset = 2530;
		$this->assertEquals($id_offset + 1, $crawler->filter('input.select_conversation.nicecheckbox')->attr('id'));
		$this->assertEquals($id_offset + 1, $crawler->filter('input.select_conversation.nicecheckbox')->attr('value'));
		$expected = 'document.location.href=\''.site_url().'/messages/conversation/my_folder/inbox/%2B33600000001/' . $id_folder . '\'';
		$this->assertEquals($expected, $crawler->filter('span.message_toggle')->attr('onclick'));
		$expected = $this->CI->config->item('img_path') . 'arrow_left.gif';
		$this->assertEquals($expected, $crawler->filter('div.message_header img')->attr('src'));
		$expected = '/\+33 6 00 00 00 01\\s*\(1\)\s*/';
		$this->_assertMatchesRegularExpression($expected, $this->crawler_text($crawler->filter('div.message_header > span > span')));
		$expected = mb_substr($message_text, 0, 20);
		$this->_assertStringContainsString($expected, $this->crawler_text($crawler->filter('div.message_header > span > span.message_preview')));

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	/*public function test_my_folder_ajaxGET_by_conversation_sort_asc($db_engine)
	{
		// For now when conversation grouping is enabled, messages are always sorted DESC
		// and the sorting setting of the user is not honored.
		// So the result is the same as the one in test_my_folder_ajaxGET_by_conversation_sort_desc()
	}*/

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_my_folder_ajaxGET_inbox_byConversation_sortDesc($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->request->setCallablePreConstructor(
			function () {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '4';
				$_SESSION['level'] = 'user';
				$_SESSION['username'] = 'user3';
			}
		);

		// Set conversation grouping
		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = '<?php '
					. '$config[\'conversation_grouping\'] = TRUE;';
		$configFile->write($content);
		$this->resetInstance();

		$db = &DBSetup::$current_setup[$db_engine];

		$id_user = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['conversation_sort' => 'asc']);
		$per_page = 20;
		$db->update('user_settings', ['id_user' => $id_user], ['paging' => $per_page]);
		$db->update('inbox', ['TextDecoded' => '%User '.$id_user.'%'], ['readed' => 'false']);

		$this->request->addCallable($db->closure());

		$id_folder = 15;
		$output = $this->ajaxRequest('GET', 'messages/my_folder/inbox/' . $id_folder);

		$crawler = new Crawler($output);
		$nodes = $crawler->filter('body')->children();
		$this->assertEquals(20, $nodes->count());
		for ($i = 1; $i <= 20; $i++)
		{
			$cur_node = $nodes->eq($i - 1);
			$this->assertEquals('div', $cur_node->nodeName());
			$expected = '+336000000'.sprintf('%02d', $i);
			$this->assertEquals($expected, $cur_node->filter('input.select_conversation.nicecheckbox')->attr('id'));
			$this->assertEquals($expected, $cur_node->filter('input.select_conversation.nicecheckbox')->attr('value'));
		}

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_my_folder_ajaxGET_inbox_byConversation_sortDesc_withOffset($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->request->setCallablePreConstructor(
			function () {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '4';
				$_SESSION['level'] = 'user';
				$_SESSION['username'] = 'user3';
			}
		);

		// Set conversation grouping
		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = '<?php '
					. '$config[\'conversation_grouping\'] = TRUE;';
		$configFile->write($content);
		$this->resetInstance();

		$db = &DBSetup::$current_setup[$db_engine];

		$id_user = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['conversation_sort' => 'asc']);
		$per_page = 20;
		$db->update('user_settings', ['id_user' => $id_user], ['paging' => $per_page]);
		$db->update('inbox', ['TextDecoded' => '%User '.$id_user.'%'], ['readed' => 'false']);

		$this->request->addCallable($db->closure());

		$id_folder = 15;
		$offset = 20;
		$output = $this->ajaxRequest('GET', 'messages/my_folder/inbox/'.$id_folder.'/'.$offset);

		$crawler = new Crawler($output);

		$nodes = $crawler->filter('body')->children();
		$this->assertEquals(5, $nodes->count());
		for ($i = 21; $i <= 25; $i++)
		{
			$cur_node = $nodes->eq($i - 21);
			$this->assertEquals('div', $cur_node->nodeName());
			$expected = '+336000000'.sprintf('%02d', $i);
			$this->assertEquals($expected, $cur_node->filter('input.select_conversation.nicecheckbox')->attr('id'));
			$this->assertEquals($expected, $cur_node->filter('input.select_conversation.nicecheckbox')->attr('value'));
		}

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_my_folder_ajaxGET_inbox_notByConversation_sortDesc($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->request->setCallablePreConstructor(
			function () {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '4';
				$_SESSION['level'] = 'user';
				$_SESSION['username'] = 'user3';
			}
		);

		// Set conversation grouping
		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = '<?php '
					. '$config[\'conversation_grouping\'] = FALSE;';
		$configFile->write($content);
		$this->resetInstance();

		$db = &DBSetup::$current_setup[$db_engine];

		$id_user = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['conversation_sort' => 'desc']);
		$per_page = 20;
		$db->update('user_settings', ['id_user' => $id_user], ['paging' => $per_page]);
		$db->update('inbox', ['TextDecoded' => '%User '.$id_user.'%'], ['readed' => 'false']);

		$this->request->addCallable($db->closure());

		$id_folder = 15;
		$output = $this->ajaxRequest('GET', 'messages/my_folder/inbox/'.$id_folder);

		$crawler = new Crawler($output);

		$nodes = $crawler->filter('body')->children();
		$this->assertEquals(20, $nodes->count());
		$id_offset = 1530;
		for ($i = 1; $i <= 20; $i++)
		{
			$cur_node = $nodes->eq($i - 1);
			$this->assertEquals('div', $cur_node->nodeName());
			$expected = $id_offset + ($i - 1) * DBSetup::nb_of_parts_per_inbox_message + 1;
			$this->assertEquals($expected, $cur_node->filter('input.select_conversation.nicecheckbox')->attr('id'));
			$this->assertEquals($expected, $cur_node->filter('input.select_conversation.nicecheckbox')->attr('value'));
		}

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_my_folder_ajaxGET_inbox_notByConversation_sortDesc_withOffset($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->request->setCallablePreConstructor(
			function () {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '4';
				$_SESSION['level'] = 'user';
				$_SESSION['username'] = 'user3';
			}
		);

		// Set conversation grouping
		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = '<?php '
					. '$config[\'conversation_grouping\'] = FALSE;';
		$configFile->write($content);
		$this->resetInstance();

		$db = &DBSetup::$current_setup[$db_engine];

		$id_user = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['conversation_sort' => 'desc']);
		$per_page = 20;
		$db->update('user_settings', ['id_user' => $id_user], ['paging' => $per_page]);
		$db->update('inbox', ['TextDecoded' => '%User '.$id_user.'%'], ['readed' => 'false']);

		$this->request->addCallable($db->closure());

		$offset = 20;
		$id_folder = 15;
		$output = $this->ajaxRequest('GET', 'messages/my_folder/inbox/'.$id_folder.'/'.$offset);

		$crawler = new Crawler($output);

		$nodes = $crawler->filter('body')->children();
		$this->assertEquals(20, $nodes->count());
		$id_offset = 1530;
		for ($i = 21; $i <= 40; $i++)
		{
			$cur_node = $nodes->eq($i - 21);
			$this->assertEquals('div', $cur_node->nodeName());
			$expected = $id_offset + ($i - 1) * DBSetup::nb_of_parts_per_inbox_message + 1;
			$this->assertEquals($expected, $cur_node->filter('input.select_conversation.nicecheckbox')->attr('id'));
			$this->assertEquals($expected, $cur_node->filter('input.select_conversation.nicecheckbox')->attr('value'));
		}

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_my_folder_ajaxGET_inbox_notByConversation_sortAsc($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->request->setCallablePreConstructor(
			function () {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '4';
				$_SESSION['level'] = 'user';
				$_SESSION['username'] = 'user3';
			}
		);

		// Set conversation grouping
		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = '<?php '
					. '$config[\'conversation_grouping\'] = FALSE;';
		$configFile->write($content);
		$this->resetInstance();

		$db = &DBSetup::$current_setup[$db_engine];

		$id_user = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['conversation_sort' => 'asc']);
		$per_page = 20;
		$db->update('user_settings', ['id_user' => $id_user], ['paging' => $per_page]);
		$db->update('inbox', ['TextDecoded' => '%User '.$id_user.'%'], ['readed' => 'false']);

		$this->request->addCallable($db->closure());

		$id_folder = 15;
		$output = $this->ajaxRequest('GET', 'messages/my_folder/inbox/'.$id_folder);
		$crawler = new Crawler($output);

		$nodes = $crawler->filter('body')->children();
		$this->assertEquals(20, $nodes->count());
		$id_offset = 1530;
		for ($i = 1; $i <= 20; $i++)
		{
			$cur_node = $nodes->eq($i - 1);
			$this->assertEquals('div', $cur_node->nodeName());
			$expected = $id_offset + (DBSetup::senders * DBSetup::messages_per_sender * DBSetup::nb_of_parts_per_inbox_message) - ($i * DBSetup::nb_of_parts_per_inbox_message) + 1;
			$this->assertEquals($expected, $cur_node->filter('input.select_conversation.nicecheckbox')->attr('id'));
			$this->assertEquals($expected, $cur_node->filter('input.select_conversation.nicecheckbox')->attr('value'));
		}

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_my_folder_ajaxGET_inbox_notByConversation_sortAsc_withOffset($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->request->setCallablePreConstructor(
			function () {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '4';
				$_SESSION['level'] = 'user';
				$_SESSION['username'] = 'user3';
			}
		);

		// Set conversation grouping
		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = '<?php '
					. '$config[\'conversation_grouping\'] = FALSE;';
		$configFile->write($content);
		$this->resetInstance();

		$db = &DBSetup::$current_setup[$db_engine];

		$id_user = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['conversation_sort' => 'asc']);
		$per_page = 20;
		$db->update('user_settings', ['id_user' => $id_user], ['paging' => $per_page]);
		$db->update('inbox', ['TextDecoded' => '%User '.$id_user.'%'], ['readed' => 'false']);

		$this->request->addCallable($db->closure());

		$id_folder = 15;
		$offset = 20;
		$output = $this->ajaxRequest('GET', 'messages/my_folder/inbox/'.$id_folder.'/'.$offset);
		$crawler = new Crawler($output);

		$nodes = $crawler->filter('body')->children();
		$this->assertEquals(20, $nodes->count());
		$id_offset = 1530;
		for ($i = 21; $i <= 40; $i++)
		{
			$cur_node = $nodes->eq($i - 21);
			$this->assertEquals('div', $cur_node->nodeName());
			$expected = $id_offset + (DBSetup::senders * DBSetup::messages_per_sender * DBSetup::nb_of_parts_per_inbox_message) - ($i * DBSetup::nb_of_parts_per_inbox_message) + 1;
			$this->assertEquals($expected, $cur_node->filter('input.select_conversation.nicecheckbox')->attr('id'));
			$this->assertEquals($expected, $cur_node->filter('input.select_conversation.nicecheckbox')->attr('value'));
		}

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_my_folder_ajaxGET_sentitems_no_message_in_folder($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->request->setCallablePreConstructor(
			function () {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '3';
				$_SESSION['level'] = 'user';
				$_SESSION['username'] = 'user2';
			}
		);

		$id_folder = 13;
		$output = $this->ajaxRequest('GET', 'messages/my_folder/sentitems/' . $id_folder);
		$crawler = new Crawler($output);
		$this->assertEquals('There is no message in this folder.', $this->crawler_text($crawler->filter('p.no_content > i ')));

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_my_folder_ajaxGET_sentitems_byConversation_single_contactInPbk($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->request->setCallablePreConstructor(
			function () {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '9';
				$_SESSION['level'] = 'user';
				$_SESSION['username'] = 'user8';
			}
		);

		// Set conversation grouping
		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = '<?php '
					. '$config[\'conversation_grouping\'] = TRUE;';
		$configFile->write($content);
		$this->resetInstance();

		$id_folder = 18;
		$output = $this->ajaxRequest('GET', 'messages/my_folder/sentitems/' . $id_folder);
		$crawler = new Crawler($output);

		$this->assertEquals(1, $crawler->filter('div.messagelist')->count());
		$message_text = DBSetup::get_multi_parts(DBSetup::text_replace_placeholder('User 9. Message 1. Recipient 1. Folder Sentitems/#' . $id_folder, DBSetup::$text_multi_gsm))[0];
		$this->assertEquals($message_text, $crawler->filter('div.messagelist')->attr('title'));
		$this->assertEquals('messagelist', trim($crawler->filter('div.messagelist')->attr('class')));
		$this->assertEquals('+33600000001', $crawler->filter('input.select_conversation.nicecheckbox')->attr('id'));
		$this->assertEquals('+33600000001', $crawler->filter('input.select_conversation.nicecheckbox')->attr('value'));
		$expected = 'document.location.href=\''.site_url().'/messages/conversation/my_folder/sentitems/%2B33600000001/' . $id_folder . '\'';
		$this->assertEquals($expected, $crawler->filter('span.message_toggle')->attr('onclick'));
		$expected = $this->CI->config->item('img_path') . 'arrow_right.gif';
		$this->assertEquals($expected, $crawler->filter('div.message_header img')->attr('src'));
		$expected = '/Contact for number 001\.\.\.\s*\(1\)\s*/';
		$this->_assertMatchesRegularExpression($expected, $this->crawler_text($crawler->filter('div.message_header > span > span')));
		$expected = mb_substr($message_text, 0, 20);
		$this->_assertStringContainsString($expected, $this->crawler_text($crawler->filter('div.message_header > span > span.message_preview')));

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_my_folder_ajaxGET_sentitems_byConversation_single_contactNotInPbk($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->request->setCallablePreConstructor(
			function () {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '8';
				$_SESSION['level'] = 'user';
				$_SESSION['username'] = 'user7';
			}
		);

		// Set conversation grouping
		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = '<?php '
					. '$config[\'conversation_grouping\'] = TRUE;';
		$configFile->write($content);
		$this->resetInstance();

		$id_folder = 17;
		$output = $this->ajaxRequest('GET', 'messages/my_folder/sentitems/' . $id_folder);
		$crawler = new Crawler($output);

		$this->assertEquals(1, $crawler->filter('div.messagelist')->count());
		$message_text = DBSetup::get_multi_parts(DBSetup::text_replace_placeholder('User 8. Message 1. Recipient 1. Folder Sentitems/#' . $id_folder, DBSetup::$text_multi_gsm))[0];
		$this->assertEquals($message_text, $crawler->filter('div.messagelist')->attr('title'));
		$this->assertEquals('messagelist', trim($crawler->filter('div.messagelist')->attr('class')));
		$this->assertEquals('+33600000001', $crawler->filter('input.select_conversation.nicecheckbox')->attr('id'));
		$this->assertEquals('+33600000001', $crawler->filter('input.select_conversation.nicecheckbox')->attr('value'));
		$expected = 'document.location.href=\''.site_url().'/messages/conversation/my_folder/sentitems/%2B33600000001/'.$id_folder.'\'';
		$this->assertEquals($expected, $crawler->filter('span.message_toggle')->attr('onclick'));
		$expected = $this->CI->config->item('img_path') . 'arrow_right.gif';
		$this->assertEquals($expected, $crawler->filter('div.message_header img')->attr('src'));
		$expected = '/\+33 6 00 00 00 01\\s*\(1\)\s*/';
		$this->_assertMatchesRegularExpression($expected, $this->crawler_text($crawler->filter('div.message_header > span > span')));
		$expected = mb_substr($message_text, 0, 20);
		$this->_assertStringContainsString($expected, $this->crawler_text($crawler->filter('div.message_header > span > span.message_preview')));

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	/*public function test_my_folder_ajaxGET_by_conversation_sort_asc($db_engine)
	{
		// For now when conversation grouping is enabled, messages are always sorted DESC
		// and the sorting setting of the user is not honored.
		// So the result is the same as the one in test_my_folder_ajaxGET_by_conversation_sort_desc()
	}*/

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_my_folder_ajaxGET_sentitems_byConversation_sortDesc($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->request->setCallablePreConstructor(
			function () {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '4';
				$_SESSION['level'] = 'user';
				$_SESSION['username'] = 'user3';
			}
		);

		// Set conversation grouping
		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = '<?php '
					. '$config[\'conversation_grouping\'] = TRUE;';
		$configFile->write($content);
		$this->resetInstance();

		$db = &DBSetup::$current_setup[$db_engine];

		$id_user = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['conversation_sort' => 'asc']);
		$per_page = 20;
		$db->update('user_settings', ['id_user' => $id_user], ['paging' => $per_page]);
		$db->update('inbox', ['TextDecoded' => '%User '.$id_user.'%'], ['readed' => 'false']);

		$this->request->addCallable($db->closure());

		$id_folder = 15;
		$output = $this->ajaxRequest('GET', 'messages/my_folder/sentitems/' . $id_folder);

		$crawler = new Crawler($output);

		$nodes = $crawler->filter('body')->children();
		$this->assertEquals(20, $nodes->count());
		for ($i = 1; $i <= 20; $i++)
		{
			$cur_node = $nodes->eq($i - 1);
			$this->assertEquals('div', $cur_node->nodeName());
			$expected = '+336000000'.sprintf('%02d', $i);
			$this->assertEquals($expected, $cur_node->filter('input.select_conversation.nicecheckbox')->attr('id'));
			$this->assertEquals($expected, $cur_node->filter('input.select_conversation.nicecheckbox')->attr('value'));
		}

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_my_folder_ajaxGET_sentitems_byConversation_sortDesc_withOffset($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->request->setCallablePreConstructor(
			function () {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '4';
				$_SESSION['level'] = 'user';
				$_SESSION['username'] = 'user3';
			}
		);

		// Set conversation grouping
		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = '<?php '
					. '$config[\'conversation_grouping\'] = TRUE;';
		$configFile->write($content);
		$this->resetInstance();

		$db = &DBSetup::$current_setup[$db_engine];

		$id_user = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['conversation_sort' => 'asc']);
		$per_page = 20;
		$db->update('user_settings', ['id_user' => $id_user], ['paging' => $per_page]);
		$db->update('inbox', ['TextDecoded' => '%User '.$id_user.'%'], ['readed' => 'false']);

		$this->request->addCallable($db->closure());

		$id_folder = 15;
		$offset = 20;
		$output = $this->ajaxRequest('GET', 'messages/my_folder/sentitems/'.$id_folder.'/'.$offset);

		$crawler = new Crawler($output);

		$nodes = $crawler->filter('body')->children();
		$this->assertEquals(5, $nodes->count());
		for ($i = 21; $i <= 25; $i++)
		{
			$cur_node = $nodes->eq($i - 21);
			$this->assertEquals('div', $cur_node->nodeName());
			$expected = '+336000000'.sprintf('%02d', $i);
			$this->assertEquals($expected, $cur_node->filter('input.select_conversation.nicecheckbox')->attr('id'));
			$this->assertEquals($expected, $cur_node->filter('input.select_conversation.nicecheckbox')->attr('value'));
		}

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_my_folder_ajaxGET_sentitems_notByConversation_sortDesc($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->request->setCallablePreConstructor(
			function () {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '4';
				$_SESSION['level'] = 'user';
				$_SESSION['username'] = 'user3';
			}
		);

		// Set conversation grouping
		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = '<?php '
					. '$config[\'conversation_grouping\'] = FALSE;';
		$configFile->write($content);
		$this->resetInstance();

		$db = &DBSetup::$current_setup[$db_engine];

		$id_user = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['conversation_sort' => 'desc']);
		$per_page = 20;
		$db->update('user_settings', ['id_user' => $id_user], ['paging' => $per_page]);
		$db->update('inbox', ['TextDecoded' => '%User '.$id_user.'%'], ['readed' => 'false']);

		$this->request->addCallable($db->closure());

		$id_folder = 15;
		$output = $this->ajaxRequest('GET', 'messages/my_folder/sentitems/'.$id_folder);

		$crawler = new Crawler($output);

		$nodes = $crawler->filter('body')->children();
		$this->assertEquals(20, $nodes->count());
		$id_offset = 1276;
		for ($i = 1; $i <= 20; $i++)
		{
			$cur_node = $nodes->eq($i - 1);
			$this->assertEquals('div', $cur_node->nodeName());
			$expected = $id_offset + $i;
			$this->assertEquals($expected, $cur_node->filter('input.select_conversation.nicecheckbox')->attr('id'));
			$this->assertEquals($expected, $cur_node->filter('input.select_conversation.nicecheckbox')->attr('value'));
		}

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_my_folder_ajaxGET_sentitems_notByConversation_sortDesc_withOffset($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->request->setCallablePreConstructor(
			function () {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '4';
				$_SESSION['level'] = 'user';
				$_SESSION['username'] = 'user3';
			}
		);

		// Set conversation grouping
		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = '<?php '
					. '$config[\'conversation_grouping\'] = FALSE;';
		$configFile->write($content);
		$this->resetInstance();

		$db = &DBSetup::$current_setup[$db_engine];

		$id_user = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['conversation_sort' => 'desc']);
		$per_page = 20;
		$db->update('user_settings', ['id_user' => $id_user], ['paging' => $per_page]);
		$db->update('inbox', ['TextDecoded' => '%User '.$id_user.'%'], ['readed' => 'false']);

		$this->request->addCallable($db->closure());

		$id_folder = 15;
		$offset = 20;
		$output = $this->ajaxRequest('GET', 'messages/my_folder/sentitems/'.$id_folder.'/'.$offset);

		$crawler = new Crawler($output);

		$nodes = $crawler->filter('body')->children();
		$this->assertEquals(20, $nodes->count());
		$id_offset = 1276;
		for ($i = 21; $i <= 40; $i++)
		{
			$cur_node = $nodes->eq($i - 21);
			$this->assertEquals('div', $cur_node->nodeName());
			$expected = $id_offset + $i;
			$this->assertEquals($expected, $cur_node->filter('input.select_conversation.nicecheckbox')->attr('id'));
			$this->assertEquals($expected, $cur_node->filter('input.select_conversation.nicecheckbox')->attr('value'));
		}

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_my_folder_ajaxGET_sentitems_notByConversation_sortAsc($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->request->setCallablePreConstructor(
			function () {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '4';
				$_SESSION['level'] = 'user';
				$_SESSION['username'] = 'user3';
			}
		);

		// Set conversation grouping
		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = '<?php '
					. '$config[\'conversation_grouping\'] = FALSE;';
		$configFile->write($content);
		$this->resetInstance();

		$db = &DBSetup::$current_setup[$db_engine];

		$id_user = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['conversation_sort' => 'asc']);
		$per_page = 20;
		$db->update('user_settings', ['id_user' => $id_user], ['paging' => $per_page]);
		$db->update('inbox', ['TextDecoded' => '%User '.$id_user.'%'], ['readed' => 'false']);

		$this->request->addCallable($db->closure());

		$id_folder = 15;
		$output = $this->ajaxRequest('GET', 'messages/my_folder/sentitems/'.$id_folder);
		$crawler = new Crawler($output);

		$nodes = $crawler->filter('body')->children();
		$this->assertEquals(20, $nodes->count());
		$id_offset = 1276;
		for ($i = 1; $i <= 20; $i++)
		{
			$cur_node = $nodes->eq($i - 1);
			$this->assertEquals('div', $cur_node->nodeName());
			$expected = $id_offset + (DBSetup::recipients * DBSetup::messages_per_recipient) - $i + 1;
			$this->assertEquals($expected, $cur_node->filter('input.select_conversation.nicecheckbox')->attr('id'));
			$this->assertEquals($expected, $cur_node->filter('input.select_conversation.nicecheckbox')->attr('value'));
		}

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_my_folder_ajaxGET_sentitems_notByConversation_sortAsc_withOffset($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->request->setCallablePreConstructor(
			function () {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '4';
				$_SESSION['level'] = 'user';
				$_SESSION['username'] = 'user3';
			}
		);

		// Set conversation grouping
		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = '<?php '
					. '$config[\'conversation_grouping\'] = FALSE;';
		$configFile->write($content);
		$this->resetInstance();

		$db = &DBSetup::$current_setup[$db_engine];

		$id_user = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['conversation_sort' => 'asc']);
		$per_page = 20;
		$db->update('user_settings', ['id_user' => $id_user], ['paging' => $per_page]);
		$db->update('inbox', ['TextDecoded' => '%User '.$id_user.'%'], ['readed' => 'false']);

		$this->request->addCallable($db->closure());

		$id_folder = 15;
		$offset = 20;
		$output = $this->ajaxRequest('GET', 'messages/my_folder/sentitems/'.$id_folder.'/'.$offset);
		$crawler = new Crawler($output);

		$nodes = $crawler->filter('body')->children();
		$this->assertEquals(20, $nodes->count());
		$id_offset = 1276;
		for ($i = 21; $i <= 40; $i++)
		{
			$cur_node = $nodes->eq($i - 21);
			$this->assertEquals('div', $cur_node->nodeName());
			$expected = $id_offset + (DBSetup::recipients * DBSetup::messages_per_recipient) - ($i) + 1;
			$this->assertEquals($expected, $cur_node->filter('input.select_conversation.nicecheckbox')->attr('id'));
			$this->assertEquals($expected, $cur_node->filter('input.select_conversation.nicecheckbox')->attr('value'));
		}

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_my_folder_GET_inbox_byConversation_withOffset($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->request->setCallablePreConstructor(
			function () {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '4';
				$_SESSION['level'] = 'user';
				$_SESSION['username'] = 'user3';
			}
		);

		// Set conversation grouping
		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = '<?php '
					. '$config[\'conversation_grouping\'] = TRUE;';
		$configFile->write($content);
		$this->resetInstance();

		$db = &DBSetup::$current_setup[$db_engine];

		$id_user = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['conversation_sort' => 'asc']);
		$per_page = 20;
		$db->update('user_settings', ['id_user' => $id_user], ['paging' => $per_page]);
		$db->update('inbox', ['TextDecoded' => '%User '.$id_user.'%'], ['readed' => 'false']);

		$this->request->addCallable($db->closure());

		$id_folder = 15;
		$offset = floor(DBSetup::recipients / $per_page) * $per_page;
		$last_page = ceil(DBSetup::recipients / $per_page);
		$output = $this->request('GET', 'messages/my_folder/inbox/'.$id_folder.'/'.$offset);
		$crawler = new Crawler($output);

		$this->assertEquals(1, $crawler->filter('#js_function')->count());
		$this->assertEquals(0, $crawler->filter('#js_conversation')->count());
		$this->assertEquals(16, $crawler->filter('div.move_to')->attr('data-id_folder'));
		$this->assertEquals(1, $crawler->filter('#renamefolderdialog')->count());
		$this->assertEquals(1, $crawler->filter('#deletefolderdialog')->count());
		$this->assertEquals(1, $crawler->filter('#deletealldialog')->count());
		$this->assertEquals(1, $crawler->filter('#two_column_container')->count());
		$folder_name = 'user3, folder1 (id:15)';
		$this->assertEquals($folder_name, $this->crawler_text($crawler->filter('span.folder_name')));
		$this->assertEquals(0, $crawler->filter('#delete-all-link')->count());
		$this->assertEquals(1, $crawler->filter('#renamefolder')->count());
		$this->assertEquals(1, $crawler->filter('#deletefolder')->count());
		$this->assertEquals(1, $crawler->filter('span.currenttab')->count());
		$this->assertEquals('Inbox', $this->crawler_text($crawler->filter('span.currenttab')));
		$this->assertEquals(site_url().'/messages/my_folder/sentitems/'.$id_folder, $crawler->filter('span.currenttab')->nextAll()->attr('href'));
		$this->assertEquals('Sent items', $this->crawler_text($crawler->filter('span.currenttab')->nextAll()));

		$this->assertEquals(1, $crawler->filter('#message_holder')->count());
		$this->assertEquals(5, $crawler->filter('div.messagelist.unreaded')->count());
		$this->assertEquals(0, $crawler->filter('div.messagelist_conversation')->count());

		// navigation.php (buttons)
		$this->assertEquals(0, $crawler->filter('.back_threadlist')->count());
		//$this->assertEquals('Back to Inbox', $this->crawler_text($crawler->filter('.back_threadlist')));
		//$this->assertEquals(site_url().'/messages/folder/inbox', $crawler->filter('.back_threadlist')->attr('href'));
		$this->assertEquals(2, $crawler->filter('.select_all_button')->count());
		$this->assertEquals(2, $crawler->filter('.clear_all_button')->count());
		$this->assertEquals(0, $crawler->filter('.recover_button')->count());
		$this->assertEquals(2, $crawler->filter('.move_to_button')->count());
		$this->assertEquals(2, $crawler->filter('.global_delete')->count());
		$this->assertEquals('Delete', $this->crawler_text($crawler->filter('.global_delete')));
		// marker of conversation + inbox + spam/ham button
		$this->assertEquals(0, $crawler->filter('.spam_button')->count());
		$this->assertEquals(0, $crawler->filter('.ham_button')->count());
		$this->assertEquals(2, $crawler->filter('.refresh_button')->count());
		$this->assertEquals(2, $crawler->filter('.process_incoming_msgs_button')->count());
		$this->assertEquals(0, $crawler->filter('.resend_bulk')->count());
		$this->assertEquals($last_page, $this->crawler_text($crawler->filter('div.paging > span.current_page')));
		$this->assertEquals(0, $crawler->filter('div.paging > span.current_page')->nextAll()->count());

		$this->assertValidHtml($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_my_folder_GET_inbox_notByConversation_withOffset($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->request->setCallablePreConstructor(
			function () {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '4';
				$_SESSION['level'] = 'user';
				$_SESSION['username'] = 'user3';
			}
		);

		// Set conversation grouping
		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = '<?php '
					. '$config[\'conversation_grouping\'] = FALSE;';
		$configFile->write($content);
		$this->resetInstance();

		$db = &DBSetup::$current_setup[$db_engine];

		$id_user = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['conversation_sort' => 'asc']);
		$per_page = 20;
		$db->update('user_settings', ['id_user' => $id_user], ['paging' => $per_page]);
		$db->update('inbox', ['TextDecoded' => '%User '.$id_user.'%'], ['readed' => 'false']);

		$this->request->addCallable($db->closure());

		$id_folder = 15;
		$offset = floor(DBSetup::senders * DBSetup::messages_per_sender / $per_page) * $per_page;
		$last_page = ceil(DBSetup::senders * DBSetup::messages_per_sender / $per_page);
		$output = $this->request('GET', 'messages/my_folder/inbox/'.$id_folder.'/'.$offset);
		$crawler = new Crawler($output);

		$folder_name = 'user3, folder1 (id:15)';
		$message_count = DBSetup::senders * DBSetup::messages_per_sender;
		$this->assertEquals($folder_name . ' ('.$message_count.')', $this->crawler_text($crawler->filter('#mf_child_menu li')));

		$this->assertEquals(1, $crawler->filter('#js_function')->count());
		$this->assertEquals(0, $crawler->filter('#js_conversation')->count());
		$this->assertEquals(16, $crawler->filter('div.move_to')->attr('data-id_folder'));
		$this->assertEquals(1, $crawler->filter('#renamefolderdialog')->count());
		$this->assertEquals(1, $crawler->filter('#deletefolderdialog')->count());
		$this->assertEquals(1, $crawler->filter('#deletealldialog')->count());
		$this->assertEquals(1, $crawler->filter('#two_column_container')->count());
		$this->assertEquals($folder_name, $this->crawler_text($crawler->filter('span.folder_name')));
		$this->assertEquals(0, $crawler->filter('#delete-all-link')->count());
		$this->assertEquals(1, $crawler->filter('#renamefolder')->count());
		$this->assertEquals(1, $crawler->filter('#deletefolder')->count());
		$this->assertEquals(1, $crawler->filter('span.currenttab')->count());
		$this->assertEquals('Inbox', $this->crawler_text($crawler->filter('span.currenttab')));
		$this->assertEquals(site_url().'/messages/my_folder/sentitems/'.$id_folder, $crawler->filter('span.currenttab')->nextAll()->attr('href'));
		$this->assertEquals('Sent items', $this->crawler_text($crawler->filter('span.currenttab')->nextAll()));

		$this->assertEquals(1, $crawler->filter('#message_holder')->count());
		$this->assertEquals(5, $crawler->filter('div.messagelist.unreaded')->count());
		$this->assertEquals(0, $crawler->filter('div.messagelist_conversation')->count());

		// navigation.php (buttons)
		$this->assertEquals(0, $crawler->filter('.back_threadlist')->count());
		//$this->assertEquals('Back to Inbox', $this->crawler_text($crawler->filter('.back_threadlist')));
		//$this->_assertStringContainsString(site_url().'/messages/folder/inbox', $crawler->filter('.back_threadlist')->attr('href'));
		$this->assertEquals(2, $crawler->filter('.select_all_button')->count());
		$this->assertEquals(2, $crawler->filter('.clear_all_button')->count());
		$this->assertEquals(0, $crawler->filter('.recover_button')->count());
		$this->assertEquals(2, $crawler->filter('.move_to_button')->count());
		$this->assertEquals(2, $crawler->filter('.global_delete')->count());
		$this->assertEquals('Delete', $this->crawler_text($crawler->filter('.global_delete')));
		// marker of conversation + inbox + spam/ham button
		$this->assertEquals(0, $crawler->filter('.spam_button')->count());
		$this->assertEquals(0, $crawler->filter('.ham_button')->count());
		$this->assertEquals(2, $crawler->filter('.refresh_button')->count());
		$this->assertEquals(2, $crawler->filter('.process_incoming_msgs_button')->count());
		$this->assertEquals(0, $crawler->filter('.resend_bulk')->count());
		$this->assertEquals($last_page, $this->crawler_text($crawler->filter('div.paging > span.current_page')));
		$this->assertEquals(0, $crawler->filter('div.paging > span.current_page')->nextAll()->count());

		$this->assertValidHtml($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_my_folder_GET_sentitems_byConversation_withOffset($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->request->setCallablePreConstructor(
			function () {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '4';
				$_SESSION['level'] = 'user';
				$_SESSION['username'] = 'user3';
			}
		);

		// Set conversation grouping
		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = '<?php '
					. '$config[\'conversation_grouping\'] = TRUE;';
		$configFile->write($content);
		$this->resetInstance();

		$db = &DBSetup::$current_setup[$db_engine];

		$id_user = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['conversation_sort' => 'asc']);
		$per_page = 20;
		$db->update('user_settings', ['id_user' => $id_user], ['paging' => $per_page]);
		$db->update('inbox', ['TextDecoded' => '%User '.$id_user.'%'], ['readed' => 'false']);

		$this->request->addCallable($db->closure());

		$id_folder = 15;
		$offset = floor(DBSetup::recipients / $per_page) * $per_page;
		$last_page = ceil(DBSetup::recipients / $per_page);
		$output = $this->request('GET', 'messages/my_folder/sentitems/'.$id_folder.'/'.$offset);
		$crawler = new Crawler($output);

		$folder_name = 'user3, folder1 (id:15)';
		$message_count = DBSetup::recipients * DBSetup::messages_per_recipient;
		$this->assertEquals($folder_name . ' ('.$message_count.')', $this->crawler_text($crawler->filter('#mf_child_menu li')));

		$this->assertEquals(1, $crawler->filter('#js_function')->count());
		$this->assertEquals(0, $crawler->filter('#js_conversation')->count());
		$this->assertEquals(16, $crawler->filter('div.move_to')->attr('data-id_folder'));
		$this->assertEquals(1, $crawler->filter('#renamefolderdialog')->count());
		$this->assertEquals(1, $crawler->filter('#deletefolderdialog')->count());
		$this->assertEquals(1, $crawler->filter('#deletealldialog')->count());
		$this->assertEquals(1, $crawler->filter('#two_column_container')->count());
		$this->assertEquals($folder_name, $this->crawler_text($crawler->filter('span.folder_name')));
		$this->assertEquals(0, $crawler->filter('#delete-all-link')->count());
		$this->assertEquals(1, $crawler->filter('#renamefolder')->count());
		$this->assertEquals(1, $crawler->filter('#deletefolder')->count());
		$this->assertEquals(1, $crawler->filter('span.currenttab')->count());
		$this->assertEquals('Sent items', $this->crawler_text($crawler->filter('span.currenttab')));
		$this->assertEquals(site_url().'/messages/my_folder/inbox/'.$id_folder, $crawler->filter('span.currenttab')->previousAll()->attr('href'));
		$this->assertEquals('Inbox', $this->crawler_text($crawler->filter('span.currenttab')->previousAll()));

		$this->assertEquals(1, $crawler->filter('#message_holder')->count());
		$this->assertEquals(0, $crawler->filter('div.messagelist.unreaded')->count());
		$this->assertEquals(5, $crawler->filter('div.messagelist')->count());
		$this->assertEquals(0, $crawler->filter('div.messagelist_conversation')->count());

		// navigation.php (buttons)
		$this->assertEquals(0, $crawler->filter('.back_threadlist')->count());
		//$this->assertEquals('Back to Inbox', $this->crawler_text($crawler->filter('.back_threadlist')));
		//$this->assertEquals(site_url().'/messages/folder/inbox', $crawler->filter('.back_threadlist')->attr('href'));
		$this->assertEquals(2, $crawler->filter('.select_all_button')->count());
		$this->assertEquals(2, $crawler->filter('.clear_all_button')->count());
		$this->assertEquals(0, $crawler->filter('.recover_button')->count());
		$this->assertEquals(2, $crawler->filter('.move_to_button')->count());
		$this->assertEquals(2, $crawler->filter('.global_delete')->count());
		$this->assertEquals('Delete', $this->crawler_text($crawler->filter('.global_delete')));
		// marker of conversation + inbox + spam/ham button
		$this->assertEquals(0, $crawler->filter('.spam_button')->count());
		$this->assertEquals(0, $crawler->filter('.ham_button')->count());
		$this->assertEquals(2, $crawler->filter('.refresh_button')->count());
		$this->assertEquals(0, $crawler->filter('.process_incoming_msgs_button')->count());
		$this->assertEquals(0, $crawler->filter('.resend_bulk')->count());
		$this->assertEquals($last_page, $this->crawler_text($crawler->filter('div.paging > span.current_page')));
		$this->assertEquals(0, $crawler->filter('div.paging > span.current_page')->nextAll()->count());

		$this->assertValidHtml($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_my_folder_GET_sentitems_notByConversation_withOffset($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->request->setCallablePreConstructor(
			function () {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '4';
				$_SESSION['level'] = 'user';
				$_SESSION['username'] = 'user3';
			}
		);

		// Set conversation grouping
		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = '<?php '
					. '$config[\'conversation_grouping\'] = FALSE;';
		$configFile->write($content);
		$this->resetInstance();

		$db = &DBSetup::$current_setup[$db_engine];

		$id_user = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['conversation_sort' => 'asc']);
		$per_page = 20;
		$db->update('user_settings', ['id_user' => $id_user], ['paging' => $per_page]);
		$db->update('inbox', ['TextDecoded' => '%User '.$id_user.'%'], ['readed' => 'false']);

		$this->request->addCallable($db->closure());

		$id_folder = 15;
		$offset = floor(DBSetup::recipients * DBSetup::messages_per_recipient / $per_page) * $per_page;
		$last_page = ceil(DBSetup::recipients * DBSetup::messages_per_recipient / $per_page);
		$output = $this->request('GET', 'messages/my_folder/sentitems/'.$id_folder.'/'.$offset);
		$crawler = new Crawler($output);

		$folder_name = 'user3, folder1 (id:15)';
		$message_count = DBSetup::recipients * DBSetup::messages_per_recipient;
		$this->assertEquals($folder_name . ' ('.$message_count.')', $this->crawler_text($crawler->filter('#mf_child_menu li')));

		$this->assertEquals(1, $crawler->filter('#js_function')->count());
		$this->assertEquals(0, $crawler->filter('#js_conversation')->count());
		$this->assertEquals(16, $crawler->filter('div.move_to')->attr('data-id_folder'));
		$this->assertEquals(1, $crawler->filter('#renamefolderdialog')->count());
		$this->assertEquals(1, $crawler->filter('#deletefolderdialog')->count());
		$this->assertEquals(1, $crawler->filter('#deletealldialog')->count());
		$this->assertEquals(1, $crawler->filter('#two_column_container')->count());
		$this->assertEquals($folder_name, $this->crawler_text($crawler->filter('span.folder_name')));
		$this->assertEquals(0, $crawler->filter('#delete-all-link')->count());
		$this->assertEquals(1, $crawler->filter('#renamefolder')->count());
		$this->assertEquals(1, $crawler->filter('#deletefolder')->count());
		$this->assertEquals(1, $crawler->filter('span.currenttab')->count());
		$this->assertEquals('Sent items', $this->crawler_text($crawler->filter('span.currenttab')));
		$this->assertEquals(site_url().'/messages/my_folder/inbox/'.$id_folder, $crawler->filter('span.currenttab')->previousAll()->attr('href'));
		$this->assertEquals('Inbox', $this->crawler_text($crawler->filter('span.currenttab')->previousAll()));

		$this->assertEquals(1, $crawler->filter('#message_holder')->count());
		$this->assertEquals(0, $crawler->filter('div.messagelist.unreaded')->count());
		$this->assertEquals(5, $crawler->filter('div.messagelist')->count());
		$this->assertEquals(0, $crawler->filter('div.messagelist_conversation')->count());

		// navigation.php (buttons)
		$this->assertEquals(0, $crawler->filter('.back_threadlist')->count());
		//$this->assertEquals('Back to Inbox', $this->crawler_text($crawler->filter('.back_threadlist')));
		//$this->assertEquals(site_url().'/messages/folder/inbox', $crawler->filter('.back_threadlist')->attr('href'));
		$this->assertEquals(2, $crawler->filter('.select_all_button')->count());
		$this->assertEquals(2, $crawler->filter('.clear_all_button')->count());
		$this->assertEquals(0, $crawler->filter('.recover_button')->count());
		$this->assertEquals(2, $crawler->filter('.move_to_button')->count());
		$this->assertEquals(2, $crawler->filter('.global_delete')->count());
		$this->assertEquals('Delete', $this->crawler_text($crawler->filter('.global_delete')));
		// marker of conversation + inbox + spam/ham button
		$this->assertEquals(0, $crawler->filter('.spam_button')->count());
		$this->assertEquals(0, $crawler->filter('.ham_button')->count());
		$this->assertEquals(2, $crawler->filter('.refresh_button')->count());
		$this->assertEquals(0, $crawler->filter('.process_incoming_msgs_button')->count());
		$this->assertEquals(0, $crawler->filter('.resend_bulk')->count());
		$this->assertEquals($last_page, $this->crawler_text($crawler->filter('div.paging > span.current_page')));
		$this->assertEquals(0, $crawler->filter('div.paging > span.current_page')->nextAll()->count());

		$this->assertValidHtml($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_my_folder_GET_inbox_trash_byConversation_withOffset($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

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

		// Set conversation grouping
		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = '<?php '
					. '$config[\'conversation_grouping\'] = TRUE;';
		$configFile->write($content);
		$this->resetInstance();

		$db = &DBSetup::$current_setup[$db_engine];

		$id_user = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['conversation_sort' => 'asc']);
		$per_page = 20;
		$db->update('user_settings', ['id_user' => $id_user], ['paging' => $per_page]);
		$db->update('inbox', ['TextDecoded' => '%User '.$id_user.'%'], ['readed' => 'false']);

		$this->request->addCallable($db->closure());

		$id_folder = 5;
		$offset = floor(DBSetup::senders / $per_page) * $per_page;
		$last_page = ceil(DBSetup::senders / $per_page);
		$output = $this->request('GET', 'messages/my_folder/inbox/'.$id_folder.'/'.$offset);
		$crawler = new Crawler($output);

		$this->assertEquals(1, $crawler->filter('#js_function')->count());
		$this->assertEquals(0, $crawler->filter('#js_conversation')->count());
		$this->assertEquals(2, $crawler->filter('div.move_to')->count());
		$this->assertEquals(11, $crawler->filter('div.move_to')->attr('data-id_folder'));
		$this->assertEquals(1, $crawler->filter('#renamefolderdialog')->count());
		$this->assertEquals(1, $crawler->filter('#deletefolderdialog')->count());
		$this->assertEquals(1, $crawler->filter('#deletealldialog')->count());
		$this->assertEquals(1, $crawler->filter('#two_column_container')->count());
		$folder_name = 'Trash';
		$this->assertEquals($folder_name, $this->crawler_text($crawler->filter('span.folder_name')));
		$this->assertEquals(1, $crawler->filter('#delete-all-link')->count());
		$this->assertEquals(0, $crawler->filter('#renamefolder')->count());
		$this->assertEquals(0, $crawler->filter('#deletefolder')->count());
		$this->assertEquals(1, $crawler->filter('span.currenttab')->count());
		$this->assertEquals('Inbox', $this->crawler_text($crawler->filter('span.currenttab')));
		$this->assertEquals(site_url().'/messages/my_folder/sentitems/'.$id_folder, $crawler->filter('span.currenttab')->nextAll()->attr('href'));
		$this->assertEquals('Sent items', $this->crawler_text($crawler->filter('span.currenttab')->nextAll()));

		$this->assertEquals(1, $crawler->filter('#message_holder')->count());
		$this->assertEquals(5, $crawler->filter('div.messagelist.unreaded')->count());
		$this->assertEquals(0, $crawler->filter('div.messagelist_conversation')->count());

		// navigation.php (buttons)
		$this->assertEquals(0, $crawler->filter('.back_threadlist')->count());
		//$this->assertEquals('Back to Inbox', $this->crawler_text($crawler->filter('.back_threadlist')));
		//$this->assertEquals(site_url().'/messages/folder/inbox', $crawler->filter('.back_threadlist')->attr('href'));
		$this->assertEquals(2, $crawler->filter('.select_all_button')->count());
		$this->assertEquals(2, $crawler->filter('.clear_all_button')->count());
		$this->assertEquals(2, $crawler->filter('.recover_button')->count());
		$this->assertEquals(2, $crawler->filter('.move_to_button')->count());
		$this->assertEquals(2, $crawler->filter('.global_delete')->count());
		$this->assertEquals('Delete permanently', $this->crawler_text($crawler->filter('.global_delete')));
		// marker of conversation + inbox + spam/ham button
		$this->assertEquals(0, $crawler->filter('.spam_button')->count());
		$this->assertEquals(0, $crawler->filter('.ham_button')->count());
		$this->assertEquals(2, $crawler->filter('.refresh_button')->count());
		$this->assertEquals(2, $crawler->filter('.process_incoming_msgs_button')->count());
		$this->assertEquals(0, $crawler->filter('.resend_bulk')->count());
		$this->assertEquals($last_page, $this->crawler_text($crawler->filter('div.paging > span.current_page')));
		$this->assertEquals(0, $crawler->filter('div.paging > span.current_page')->nextAll()->count());

		$this->assertValidHtml($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_my_folder_GET_sentitems_trash_byConversation_withOffset($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

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

		// Set conversation grouping
		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = '<?php '
					. '$config[\'conversation_grouping\'] = TRUE;';
		$configFile->write($content);
		$this->resetInstance();

		$db = &DBSetup::$current_setup[$db_engine];

		$id_user = 2;
		$db->update('user_settings', ['id_user' => $id_user], ['conversation_sort' => 'asc']);
		$per_page = 20;
		$db->update('user_settings', ['id_user' => $id_user], ['paging' => $per_page]);
		$db->update('inbox', ['TextDecoded' => '%User '.$id_user.'%'], ['readed' => 'false']);

		$this->request->addCallable($db->closure());

		$id_folder = 5;
		$offset = floor(DBSetup::recipients / $per_page) * $per_page;
		$last_page = ceil(DBSetup::recipients / $per_page);
		$output = $this->request('GET', 'messages/my_folder/sentitems/'.$id_folder.'/'.$offset);
		$crawler = new Crawler($output);

		$this->assertEquals(1, $crawler->filter('#js_function')->count());
		$this->assertEquals(0, $crawler->filter('#js_conversation')->count());
		$this->assertEquals(11, $crawler->filter('div.move_to')->attr('data-id_folder'));
		$this->assertEquals(1, $crawler->filter('#renamefolderdialog')->count());
		$this->assertEquals(1, $crawler->filter('#deletefolderdialog')->count());
		$this->assertEquals(1, $crawler->filter('#deletealldialog')->count());
		$this->assertEquals(1, $crawler->filter('#two_column_container')->count());
		$folder_name = 'Trash';
		$this->assertEquals($folder_name, $this->crawler_text($crawler->filter('span.folder_name')));
		$this->assertEquals(1, $crawler->filter('#delete-all-link')->count());
		$this->assertEquals(0, $crawler->filter('#renamefolder')->count());
		$this->assertEquals(0, $crawler->filter('#deletefolder')->count());
		$this->assertEquals(1, $crawler->filter('span.currenttab')->count());
		$this->assertEquals('Sent items', $this->crawler_text($crawler->filter('span.currenttab')));
		$this->assertEquals(site_url().'/messages/my_folder/inbox/'.$id_folder, $crawler->filter('span.currenttab')->previousAll()->attr('href'));
		$this->assertEquals('Inbox', $this->crawler_text($crawler->filter('span.currenttab')->previousAll()));

		$this->assertEquals(1, $crawler->filter('#message_holder')->count());
		$this->assertEquals(0, $crawler->filter('div.messagelist.unreaded')->count());
		$this->assertEquals(5, $crawler->filter('div.messagelist')->count());
		$this->assertEquals(0, $crawler->filter('div.messagelist_conversation')->count());

		// navigation.php (buttons)
		$this->assertEquals(0, $crawler->filter('.back_threadlist')->count());
		//$this->assertEquals('Back to Inbox', $this->crawler_text($crawler->filter('.back_threadlist')));
		//$this->assertEquals(site_url().'/messages/folder/inbox', $crawler->filter('.back_threadlist')->attr('href'));
		$this->assertEquals(2, $crawler->filter('.select_all_button')->count());
		$this->assertEquals(2, $crawler->filter('.clear_all_button')->count());
		$this->assertEquals(2, $crawler->filter('.recover_button')->count());
		$this->assertEquals(2, $crawler->filter('.move_to_button')->count());
		$this->assertEquals(2, $crawler->filter('.global_delete')->count());
		$this->assertEquals('Delete permanently', $this->crawler_text($crawler->filter('.global_delete')));
		// marker of conversation + inbox + spam/ham button
		$this->assertEquals(0, $crawler->filter('.spam_button')->count());
		$this->assertEquals(0, $crawler->filter('.ham_button')->count());
		$this->assertEquals(2, $crawler->filter('.refresh_button')->count());
		$this->assertEquals(0, $crawler->filter('.process_incoming_msgs_button')->count());
		$this->assertEquals(0, $crawler->filter('.resend_bulk')->count());
		$this->assertEquals($last_page, $this->crawler_text($crawler->filter('div.paging > span.current_page')));
		$this->assertEquals(0, $crawler->filter('div.paging > span.current_page')->nextAll()->count());

		$this->assertValidHtml($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_my_folder_GET_inbox_spam_byConversation_withOffset($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->request->setCallablePreConstructor(
			function () {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '4';
				$_SESSION['level'] = 'user';
				$_SESSION['username'] = 'user3';
			}
		);

		// Set conversation grouping
		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = '<?php '
					. '$config[\'conversation_grouping\'] = TRUE;';
		$configFile->write($content);
		$this->resetInstance();

		$db = &DBSetup::$current_setup[$db_engine];

		$id_user = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['conversation_sort' => 'asc']);
		$per_page = 20;
		$db->update('user_settings', ['id_user' => $id_user], ['paging' => $per_page]);
		$db->update('inbox', ['TextDecoded' => '%User '.$id_user.'%'], ['readed' => 'false']);

		$this->request->addCallable($db->closure());

		$id_folder = 6;
		$offset = floor(DBSetup::senders / $per_page) * $per_page;
		$last_page = ceil(DBSetup::senders / $per_page);
		$output = $this->request('GET', 'messages/my_folder/inbox/'.$id_folder.'/'.$offset);
		$crawler = new Crawler($output);

		$this->assertEquals(1, $crawler->filter('#js_function')->count());
		$this->assertEquals(0, $crawler->filter('#js_conversation')->count());
		$this->assertEquals(2, $crawler->filter('div.move_to')->count());
		$this->assertEquals(15, $crawler->filter('div.move_to')->attr('data-id_folder'));
		$this->assertEquals(1, $crawler->filter('#renamefolderdialog')->count());
		$this->assertEquals(1, $crawler->filter('#deletefolderdialog')->count());
		$this->assertEquals(1, $crawler->filter('#deletealldialog')->count());
		$this->assertEquals(1, $crawler->filter('#two_column_container')->count());
		$folder_name = 'Spam';
		$this->assertEquals($folder_name, $this->crawler_text($crawler->filter('span.folder_name')));
		$this->assertEquals(1, $crawler->filter('#delete-all-link')->count());
		$this->assertEquals(0, $crawler->filter('#renamefolder')->count());
		$this->assertEquals(0, $crawler->filter('#deletefolder')->count());
		$this->assertEquals(0, $crawler->filter('span.currenttab')->count());

		$this->assertEquals(1, $crawler->filter('#message_holder')->count());
		$this->assertEquals(5, $crawler->filter('div.messagelist.unreaded')->count());
		$this->assertEquals(0, $crawler->filter('div.messagelist_conversation')->count());

		// navigation.php (buttons)
		$this->assertEquals(0, $crawler->filter('.back_threadlist')->count());
		//$this->assertEquals('Back to Inbox', $this->crawler_text($crawler->filter('.back_threadlist')));
		//$this->assertEquals(site_url().'/messages/folder/inbox', $crawler->filter('.back_threadlist')->attr('href'));
		$this->assertEquals(2, $crawler->filter('.select_all_button')->count());
		$this->assertEquals(2, $crawler->filter('.clear_all_button')->count());
		$this->assertEquals(0, $crawler->filter('.recover_button')->count());
		$this->assertEquals(2, $crawler->filter('.move_to_button')->count());
		$this->assertEquals(2, $crawler->filter('.global_delete')->count());
		$this->assertEquals('Delete permanently', $this->crawler_text($crawler->filter('.global_delete')));
		// marker of conversation + inbox + spam/ham button
		$this->assertEquals(0, $crawler->filter('.spam_button')->count());
		$this->assertEquals(0, $crawler->filter('.ham_button')->count());
		$this->assertEquals(2, $crawler->filter('.refresh_button')->count());
		$this->assertEquals(2, $crawler->filter('.process_incoming_msgs_button')->count());
		$this->assertEquals(0, $crawler->filter('.resend_bulk')->count());
		$this->assertEquals($last_page, $this->crawler_text($crawler->filter('div.paging > span.current_page')));
		$this->assertEquals(0, $crawler->filter('div.paging > span.current_page')->nextAll()->count());

		$this->assertValidHtml($output);
	}
}
