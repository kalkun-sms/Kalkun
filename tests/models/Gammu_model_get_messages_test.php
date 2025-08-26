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
require_once __DIR__.'/../testutils/KalkunTestCase.php';

use PHPUnit\Framework\Attributes\DataProvider;

class Gammu_model_get_messages_test extends KalkunTestCase {

	public function setUp() : void
	{
		DBSetup::setup_db_kalkun_testing2($this);
	}

	public function reloadCIwithEngine($db_engine)
	{
		DBSetup::$current_setup[$db_engine]->write_config_file_for_database();
		$this->resetInstance();
		$this->CI->load->database();
		$this->CI->load->model('gateway/Gammu_model');
		$this->obj = $this->CI->Gammu_model;
	}

	public static function database_Provider()
	{
		return DBSetup::$db_engines_to_test;
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_get_messages_invalid($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->CI->load->library('session');
		$_SESSION['loggedin'] = 'TRUE';
		$_SESSION['id_user'] = '1';
		$_SESSION['level'] = 'admin';
		$_SESSION['username'] = 'kalkun';

		$options = ['type' => 'invalid'];

		$this->expectException(CIPHPUnitTestShowErrorException::class);
		$this->_expectExceptionMessageMatches('/Invalid type request on class Gammu_model function get_messages/');

		$output = $this->obj->get_messages($options);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_get_messages_wrong_user($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->CI->load->library('session');
		$_SESSION['loggedin'] = 'TRUE';
		$_SESSION['id_user'] = '1';
		$_SESSION['level'] = 'admin';
		$_SESSION['username'] = 'kalkun';

		$options = [
			'type' => 'inbox',
			'number' => '+33600000001', // sending_error or phone number
			//'id_message' => '', // mutually exclusive with id_folder (used to move messages...)
			//'id_folder' => '', // mutually exclusive with id_message (set to the folder id for custom folders + spam + trash)
			//'search_string' => '', // used in Messages::search()
			//'readed' => '', // used by 'Kalkun::unread_count()'
			//'processed' => '', // only on type=inbox / used in Daemon::message_routine()
			'uid' => '1',
			//'trash' => '', // never set?
			//'order_by' => '', // SendingDateTime or ReceivingDateTime
			//'order_by_type' => '', // asc or desc
			//'limit' => '',
			//'offset' => '',
		];
		$output = $this->obj->get_messages($options);

		$this->assertEquals(0, $output->num_rows());
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_get_messages_notProcessed($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->CI->load->library('session');
		$_SESSION['loggedin'] = 'TRUE';
		$_SESSION['id_user'] = '1';
		$_SESSION['level'] = 'admin';
		$_SESSION['username'] = 'kalkun';

		$options = [
			//'type' => 'inbox',
			//'number' => '+33600000001', // sending_error or phone number
			//'id_message' => '', // mutually exclusive with id_folder (used to move messages...)
			//'id_folder' => '', // mutually exclusive with id_message (set to the folder id for custom folders + spam + trash)
			//'search_string' => '', // used in Messages::search()
			//'readed' => '', // used by 'Kalkun::unread_count()'
			'processed' => FALSE, // only on type=inbox / used in Daemon::message_routine()
			//'uid' => '1',
			//'trash' => '', // never set?
			//'order_by' => '', // SendingDateTime or ReceivingDateTime
			//'order_by_type' => '', // asc or desc
			//'limit' => '',
			//'offset' => '',
		];
		$output = $this->obj->get_messages($options);

		$nb_of_users_having_not_processed_msg = 2;

		$this->assertEquals($nb_of_users_having_not_processed_msg * DBSetup::messages_per_sender * DBSetup::senders, $output->num_rows());

		// Messages of user 2
		$number = 1;
		$id_offset = 250; // 250 for user1,
		$expected_id = $id_offset + 1;
		for ($message = 1; $message <= DBSetup::messages_per_sender * DBSetup::senders; $message++)
		{
			$this->assertEquals($expected_id, $output->row($message - 1)->ID);
			$this->assertEquals('+336' . sprintf('%08d', $number), $output->row($message - 1)->SenderNumber);
			if ($message % DBSetup::messages_per_sender === 0)
			{
				$number++;
			}
			$expected_id++;
		}
		// Messages of user 4 (multipart)
		$number = 1;
		$id_offset = 875;  // 250 for user1, 125 for user2 500 for user3
		$expected_id = $id_offset + 1;
		for ($message = DBSetup::messages_per_sender * DBSetup::senders + 1; $message <= $nb_of_users_having_not_processed_msg * DBSetup::messages_per_sender * DBSetup::senders; $message++)
		{
			$this->assertEquals($expected_id, $output->row($message - 1)->ID);
			$this->assertEquals('+336' . sprintf('%08d', $number), $output->row($message - 1)->SenderNumber);
			if ($message % DBSetup::messages_per_sender === 0)
			{
				$number++;
			}
			$expected_id += DBSetup::nb_of_parts_per_inbox_message;
		}
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_get_messages_inbox_no_limit_no_offset($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->CI->load->library('session');
		$_SESSION['loggedin'] = 'TRUE';
		$_SESSION['id_user'] = '2';
		$_SESSION['level'] = 'admin';
		$_SESSION['username'] = 'kalkun';

		$db = &DBSetup::$current_setup[$db_engine];
		$id_user = 2;
		$db->update('inbox', ['TextDecoded' => '%User '.$id_user.'%'], ['readed' => 'false']);
		$db->execute($this->CI);

		$options = [
			'type' => 'inbox',
			'number' => '+33600000001', // sending_error or phone number
			//'id_message' => '', // mutually exclusive with id_folder (used to move messages...)
			//'id_folder' => '', // mutually exclusive with id_message (set to the folder id for custom folders + spam + trash)
			//'search_string' => '', // used in Messages::search()
			//'readed' => '', // used by 'Kalkun::unread_count()'
			//'processed' => '', // only on type=inbox / used in Daemon::message_routine()
			'uid' => '2',
			//'trash' => '', // never set?
			//'order_by' => '', // SendingDateTime or ReceivingDateTime
			//'order_by_type' => '', // asc or desc
			//'limit' => '',
			//'offset' => '',
		];

		$output = $this->obj->get_messages($options);

		$this->assertEquals(DBSetup::messages_per_sender, $output->num_rows());

		$id_offset = 0;
		$expected_id = $id_offset + 1;
		for ($message = 1; $message <= DBSetup::messages_per_sender; $message++)
		{
			$this->assertEquals($expected_id, $output->row($message - 1)->ID);
			$this->assertEquals('+33600000001', $output->row($message - 1)->SenderNumber);
			$expected = DBSetup::text_replace_placeholder('User 2. Message '.$message.'. Sender 1. Folder Inbox', DBSetup::$text_mono_gsm);
			$this->assertEquals($expected, $output->row($message - 1)->TextDecoded);
			$expected_id++;
		}
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_get_messages_inbox_trash_no_limit_no_offset($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->CI->load->library('session');
		$_SESSION['loggedin'] = 'TRUE';
		$_SESSION['id_user'] = '2';
		$_SESSION['level'] = 'admin';
		$_SESSION['username'] = 'kalkun';

		$db = &DBSetup::$current_setup[$db_engine];
		$id_user = 2;
		$db->update('inbox', ['TextDecoded' => '%User '.$id_user.'%'], ['readed' => 'false']);
		$db->execute($this->CI);

		$options = [
			'type' => 'inbox',
			'number' => '+33600000001', // sending_error or phone number
			//'id_message' => '', // mutually exclusive with id_folder (used to move messages...)
			'id_folder' => '5', // mutually exclusive with id_message (set to the folder id for custom folders + spam + trash)
			//'search_string' => '', // used in Messages::search()
			//'readed' => '', // used by 'Kalkun::unread_count()'
			//'processed' => '', // only on type=inbox / used in Daemon::message_routine()
			'uid' => '2',
			//'trash' => '', // never set?
			//'order_by' => '', // SendingDateTime or ReceivingDateTime
			//'order_by_type' => '', // asc or desc
			//'limit' => '',
			//'offset' => '',
		];

		$output = $this->obj->get_messages($options);

		$this->assertEquals(DBSetup::messages_per_sender, $output->num_rows());

		$id_offset = 125;
		$expected_id = $id_offset + 1;
		for ($message = 1; $message <= DBSetup::messages_per_sender; $message++)
		{
			$this->assertEquals($expected_id, $output->row($message - 1)->ID);
			$this->assertEquals('+33600000001', $output->row($message - 1)->SenderNumber);
			$expected = DBSetup::text_replace_placeholder('User 2. Message '.$message.'. Sender 1. Folder Inbox/Trash', DBSetup::$text_mono_gsm);
			$this->assertEquals($expected, $output->row($message - 1)->TextDecoded);
			$expected_id++;
		}
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_get_messages_inbox_no_limit_no_offset_order_asc($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->CI->load->library('session');
		$_SESSION['loggedin'] = 'TRUE';
		$_SESSION['id_user'] = '2';
		$_SESSION['level'] = 'admin';
		$_SESSION['username'] = 'kalkun';

		$db = &DBSetup::$current_setup[$db_engine];
		$id_user = 2;
		$db->update('inbox', ['TextDecoded' => '%User '.$id_user.'%'], ['readed' => 'false']);
		$db->execute($this->CI);

		$options = [
			'type' => 'inbox',
			'number' => '+33600000001', // sending_error or phone number
			//'id_message' => '', // mutually exclusive with id_folder (used to move messages...)
			//'id_folder' => '', // mutually exclusive with id_message (set to the folder id for custom folders + spam + trash)
			//'search_string' => '', // used in Messages::search()
			//'readed' => '', // used by 'Kalkun::unread_count()'
			//'processed' => '', // only on type=inbox / used in Daemon::message_routine()
			'uid' => '2',
			//'trash' => '', // never set?
			'order_by' => 'ReceivingDateTime', // SendingDateTime or ReceivingDateTime
			'order_by_type' => 'asc', // asc or desc
			//'limit' => '',
			//'offset' => '',
		];
		$output = $this->obj->get_messages($options);

		$this->assertEquals(DBSetup::messages_per_sender, $output->num_rows());

		$id_offset = 0;
		//$expected_id = $id_offset + 1;
		for ($message = 1; $message <= DBSetup::messages_per_sender; $message++)
		{
			$expected_id = $id_offset + DBSetup::messages_per_sender - $message + 1;
			$this->assertEquals($expected_id, $output->row($message - 1)->ID);
			$this->assertEquals('+33600000001', $output->row($message - 1)->SenderNumber);
			$expected = DBSetup::text_replace_placeholder('User 2. Message '.(DBSetup::messages_per_sender - $message + 1).'. Sender 1. Folder Inbox', DBSetup::$text_mono_gsm);
			$this->assertEquals($expected, $output->row($message - 1)->TextDecoded);
		}
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_get_messages_inbox_multipart_no_limit_no_offset($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->CI->load->library('session');
		$_SESSION['loggedin'] = 'TRUE';
		$_SESSION['id_user'] = '4';
		$_SESSION['level'] = 'user';
		$_SESSION['username'] = 'user3';

		$db = &DBSetup::$current_setup[$db_engine];
		$id_user = 4;
		$db->update('inbox', ['TextDecoded' => '%User '.$id_user.'%'], ['readed' => 'false']);
		$db->execute($this->CI);

		$options = [
			'type' => 'inbox',
			'number' => '+33600000001', // sending_error or phone number
			//'id_message' => '', // mutually exclusive with id_folder (used to move messages...)
			//'id_folder' => '', // mutually exclusive with id_message (set to the folder id for custom folders + spam + trash)
			//'search_string' => '', // used in Messages::search()
			//'readed' => '', // used by 'Kalkun::unread_count()'
			//'processed' => '', // only on type=inbox / used in Daemon::message_routine()
			'uid' => '4',
			//'trash' => '', // never set?
			//'order_by' => '', // SendingDateTime or ReceivingDateTime
			//'order_by_type' => '', // asc or desc
			//'limit' => '',
			//'offset' => '',
		];
		$output = $this->obj->get_messages($options);

		$this->assertEquals(DBSetup::messages_per_sender, $output->num_rows());

		$id_offset = 375; // user1 250 user2 125
		for ($message = 1; $message <= DBSetup::messages_per_sender; $message++)
		{
			$expected_id = $id_offset + (($message - 1) * DBSetup::nb_of_parts_per_inbox_message) + 1;
			$this->assertEquals($expected_id, $output->row($message - 1)->ID);
			$this->assertEquals('+33600000001', $output->row($message - 1)->SenderNumber);
			$expected = DBSetup::text_replace_placeholder('User 4. Message '.$message.'. Sender 1. Folder Inbox', DBSetup::$text_multi_unicode);
			$this->assertEquals($expected, $output->row($message - 1)->TextDecoded);
		}
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_get_messages_inbox_limit_2_offset_2($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->CI->load->library('session');
		$_SESSION['loggedin'] = 'TRUE';
		$_SESSION['id_user'] = '2';
		$_SESSION['level'] = 'admin';
		$_SESSION['username'] = 'kalkun';

		$db = &DBSetup::$current_setup[$db_engine];
		$id_user = 2;
		$db->update('inbox', ['TextDecoded' => '%User '.$id_user.'%'], ['readed' => 'false']);
		$db->execute($this->CI);

		$limit = 2;
		$offset = 2;
		$options = [
			'type' => 'inbox',
			'number' => '+33600000001', // sending_error or phone number
			//'id_message' => '', // mutually exclusive with id_folder (used to move messages...)
			//'id_folder' => '', // mutually exclusive with id_message (set to the folder id for custom folders + spam + trash)
			//'search_string' => '', // used in Messages::search()
			//'readed' => '', // used by 'Kalkun::unread_count()'
			//'processed' => '', // only on type=inbox / used in Daemon::message_routine()
			'uid' => '2',
			//'trash' => '', // never set?
			//'order_by' => '', // SendingDateTime or ReceivingDateTime
			//'order_by_type' => '', // asc or desc
			'limit' => $limit,
			'offset' => $offset,
		];
		$output = $this->obj->get_messages($options);

		$this->assertEquals(min($limit, DBSetup::messages_per_sender - $offset), $output->num_rows());

		$id_offset = 0;
		for ($row = 0; $row < min($limit, DBSetup::messages_per_sender - $offset); $row++)
		{
			$message = $row + $offset + 1;
			$expected_id = $id_offset + $row + $offset + 1;
			$this->assertEquals($expected_id, $output->row($row)->ID);
			$this->assertEquals('+33600000001', $output->row($row)->SenderNumber);
			$expected = DBSetup::text_replace_placeholder('User 2. Message '.$message.'. Sender 1. Folder Inbox', DBSetup::$text_mono_gsm);
			$this->assertEquals($expected, $output->row($row)->TextDecoded);
		}
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_get_messages_inbox_folder11_no_limit_no_offset($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->CI->load->library('session');
		$_SESSION['loggedin'] = 'TRUE';
		$_SESSION['id_user'] = '2';
		$_SESSION['level'] = 'admin';
		$_SESSION['username'] = 'kalkun';

		$db = &DBSetup::$current_setup[$db_engine];
		$id_user = 2;
		$db->update('inbox', ['TextDecoded' => '%User '.$id_user.'%'], ['readed' => 'false']);
		$db->execute($this->CI);

		$id_folder = 11;
		$options = [
			'type' => 'inbox',
			'number' => '+33600000001', // sending_error or phone number
			//'id_message' => '', // mutually exclusive with id_folder (used to move messages...)
			'id_folder' => $id_folder, // mutually exclusive with id_message (set to the folder id for custom folders + spam + trash)
			//'search_string' => '', // used in Messages::search()
			//'readed' => '', // used by 'Kalkun::unread_count()'
			//'processed' => '', // only on type=inbox / used in Daemon::message_routine()
			'uid' => '2',
			//'trash' => '', // never set?
			//'order_by' => '', // SendingDateTime or ReceivingDateTime
			//'order_by_type' => '', // asc or desc
			//'limit' => '',
			//'offset' => '',
		];
		$output = $this->obj->get_messages($options);

		$this->assertEquals(DBSetup::messages_per_sender, $output->num_rows());

		$id_offset = 1375;
		for ($message = 1; $message <= DBSetup::messages_per_sender; $message++)
		{
			$expected_id = $id_offset + $message;
			$this->assertEquals($expected_id, $output->row($message - 1)->ID);
			$this->assertEquals('+33600000001', $output->row($message - 1)->SenderNumber);
			$expected = DBSetup::text_replace_placeholder('User 2. Message '.$message.'. Sender 1. Folder Inbox/#'.$id_folder, DBSetup::$text_mono_gsm);
			$this->assertEquals($expected, $output->row($message - 1)->TextDecoded);
		}
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_get_messages_outbox_no_limit_no_offset($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->CI->load->library('session');
		$_SESSION['loggedin'] = 'TRUE';
		$_SESSION['id_user'] = '2';
		$_SESSION['level'] = 'admin';
		$_SESSION['username'] = 'kalkun';

		$this->CI->load->database();
		$this->CI->load->model('gateway/Gammu_model');
		$this->obj = $this->CI->Gammu_model;

		$options = [
			'type' => 'outbox',
			'number' => '+33600000001', // sending_error or phone number
			//'id_message' => '', // mutually exclusive with id_folder (used to move messages...)
			//'id_folder' => '', // mutually exclusive with id_message (set to the folder id for custom folders + spam + trash)
			//'search_string' => '', // used in Messages::search()
			//'readed' => '', // used by 'Kalkun::unread_count()'
			//'processed' => '', // only on type=inbox / used in Daemon::message_routine()
			'uid' => '2',
			//'trash' => '', // never set?
			//'order_by' => '', // SendingDateTime or ReceivingDateTime
			//'order_by_type' => '', // asc or desc
			//'limit' => '',
			//'offset' => '',
		];
		$output = $this->obj->get_messages($options);

		$this->assertEquals(DBSetup::messages_per_recipient, $output->num_rows());

		$id_offset = 0;
		for ($message = 1; $message <= DBSetup::messages_per_recipient; $message++)
		{
			$expected_id = $id_offset + $message;
			$this->assertEquals($expected_id, $output->row($message - 1)->ID);
			$this->assertEquals('+33600000001', $output->row($message - 1)->DestinationNumber);
			$expected = DBSetup::text_replace_placeholder('User 2. Message '.$message.'. Recipient 1. Folder Outbox', DBSetup::$text_mono_gsm);
			$this->assertEquals($expected, $output->row($message - 1)->TextDecoded);
		}
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_get_messages_sentitems_no_limit_no_offset($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->CI->load->library('session');
		$_SESSION['loggedin'] = 'TRUE';
		$_SESSION['id_user'] = '2';
		$_SESSION['level'] = 'admin';
		$_SESSION['username'] = 'kalkun';

		$this->CI->load->database();
		$this->CI->load->model('gateway/Gammu_model');
		$this->obj = $this->CI->Gammu_model;

		$options = [
			'type' => 'sentitems',
			'number' => '+33600000001', // sending_error or phone number
			//'id_message' => '', // mutually exclusive with id_folder (used to move messages...)
			//'id_folder' => '', // mutually exclusive with id_message (set to the folder id for custom folders + spam + trash)
			//'search_string' => '', // used in Messages::search()
			//'readed' => '', // used by 'Kalkun::unread_count()'
			//'processed' => '', // only on type=inbox / used in Daemon::message_routine()
			'uid' => '2',
			//'trash' => '', // never set?
			//'order_by' => '', // SendingDateTime or ReceivingDateTime
			//'order_by_type' => '', // asc or desc
			//'limit' => '',
			//'offset' => '',
		];
		$output = $this->obj->get_messages($options);

		$this->assertEquals(DBSetup::messages_per_recipient, $output->num_rows());

		$id_offset = 125;
		for ($message = 1; $message <= DBSetup::messages_per_recipient; $message++)
		{
			$expected_id = $id_offset + $message;
			$this->assertEquals($expected_id, $output->row($message - 1)->ID);
			$this->assertEquals('+33600000001', $output->row($message - 1)->DestinationNumber);
			$expected = DBSetup::text_replace_placeholder('User 2. Message '.$message.'. Recipient 1. Folder Sentitems', DBSetup::$text_mono_gsm);
			$this->assertEquals($expected, $output->row($message - 1)->TextDecoded);
		}
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_get_messages_sentitems_multipart_no_limit_no_offset($db_engine)
	{
		$this->reloadCIwithEngine($db_engine);

		$this->CI->load->library('session');
		$_SESSION['loggedin'] = 'TRUE';
		$_SESSION['id_user'] = '4';
		$_SESSION['level'] = 'user';
		$_SESSION['username'] = 'user3';

		$options = [
			'type' => 'sentitems',
			'number' => '+33600000001', // sending_error or phone number
			//'id_message' => '', // mutually exclusive with id_folder (used to move messages...)
			//'id_folder' => '', // mutually exclusive with id_message (set to the folder id for custom folders + spam + trash)
			//'search_string' => '', // used in Messages::search()
			//'readed' => '', // used by 'Kalkun::unread_count()'
			//'processed' => '', // only on type=inbox / used in Daemon::message_routine()
			'uid' => '4',
			//'trash' => '', // never set?
			//'order_by' => '', // SendingDateTime or ReceivingDateTime
			//'order_by_type' => '', // asc or desc
			//'limit' => '',
			//'offset' => '',
		];
		$output = $this->obj->get_messages($options);

		$this->assertEquals(DBSetup::messages_per_sender, $output->num_rows());

		$id_offset = 750;
		for ($message = 1; $message <= DBSetup::messages_per_sender; $message++)
		{
			$expected_id = $id_offset + $message;
			$this->assertEquals($expected_id, $output->row($message - 1)->ID);
			$this->assertEquals('+33600000001', $output->row($message - 1)->DestinationNumber);
			// get_messages() returns the text for the 1st part only. The rest is managed in the view directly.
			$text = DBSetup::get_multi_parts(DBSetup::text_replace_placeholder('User 4. Message '.$message.'. Recipient 1. Folder Sentitems', DBSetup::$text_multi_gsm))[0];
			$this->assertEquals($text, $output->row($message - 1)->TextDecoded);
		}
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_get_messages_outbox($db_engine)
	{
		$this->markTestIncomplete();
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_get_messages_sentitems($db_engine)
	{
		$this->markTestIncomplete();
	}
}
