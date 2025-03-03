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

class Messages_conversation_test extends KalkunTestCase {

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
		$this->resetInstance();
	}

	public static function database_Provider()
	{
		return DBSetup::$db_engines_to_test;
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_conversation_ajaxGET_folder_invalid($db_engine)
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

		$type = 'folder';
		$folder = 'invalid';
		$number = rawurlencode('+33600000001');
		$id_folder = NULL;
		$url = implode('/', [$type, $folder, $number, $id_folder]);
		$output = $this->ajaxRequest('GET', 'messages/conversation/' . $url);
		$this->assertResponseCode(400);
	}

	/**
	 * @dataProvider folderAndMyFolder_inboxAndSentitems_Provider
	 *
	 */
	public function test_conversation_ajaxGET_folder_inboxAndSentitems_no_message($db_engine, $folder, $id_folder)
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

		$type = 'folder';
		$number = rawurlencode('+33699999999'); // Number without messages
		$url = implode('/', [$type, $folder, $number, $id_folder]);
		$output = $this->ajaxRequest('GET', 'messages/conversation/' . $url);
		$crawler = new Crawler($output);

		$this->assertEquals('There is no message in this conversation.', $this->crawler_text($crawler->filter('p > i ')));

		$this->assertValidHtmlSnippet($output);
	}

	public function assertDetailAreaPartsCounter($crawler_item, $index, $parts_count)
	{
		if ($parts_count > 1)
		{
			$parts = $parts_count . ' part messages';
			$this->assertEquals($parts, $this->crawler_text($crawler_item->filter('div.detail_area > table > tr')->eq($index)->filter('td')->last()));
			return $index;
		}
		return $index - 1;
	}

	public function assertDetailArea($crawler_item, $source, $data)
	{
		$index = 0;
		switch ($source)
		{
			case 'inbox':
				$this->assertEquals('From', $this->crawler_text($crawler_item->filter('div.detail_area > table > tr')->eq($index)->filter('td')->first()));
				$this->assertEquals($data['number'], $this->crawler_text($crawler_item->filter('div.detail_area > table > tr')->eq($index)->filter('td')->last()));
				$index++;
				//$this->assertEquals('Inserted', $this->crawler_text($crawler_item->filter('div.detail_area > table > tr')->eq($index)->filter('td')->first()));
				//$this->assertEquals($data['inserted'], $this->crawler_text($crawler_item->filter('div.detail_area > table > tr')->eq($index)->filter('td')->last()));
				//$index ++;
				$this->assertEquals('Date', $this->crawler_text($crawler_item->filter('div.detail_area > table > tr')->eq($index)->filter('td')->first()));
				$this->assertEquals($data['date'], $this->crawler_text($crawler_item->filter('div.detail_area > table > tr')->eq($index)->filter('td')->last()));
				$index++;
				$this->assertEquals('SMSC', $this->crawler_text($crawler_item->filter('div.detail_area > table > tr')->eq($index)->filter('td')->first()));
				$this->assertEquals($data['smsc'], $this->crawler_text($crawler_item->filter('div.detail_area > table > tr')->eq($index)->filter('td')->last()));
				$index++;
				$index = $this->assertDetailAreaPartsCounter($crawler_item, $index, $data['parts']);
				$index++;
				//$this->assertEquals('Status', $this->crawler_text($crawler_item->filter('div.detail_area > table > tr')->eq($index)->filter('td')->first()));
				//$this->assertEquals($data['status'], $this->crawler_text($crawler_item->filter('div.detail_area > table > tr')->eq($index)->filter('td')->last()));
				break;
			case 'outbox':
				$this->assertEquals('To', $this->crawler_text($crawler_item->filter('div.detail_area > table > tr')->eq($index)->filter('td')->first()));
				$this->assertEquals($data['number'], $this->crawler_text($crawler_item->filter('div.detail_area > table > tr')->eq($index)->filter('td')->last()));
				$index++;
				$this->assertEquals('Inserted', $this->crawler_text($crawler_item->filter('div.detail_area > table > tr')->eq($index)->filter('td')->first()));
				$this->assertDateEqualsWithDelta($data['inserted'], $this->crawler_text($crawler_item->filter('div.detail_area > table > tr')->eq($index)->filter('td')->last()), 3600);
				$index++;
				$this->assertEquals('Date', $this->crawler_text($crawler_item->filter('div.detail_area > table > tr')->eq($index)->filter('td')->first()));
				$this->assertEquals($data['date'], $this->crawler_text($crawler_item->filter('div.detail_area > table > tr')->eq($index)->filter('td')->last()));
				$index++;
				//$this->assertEquals('SMSC', $this->crawler_text($crawler_item->filter('div.detail_area > table > tr')->eq($index)->filter('td')->first()));
				//$this->assertEquals($data['smsc'], $this->crawler_text($crawler_item->filter('div.detail_area > table > tr')->eq($index)->filter('td')->last()));
				//$index ++;
				//$index = $this->assertDetailAreaPartsCounter($crawler_item, $index, $data['parts']);
				//$index ++;
				//$this->assertEquals('Status', $this->crawler_text($crawler_item->filter('div.detail_area > table > tr')->eq($index)->filter('td')->first()));
				//$this->assertEquals($data['status'], $this->crawler_text($crawler_item->filter('div.detail_area > table > tr')->eq($index)->filter('td')->last()));
				//$index ++;
				break;
			case 'sentitems':
				$this->assertEquals('To', $this->crawler_text($crawler_item->filter('div.detail_area > table > tr')->eq($index)->filter('td')->first()));
				$this->assertEquals($data['number'], $this->crawler_text($crawler_item->filter('div.detail_area > table > tr')->eq($index)->filter('td')->last()));
				$index++;
				//$this->assertEquals('Inserted', $this->crawler_text($crawler_item->filter('div.detail_area > table > tr')->eq($index)->filter('td')->first()));
				//$this->assertEquals($data['inserted'], $this->crawler_text($crawler_item->filter('div.detail_area > table > tr')->eq($index)->filter('td')->last()));
				//$index ++;
				$this->assertEquals('Date', $this->crawler_text($crawler_item->filter('div.detail_area > table > tr')->eq($index)->filter('td')->first()));
				$this->assertEquals($data['date'], $this->crawler_text($crawler_item->filter('div.detail_area > table > tr')->eq($index)->filter('td')->last()));
				$index++;
				$this->assertEquals('SMSC', $this->crawler_text($crawler_item->filter('div.detail_area > table > tr')->eq($index)->filter('td')->first()));
				$this->assertEquals($data['smsc'], $this->crawler_text($crawler_item->filter('div.detail_area > table > tr')->eq($index)->filter('td')->last()));
				$index++;
				$index = $this->assertDetailAreaPartsCounter($crawler_item, $index, $data['parts']);
				$index++;
				$this->assertEquals('Status', $this->crawler_text($crawler_item->filter('div.detail_area > table > tr')->eq($index)->filter('td')->first()));
				$this->assertEquals($data['status'], $this->crawler_text($crawler_item->filter('div.detail_area > table > tr')->eq($index)->filter('td')->last()));
				$index++;
				break;
			default:
				throw new Exception('Not expected. Update the test to add support.');
				break;
		}
	}

	public function assertOptionMenuButtons($crawler_item, $source, $is_on_pbk)
	{
		if ($is_on_pbk)
		{
			$this->assertEquals(0, $crawler_item->filter('div.optionmenu li a.add_to_pbk')->count());
		}
		else
		{
			$this->assertEquals(1, $crawler_item->filter('div.optionmenu li a.add_to_pbk')->count());
		}

		switch ($source)
		{
			case 'inbox':
				$this->assertEquals(1, $crawler_item->filter('div.optionmenu li a.detail_button')->count());
				$this->assertEquals(1, $crawler_item->filter('div.optionmenu li a.reply_button')->count());
				$this->assertEquals(1, $crawler_item->filter('div.optionmenu li a.forward_button')->count());
				$this->assertEquals(0, $crawler_item->filter('div.optionmenu li a.resend')->count());
				break;
			case 'outbox':
				$this->assertEquals(1, $crawler_item->filter('div.optionmenu li a.detail_button')->count());
				$this->assertEquals(0, $crawler_item->filter('div.optionmenu li a.reply_button')->count());
				$this->assertEquals(0, $crawler_item->filter('div.optionmenu li a.forward_button')->count());
				$this->assertEquals(0, $crawler_item->filter('div.optionmenu li a.resend')->count());
				break;
			case 'sentitems':
				$this->assertEquals(1, $crawler_item->filter('div.optionmenu li a.detail_button')->count());
				$this->assertEquals(0, $crawler_item->filter('div.optionmenu li a.reply_button')->count());
				$this->assertEquals(1, $crawler_item->filter('div.optionmenu li a.forward_button')->count());
				$this->assertEquals(1, $crawler_item->filter('div.optionmenu li a.resend')->count());
				break;
			default:
				throw new Exception('Not expected. Update the test to add support.');
				break;
		}
	}

	public static function inboxAndSentitems_Provider()
	{
		return DBSetup::prepend_db_engine([
			'inbox' => ['inbox'],
			'sentitems' => ['sentitems'],
		]);
	}

	public static function folderAndMyFolder_inboxAndSentitems_Provider()
	{
		return DBSetup::prepend_db_engine([
			'inbox' => ['inbox', NULL],
			'sentitems' => ['sentitems', NULL],
			'inbox my_folder' => ['inbox', 15],
			'sentitems my_folder' => ['sentitems', 15],
		]);
	}

	/**
	 * @dataProvider folderAndMyFolder_inboxAndSentitems_Provider
	 *
	 */
	public function test_conversation_ajaxGET_folder_inboxAndSentitems_multipart_sortAsc_contactNotInPbk($db_engine, $folder, $id_folder)
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

		$db = &DBSetup::$current_setup[$db_engine];

		$id_user = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['conversation_sort' => 'asc']);
		$per_page = 20;
		$db->update('user_settings', ['id_user' => $id_user], ['paging' => $per_page]);
		$db->update('inbox', ['TextDecoded' => '%User 4%'], ['readed' => 'false']);

		$this->request->addCallable($db->closure());

		if ($id_folder === NULL)
		{
			$type = 'folder';
			$id_offset_inbox = 375;
			$id_offset_sentitems = 750;
			$msg_fld_inbox = 'Inbox';
			$msg_fld_sentitems = 'Sentitems';
		}
		else
		{
			$type = 'my_folder';
			$id_offset_inbox = 1530;
			$id_offset_sentitems = 1276;
			$msg_fld_inbox = 'Inbox/#' . $id_folder;
			$msg_fld_sentitems = 'Sentitems/#' . $id_folder;
		}
		$number = '+33600000001';
		$url = implode('/', [$type, $folder, rawurlencode($number), $id_folder]);
		$output = $this->ajaxRequest('GET', 'messages/conversation/' . $url);
		$crawler = new Crawler($output);

		$this->assertEquals(DBSetup::messages_per_recipient + DBSetup::messages_per_sender, $crawler->filter('div.messagelist_conversation')->count());
		$counter_inbox = $counter_sentitems = 0;
		$inbox_messages_marked_as_read = [];
		for ($i = 1; $i <= DBSetup::messages_per_recipient + DBSetup::messages_per_sender; $i++)
		{
			$source = ($i % 2 === 1) ? 'inbox' : 'sentitems';
			if ($source === 'inbox')
			{
				$counter_inbox = $counter_inbox + 1;
				$id_expected_inbox = $id_offset_inbox + DBSetup::nb_of_parts_per_inbox_message * DBSetup::messages_per_sender - $counter_inbox * DBSetup::nb_of_parts_per_inbox_message + 1; // By default, sorting is ascending. So we have to revert the order of ID
				$id = $id_expected_inbox;
				$message_full = DBSetup::text_replace_placeholder('User 4. Message ' . (DBSetup::messages_per_sender - $counter_inbox + 1) . '. Sender 1. Folder '.$msg_fld_inbox, DBSetup::$text_multi_unicode);
				$arrow = $this->CI->config->item('img_path') . 'arrow_left.gif';
				$inbox_messages_marked_as_read[] = $id_expected_inbox;
			}
			else
			{
				$counter_sentitems = $counter_sentitems + 1;
				$id_expected_sentitems = $id_offset_sentitems + DBSetup::messages_per_recipient - $counter_sentitems + 1; // By default, sorting is ascending. So we have to revert the order of ID
				$id = $id_expected_sentitems;
				$message_full = DBSetup::text_replace_placeholder('User 4. Message ' . (DBSetup::messages_per_recipient - $counter_sentitems + 1) . '. Recipient 1. Folder '.$msg_fld_sentitems, DBSetup::$text_multi_gsm);
				$arrow = $this->CI->config->item('img_path') . 'arrow_right.gif';
			}

			$crawler_item = $crawler->filter('div.messagelist_conversation')->eq($i - 1);

			$this->assertEquals('message_container '. $source, $crawler_item->filter('div.message_container')->attr('class'));
			$this->assertEquals('item_source' . $id, $crawler_item->filter('div.message_header input')->eq(0)->attr('id'));
			$this->assertEquals('item_source' . $id, $crawler_item->filter('div.message_header input')->eq(0)->attr('name'));
			$this->assertEquals($source, $crawler_item->filter('div.message_header input')->eq(0)->attr('value'));
			$this->assertEquals('item_number' . $id, $crawler_item->filter('div.message_header input')->eq(1)->attr('id'));
			$this->assertEquals('item_number' . $id, $crawler_item->filter('div.message_header input')->eq(1)->attr('name'));
			$this->assertEquals($number, $crawler_item->filter('div.message_header input')->eq(1)->attr('value'));
			$this->assertEquals($id, $crawler_item->filter('div.message_header input')->eq(2)->attr('id'));
			$this->assertEquals($id, $crawler_item->filter('div.message_header input')->eq(2)->attr('value'));
			if ($source === 'inbox')
			{
				$this->assertEquals('font-weight: bold', $crawler_item->filter('span.message_toggle span')->attr('style'));
			}
			else
			{
				$this->assertEmpty($crawler_item->filter('span.message_toggle span')->attr('style'));
			}
			$this->assertEquals($arrow, $crawler_item->filter('div.message_header img')->attr('src'));

			$name = '/' . preg_quote(phone_format_human($number), '/') . '/';
			$this->_assertMatchesRegularExpression($name, $this->crawler_text($crawler_item->filter('div.message_header > span > span')));

			$message_preview = mb_substr($message_full, 0, 20);
			$this->_assertStringContainsString($message_preview, $this->crawler_text($crawler_item->filter('span.message_toggle span.message_preview')));

			if ($source === 'inbox')
			{
				$this->assertDetailArea($crawler_item, $source, [
					'number' => $number,
					'inserted' => '',
					'date' => simple_date(date('Y-m-d H:i:s', strtotime(DBSetup::BASE_MESSAGE_DATE . ' -' . (DBSetup::messages_per_sender - $counter_inbox + 1) . ' minutes'))), //TODO
					'smsc' => '',
					'parts' => DBSetup::nb_of_parts_per_inbox_message,
					'status' => '',
				]);
			}
			else
			{
				$this->assertDetailArea($crawler_item, $source, [
					'number' => $number,
					'inserted' => '',
					'date' => simple_date(date('Y-m-d H:i:s', strtotime(DBSetup::BASE_MESSAGE_DATE . ' -' . (DBSetup::messages_per_recipient - $counter_sentitems + 1) . ' minutes'))), //TODO
					'smsc' => '+33600000000',
					'parts' => DBSetup::nb_of_parts_per_outbox_message,
					'status' => check_delivery_report(DBsetup::$sentitems_status[(DBSetup::messages_per_recipient - $counter_sentitems)]),
				]);
			}

			$this->assertEquals($message_full, $this->crawler_text($crawler_item->filter('div.message_content')));

			$this->assertOptionMenuButtons($crawler_item, $source, FALSE);

			$this->assertNotEmpty($this->crawler_text($crawler_item->filter('div.message_metadata')));
		}

		foreach ($inbox_messages_marked_as_read as $id)
		{
			// assert that inbox box messages are now marked as read
			$status = db_boolean_to_php_bool($db_engine, $this->CI->db->where('ID', $id)->get('inbox')->row()->readed);
			$this->assertTrue($status, $id);
		}

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider folderAndMyFolder_inboxAndSentitems_Provider
	 *
	 */
	public function test_conversation_ajaxGET_folder_inboxAndSentitems_multipart_sortAsc_contactNotInPbk_withOffset($db_engine, $folder, $id_folder)
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

		$db = &DBSetup::$current_setup[$db_engine];

		$id_user = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['conversation_sort' => 'asc']);
		$per_page = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['paging' => $per_page]);
		$db->update('inbox', ['TextDecoded' => '%User 4%'], ['readed' => 'false']);

		$this->request->addCallable($db->closure());

		if ($id_folder === NULL)
		{
			$type = 'folder';
			$id_offset_inbox = 375;
			$id_offset_sentitems = 750;
			$msg_fld_inbox = 'Inbox';
			$msg_fld_sentitems = 'Sentitems';
		}
		else
		{
			$type = 'my_folder';
			$id_offset_inbox = 1530;
			$id_offset_sentitems = 1276;
			$msg_fld_inbox = 'Inbox/#' . $id_folder;
			$msg_fld_sentitems = 'Sentitems/#' . $id_folder;
		}
		$number = '+33600000001';
		$url = implode('/', [$type, $folder, rawurlencode($number), $id_folder]);
		$offset = floor((DBSetup::messages_per_recipient + DBSetup::messages_per_sender) / $per_page) * $per_page;
		$output = $this->ajaxRequest('GET', 'messages/conversation/' . $url . '/' . $offset);
		$crawler = new Crawler($output);

		$this->assertEquals(DBSetup::messages_per_recipient + DBSetup::messages_per_sender - $offset, $crawler->filter('div.messagelist_conversation')->count());
		$counter_inbox = $counter_sentitems = 0;
		$inbox_messages_marked_as_read = [];
		for ($i = 1; $i <= DBSetup::messages_per_recipient + DBSetup::messages_per_sender; $i++)
		{
			$source = ($i % 2 === 1) ? 'inbox' : 'sentitems';
			if ($source === 'inbox')
			{
				$counter_inbox = $counter_inbox + 1;
				$id_expected_inbox = $id_offset_inbox + DBSetup::nb_of_parts_per_inbox_message * DBSetup::messages_per_sender - $counter_inbox * DBSetup::nb_of_parts_per_inbox_message + 1; // By default, sorting is ascending. So we have to revert the order of ID
				$id = $id_expected_inbox;
				$message_full = DBSetup::text_replace_placeholder('User 4. Message ' . (DBSetup::messages_per_sender - $counter_inbox + 1) . '. Sender 1. Folder '.$msg_fld_inbox, DBSetup::$text_multi_unicode);
				$arrow = $this->CI->config->item('img_path') . 'arrow_left.gif';
				$inbox_messages_marked_as_read[] = $id_expected_inbox;
			}
			else
			{
				$counter_sentitems = $counter_sentitems + 1;
				$id_expected_sentitems = $id_offset_sentitems + DBSetup::messages_per_recipient - $counter_sentitems + 1; // By default, sorting is ascending. So we have to revert the order of ID
				$id = $id_expected_sentitems;
				$message_full = DBSetup::text_replace_placeholder('User 4. Message ' . (DBSetup::messages_per_recipient - $counter_sentitems + 1) . '. Recipient 1. Folder '.$msg_fld_sentitems, DBSetup::$text_multi_gsm);
				$arrow = $this->CI->config->item('img_path') . 'arrow_right.gif';
			}

			if ($i <= $offset)
			{
				// We haven't reached the 1st message to display yet, so jump to the next one.
				array_pop($inbox_messages_marked_as_read);
				continue;
			}

			$crawler_item = $crawler->filter('div.messagelist_conversation')->eq($i - 1 - $offset);

			$this->assertEquals('message_container '. $source, $crawler_item->filter('div.message_container')->attr('class'));
			$this->assertEquals('item_source' . $id, $crawler_item->filter('div.message_header input')->eq(0)->attr('id'));
			$this->assertEquals('item_source' . $id, $crawler_item->filter('div.message_header input')->eq(0)->attr('name'));
			$this->assertEquals($source, $crawler_item->filter('div.message_header input')->eq(0)->attr('value'));
			$this->assertEquals('item_number' . $id, $crawler_item->filter('div.message_header input')->eq(1)->attr('id'));
			$this->assertEquals('item_number' . $id, $crawler_item->filter('div.message_header input')->eq(1)->attr('name'));
			$this->assertEquals($number, $crawler_item->filter('div.message_header input')->eq(1)->attr('value'));
			$this->assertEquals($id, $crawler_item->filter('div.message_header input')->eq(2)->attr('id'));
			$this->assertEquals($id, $crawler_item->filter('div.message_header input')->eq(2)->attr('value'));
			if ($source === 'inbox')
			{
				$this->assertEquals('font-weight: bold', $crawler_item->filter('span.message_toggle span')->attr('style'));
			}
			else
			{
				$this->assertEmpty($crawler_item->filter('span.message_toggle span')->attr('style'));
			}
			$this->assertEquals($arrow, $crawler_item->filter('div.message_header img')->attr('src'));

			$name = '/' . preg_quote(phone_format_human($number), '/') . '/';
			$this->_assertMatchesRegularExpression($name, $this->crawler_text($crawler_item->filter('div.message_header > span > span')));

			$message_preview = mb_substr($message_full, 0, 20);
			$this->_assertStringContainsString($message_preview, $this->crawler_text($crawler_item->filter('span.message_toggle span.message_preview')));

			if ($source === 'inbox')
			{
				$this->assertDetailArea($crawler_item, $source, [
					'number' => $number,
					'inserted' => '',
					'date' => simple_date(date('Y-m-d H:i:s', strtotime(DBSetup::BASE_MESSAGE_DATE . ' -' . (DBSetup::messages_per_sender - $counter_inbox + 1) . ' minutes'))), //TODO
					'smsc' => '',
					'parts' => DBSetup::nb_of_parts_per_inbox_message,
					'status' => '',
				]);
			}
			else
			{
				$this->assertDetailArea($crawler_item, $source, [
					'number' => $number,
					'inserted' => '',
					'date' => simple_date(date('Y-m-d H:i:s', strtotime(DBSetup::BASE_MESSAGE_DATE . ' -' . (DBSetup::messages_per_recipient - $counter_sentitems + 1) . ' minutes'))), //TODO
					'smsc' => '+33600000000',
					'parts' => DBSetup::nb_of_parts_per_outbox_message,
					'status' => check_delivery_report(DBsetup::$sentitems_status[(DBSetup::messages_per_recipient - $counter_sentitems)]),
				]);
			}

			$this->assertEquals($message_full, $this->crawler_text($crawler_item->filter('div.message_content')));

			$this->assertOptionMenuButtons($crawler_item, $source, FALSE);

			$this->assertNotEmpty($this->crawler_text($crawler_item->filter('div.message_metadata')));
		}

		foreach ($inbox_messages_marked_as_read as $id)
		{
			// assert that inbox box messages are now marked as read
			$status = db_boolean_to_php_bool($db_engine, $this->CI->db->where('ID', $id)->get('inbox')->row()->readed);
			$this->assertTrue($status, $id);
		}

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider folderAndMyFolder_inboxAndSentitems_Provider
	 *
	 */
	public function test_conversation_ajaxGET_folder_inboxAndSentitems_multipart_sortAsc_contactInPbk($db_engine, $folder, $id_folder)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->request->setCallablePreConstructor(
			function () use ($id_folder) {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = ($id_folder === NULL) ? '7' : '9';
				$_SESSION['level'] = 'user';
				$_SESSION['username'] = 'user6';
			}
		);

		$db = &DBSetup::$current_setup[$db_engine];

		$id_user = ($id_folder === NULL) ? 7 : 9;
		$db->update('user_settings', ['id_user' => $id_user], ['conversation_sort' => 'asc']);
		$db->update('inbox', ['TextDecoded' => '%User '.$id_user.'%'], ['readed' => 'false']);

		$this->request->addCallable($db->closure());

		if ($id_folder === NULL)
		{
			$type = 'folder';
			$id_offset_inbox = 1504;
			$id_offset_sentitems = 1253;
			$msg_fld_inbox = 'Inbox';
			$msg_fld_sentitems = 'Sentitems';
		}
		else
		{
			$id_folder = 18; // Override because id_user 7 doesn't have folder 15
			$type = 'my_folder';
			$id_offset_inbox = 2534;
			$id_offset_sentitems = 1402;
			$msg_fld_inbox = 'Inbox/#' . $id_folder;
			$msg_fld_sentitems = 'Sentitems/#' . $id_folder;
		}
		$number = '+33600000001';
		$url = implode('/', [$type, $folder, rawurlencode($number), $id_folder]);
		$output = $this->ajaxRequest('GET', 'messages/conversation/' . $url);
		$crawler = new Crawler($output);

		$this->assertEquals(2, $crawler->filter('div.messagelist_conversation')->count());
		$counter_inbox = $counter_sentitems = 0;
		$inbox_messages_marked_as_read = [];
		for ($i = 1; $i <= 2; $i++)
		{
			$source = ($i % 2 === 1) ? 'inbox' : 'sentitems';
			if ($source === 'inbox')
			{
				$counter_inbox = $counter_inbox + 1;
				$id_expected_inbox = $id_offset_inbox + DBSetup::nb_of_parts_per_inbox_message - $counter_inbox * DBSetup::nb_of_parts_per_inbox_message + 1; // By default, sorting is ascending. So we have to revert the order of ID
				$id = $id_expected_inbox;
				$message_full = DBSetup::text_replace_placeholder('User '.$id_user.'. Message ' . (1 - $counter_inbox + 1) . '. Sender 1. Folder '.$msg_fld_inbox, DBSetup::$text_multi_unicode);
				$arrow = $this->CI->config->item('img_path') . 'arrow_left.gif';
				$inbox_messages_marked_as_read[] = $id_expected_inbox;
			}
			else
			{
				$counter_sentitems = $counter_sentitems + 1;
				$id_expected_sentitems = $id_offset_sentitems + 1 - $counter_sentitems + 1; // By default, sorting is ascending. So we have to revert the order of ID
				$id = $id_expected_sentitems;
				$message_full = DBSetup::text_replace_placeholder('User '.$id_user.'. Message ' . (1 - $counter_sentitems + 1) . '. Recipient 1. Folder '.$msg_fld_sentitems, DBSetup::$text_multi_gsm);
				$arrow = $this->CI->config->item('img_path') . 'arrow_right.gif';
			}

			$crawler_item = $crawler->filter('div.messagelist_conversation')->eq($i - 1);

			$this->assertEquals('message_container '. $source, $crawler_item->filter('div.message_container')->attr('class'));
			$this->assertEquals('item_source' . $id, $crawler_item->filter('div.message_header input')->eq(0)->attr('id'));
			$this->assertEquals('item_source' . $id, $crawler_item->filter('div.message_header input')->eq(0)->attr('name'));
			$this->assertEquals($source, $crawler_item->filter('div.message_header input')->eq(0)->attr('value'));
			$this->assertEquals('item_number' . $id, $crawler_item->filter('div.message_header input')->eq(1)->attr('id'));
			$this->assertEquals('item_number' . $id, $crawler_item->filter('div.message_header input')->eq(1)->attr('name'));
			$this->assertEquals($number, $crawler_item->filter('div.message_header input')->eq(1)->attr('value'));
			$this->assertEquals($id, $crawler_item->filter('div.message_header input')->eq(2)->attr('id'));
			$this->assertEquals($id, $crawler_item->filter('div.message_header input')->eq(2)->attr('value'));
			if ($source === 'inbox')
			{
				$this->assertEquals('font-weight: bold', $crawler_item->filter('span.message_toggle span')->attr('style'));
			}
			else
			{
				$this->assertEmpty($crawler_item->filter('span.message_toggle span')->attr('style'));
			}
			$this->assertEquals($arrow, $crawler_item->filter('div.message_header img')->attr('src'));

			$name = '/Contact for number 001\.\.\./';
			$this->_assertMatchesRegularExpression($name, $this->crawler_text($crawler_item->filter('div.message_header > span > span')));

			$message_preview = mb_substr($message_full, 0, 20);
			$this->_assertStringContainsString($message_preview, $this->crawler_text($crawler_item->filter('span.message_toggle span.message_preview')));

			if ($source === 'inbox')
			{
				$this->assertDetailArea($crawler_item, $source, [
					'number' => $number,
					'inserted' => '',
					'date' => simple_date(date('Y-m-d H:i:s', strtotime(DBSetup::BASE_MESSAGE_DATE . ' -' . (1 - $counter_inbox + 1) . ' minutes'))), //TODO
					'smsc' => '',
					'parts' => DBSetup::nb_of_parts_per_inbox_message,
					'status' => '',
				]);
			}
			else
			{
				$this->assertDetailArea($crawler_item, $source, [
					'number' => $number,
					'inserted' => '',
					'date' => simple_date(date('Y-m-d H:i:s', strtotime(DBSetup::BASE_MESSAGE_DATE . ' -' . (1 - $counter_sentitems + 1) . ' minutes'))), //TODO
					'smsc' => '+33600000000',
					'parts' => DBSetup::nb_of_parts_per_outbox_message,
					'status' => check_delivery_report(DBsetup::$sentitems_status[1 - $counter_sentitems]),
				]);
			}

			$this->assertEquals($message_full, $this->crawler_text($crawler_item->filter('div.message_content')));

			$this->assertOptionMenuButtons($crawler_item, $source, TRUE);

			$this->assertNotEmpty($this->crawler_text($crawler_item->filter('div.message_metadata')));
		}

		foreach ($inbox_messages_marked_as_read as $id)
		{
			// assert that inbox box messages are now marked as read
			$status = db_boolean_to_php_bool($db_engine, $this->CI->db->where('ID', $id)->get('inbox')->row()->readed);
			$this->assertTrue($status, $id);
		}

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider folderAndMyFolder_inboxAndSentitems_Provider
	 *
	 */
	public function test_conversation_ajaxGET_folder_inboxAndSentitems_multipart_sortDesc_contactNotInPbk($db_engine, $folder, $id_folder)
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

		$db = &DBSetup::$current_setup[$db_engine];

		$id_user = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['conversation_sort' => 'desc']);
		$per_page = 20;
		$db->update('user_settings', ['id_user' => $id_user], ['paging' => $per_page]);
		$db->update('inbox', ['TextDecoded' => '%User 4%'], ['readed' => 'false']);

		$this->request->addCallable($db->closure());

		if ($id_folder === NULL)
		{
			$type = 'folder';
			$id_offset_inbox = 375;
			$id_offset_sentitems = 750;
			$msg_fld_inbox = 'Inbox';
			$msg_fld_sentitems = 'Sentitems';
		}
		else
		{
			$type = 'my_folder';
			$id_offset_inbox = 1530;
			$id_offset_sentitems = 1276;
			$msg_fld_inbox = 'Inbox/#' . $id_folder;
			$msg_fld_sentitems = 'Sentitems/#' . $id_folder;
		}
		$number = '+33600000001';
		$url = implode('/', [$type, $folder, rawurlencode($number), $id_folder]);
		$output = $this->ajaxRequest('GET', 'messages/conversation/' . $url);

		$crawler = new Crawler($output);

		$this->assertEquals(DBSetup::messages_per_recipient + DBSetup::messages_per_sender, $crawler->filter('div.messagelist_conversation')->count());
		$counter_inbox = $counter_sentitems = 0;
		$inbox_messages_marked_as_read = [];
		for ($i = 1; $i <= DBSetup::messages_per_recipient + DBSetup::messages_per_sender; $i++)
		{
			$source = ($i % 2 === 1) ? 'inbox' : 'sentitems';
			if ($source === 'inbox')
			{
				$counter_inbox = $counter_inbox + 1;
				$id_expected_inbox = $id_offset_inbox + ($counter_inbox) * DBSetup::nb_of_parts_per_inbox_message - (DBSetup::nb_of_parts_per_inbox_message - 1);
				$id = $id_expected_inbox;
				$message_full = DBSetup::text_replace_placeholder('User 4. Message ' . $counter_inbox . '. Sender 1. Folder '.$msg_fld_inbox, DBSetup::$text_multi_unicode);
				$arrow = $this->CI->config->item('img_path') . 'arrow_left.gif';
				$inbox_messages_marked_as_read[] = $id_expected_inbox;
			}
			else
			{
				$counter_sentitems = $counter_sentitems + 1;
				$id_expected_sentitems = $id_offset_sentitems + $counter_sentitems; // By default, sorting is ascending. So we have to revert the order of ID
				$id = $id_expected_sentitems;
				$message_full = DBSetup::text_replace_placeholder('User 4. Message ' . $counter_sentitems . '. Recipient 1. Folder '.$msg_fld_sentitems, DBSetup::$text_multi_gsm);
				$arrow = $this->CI->config->item('img_path') . 'arrow_right.gif';
			}

			$crawler_item = $crawler->filter('div.messagelist_conversation')->eq($i - 1);

			$this->assertEquals('message_container '. $source, $crawler_item->filter('div.message_container')->attr('class'));
			$this->assertEquals('item_source' . $id, $crawler_item->filter('div.message_header input')->eq(0)->attr('id'));
			$this->assertEquals('item_source' . $id, $crawler_item->filter('div.message_header input')->eq(0)->attr('name'));
			$this->assertEquals($source, $crawler_item->filter('div.message_header input')->eq(0)->attr('value'));
			$this->assertEquals('item_number' . $id, $crawler_item->filter('div.message_header input')->eq(1)->attr('id'));
			$this->assertEquals('item_number' . $id, $crawler_item->filter('div.message_header input')->eq(1)->attr('name'));
			$this->assertEquals($number, $crawler_item->filter('div.message_header input')->eq(1)->attr('value'));
			$this->assertEquals($id, $crawler_item->filter('div.message_header input')->eq(2)->attr('id'));
			$this->assertEquals($id, $crawler_item->filter('div.message_header input')->eq(2)->attr('value'));
			if ($source === 'inbox')
			{
				$this->assertEquals('font-weight: bold', $crawler_item->filter('span.message_toggle span')->attr('style'));
			}
			else
			{
				$this->assertEmpty($crawler_item->filter('span.message_toggle span')->attr('style'));
			}
			$this->assertEquals($arrow, $crawler_item->filter('div.message_header img')->attr('src'));

			$name = '/' . preg_quote(phone_format_human($number), '/') . '/';
			$this->_assertMatchesRegularExpression($name, $this->crawler_text($crawler_item->filter('div.message_header > span > span')));

			$message_preview = mb_substr($message_full, 0, 20);
			$this->_assertStringContainsString($message_preview, $this->crawler_text($crawler_item->filter('span.message_toggle span.message_preview')));

			if ($source === 'inbox')
			{
				$this->assertDetailArea($crawler_item, $source, [
					'number' => $number,
					'inserted' => '',
					'date' => simple_date(date('Y-m-d H:i:s', strtotime(DBSetup::BASE_MESSAGE_DATE . ' -' . $counter_inbox . ' minutes'))), //TODO
					'smsc' => '',
					'parts' => DBSetup::nb_of_parts_per_inbox_message,
					'status' => '',
				]);
			}
			else
			{
				$this->assertDetailArea($crawler_item, $source, [
					'number' => $number,
					'inserted' => '',
					'date' => simple_date(date('Y-m-d H:i:s', strtotime(DBSetup::BASE_MESSAGE_DATE . ' -' . $counter_sentitems . ' minutes'))), //TODO
					'smsc' => '+33600000000',
					'parts' => DBSetup::nb_of_parts_per_outbox_message,
					'status' => check_delivery_report(DBsetup::$sentitems_status[$counter_sentitems - 1]),
				]);
			}

			$this->assertEquals($message_full, $this->crawler_text($crawler_item->filter('div.message_content')));

			$this->assertOptionMenuButtons($crawler_item, $source, FALSE);

			$this->assertNotEmpty($this->crawler_text($crawler_item->filter('div.message_metadata')));
		}

		foreach ($inbox_messages_marked_as_read as $id)
		{
			// assert that inbox box messages are now marked as read
			$status = db_boolean_to_php_bool($db_engine, $this->CI->db->where('ID', $id)->get('inbox')->row()->readed);
			$this->assertTrue($status, $id);
		}

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider folderAndMyFolder_inboxAndSentitems_Provider
	 *
	 */
	public function test_conversation_ajaxGET_folder_inboxAndSentitems_multipart_sortDesc_contactNotInPbk_withOffset($db_engine, $folder, $id_folder)
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

		$db = &DBSetup::$current_setup[$db_engine];

		$id_user = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['conversation_sort' => 'desc']);
		$per_page = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['paging' => $per_page]);
		$db->update('inbox', ['TextDecoded' => '%User 4%'], ['readed' => 'false']);

		$this->request->addCallable($db->closure());

		if ($id_folder === NULL)
		{
			$type = 'folder';
			$id_offset_inbox = 375;
			$id_offset_sentitems = 750;
			$msg_fld_inbox = 'Inbox';
			$msg_fld_sentitems = 'Sentitems';
		}
		else
		{
			$type = 'my_folder';
			$id_offset_inbox = 1530;
			$id_offset_sentitems = 1276;
			$msg_fld_inbox = 'Inbox/#' . $id_folder;
			$msg_fld_sentitems = 'Sentitems/#' . $id_folder;
		}
		$number = '+33600000001';
		$url = implode('/', [$type, $folder, rawurlencode($number), $id_folder]);
		$offset = floor((DBSetup::messages_per_recipient + DBSetup::messages_per_sender) / $per_page) * $per_page;
		$output = $this->ajaxRequest('GET', 'messages/conversation/' . $url . '/' . $offset);

		$crawler = new Crawler($output);

		$this->assertEquals(DBSetup::messages_per_recipient + DBSetup::messages_per_sender - $offset, $crawler->filter('div.messagelist_conversation')->count());
		$counter_inbox = $counter_sentitems = 0;
		$inbox_messages_marked_as_read = [];
		for ($i = 1; $i <= DBSetup::messages_per_recipient + DBSetup::messages_per_sender; $i++)
		{
			$source = ($i % 2 === 1) ? 'inbox' : 'sentitems';
			if ($source === 'inbox')
			{
				$counter_inbox = $counter_inbox + 1;
				$id_expected_inbox = $id_offset_inbox + ($counter_inbox) * DBSetup::nb_of_parts_per_inbox_message - (DBSetup::nb_of_parts_per_inbox_message - 1);
				$id = $id_expected_inbox;
				$message_full = DBSetup::text_replace_placeholder('User 4. Message ' . $counter_inbox . '. Sender 1. Folder '.$msg_fld_inbox, DBSetup::$text_multi_unicode);
				$arrow = $this->CI->config->item('img_path') . 'arrow_left.gif';
				$inbox_messages_marked_as_read[] = $id_expected_inbox;
			}
			else
			{
				$counter_sentitems = $counter_sentitems + 1;
				$id_expected_sentitems = $id_offset_sentitems + $counter_sentitems; // By default, sorting is ascending. So we have to revert the order of ID
				$id = $id_expected_sentitems;
				$message_full = DBSetup::text_replace_placeholder('User 4. Message ' . $counter_sentitems . '. Recipient 1. Folder '.$msg_fld_sentitems, DBSetup::$text_multi_gsm);
				$arrow = $this->CI->config->item('img_path') . 'arrow_right.gif';
			}

			if ($i <= $offset)
			{
				// We haven't reached the 1st message to display yet, so jump to the next one.
				array_pop($inbox_messages_marked_as_read);
				continue;
			}

			$crawler_item = $crawler->filter('div.messagelist_conversation')->eq($i - 1 - $offset);

			$this->assertEquals('message_container '. $source, $crawler_item->filter('div.message_container')->attr('class'));
			$this->assertEquals('item_source' . $id, $crawler_item->filter('div.message_header input')->eq(0)->attr('id'));
			$this->assertEquals('item_source' . $id, $crawler_item->filter('div.message_header input')->eq(0)->attr('name'));
			$this->assertEquals($source, $crawler_item->filter('div.message_header input')->eq(0)->attr('value'));
			$this->assertEquals('item_number' . $id, $crawler_item->filter('div.message_header input')->eq(1)->attr('id'));
			$this->assertEquals('item_number' . $id, $crawler_item->filter('div.message_header input')->eq(1)->attr('name'));
			$this->assertEquals($number, $crawler_item->filter('div.message_header input')->eq(1)->attr('value'));
			$this->assertEquals($id, $crawler_item->filter('div.message_header input')->eq(2)->attr('id'));
			$this->assertEquals($id, $crawler_item->filter('div.message_header input')->eq(2)->attr('value'));
			if ($source === 'inbox')
			{
				$this->assertEquals('font-weight: bold', $crawler_item->filter('span.message_toggle span')->attr('style'));
			}
			else
			{
				$this->assertEmpty($crawler_item->filter('span.message_toggle span')->attr('style'));
			}
			$this->assertEquals($arrow, $crawler_item->filter('div.message_header img')->attr('src'));

			$name = '/' . preg_quote(phone_format_human($number), '/') . '/';
			$this->_assertMatchesRegularExpression($name, $this->crawler_text($crawler_item->filter('div.message_header > span > span')));

			$message_preview = mb_substr($message_full, 0, 20);
			$this->_assertStringContainsString($message_preview, $this->crawler_text($crawler_item->filter('span.message_toggle span.message_preview')));

			if ($source === 'inbox')
			{
				$this->assertDetailArea($crawler_item, $source, [
					'number' => $number,
					'inserted' => '',
					'date' => simple_date(date('Y-m-d H:i:s', strtotime(DBSetup::BASE_MESSAGE_DATE . ' -' . $counter_inbox . ' minutes'))), //TODO
					'smsc' => '',
					'parts' => DBSetup::nb_of_parts_per_inbox_message,
					'status' => '',
				]);
			}
			else
			{
				$this->assertDetailArea($crawler_item, $source, [
					'number' => $number,
					'inserted' => '',
					'date' => simple_date(date('Y-m-d H:i:s', strtotime(DBSetup::BASE_MESSAGE_DATE . ' -' . $counter_sentitems . ' minutes'))), //TODO
					'smsc' => '+33600000000',
					'parts' => DBSetup::nb_of_parts_per_outbox_message,
					'status' => check_delivery_report(DBsetup::$sentitems_status[$counter_sentitems - 1]),
				]);
			}

			$this->assertEquals($message_full, $this->crawler_text($crawler_item->filter('div.message_content')));

			$this->assertOptionMenuButtons($crawler_item, $source, FALSE);

			$this->assertNotEmpty($this->crawler_text($crawler_item->filter('div.message_metadata')));
		}

		foreach ($inbox_messages_marked_as_read as $id)
		{
			// assert that inbox box messages are now marked as read
			$status = db_boolean_to_php_bool($db_engine, $this->CI->db->where('ID', $id)->get('inbox')->row()->readed);
			$this->assertTrue($status, $id);
		}

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_conversation_ajaxGET_folder_sentitems_sendingError_no_message($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->request->setCallablePreConstructor(
			function () {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '7';
				$_SESSION['level'] = 'user';
				$_SESSION['username'] = 'user6';
			}
		);

		$type = 'folder';
		$folder = 'sentitems';
		$id_folder = NULL;
		$url = implode('/', [$type, $folder, 'sending_error', $id_folder]);
		$output = $this->ajaxRequest('GET', 'messages/conversation/' . $url);
		$crawler = new Crawler($output);

		$this->assertEquals('There is no message in Sending error.', $this->crawler_text($crawler->filter('p > i ')));

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_conversation_ajaxGET_folder_sentitems_sendingError($db_engine)
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

		$db = &DBSetup::$current_setup[$db_engine];

		$id_user = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['conversation_sort' => 'asc']);
		$per_page = 20;
		$db->update('user_settings', ['id_user' => $id_user], ['paging' => $per_page]);
		$db->update('inbox', ['TextDecoded' => '%User 4%'], ['readed' => 'false']);

		$this->request->addCallable($db->closure());

		$type = 'folder';
		$folder = 'sentitems';
		$id_folder = NULL;
		$url = implode('/', [$type, $folder, 'sending_error', $id_folder]);
		$output = $this->ajaxRequest('GET', 'messages/conversation/' . $url);
		$crawler = new Crawler($output);

		$this->assertEquals(min($per_page, DBSetup::recipients), $crawler->filter('div.messagelist_conversation')->count());
		$id_offset_sentitems = 750;
		$counter_sentitems = 0;
		$index_of_SendingError = 3; // This is the index of value "SendingError" of the DBSetup::$sentitems_status
		for ($i = 1; $i <= min($per_page, DBSetup::recipients); $i++)
		{
			$number = '+336' . sprintf('%08d', DBSetup::recipients - $i + 1);
			$source = 'sentitems';
			$counter_sentitems += $index_of_SendingError;
			$id_expected_sentitems = $id_offset_sentitems + (DBsetup::recipients * DBSetup::messages_per_recipient) - $counter_sentitems + 1; // By default, sorting is ascending. So we have to revert the order of ID
			$id = $id_expected_sentitems;
			$msg_number = $index_of_SendingError; // This is the index of value "SendingError" of the DBSetup::$sentitems_status
			$message_full = DBSetup::text_replace_placeholder('User 4. Message ' . $msg_number . '. Recipient '.(DBsetup::recipients - $i + 1).'. Folder Sentitems', DBSetup::$text_multi_gsm);
			$arrow = $this->CI->config->item('img_path') . 'arrow_right.gif';

			$crawler_item = $crawler->filter('div.messagelist_conversation')->eq($i - 1);

			$this->assertEquals('message_container '. $source, $crawler_item->filter('div.message_container')->attr('class'));
			$this->assertEquals('item_source' . $id, $crawler_item->filter('div.message_header input')->eq(0)->attr('id'));
			$this->assertEquals('item_source' . $id, $crawler_item->filter('div.message_header input')->eq(0)->attr('name'));
			$this->assertEquals($source, $crawler_item->filter('div.message_header input')->eq(0)->attr('value'));
			$this->assertEquals('item_number' . $id, $crawler_item->filter('div.message_header input')->eq(1)->attr('id'));
			$this->assertEquals('item_number' . $id, $crawler_item->filter('div.message_header input')->eq(1)->attr('name'));
			$this->assertEquals($number, $crawler_item->filter('div.message_header input')->eq(1)->attr('value'));
			$this->assertEquals($id, $crawler_item->filter('div.message_header input')->eq(2)->attr('id'));
			$this->assertEquals($id, $crawler_item->filter('div.message_header input')->eq(2)->attr('value'));

			$this->assertEmpty($crawler_item->filter('span.message_toggle span')->attr('style'));

			$this->assertEquals($arrow, $crawler_item->filter('div.message_header img')->attr('src'));

			$name = '/' . preg_quote(phone_format_human($number), '/') . '/';
			$this->_assertMatchesRegularExpression($name, $this->crawler_text($crawler_item->filter('div.message_header > span > span')));

			$message_preview = mb_substr($message_full, 0, 20);
			$this->_assertStringContainsString($message_preview, $this->crawler_text($crawler_item->filter('span.message_toggle span.message_preview')));

			$this->assertDetailArea($crawler_item, $source, [
				'number' => $number,
				'inserted' => '',
				'date' => simple_date(date('Y-m-d H:i:s', strtotime(DBSetup::BASE_MESSAGE_DATE . ' -' . (DBSetup::messages_per_recipient * DBsetup::recipients - $counter_sentitems + 1) . ' minutes'))), //TODO
				'smsc' => '+33600000000',
				'parts' => DBSetup::nb_of_parts_per_outbox_message,
				'status' => check_delivery_report(DBsetup::$sentitems_status[$msg_number - 1]),
			]);

			$this->assertEquals($message_full, $this->crawler_text($crawler_item->filter('div.message_content')));

			$this->assertOptionMenuButtons($crawler_item, $source, FALSE);

			$this->assertNotEmpty($this->crawler_text($crawler_item->filter('div.message_metadata')));
			$counter_sentitems += DBSetup::messages_per_recipient - $index_of_SendingError;
		}

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_conversation_ajaxGET_folder_outbox_multipart_sortAsc_contactNotInPbk($db_engine)
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

		$db = &DBSetup::$current_setup[$db_engine];

		$id_user = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['conversation_sort' => 'asc']);
		$per_page = 20;
		$db->update('user_settings', ['id_user' => $id_user], ['paging' => $per_page]);

		$this->request->addCallable($db->closure());

		$type = 'folder';
		$folder = 'outbox';
		$number = '+33600000001';
		$id_folder = NULL;
		$url = implode('/', [$type, $folder, rawurlencode($number), $id_folder]);
		$output = $this->ajaxRequest('GET', 'messages/conversation/' . $url);
		$crawler = new Crawler($output);

		$this->assertEquals(DBSetup::messages_per_recipient, $crawler->filter('div.messagelist_conversation')->count());
		$id_offset_outbox = 625;
		$counter_outbox = 0;
		for ($i = 1; $i <= DBSetup::messages_per_recipient; $i++)
		{
			$source = $folder;
			$counter_outbox = $counter_outbox + 1;
			$id_expected_outbox = $id_offset_outbox + DBSetup::messages_per_recipient - $counter_outbox + 1; // By default, sorting is ascending. So we have to revert the order of ID
			$id = $id_expected_outbox;
			$message_full = DBSetup::text_replace_placeholder('User 4. Message ' . (DBSetup::messages_per_recipient - $counter_outbox + 1) . '. Recipient 1. Folder Outbox', DBSetup::$text_multi_gsm);
			$arrow = $this->CI->config->item('img_path') . 'circle.gif';

			$crawler_item = $crawler->filter('div.messagelist_conversation')->eq($i - 1);

			$this->assertEquals('message_container '. $source, $crawler_item->filter('div.message_container')->attr('class'));
			$this->assertEquals('item_source' . $id, $crawler_item->filter('div.message_header input')->eq(0)->attr('id'));
			$this->assertEquals('item_source' . $id, $crawler_item->filter('div.message_header input')->eq(0)->attr('name'));
			$this->assertEquals($source, $crawler_item->filter('div.message_header input')->eq(0)->attr('value'));
			$this->assertEquals('item_number' . $id, $crawler_item->filter('div.message_header input')->eq(1)->attr('id'));
			$this->assertEquals('item_number' . $id, $crawler_item->filter('div.message_header input')->eq(1)->attr('name'));
			$this->assertEquals($number, $crawler_item->filter('div.message_header input')->eq(1)->attr('value'));
			$this->assertEquals($id, $crawler_item->filter('div.message_header input')->eq(2)->attr('id'));
			$this->assertEquals($id, $crawler_item->filter('div.message_header input')->eq(2)->attr('value'));

			$this->assertEmpty($crawler_item->filter('span.message_toggle span')->attr('style'));

			$this->assertEquals($arrow, $crawler_item->filter('div.message_header img')->attr('src'));

			$name = '/' . preg_quote(phone_format_human($number), '/') . '/';
			$this->_assertMatchesRegularExpression($name, $this->crawler_text($crawler_item->filter('div.message_header > span > span')));

			$message_preview = mb_substr($message_full, 0, 20);
			$this->_assertStringContainsString($message_preview, $this->crawler_text($crawler_item->filter('span.message_toggle span.message_preview')));

			$this->assertDetailArea($crawler_item, $source, [
				'number' => $number,
				'inserted' => 'now',
				'date' => simple_date(date('Y-m-d H:i:s', strtotime(DBSetup::BASE_MESSAGE_DATE . ' -' . (DBSetup::messages_per_recipient - $counter_outbox + 1) . ' minutes'))), //TODO
				'smsc' => '+33600000000',
				'parts' => DBSetup::nb_of_parts_per_outbox_message,
				'status' => check_delivery_report(DBsetup::$sentitems_status[(DBSetup::messages_per_recipient - $counter_outbox)]),
			]);

			$this->assertEquals($message_full, $this->crawler_text($crawler_item->filter('div.message_content')));

			$this->assertOptionMenuButtons($crawler_item, $source, FALSE);

			$this->assertNotEmpty($this->crawler_text($crawler_item->filter('div.message_metadata')));
		}

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_conversation_ajaxGET_folder_outbox_multipart_sortAsc_contactNotInPbk_withOffset($db_engine)
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

		$db = &DBSetup::$current_setup[$db_engine];

		$id_user = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['conversation_sort' => 'asc']);
		$per_page = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['paging' => $per_page]);

		$this->request->addCallable($db->closure());

		$type = 'folder';
		$folder = 'outbox';
		$number = '+33600000001';
		$id_folder = NULL;
		$url = implode('/', [$type, $folder, rawurlencode($number), $id_folder]);
		$offset = floor(DBSetup::messages_per_recipient / $per_page) * $per_page;
		$output = $this->ajaxRequest('GET', 'messages/conversation/' . $url . '/' . $offset);
		$crawler = new Crawler($output);

		$this->assertEquals(DBSetup::messages_per_recipient - $offset, $crawler->filter('div.messagelist_conversation')->count());
		$id_offset_outbox = 625;
		$counter_outbox = 0;
		for ($i = 1; $i <= DBSetup::messages_per_recipient; $i++)
		{
			$source = $folder;
			$counter_outbox = $counter_outbox + 1;
			$id_expected_outbox = $id_offset_outbox + DBSetup::messages_per_recipient - $counter_outbox + 1; // By default, sorting is ascending. So we have to revert the order of ID
			$id = $id_expected_outbox;
			$message_full = DBSetup::text_replace_placeholder('User 4. Message ' . (DBSetup::messages_per_recipient - $counter_outbox + 1) . '. Recipient 1. Folder Outbox', DBSetup::$text_multi_gsm);
			$arrow = $this->CI->config->item('img_path') . 'circle.gif';

			if ($i <= $offset)
			{
				// We haven't reached the 1st message to display yet, so jump to the next one.
				continue;
			}

			$crawler_item = $crawler->filter('div.messagelist_conversation')->eq($i - 1 - $offset);

			$this->assertEquals('message_container '. $source, $crawler_item->filter('div.message_container')->attr('class'));
			$this->assertEquals('item_source' . $id, $crawler_item->filter('div.message_header input')->eq(0)->attr('id'));
			$this->assertEquals('item_source' . $id, $crawler_item->filter('div.message_header input')->eq(0)->attr('name'));
			$this->assertEquals($source, $crawler_item->filter('div.message_header input')->eq(0)->attr('value'));
			$this->assertEquals('item_number' . $id, $crawler_item->filter('div.message_header input')->eq(1)->attr('id'));
			$this->assertEquals('item_number' . $id, $crawler_item->filter('div.message_header input')->eq(1)->attr('name'));
			$this->assertEquals($number, $crawler_item->filter('div.message_header input')->eq(1)->attr('value'));
			$this->assertEquals($id, $crawler_item->filter('div.message_header input')->eq(2)->attr('id'));
			$this->assertEquals($id, $crawler_item->filter('div.message_header input')->eq(2)->attr('value'));

			$this->assertEmpty($crawler_item->filter('span.message_toggle span')->attr('style'));

			$this->assertEquals($arrow, $crawler_item->filter('div.message_header img')->attr('src'));

			$name = '/' . preg_quote(phone_format_human($number), '/') . '/';
			$this->_assertMatchesRegularExpression($name, $this->crawler_text($crawler_item->filter('div.message_header > span > span')));

			$message_preview = mb_substr($message_full, 0, 20);
			$this->_assertStringContainsString($message_preview, $this->crawler_text($crawler_item->filter('span.message_toggle span.message_preview')));

			$this->assertDetailArea($crawler_item, $source, [
				'number' => $number,
				'inserted' => 'now',
				'date' => simple_date(date('Y-m-d H:i:s', strtotime(DBSetup::BASE_MESSAGE_DATE . ' -' . (DBSetup::messages_per_recipient - $counter_outbox + 1) . ' minutes'))), //TODO
				'smsc' => '+33600000000',
				'parts' => DBSetup::nb_of_parts_per_outbox_message,
				'status' => check_delivery_report(DBsetup::$sentitems_status[(DBSetup::messages_per_recipient - $counter_outbox)]),
			]);

			$this->assertEquals($message_full, $this->crawler_text($crawler_item->filter('div.message_content')));

			$this->assertOptionMenuButtons($crawler_item, $source, FALSE);

			$this->assertNotEmpty($this->crawler_text($crawler_item->filter('div.message_metadata')));
		}

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_conversation_ajaxGET_folder_outbox_multipart_sortDesc_contactNotInPbk($db_engine)
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

		$db = &DBSetup::$current_setup[$db_engine];

		$id_user = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['conversation_sort' => 'desc']);
		$per_page = 20;
		$db->update('user_settings', ['id_user' => $id_user], ['paging' => $per_page]);

		$this->request->addCallable($db->closure());

		$type = 'folder';
		$folder = 'outbox';
		$number = '+33600000001';
		$id_folder = NULL;
		$url = implode('/', [$type, $folder, rawurlencode($number), $id_folder]);
		$output = $this->ajaxRequest('GET', 'messages/conversation/' . $url);
		$crawler = new Crawler($output);

		$this->assertEquals(DBSetup::messages_per_recipient, $crawler->filter('div.messagelist_conversation')->count());
		$id_offset_outbox = 625;
		$counter_outbox = 0;
		for ($i = 1; $i <= DBSetup::messages_per_recipient; $i++)
		{
			$source = $folder;
			$counter_outbox = $counter_outbox + 1;
			$id_expected_outbox = $id_offset_outbox + $counter_outbox;
			$id = $id_expected_outbox;
			$message_full = DBSetup::text_replace_placeholder('User 4. Message ' .  $counter_outbox . '. Recipient 1. Folder Outbox', DBSetup::$text_multi_gsm);
			$arrow = $this->CI->config->item('img_path') . 'circle.gif';

			$crawler_item = $crawler->filter('div.messagelist_conversation')->eq($i - 1);

			$this->assertEquals('message_container '. $source, $crawler_item->filter('div.message_container')->attr('class'));
			$this->assertEquals('item_source' . $id, $crawler_item->filter('div.message_header input')->eq(0)->attr('id'));
			$this->assertEquals('item_source' . $id, $crawler_item->filter('div.message_header input')->eq(0)->attr('name'));
			$this->assertEquals($source, $crawler_item->filter('div.message_header input')->eq(0)->attr('value'));
			$this->assertEquals('item_number' . $id, $crawler_item->filter('div.message_header input')->eq(1)->attr('id'));
			$this->assertEquals('item_number' . $id, $crawler_item->filter('div.message_header input')->eq(1)->attr('name'));
			$this->assertEquals($number, $crawler_item->filter('div.message_header input')->eq(1)->attr('value'));
			$this->assertEquals($id, $crawler_item->filter('div.message_header input')->eq(2)->attr('id'));
			$this->assertEquals($id, $crawler_item->filter('div.message_header input')->eq(2)->attr('value'));

			$this->assertEmpty($crawler_item->filter('span.message_toggle span')->attr('style'));

			$this->assertEquals($arrow, $crawler_item->filter('div.message_header img')->attr('src'));

			$name = '/' . preg_quote(phone_format_human($number), '/') . '/';
			$this->_assertMatchesRegularExpression($name, $this->crawler_text($crawler_item->filter('div.message_header > span > span')));

			$message_preview = mb_substr($message_full, 0, 20);
			$this->_assertStringContainsString($message_preview, $this->crawler_text($crawler_item->filter('span.message_toggle span.message_preview')));

			$this->assertDetailArea($crawler_item, $source, [
				'number' => $number,
				'inserted' => 'now',
				'date' => simple_date(date('Y-m-d H:i:s', strtotime(DBSetup::BASE_MESSAGE_DATE . ' -' . $counter_outbox . ' minutes'))), //TODO
				'smsc' => '+33600000000',
				'parts' => DBSetup::nb_of_parts_per_outbox_message,
				'status' => '',
			]);

			$this->assertEquals($message_full, $this->crawler_text($crawler_item->filter('div.message_content')));

			$this->assertOptionMenuButtons($crawler_item, $source, FALSE);

			$this->assertNotEmpty($this->crawler_text($crawler_item->filter('div.message_metadata')));
		}

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_conversation_ajaxGET_folder_outbox_multipart_sortDesc_contactNotInPbk_withOffset($db_engine)
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

		$db = &DBSetup::$current_setup[$db_engine];

		$id_user = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['conversation_sort' => 'desc']);
		$per_page = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['paging' => $per_page]);

		$this->request->addCallable($db->closure());

		$type = 'folder';
		$folder = 'outbox';
		$number = '+33600000001';
		$id_folder = NULL;
		$url = implode('/', [$type, $folder, rawurlencode($number), $id_folder]);
		$offset = floor(DBSetup::messages_per_recipient / $per_page) * $per_page;
		$output = $this->ajaxRequest('GET', 'messages/conversation/' . $url . '/' . $offset);
		$crawler = new Crawler($output);

		$this->assertEquals(DBSetup::messages_per_recipient - $offset, $crawler->filter('div.messagelist_conversation')->count());
		$id_offset_outbox = 625;
		$counter_outbox = 0;
		for ($i = 1; $i <= DBSetup::messages_per_recipient; $i++)
		{
			$source = $folder;
			$counter_outbox = $counter_outbox + 1;
			$id_expected_outbox = $id_offset_outbox + $counter_outbox; // By default, sorting is ascending. So we have to revert the order of ID
			$id = $id_expected_outbox;
			$message_full = DBSetup::text_replace_placeholder('User 4. Message ' . $counter_outbox . '. Recipient 1. Folder Outbox', DBSetup::$text_multi_gsm);
			$arrow = $this->CI->config->item('img_path') . 'circle.gif';

			if ($i <= $offset)
			{
				// We haven't reached the 1st message to display yet, so jump to the next one.
				continue;
			}

			$crawler_item = $crawler->filter('div.messagelist_conversation')->eq($i - 1 - $offset);

			$this->assertEquals('message_container '. $source, $crawler_item->filter('div.message_container')->attr('class'));
			$this->assertEquals('item_source' . $id, $crawler_item->filter('div.message_header input')->eq(0)->attr('id'));
			$this->assertEquals('item_source' . $id, $crawler_item->filter('div.message_header input')->eq(0)->attr('name'));
			$this->assertEquals($source, $crawler_item->filter('div.message_header input')->eq(0)->attr('value'));
			$this->assertEquals('item_number' . $id, $crawler_item->filter('div.message_header input')->eq(1)->attr('id'));
			$this->assertEquals('item_number' . $id, $crawler_item->filter('div.message_header input')->eq(1)->attr('name'));
			$this->assertEquals($number, $crawler_item->filter('div.message_header input')->eq(1)->attr('value'));
			$this->assertEquals($id, $crawler_item->filter('div.message_header input')->eq(2)->attr('id'));
			$this->assertEquals($id, $crawler_item->filter('div.message_header input')->eq(2)->attr('value'));

			$this->assertEmpty($crawler_item->filter('span.message_toggle span')->attr('style'));

			$this->assertEquals($arrow, $crawler_item->filter('div.message_header img')->attr('src'));

			$name = '/' . preg_quote(phone_format_human($number), '/') . '/';
			$this->_assertMatchesRegularExpression($name, $this->crawler_text($crawler_item->filter('div.message_header > span > span')));

			$message_preview = mb_substr($message_full, 0, 20);
			$this->_assertStringContainsString($message_preview, $this->crawler_text($crawler_item->filter('span.message_toggle span.message_preview')));

			$this->assertDetailArea($crawler_item, $source, [
				'number' => $number,
				'inserted' => 'now',
				'date' => simple_date(date('Y-m-d H:i:s', strtotime(DBSetup::BASE_MESSAGE_DATE . ' -' . $counter_outbox . ' minutes'))), //TODO
				'smsc' => '+33600000000',
				'parts' => DBSetup::nb_of_parts_per_outbox_message,
				'status' => '',
			]);

			$this->assertEquals($message_full, $this->crawler_text($crawler_item->filter('div.message_content')));

			$this->assertOptionMenuButtons($crawler_item, $source, FALSE);

			$this->assertNotEmpty($this->crawler_text($crawler_item->filter('div.message_metadata')));
		}

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	/*public function test_conversation_ajaxGET_my_folder_folder($db_engine)
	{
		$this->markTestSkipped('Covered by test_conversation_ajaxGET_folder_inboxAndSentitems*');
	}*/

	/**
	 * @dataProvider database_Provider
	 *
	 */
	/*public function test_conversation_ajaxGET_my_folder_trash($db_engine)
	{
		$this->markTestSkipped('Covered by test_conversation_ajaxGET_folder_inboxAndSentitems*');
	}*/

	/**
	 * @dataProvider database_Provider
	 *
	 */
	/*public function test_conversation_ajaxGET_my_folder_spam($db_engine)
	{
		$this->markTestSkipped('Covered by test_conversation_ajaxGET_folder_inboxAndSentitems*');
	}*/

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_conversation_ajaxGET_folder_other($db_engine)
	{
		// This one is used when in the phonebook, there is a link to see the conversation with the contact
		// The requested URL is in that case 'messages/conversation/folder/phonebook/%2B33600000001/'
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

		$db = &DBSetup::$current_setup[$db_engine];

		$id_user = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['conversation_sort' => 'asc']);
		$per_page = 30; // Voluntarily increased above default 20 so that we see all the results of the query
		$db->update('user_settings', ['id_user' => $id_user], ['paging' => $per_page]);
		$db->update('inbox', ['TextDecoded' => '%User 4%'], ['readed' => 'false']);

		$this->request->addCallable($db->closure());

		$type = 'folder';
		$folder = 'phonebook';
		$number = '+33600000001';
		$id_folder = NULL;
		$url = implode('/', [$type, $folder, rawurlencode($number), $id_folder]);
		$output = $this->ajaxRequest('GET', 'messages/conversation/' . $url);
		$crawler = new Crawler($output);

		// Folders: 3×Inbox (inbox, inbox/spam, inbox/#15) + 2×Sentitems (sentitems, sentitems/#15)
		// It should not contain the entries from trash (TODO: not tested yet, the test data doesn't have anything in trash)
		// TODO: should probably also return entries from 'outbox', but it doesn't.
		$total_number_of_records = 2 * DBSetup::messages_per_recipient + 3 * DBSetup::messages_per_sender;
		$this->assertEquals($total_number_of_records, $crawler->filter('div.messagelist_conversation')->count());
		$counter_inbox = $counter_sentitems = 0;
		for ($i = 1; $i <= $total_number_of_records; $i++)
		{
			switch (($i - 1) % 5)
			{
				case 0:
					$source = 'inbox';
					$msg_fld = 'Inbox';
					$counter_inbox = $counter_inbox + 1;
					break;
				case 1:
					$source = 'inbox';
					$msg_fld = 'Inbox/#15';
					break;
				case 2:
					$source = 'inbox';
					$msg_fld = 'Inbox/Spam';
					break;
				case 3:
					$counter_sentitems = $counter_sentitems + 1;
					$source = 'sentitems';
					$msg_fld = 'Sentitems';
					break;
				case 4:
					$source = 'sentitems';
					$msg_fld = 'Sentitems/#15';
					break;
			}

			if ($source === 'inbox')
			{
				$message_full = DBSetup::text_replace_placeholder('User 4. Message ' . (DBSetup::messages_per_sender - $counter_inbox + 1) . '. Sender 1. Folder '.$msg_fld, DBSetup::$text_multi_unicode);
				$arrow = $this->CI->config->item('img_path') . 'arrow_left.gif';
			}
			else
			{
				$message_full = DBSetup::text_replace_placeholder('User 4. Message ' . (DBSetup::messages_per_recipient - $counter_sentitems + 1) . '. Recipient 1. Folder '.$msg_fld, DBSetup::$text_multi_gsm);
				$arrow = $this->CI->config->item('img_path') . 'arrow_right.gif';
			}

			$crawler_item = $crawler->filter('div.messagelist_conversation')->eq($i - 1);

			$this->assertEquals('message_container '. $source, $crawler_item->filter('div.message_container')->attr('class'));
			// We don't check the ids here. Too complex. This is somehow covered by the other tests.
			$this->assertEquals($source, $crawler_item->filter('div.message_header input')->eq(0)->attr('value'));
			$this->assertEquals($number, $crawler_item->filter('div.message_header input')->eq(1)->attr('value'));
			if ($source === 'inbox')
			{
				$this->assertEquals('font-weight: bold', $crawler_item->filter('span.message_toggle span')->attr('style'));
			}
			else
			{
				$this->assertEmpty($crawler_item->filter('span.message_toggle span')->attr('style'));
			}
			$this->assertEquals($arrow, $crawler_item->filter('div.message_header img')->attr('src'));

			$name = '/' . preg_quote(phone_format_human($number), '/') . '/';
			$this->_assertMatchesRegularExpression($name, $this->crawler_text($crawler_item->filter('div.message_header > span > span')));

			$message_preview = mb_substr($message_full, 0, 20);
			$this->_assertStringContainsString($message_preview, $this->crawler_text($crawler_item->filter('span.message_toggle span.message_preview')));

			$this->assertEquals($message_full, $this->crawler_text($crawler_item->filter('div.message_content')));
		}

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_conversation_ajaxGET_folder_other_withOffset($db_engine)
	{
		// This one is used when in the phonebook, there is a link to see the conversation with the contact
		// The requested URL is in that case 'messages/conversation/folder/phonebook/%2B33600000001/'
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

		$db = &DBSetup::$current_setup[$db_engine];

		$id_user = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['conversation_sort' => 'asc']);
		$per_page = 20;
		$db->update('user_settings', ['id_user' => $id_user], ['paging' => $per_page]);
		$db->update('inbox', ['TextDecoded' => '%User 4%'], ['readed' => 'false']);

		$this->request->addCallable($db->closure());

		$type = 'folder';
		$folder = 'phonebook';
		$number = '+33600000001';
		$id_folder = NULL;
		$url = implode('/', [$type, $folder, rawurlencode($number), $id_folder]);
		$total_number_of_records = 2 * DBSetup::messages_per_recipient + 3 * DBSetup::messages_per_sender;
		$offset = floor(($total_number_of_records) / $per_page) * $per_page;
		$output = $this->ajaxRequest('GET', 'messages/conversation/' . $url . '/' . $offset);
		$crawler = new Crawler($output);

		// Folders: 3×Inbox (inbox, inbox/spam, inbox/#15) + 2×Sentitems (sentitems, sentitems/#15)
		// It should not contain the entries from trash (TODO: not tested yet, the test data doesn't have anything in trash)
		// TODO: should probably also return entries from 'outbox', but it doesn't.
		$this->assertEquals($total_number_of_records - $offset, $crawler->filter('div.messagelist_conversation')->count());
		$counter_inbox = $counter_sentitems = 0;
		for ($i = 1; $i <= $total_number_of_records; $i++)
		{
			switch (($i - 1) % 5)
			{
				case 0:
					$source = 'inbox';
					$msg_fld = 'Inbox';
					$counter_inbox = $counter_inbox + 1;
					break;
				case 1:
					$source = 'inbox';
					$msg_fld = 'Inbox/#15';
					break;
				case 2:
					$source = 'inbox';
					$msg_fld = 'Inbox/Spam';
					break;
				case 3:
					$counter_sentitems = $counter_sentitems + 1;
					$source = 'sentitems';
					$msg_fld = 'Sentitems';
					break;
				case 4:
					$source = 'sentitems';
					$msg_fld = 'Sentitems/#15';
					break;
			}

			if ($i <= $offset)
			{
				// We haven't reached the 1st message to display yet, so jump to the next one.
				continue;
			}

			if ($source === 'inbox')
			{
				$message_full = DBSetup::text_replace_placeholder('User 4. Message ' . (DBSetup::messages_per_sender - $counter_inbox + 1) . '. Sender 1. Folder '.$msg_fld, DBSetup::$text_multi_unicode);
				$arrow = $this->CI->config->item('img_path') . 'arrow_left.gif';
			}
			else
			{
				$message_full = DBSetup::text_replace_placeholder('User 4. Message ' . (DBSetup::messages_per_recipient - $counter_sentitems + 1) . '. Recipient 1. Folder '.$msg_fld, DBSetup::$text_multi_gsm);
				$arrow = $this->CI->config->item('img_path') . 'arrow_right.gif';
			}

			$crawler_item = $crawler->filter('div.messagelist_conversation')->eq($i - 1 - $offset);

			$this->assertEquals('message_container '. $source, $crawler_item->filter('div.message_container')->attr('class'));
			// We don't check the ids here. Too complex. This is somehow covered by the other tests.
			$this->assertEquals($source, $crawler_item->filter('div.message_header input')->eq(0)->attr('value'));
			$this->assertEquals($number, $crawler_item->filter('div.message_header input')->eq(1)->attr('value'));
			if ($source === 'inbox')
			{
				$this->assertEquals('font-weight: bold', $crawler_item->filter('span.message_toggle span')->attr('style'));
			}
			else
			{
				$this->assertEmpty($crawler_item->filter('span.message_toggle span')->attr('style'));
			}
			$this->assertEquals($arrow, $crawler_item->filter('div.message_header img')->attr('src'));

			$name = '/' . preg_quote(phone_format_human($number), '/') . '/';
			$this->_assertMatchesRegularExpression($name, $this->crawler_text($crawler_item->filter('div.message_header > span > span')));

			$message_preview = mb_substr($message_full, 0, 20);
			$this->_assertStringContainsString($message_preview, $this->crawler_text($crawler_item->filter('span.message_toggle span.message_preview')));

			$this->assertEquals($message_full, $this->crawler_text($crawler_item->filter('div.message_content')));
		}

		$this->assertValidHtmlSnippet($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_conversation_GET_folder_inbox_withOffset($db_engine)
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

		$db = &DBSetup::$current_setup[$db_engine];

		$id_user = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['conversation_sort' => 'asc']);
		$per_page = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['paging' => $per_page]);
		$db->update('inbox', ['TextDecoded' => '%User 4%'], ['readed' => 'false']);

		$this->request->addCallable($db->closure());

		$type = 'folder';
		$folder = 'inbox';
		$number = '+33600000001';
		$id_folder = NULL;
		$url = implode('/', [$type, $folder, rawurlencode($number), $id_folder]);
		$offset = floor((DBSetup::messages_per_recipient + DBSetup::messages_per_sender) / $per_page) * $per_page;
		$last_page = ceil((DBSetup::messages_per_recipient + DBSetup::messages_per_sender) / $per_page);
		$output = $this->request('GET', 'messages/conversation/' . $url . '/' . $offset);
		$crawler = new Crawler($output);

		$this->assertEquals(0, $crawler->filter('#js_function')->count());
		$this->assertEquals(1, $crawler->filter('#js_conversation')->count());
		$this->assertEquals(15, $crawler->filter('div.move_to')->attr('data-id_folder'));
		$this->assertEquals(0, $crawler->filter('#renamefolderdialog')->count());
		$this->assertEquals(0, $crawler->filter('#deletefolderdialog')->count());
		$this->assertEquals(0, $crawler->filter('#deletealldialog')->count());
		$this->assertEquals(0, $crawler->filter('#two_column_container')->count());
		//$this->assertEquals($folder_name, $this->crawler_text($crawler->filter('span.folder_name')));
		$this->assertEquals(0, $crawler->filter('#delete-all-link')->count());
		$this->assertEquals(0, $crawler->filter('#renamefolder')->count());
		$this->assertEquals(0, $crawler->filter('#deletefolder')->count());
		$this->assertEquals(0, $crawler->filter('span.currenttab')->count());
		//$this->assertEquals('Inbox', $this->crawler_text($crawler->filter('span.currenttab')));
		//$this->assertEquals(site_url().'/messages/my_folder/sentitems/'.$id_folder, $crawler->filter('span.currenttab')->nextAll()->attr('href'));
		//$this->assertEquals('Sent items', $this->crawler_text($crawler->filter('span.currenttab')->nextAll()));

		$this->assertEquals(1, $crawler->filter('#message_holder')->count());
		$this->assertEquals(0, $crawler->filter('div.messagelist.unreaded')->count());
		$this->assertEquals(DBSetup::messages_per_recipient + DBSetup::messages_per_sender - $offset, $crawler->filter('div.messagelist_conversation')->count());

		// navigation.php (buttons)
		$this->assertEquals(2, $crawler->filter('.back_threadlist')->count());
		$this->assertEquals('‹‹ Back to Inbox', $this->crawler_text($crawler->filter('.back_threadlist')));
		$this->assertEquals(site_url().'/messages/folder/inbox', $crawler->filter('.back_threadlist')->attr('href'));
		$this->assertEquals(2, $crawler->filter('.select_all_button')->count());
		$this->assertEquals(2, $crawler->filter('.clear_all_button')->count());
		// marker of conversation + inbox + spam/ham button
		$this->assertEquals(2, $crawler->filter('.spam_button')->count());
		$this->assertEquals(0, $crawler->filter('.ham_button')->count());
		$this->assertEquals(0, $crawler->filter('.recover_button')->count());
		$this->assertEquals(2, $crawler->filter('.move_to_button')->count());
		$this->assertEquals(2, $crawler->filter('.global_delete')->count());
		$this->assertEquals('Delete', $this->crawler_text($crawler->filter('.global_delete')));
		$this->assertEquals(2, $crawler->filter('.refresh_button')->count());
		$this->assertEquals(2, $crawler->filter('.process_incoming_msgs_button')->count());
		$this->assertEquals(0, $crawler->filter('.resend_bulk')->count());
		$this->assertEquals($last_page, $this->crawler_text($crawler->filter('div.paging > span.current_page')));
		$this->assertEquals(0, $crawler->filter('div.paging > span.current_page')->nextAll()->count());

		$db->update('inbox', ['TextDecoded' => '%User 4%'], ['readed' => 'false']);
		$db->execute($this->CI);

		$this->assertValidHtml($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_conversation_GET_folder_outbox_withOffset($db_engine)
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

		$db = &DBSetup::$current_setup[$db_engine];

		$id_user = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['conversation_sort' => 'asc']);
		$per_page = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['paging' => $per_page]);
		$db->update('inbox', ['TextDecoded' => '%User 4%'], ['readed' => 'false']);

		$this->request->addCallable($db->closure());

		$type = 'folder';
		$folder = 'outbox';
		$number = '+33600000001';
		$id_folder = NULL;
		$url = implode('/', [$type, $folder, rawurlencode($number), $id_folder]);
		$offset = floor((DBSetup::messages_per_recipient) / $per_page) * $per_page;
		$last_page = ceil((DBSetup::messages_per_recipient) / $per_page);
		$output = $this->request('GET', 'messages/conversation/' . $url . '/' . $offset);
		$crawler = new Crawler($output);

		$this->assertEquals(0, $crawler->filter('#js_function')->count());
		$this->assertEquals(1, $crawler->filter('#js_conversation')->count());
		$this->assertEquals(15, $crawler->filter('div.move_to')->attr('data-id_folder'));
		$this->assertEquals(0, $crawler->filter('#renamefolderdialog')->count());
		$this->assertEquals(0, $crawler->filter('#deletefolderdialog')->count());
		$this->assertEquals(0, $crawler->filter('#deletealldialog')->count());
		$this->assertEquals(0, $crawler->filter('#two_column_container')->count());
		//$this->assertEquals($folder_name, $this->crawler_text($crawler->filter('span.folder_name')));
		$this->assertEquals(0, $crawler->filter('#delete-all-link')->count());
		$this->assertEquals(0, $crawler->filter('#renamefolder')->count());
		$this->assertEquals(0, $crawler->filter('#deletefolder')->count());
		$this->assertEquals(0, $crawler->filter('span.currenttab')->count());
		//$this->assertEquals('Inbox', $this->crawler_text($crawler->filter('span.currenttab')));
		//$this->assertEquals(site_url().'/messages/my_folder/sentitems/'.$id_folder, $crawler->filter('span.currenttab')->nextAll()->attr('href'));
		//$this->assertEquals('Sent items', $this->crawler_text($crawler->filter('span.currenttab')->nextAll()));

		$this->assertEquals(1, $crawler->filter('#message_holder')->count());
		$this->assertEquals(0, $crawler->filter('div.messagelist.unreaded')->count());
		$this->assertEquals(DBSetup::messages_per_recipient - $offset, $crawler->filter('div.messagelist_conversation')->count());

		// navigation.php (buttons)
		$this->assertEquals(2, $crawler->filter('.back_threadlist')->count());
		$this->assertEquals('‹‹ Back to Outbox', $this->crawler_text($crawler->filter('.back_threadlist')));
		$this->assertEquals(site_url().'/messages/folder/outbox', $crawler->filter('.back_threadlist')->attr('href'));
		$this->assertEquals(2, $crawler->filter('.select_all_button')->count());
		$this->assertEquals(2, $crawler->filter('.clear_all_button')->count());
		// marker of conversation + inbox + spam/ham button
		$this->assertEquals(0, $crawler->filter('.spam_button')->count());
		$this->assertEquals(0, $crawler->filter('.ham_button')->count());
		$this->assertEquals(0, $crawler->filter('.recover_button')->count());
		$this->assertEquals(0, $crawler->filter('.move_to_button')->count());
		$this->assertEquals(2, $crawler->filter('.global_delete')->count());
		$this->assertEquals('Delete', $this->crawler_text($crawler->filter('.global_delete')));
		$this->assertEquals(2, $crawler->filter('.refresh_button')->count());
		$this->assertEquals(0, $crawler->filter('.process_incoming_msgs_button')->count());
		$this->assertEquals(0, $crawler->filter('.resend_bulk')->count());
		$this->assertEquals($last_page, $this->crawler_text($crawler->filter('div.paging > span.current_page')));
		$this->assertEquals(0, $crawler->filter('div.paging > span.current_page')->nextAll()->count());

		$db->update('inbox', ['TextDecoded' => '%User 4%'], ['readed' => 'false']);
		$db->execute($this->CI);

		$this->assertValidHtml($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_conversation_GET_folder_sentitems_withOffset($db_engine)
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

		$db = &DBSetup::$current_setup[$db_engine];

		$id_user = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['conversation_sort' => 'asc']);
		$per_page = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['paging' => $per_page]);
		$db->update('inbox', ['TextDecoded' => '%User 4%'], ['readed' => 'false']);

		$this->request->addCallable($db->closure());

		$type = 'folder';
		$folder = 'sentitems';
		$number = '+33600000001';
		$id_folder = NULL;
		$url = implode('/', [$type, $folder, rawurlencode($number), $id_folder]);
		$offset = floor((DBSetup::messages_per_sender + DBSetup::messages_per_recipient) / $per_page) * $per_page;
		$last_page = ceil((DBSetup::messages_per_sender + DBSetup::messages_per_recipient) / $per_page);
		$output = $this->request('GET', 'messages/conversation/' . $url . '/' . $offset);
		$crawler = new Crawler($output);

		$this->assertEquals(0, $crawler->filter('#js_function')->count());
		$this->assertEquals(1, $crawler->filter('#js_conversation')->count());
		$this->assertEquals(15, $crawler->filter('div.move_to')->attr('data-id_folder'));
		$this->assertEquals(0, $crawler->filter('#renamefolderdialog')->count());
		$this->assertEquals(0, $crawler->filter('#deletefolderdialog')->count());
		$this->assertEquals(0, $crawler->filter('#deletealldialog')->count());
		$this->assertEquals(0, $crawler->filter('#two_column_container')->count());
		//$this->assertEquals($folder_name, $this->crawler_text($crawler->filter('span.folder_name')));
		$this->assertEquals(0, $crawler->filter('#delete-all-link')->count());
		$this->assertEquals(0, $crawler->filter('#renamefolder')->count());
		$this->assertEquals(0, $crawler->filter('#deletefolder')->count());
		$this->assertEquals(0, $crawler->filter('span.currenttab')->count());
		//$this->assertEquals('Inbox', $this->crawler_text($crawler->filter('span.currenttab')));
		//$this->assertEquals(site_url().'/messages/my_folder/sentitems/'.$id_folder, $crawler->filter('span.currenttab')->nextAll()->attr('href'));
		//$this->assertEquals('Sent items', $this->crawler_text($crawler->filter('span.currenttab')->nextAll()));

		$this->assertEquals(1, $crawler->filter('#message_holder')->count());
		$this->assertEquals(0, $crawler->filter('div.messagelist.unreaded')->count());
		$this->assertEquals(DBSetup::messages_per_sender + DBSetup::messages_per_recipient - $offset, $crawler->filter('div.messagelist_conversation')->count());

		// navigation.php (buttons)
		$this->assertEquals(2, $crawler->filter('.back_threadlist')->count());
		$this->assertEquals('‹‹ Back to Sent items', $this->crawler_text($crawler->filter('.back_threadlist')));
		$this->assertEquals(site_url().'/messages/folder/sentitems', $crawler->filter('.back_threadlist')->attr('href'));
		$this->assertEquals(2, $crawler->filter('.select_all_button')->count());
		$this->assertEquals(2, $crawler->filter('.clear_all_button')->count());
		// marker of conversation + inbox + spam/ham button
		$this->assertEquals(0, $crawler->filter('.spam_button')->count());
		$this->assertEquals(0, $crawler->filter('.ham_button')->count());
		$this->assertEquals(0, $crawler->filter('.recover_button')->count());
		$this->assertEquals(2, $crawler->filter('.move_to_button')->count());
		$this->assertEquals(2, $crawler->filter('.global_delete')->count());
		$this->assertEquals('Delete', $this->crawler_text($crawler->filter('.global_delete')));
		$this->assertEquals(2, $crawler->filter('.refresh_button')->count());
		$this->assertEquals(0, $crawler->filter('.process_incoming_msgs_button')->count());
		$this->assertEquals(2, $crawler->filter('.resend_bulk')->count());
		$this->assertEquals($last_page, $this->crawler_text($crawler->filter('div.paging > span.current_page')));
		$this->assertEquals(0, $crawler->filter('div.paging > span.current_page')->nextAll()->count());

		$db->update('inbox', ['TextDecoded' => '%User 4%'], ['readed' => 'false']);
		$db->execute($this->CI);

		$this->assertValidHtml($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_conversation_GET_folder_spam_withOffset($db_engine)
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

		$db = &DBSetup::$current_setup[$db_engine];

		$id_user = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['conversation_sort' => 'asc']);
		$per_page = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['paging' => $per_page]);
		$db->update('inbox', ['TextDecoded' => '%User 4%'], ['readed' => 'false']);

		$this->request->addCallable($db->closure());

		$type = 'my_folder';
		$folder = 'inbox';
		$number = '+33600000001';
		$id_folder = '6';
		$url = implode('/', [$type, $folder, rawurlencode($number), $id_folder]);
		$offset = floor((DBSetup::messages_per_sender) / $per_page) * $per_page;
		$last_page = ceil((DBSetup::messages_per_sender) / $per_page);
		$output = $this->request('GET', 'messages/conversation/' . $url . '/' . $offset);
		$crawler = new Crawler($output);

		$this->assertEquals(0, $crawler->filter('#js_function')->count());
		$this->assertEquals(1, $crawler->filter('#js_conversation')->count());
		$this->assertEquals(15, $crawler->filter('div.move_to')->attr('data-id_folder'));
		$this->assertEquals(0, $crawler->filter('#renamefolderdialog')->count());
		$this->assertEquals(0, $crawler->filter('#deletefolderdialog')->count());
		$this->assertEquals(0, $crawler->filter('#deletealldialog')->count());
		$this->assertEquals(0, $crawler->filter('#two_column_container')->count());
		//$this->assertEquals($folder_name, $this->crawler_text($crawler->filter('span.folder_name')));
		$this->assertEquals(0, $crawler->filter('#delete-all-link')->count());
		$this->assertEquals(0, $crawler->filter('#renamefolder')->count());
		$this->assertEquals(0, $crawler->filter('#deletefolder')->count());
		$this->assertEquals(0, $crawler->filter('span.currenttab')->count());
		//$this->assertEquals('Inbox', $this->crawler_text($crawler->filter('span.currenttab')));
		//$this->assertEquals(site_url().'/messages/my_folder/sentitems/'.$id_folder, $crawler->filter('span.currenttab')->nextAll()->attr('href'));
		//$this->assertEquals('Sent items', $this->crawler_text($crawler->filter('span.currenttab')->nextAll()));

		$this->assertEquals(1, $crawler->filter('#message_holder')->count());
		$this->assertEquals(0, $crawler->filter('div.messagelist.unreaded')->count());
		$this->assertEquals(DBSetup::messages_per_sender - $offset, $crawler->filter('div.messagelist_conversation')->count());

		// navigation.php (buttons)
		$this->assertEquals(2, $crawler->filter('.back_threadlist')->count());
		$this->assertEquals('‹‹ Back to Spam', $this->crawler_text($crawler->filter('.back_threadlist')));
		$this->assertEquals(site_url().'/messages/my_folder/inbox/6', $crawler->filter('.back_threadlist')->attr('href'));
		$this->assertEquals(2, $crawler->filter('.select_all_button')->count());
		$this->assertEquals(2, $crawler->filter('.clear_all_button')->count());
		// marker of conversation + inbox + spam/ham button
		$this->assertEquals(0, $crawler->filter('.spam_button')->count());
		$this->assertEquals(2, $crawler->filter('.ham_button')->count());
		$this->assertEquals(0, $crawler->filter('.recover_button')->count());
		$this->assertEquals(2, $crawler->filter('.move_to_button')->count());
		$this->assertEquals(2, $crawler->filter('.global_delete')->count());
		$this->assertEquals('Delete permanently', $this->crawler_text($crawler->filter('.global_delete')));
		$this->assertEquals(2, $crawler->filter('.refresh_button')->count());
		$this->assertEquals(2, $crawler->filter('.process_incoming_msgs_button')->count());
		$this->assertEquals(0, $crawler->filter('.resend_bulk')->count());
		$this->assertEquals($last_page, $this->crawler_text($crawler->filter('div.paging > span.current_page')));
		$this->assertEquals(0, $crawler->filter('div.paging > span.current_page')->nextAll()->count());

		$db->update('inbox', ['TextDecoded' => '%User '.$id_user.'%'], ['readed' => 'false']);
		$db->execute($this->CI);

		$this->assertValidHtml($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_conversation_GET_folder_trashInbox_withOffset($db_engine)
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

		$db = &DBSetup::$current_setup[$db_engine];

		$id_user = 2;
		$db->update('user_settings', ['id_user' => $id_user], ['conversation_sort' => 'asc']);
		$per_page = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['paging' => $per_page]);
		$db->update('inbox', ['TextDecoded' => '%User 4%'], ['readed' => 'false']);

		$this->request->addCallable($db->closure());

		$type = 'my_folder';
		$folder = 'inbox';
		$number = '+33600000001';
		$id_folder = '5';
		$url = implode('/', [$type, $folder, rawurlencode($number), $id_folder]);
		$offset = floor((DBSetup::messages_per_recipient + DBSetup::messages_per_sender) / $per_page) * $per_page;
		$last_page = ceil((DBSetup::messages_per_recipient + DBSetup::messages_per_sender) / $per_page);
		$output = $this->request('GET', 'messages/conversation/' . $url . '/' . $offset);
		$crawler = new Crawler($output);

		$this->assertEquals(0, $crawler->filter('#js_function')->count());
		$this->assertEquals(1, $crawler->filter('#js_conversation')->count());
		$this->assertEquals(11, $crawler->filter('div.move_to')->attr('data-id_folder'));
		$this->assertEquals(0, $crawler->filter('#renamefolderdialog')->count());
		$this->assertEquals(0, $crawler->filter('#deletefolderdialog')->count());
		$this->assertEquals(0, $crawler->filter('#deletealldialog')->count());
		$this->assertEquals(0, $crawler->filter('#two_column_container')->count());
		//$this->assertEquals($folder_name, $this->crawler_text($crawler->filter('span.folder_name')));
		$this->assertEquals(0, $crawler->filter('#delete-all-link')->count());
		$this->assertEquals(0, $crawler->filter('#renamefolder')->count());
		$this->assertEquals(0, $crawler->filter('#deletefolder')->count());
		$this->assertEquals(0, $crawler->filter('span.currenttab')->count());
		//$this->assertEquals('Inbox', $this->crawler_text($crawler->filter('span.currenttab')));
		//$this->assertEquals(site_url().'/messages/my_folder/sentitems/'.$id_folder, $crawler->filter('span.currenttab')->nextAll()->attr('href'));
		//$this->assertEquals('Sent items', $this->crawler_text($crawler->filter('span.currenttab')->nextAll()));

		$this->assertEquals(1, $crawler->filter('#message_holder')->count());
		$this->assertEquals(0, $crawler->filter('div.messagelist.unreaded')->count());
		$this->assertEquals(DBSetup::messages_per_recipient + DBSetup::messages_per_sender - $offset, $crawler->filter('div.messagelist_conversation')->count());

		// navigation.php (buttons)
		$this->assertEquals(2, $crawler->filter('.back_threadlist')->count());
		$this->assertEquals('‹‹ Back to Trash', $this->crawler_text($crawler->filter('.back_threadlist')));
		$this->assertEquals(site_url().'/messages/my_folder/inbox/5', $crawler->filter('.back_threadlist')->attr('href'));
		$this->assertEquals(2, $crawler->filter('.select_all_button')->count());
		$this->assertEquals(2, $crawler->filter('.clear_all_button')->count());
		// marker of conversation + inbox + spam/ham button
		$this->assertEquals(2, $crawler->filter('.spam_button')->count());
		$this->assertEquals(0, $crawler->filter('.ham_button')->count());
		$this->assertEquals(2, $crawler->filter('.recover_button')->count());
		$this->assertEquals(2, $crawler->filter('.move_to_button')->count());
		$this->assertEquals(2, $crawler->filter('.global_delete')->count());
		$this->assertEquals('Delete permanently', $this->crawler_text($crawler->filter('.global_delete')));
		$this->assertEquals(2, $crawler->filter('.refresh_button')->count());
		$this->assertEquals(2, $crawler->filter('.process_incoming_msgs_button')->count());
		$this->assertEquals(0, $crawler->filter('.resend_bulk')->count());
		$this->assertEquals($last_page, $this->crawler_text($crawler->filter('div.paging > span.current_page')));
		$this->assertEquals(0, $crawler->filter('div.paging > span.current_page')->nextAll()->count());

		$db->update('inbox', ['TextDecoded' => '%User '.$id_user.'%'], ['readed' => 'false']);
		$db->execute($this->CI);

		$this->assertValidHtml($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_conversation_GET_folder_trashSentitems_withOffset($db_engine)
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

		$db = &DBSetup::$current_setup[$db_engine];

		$id_user = 2;
		$db->update('user_settings', ['id_user' => $id_user], ['conversation_sort' => 'asc']);
		$per_page = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['paging' => $per_page]);
		$db->update('inbox', ['TextDecoded' => '%User 4%'], ['readed' => 'false']);

		$this->request->addCallable($db->closure());

		$type = 'my_folder';
		$folder = 'sentitems';
		$number = '+33600000001';
		$id_folder = '5';
		$url = implode('/', [$type, $folder, rawurlencode($number), $id_folder]);
		$offset = floor((DBSetup::messages_per_recipient + DBSetup::messages_per_sender) / $per_page) * $per_page;
		$last_page = ceil((DBSetup::messages_per_recipient + DBSetup::messages_per_sender) / $per_page);
		$output = $this->request('GET', 'messages/conversation/' . $url . '/' . $offset);
		$crawler = new Crawler($output);

		$this->assertEquals(0, $crawler->filter('#js_function')->count());
		$this->assertEquals(1, $crawler->filter('#js_conversation')->count());
		$this->assertEquals(11, $crawler->filter('div.move_to')->attr('data-id_folder'));
		$this->assertEquals(0, $crawler->filter('#renamefolderdialog')->count());
		$this->assertEquals(0, $crawler->filter('#deletefolderdialog')->count());
		$this->assertEquals(0, $crawler->filter('#deletealldialog')->count());
		$this->assertEquals(0, $crawler->filter('#two_column_container')->count());
		//$this->assertEquals($folder_name, $this->crawler_text($crawler->filter('span.folder_name')));
		$this->assertEquals(0, $crawler->filter('#delete-all-link')->count());
		$this->assertEquals(0, $crawler->filter('#renamefolder')->count());
		$this->assertEquals(0, $crawler->filter('#deletefolder')->count());
		$this->assertEquals(0, $crawler->filter('span.currenttab')->count());
		//$this->assertEquals('Inbox', $this->crawler_text($crawler->filter('span.currenttab')));
		//$this->assertEquals(site_url().'/messages/my_folder/sentitems/'.$id_folder, $crawler->filter('span.currenttab')->nextAll()->attr('href'));
		//$this->assertEquals('Sent items', $this->crawler_text($crawler->filter('span.currenttab')->nextAll()));

		$this->assertEquals(1, $crawler->filter('#message_holder')->count());
		$this->assertEquals(0, $crawler->filter('div.messagelist.unreaded')->count());
		$this->assertEquals(DBSetup::messages_per_recipient + DBSetup::messages_per_sender - $offset, $crawler->filter('div.messagelist_conversation')->count());

		// navigation.php (buttons)
		$this->assertEquals(2, $crawler->filter('.back_threadlist')->count());
		$this->assertEquals('‹‹ Back to Trash', $this->crawler_text($crawler->filter('.back_threadlist')));
		$this->assertEquals(site_url().'/messages/my_folder/sentitems/5', $crawler->filter('.back_threadlist')->attr('href'));
		$this->assertEquals(2, $crawler->filter('.select_all_button')->count());
		$this->assertEquals(2, $crawler->filter('.clear_all_button')->count());
		// marker of conversation + inbox + spam/ham button
		$this->assertEquals(0, $crawler->filter('.spam_button')->count());
		$this->assertEquals(0, $crawler->filter('.ham_button')->count());
		$this->assertEquals(2, $crawler->filter('.recover_button')->count());
		$this->assertEquals(2, $crawler->filter('.move_to_button')->count());
		$this->assertEquals(2, $crawler->filter('.global_delete')->count());
		$this->assertEquals('Delete permanently', $this->crawler_text($crawler->filter('.global_delete')));
		$this->assertEquals(2, $crawler->filter('.refresh_button')->count());
		$this->assertEquals(0, $crawler->filter('.process_incoming_msgs_button')->count());
		$this->assertEquals(2, $crawler->filter('.resend_bulk')->count());
		$this->assertEquals($last_page, $this->crawler_text($crawler->filter('div.paging > span.current_page')));
		$this->assertEquals(0, $crawler->filter('div.paging > span.current_page')->nextAll()->count());

		$db->update('inbox', ['TextDecoded' => '%User '.$id_user.'%'], ['readed' => 'false']);
		$db->execute($this->CI);

		$this->assertValidHtml($output);
	}


	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_conversation_GET_folder_my_folderInbox_withOffset($db_engine)
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

		$db = &DBSetup::$current_setup[$db_engine];

		$id_user = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['conversation_sort' => 'asc']);
		$per_page = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['paging' => $per_page]);
		$db->update('inbox', ['TextDecoded' => '%User 4%'], ['readed' => 'false']);

		$this->request->addCallable($db->closure());

		$type = 'my_folder';
		$folder = 'inbox';
		$number = '+33600000001';
		$id_folder = 15;
		$url = implode('/', [$type, $folder, rawurlencode($number), $id_folder]);
		$offset = floor((DBSetup::messages_per_recipient + DBSetup::messages_per_sender) / $per_page) * $per_page;
		$last_page = ceil((DBSetup::messages_per_recipient + DBSetup::messages_per_sender) / $per_page);
		$output = $this->request('GET', 'messages/conversation/' . $url . '/' . $offset);
		$crawler = new Crawler($output);

		$this->assertEquals(0, $crawler->filter('#js_function')->count());
		$this->assertEquals(1, $crawler->filter('#js_conversation')->count());
		$this->assertEquals(15, $crawler->filter('div.move_to')->attr('data-id_folder'));
		$this->assertEquals(0, $crawler->filter('#renamefolderdialog')->count());
		$this->assertEquals(0, $crawler->filter('#deletefolderdialog')->count());
		$this->assertEquals(0, $crawler->filter('#deletealldialog')->count());
		$this->assertEquals(0, $crawler->filter('#two_column_container')->count());
		//$this->assertEquals($folder_name, $this->crawler_text($crawler->filter('span.folder_name')));
		$this->assertEquals(0, $crawler->filter('#delete-all-link')->count());
		$this->assertEquals(0, $crawler->filter('#renamefolder')->count());
		$this->assertEquals(0, $crawler->filter('#deletefolder')->count());
		$this->assertEquals(0, $crawler->filter('span.currenttab')->count());
		//$this->assertEquals('Inbox', $this->crawler_text($crawler->filter('span.currenttab')));
		//$this->assertEquals(site_url().'/messages/my_folder/sentitems/'.$id_folder, $crawler->filter('span.currenttab')->nextAll()->attr('href'));
		//$this->assertEquals('Sent items', $this->crawler_text($crawler->filter('span.currenttab')->nextAll()));

		$this->assertEquals(1, $crawler->filter('#message_holder')->count());
		$this->assertEquals(0, $crawler->filter('div.messagelist.unreaded')->count());
		$this->assertEquals(DBSetup::messages_per_recipient + DBSetup::messages_per_sender - $offset, $crawler->filter('div.messagelist_conversation')->count());

		// navigation.php (buttons)
		$this->assertEquals(2, $crawler->filter('.back_threadlist')->count());
		$this->assertEquals('‹‹ Back to User3, Folder1 (id:15)', $this->crawler_text($crawler->filter('.back_threadlist')));
		$this->assertEquals(site_url().'/messages/my_folder/inbox/'.$id_folder, $crawler->filter('.back_threadlist')->attr('href'));
		$this->assertEquals(2, $crawler->filter('.select_all_button')->count());
		$this->assertEquals(2, $crawler->filter('.clear_all_button')->count());
		// marker of conversation + inbox + spam/ham button
		$this->assertEquals(2, $crawler->filter('.spam_button')->count());
		$this->assertEquals(0, $crawler->filter('.ham_button')->count());
		$this->assertEquals(0, $crawler->filter('.recover_button')->count());
		$this->assertEquals(2, $crawler->filter('.move_to_button')->count());
		$this->assertEquals(2, $crawler->filter('.global_delete')->count());
		$this->assertEquals('Delete', $this->crawler_text($crawler->filter('.global_delete')));
		$this->assertEquals(2, $crawler->filter('.refresh_button')->count());
		$this->assertEquals(2, $crawler->filter('.process_incoming_msgs_button')->count());
		$this->assertEquals(0, $crawler->filter('.resend_bulk')->count());
		$this->assertEquals($last_page, $this->crawler_text($crawler->filter('div.paging > span.current_page')));
		$this->assertEquals(0, $crawler->filter('div.paging > span.current_page')->nextAll()->count());

		$db->update('inbox', ['TextDecoded' => '%User '.$id_user.'%'], ['readed' => 'false']);
		$db->execute($this->CI);

		$this->assertValidHtml($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_conversation_GET_folder_my_folderSentitems_withOffset($db_engine)
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

		$db = &DBSetup::$current_setup[$db_engine];

		$id_user = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['conversation_sort' => 'asc']);
		$per_page = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['paging' => $per_page]);
		$db->update('inbox', ['TextDecoded' => '%User 4%'], ['readed' => 'false']);

		$this->request->addCallable($db->closure());

		$type = 'my_folder';
		$folder = 'sentitems';
		$number = '+33600000001';
		$id_folder = 15;
		$url = implode('/', [$type, $folder, rawurlencode($number), $id_folder]);
		$offset = floor((DBSetup::messages_per_recipient + DBSetup::messages_per_sender) / $per_page) * $per_page;
		$last_page = ceil((DBSetup::messages_per_recipient + DBSetup::messages_per_sender) / $per_page);
		$output = $this->request('GET', 'messages/conversation/' . $url . '/' . $offset);
		$crawler = new Crawler($output);

		$this->assertEquals(0, $crawler->filter('#js_function')->count());
		$this->assertEquals(1, $crawler->filter('#js_conversation')->count());
		$this->assertEquals(15, $crawler->filter('div.move_to')->attr('data-id_folder'));
		$this->assertEquals(0, $crawler->filter('#renamefolderdialog')->count());
		$this->assertEquals(0, $crawler->filter('#deletefolderdialog')->count());
		$this->assertEquals(0, $crawler->filter('#deletealldialog')->count());
		$this->assertEquals(0, $crawler->filter('#two_column_container')->count());
		//$this->assertEquals($folder_name, $this->crawler_text($crawler->filter('span.folder_name')));
		$this->assertEquals(0, $crawler->filter('#delete-all-link')->count());
		$this->assertEquals(0, $crawler->filter('#renamefolder')->count());
		$this->assertEquals(0, $crawler->filter('#deletefolder')->count());
		$this->assertEquals(0, $crawler->filter('span.currenttab')->count());
		//$this->assertEquals('Inbox', $this->crawler_text($crawler->filter('span.currenttab')));
		//$this->assertEquals(site_url().'/messages/my_folder/sentitems/'.$id_folder, $crawler->filter('span.currenttab')->nextAll()->attr('href'));
		//$this->assertEquals('Sent items', $this->crawler_text($crawler->filter('span.currenttab')->nextAll()));

		$this->assertEquals(1, $crawler->filter('#message_holder')->count());
		$this->assertEquals(0, $crawler->filter('div.messagelist.unreaded')->count());
		$this->assertEquals(DBSetup::messages_per_recipient + DBSetup::messages_per_sender - $offset, $crawler->filter('div.messagelist_conversation')->count());

		// navigation.php (buttons)
		$this->assertEquals(2, $crawler->filter('.back_threadlist')->count());
		$this->assertEquals('‹‹ Back to User3, Folder1 (id:15)', $this->crawler_text($crawler->filter('.back_threadlist')));
		$this->assertEquals(site_url().'/messages/my_folder/sentitems/'.$id_folder, $crawler->filter('.back_threadlist')->attr('href'));
		$this->assertEquals(2, $crawler->filter('.select_all_button')->count());
		$this->assertEquals(2, $crawler->filter('.clear_all_button')->count());
		// marker of conversation + inbox + spam/ham button
		$this->assertEquals(0, $crawler->filter('.spam_button')->count());
		$this->assertEquals(0, $crawler->filter('.ham_button')->count());
		$this->assertEquals(0, $crawler->filter('.recover_button')->count());
		$this->assertEquals(2, $crawler->filter('.move_to_button')->count());
		$this->assertEquals(2, $crawler->filter('.global_delete')->count());
		$this->assertEquals('Delete', $this->crawler_text($crawler->filter('.global_delete')));
		$this->assertEquals(2, $crawler->filter('.refresh_button')->count());
		$this->assertEquals(0, $crawler->filter('.process_incoming_msgs_button')->count());
		$this->assertEquals(2, $crawler->filter('.resend_bulk')->count());
		$this->assertEquals($last_page, $this->crawler_text($crawler->filter('div.paging > span.current_page')));
		$this->assertEquals(0, $crawler->filter('div.paging > span.current_page')->nextAll()->count());

		$db->update('inbox', ['TextDecoded' => '%User '.$id_user.'%'], ['readed' => 'false']);
		$db->execute($this->CI);

		$this->assertValidHtml($output);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	public function test_conversation_GET_folder_phonebook($db_engine)
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

		$db = &DBSetup::$current_setup[$db_engine];

		$id_user = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['conversation_sort' => 'asc']);
		$per_page = 4;
		$db->update('user_settings', ['id_user' => $id_user], ['paging' => $per_page]);
		$db->update('inbox', ['TextDecoded' => '%User 4%'], ['readed' => 'false']);

		$this->request->addCallable($db->closure());

		$type = 'folder';
		$folder = 'phonebook';
		$number = '+33600000001';
		$id_folder = NULL;
		$url = implode('/', [$type, $folder, rawurlencode($number), $id_folder]);
		$total_number_of_records = 2 * DBSetup::messages_per_recipient + 3 * DBSetup::messages_per_sender;
		$offset = floor($total_number_of_records / $per_page) * $per_page;
		$last_page = ceil($total_number_of_records / $per_page);
		$output = $this->request('GET', 'messages/conversation/' . $url . '/' . $offset);
		$crawler = new Crawler($output);

		$this->assertEquals(0, $crawler->filter('#js_function')->count());
		$this->assertEquals(1, $crawler->filter('#js_conversation')->count());
		$this->assertEquals(15, $crawler->filter('div.move_to')->attr('data-id_folder'));
		$this->assertEquals(0, $crawler->filter('#renamefolderdialog')->count());
		$this->assertEquals(0, $crawler->filter('#deletefolderdialog')->count());
		$this->assertEquals(0, $crawler->filter('#deletealldialog')->count());
		$this->assertEquals(0, $crawler->filter('#two_column_container')->count());
		//$this->assertEquals($folder_name, $this->crawler_text($crawler->filter('span.folder_name')));
		$this->assertEquals(0, $crawler->filter('#delete-all-link')->count());
		$this->assertEquals(0, $crawler->filter('#renamefolder')->count());
		$this->assertEquals(0, $crawler->filter('#deletefolder')->count());
		$this->assertEquals(0, $crawler->filter('span.currenttab')->count());
		//$this->assertEquals('Inbox', $this->crawler_text($crawler->filter('span.currenttab')));
		//$this->assertEquals(site_url().'/messages/my_folder/sentitems/'.$id_folder, $crawler->filter('span.currenttab')->nextAll()->attr('href'));
		//$this->assertEquals('Sent items', $this->crawler_text($crawler->filter('span.currenttab')->nextAll()));

		$this->assertEquals(1, $crawler->filter('#message_holder')->count());
		$this->assertEquals(0, $crawler->filter('div.messagelist.unreaded')->count());
		$this->assertEquals($total_number_of_records - $offset, $crawler->filter('div.messagelist_conversation')->count());

		// navigation.php (buttons)
		$this->assertEquals(2, $crawler->filter('.back_threadlist')->count());
		$this->assertEquals('‹‹ Back to Phonebook', $this->crawler_text($crawler->filter('.back_threadlist')));
		$this->assertEquals(site_url().'/messages/folder/phonebook', $crawler->filter('.back_threadlist')->attr('href'));
		$this->assertEquals(2, $crawler->filter('.select_all_button')->count());
		$this->assertEquals(2, $crawler->filter('.clear_all_button')->count());
		// marker of conversation + inbox + spam/ham button
		$this->assertEquals(0, $crawler->filter('.spam_button')->count());
		$this->assertEquals(0, $crawler->filter('.ham_button')->count());
		$this->assertEquals(0, $crawler->filter('.recover_button')->count());
		$this->assertEquals(2, $crawler->filter('.move_to_button')->count());
		$this->assertEquals(2, $crawler->filter('.global_delete')->count());
		$this->assertEquals('Delete', $this->crawler_text($crawler->filter('.global_delete')));
		$this->assertEquals(2, $crawler->filter('.refresh_button')->count());
		$this->assertEquals(0, $crawler->filter('.process_incoming_msgs_button')->count());
		$this->assertEquals(0, $crawler->filter('.resend_bulk')->count());
		$this->assertEquals($last_page, $this->crawler_text($crawler->filter('div.paging > span.current_page')));
		$this->assertEquals(0, $crawler->filter('div.paging > span.current_page')->nextAll()->count());

		$db->update('inbox', ['TextDecoded' => '%User '.$id_user.'%'], ['readed' => 'false']);
		$db->execute($this->CI);

		$this->assertValidHtml($output);
	}
}
