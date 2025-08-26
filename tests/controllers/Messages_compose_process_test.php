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
require_once __DIR__.'/../testutils/GammuSmsdConfigFile.php';
require_once __DIR__.'/../testutils/KalkunTestCase.php';

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;

class Messages_compose_process_test extends KalkunTestCase {

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

	public function test_compose_process_import_file()
	{
		$dbsetup = new DBSetup(['engine' => 'sqlite']);
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

		$filename = 'contact_sample.csv';
		$filepath = FCPATH . 'media/csv/' . $filename;

		$files = [
			'import_file' => [
				'name' => $filename,
				'type' => 'image/csv',
				'tmp_name' => $filepath,
			],
		];

		$this->request->setFiles($files);
		$output = $this->request('POST', 'messages/compose_process');

		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($output);

		$data_decoded = json_decode($output, TRUE);
		$this->assertEquals('Azhari Harahap,Budi Dani,Andi Raharja', $data_decoded['Name']);
		$this->assertEquals('123123123,23123123,4215456456', $data_decoded['Number']);
		$this->assertEquals(['Name', 'Number'], $data_decoded['Field']);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_compose_process_sendoption1_contact_group($db_engine)
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

		$delivery_report = 'no';
		$userid = 2;

		// Add user
		$dbsetup->insert('user', ['delivery_report' => $delivery_report, 'id_user' => $userid]);

		// Add contacts to phonebook and pbk group
		$recipients = 5; // 1st is defined by number, following is defined by group. There will be 4 contacts in the pbk group
		$dbsetup->insert('pbk_groups');
		for ($i = 1; $i < $recipients; $i++)
		{
			$dbsetup->insert('pbk', [
				'ID' => $i,
				'Name' => 'matching contact ' . $i,
				'Number' => '+336' . str_repeat($i, 8),
				'id_user' => $userid, ])
					->insert('user_group', [
						'id_group' => $i,
						'id_pbk' => $i,
						'id_pbk_groups' => 1,
						'id_user' => $userid, ]);
		}
		// Add a contact that is not in the group
		$dbsetup->insert('pbk', [
			'ID' => $recipients,
			'Name' => 'matching contact ' . $recipients,
			'Number' => '+336' . str_repeat($recipients, 8),
			'id_user' => $userid, ]);

		// Add a contact that is in another group
		$dbsetup->insert('pbk_groups', ['ID' => 2, 'Name' => 'Group2'])
				->insert('pbk', [
					'ID' => $recipients + 1,
					'Name' => 'matching contact ' . ($recipients + 1),
					'Number' => '+336' . str_repeat($recipients + 1, 8),
					'id_user' => $userid, ])
				->insert('user_group', [
					'id_group' => $recipients + 1,
					'id_pbk' => $recipients + 1,
					'id_pbk_groups' => 2,
					'id_user' => $userid, ]);

		$this->request->addCallable($dbsetup->closure());

		$post_data = [
			'sendoption' => 'sendoption1',
			// For sendoption1
			'personvalue' => '+33600000000:c,1:g', // for 'g' should be value of column id_pbk_groups of table "user_group"
			'msg_id' => '',
			// For sendoption3
			//'manualvalue' => '',
			// For sendoption4
			//'import_value_count' => '',
			//'Number' => '',
			// For reply
			//'reply_value' => '',
			// For resend
			//'resend_value' => '',
			//'resend_delete_original' => '',
			//'orig_msg_id' => '',
			// For pbk_groups
			//'id_pbk' => '',

			'senddateoption' => 'option1', // now
			// For senddateoption == option2
			'datevalue' => '', // if senddateoption == option2
			'hour' => '00',
			'minute' => '00',
			// For senddateoption == option3
			'delayhour' => '00',   // if senddateoption == option3
			'delayminute' => '00', // if senddateoption == option3

			'smstype' => 'normal', // normal, flash, waplink
			'message' => 'Message content',
			'validity' => '-1',
			//'ncpr' => '',
			'url' => '',

			'sms_loop' => '1', // Is set to 1 in form when SMS bomber is not enabled.
		];

		$output = $this->request('POST', 'messages/compose_process', $post_data);
		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($output);
		$data_decoded = json_decode($output, TRUE);
		$this->assertEquals('Copy of the message was placed in the outbox and is ready for delivery.', $data_decoded['msg']);
		$this->assertEquals('info', $data_decoded['type']);

		$result = $this->CI->db->get('outbox');
		$row = $result->row();
		$this->assertEquals($recipients, $result->num_rows());
		for ($i = 0; $i < $recipients; $i++)
		{
			$this->assertDateEqualsWithDelta(date('Y-m-d H:i:s'), $row->SendingDateTime);
			$this->assertEquals('', $row->Text);
			$this->assertEquals('+336' . str_repeat($i, 8), $row->DestinationNumber);
			$this->assertEquals('Default_No_Compression', $row->Coding);
			$this->assertEquals('', $row->UDH);
			$this->assertEquals(1, $row->Class);
			$this->assertEquals($post_data['message'], $row->TextDecoded);
			$this->assertEquals($i + 1, $row->ID);
			$this->assertEquals(FALSE, db_boolean_to_php_bool($db_engine, $row->MultiPart));
			$this->assertEquals($post_data['validity'], $row->RelativeValidity);
			$this->assertEquals('', $row->SenderID);
			$this->assertEquals($delivery_report, $row->DeliveryReport); //as per user configuration
			$this->assertEquals('🦃 Kalkun ' . $this->CI->config->item('kalkun_version'), $row->CreatorID);
			$row = $result->next_row();

			$result_user_outbox = $this->CI->db
					->where('id_outbox', ($i + 1))
					->get('user_outbox');
			$this->assertEquals($userid, $result_user_outbox->row()->id_user);
		}
		// Check that there isn't another message (eg. for a contact that doesn't belong to the group)
		$this->assertEmpty($result->next_row());
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_compose_process_sendoption1_user($db_engine)
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

		$delivery_report = 'no';
		$userid = 2;

		// Add users
		$dbsetup->insert('user', ['delivery_report' => $delivery_report, 'id_user' => $userid]);
		$dbsetup->insert('user', [
			'delivery_report' => $delivery_report,
			'id_user' => $userid + 1,
			'realname' => 'User number 2',
			'username' => 'user2',
			'phone_number' => '+33622222222',
		]);

		// Add existing inbox message
		$dbsetup->insert('inbox', ['Processed' => 'true']);
		$dbsetup->insert('user_inbox', ['id_user' => 2]);

		$this->request->addCallable($dbsetup->closure());

		$post_data = [
			'sendoption' => 'sendoption1',
			// For sendoption1
			'personvalue' => '1:u,'.($userid + 1).':u', // Send copy to user ID = 1 => Kalkun default & userID=3
			'msg_id' => '1',
			// For sendoption3
			//'manualvalue' => '',
			// For sendoption4
			//'import_value_count' => '',
			//'Number' => '',
			// For reply
			//'reply_value' => '',
			// For resend
			//'resend_value' => '',
			//'resend_delete_original' => '',
			//'orig_msg_id' => '',
			// For pbk_groups
			//'id_pbk' => '',

			'senddateoption' => 'option1', // now
			// For senddateoption == option2
			'datevalue' => '', // if senddateoption == option2
			'hour' => '00',
			'minute' => '00',
			// For senddateoption == option3
			'delayhour' => '00',   // if senddateoption == option3
			'delayminute' => '00', // if senddateoption == option3

			'smstype' => 'normal', // normal, flash, waplink
			'message' => 'Message content',
			'validity' => '-1',
			//'ncpr' => '',
			'url' => '',

			'sms_loop' => '1', // Is set to 1 in form when SMS bomber is not enabled.
		];

		$output = $this->request('POST', 'messages/compose_process', $post_data);
		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($output);
		$data_decoded = json_decode($output, TRUE);
		$this->assertEquals('Message delivered successfully to user inbox.', $data_decoded['msg']);
		$this->assertEquals('info', $data_decoded['type']);

		$result = $this->CI->db->get('inbox');
		$row = $result->row();
		$this->assertEquals(3, $result->num_rows());
		$message = DBSetup::$text_mono_gsm;
		for ($i = 0; $i < 3; $i++)
		{
			$this->assertEquals(date('Y-m-d').' 03:00:00', $row->ReceivingDateTime);
			$this->assertEquals('00760065007200790020006C006F006E00670020006D006500730073006100670065', $row->Text);
			$this->assertEquals('+33600000000', $row->SenderNumber);
			$this->assertEquals('Default_No_Compression', $row->Coding);
			$this->assertEquals('', $row->UDH);
			$this->assertEquals(-1, $row->Class);
			$this->assertEquals($message, $row->TextDecoded);
			$this->assertEquals($i + 1, $row->ID);
			$this->assertEquals('', $row->RecipientID);
			$this->assertEquals(TRUE, db_boolean_to_php_bool($db_engine, $row->Processed));
			$this->assertEquals(0, $row->Status);
			$row = $result->next_row();
		}

		$result_user_inbox = $this->CI->db
				->where('id_inbox', 2)
				->get('user_inbox');
		$this->assertEquals(1, $result_user_inbox->row()->id_user);
		$result_user_inbox = $this->CI->db
				->where('id_inbox', 3)
				->get('user_inbox');
		$this->assertEquals($userid + 1, $result_user_inbox->row()->id_user);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_compose_process_sendoption3($db_engine)
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

		$delivery_report = 'yes';
		$userid = 2;
		$this->request->addCallable($dbsetup->insert('user', ['delivery_report' => $delivery_report, 'id_user' => $userid])->closure());

		$post_data = [
			'sendoption' => 'sendoption3',
			// For sendoption1
			//'personvalue' => '',
			//'msg_id' => '',
			// For sendoption3
			'manualvalue' => '+33633333333, +33622222222',
			// For sendoption4
			//'import_value_count' => '',
			//'Number' => '',
			// For reply
			//'reply_value' => '',
			// For resend
			//'resend_value' => '',
			//'resend_delete_original' => '',
			//'orig_msg_id' => '',
			// For pbk_groups
			//'id_pbk' => '',

			'senddateoption' => 'option1', // now
			// For senddateoption == option2
			'datevalue' => '', // if senddateoption == option2
			'hour' => '00',
			'minute' => '00',
			// For senddateoption == option3
			'delayhour' => '00',   // if senddateoption == option3
			'delayminute' => '00', // if senddateoption == option3

			'smstype' => 'normal', // normal, flash, waplink
			'message' => 'Message content',
			'validity' => '-1',
			//'ncpr' => '',
			'url' => '',

			'sms_loop' => '1', // Is set to 1 in form when SMS bomber is not enabled.
		];

		$output = $this->request('POST', 'messages/compose_process', $post_data);
		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($output);
		$data_decoded = json_decode($output, TRUE);
		$this->assertEquals('Copy of the message was placed in the outbox and is ready for delivery.', $data_decoded['msg']);
		$this->assertEquals('info', $data_decoded['type']);

		$result = $this->CI->db->get('outbox');
		$row = $result->row();
		$numbers = array_map('trim', explode(',', $post_data['manualvalue']));
		for ($i = 0; $i < count($numbers); $i++)
		{
			$this->assertDateEqualsWithDelta(date('Y-m-d H:i:s'), $row->SendingDateTime);
			$this->assertEquals('', $row->Text);
			$this->assertEquals(phone_format_e164($numbers[$i]), $row->DestinationNumber);
			$this->assertEquals('Default_No_Compression', $row->Coding);
			$this->assertEquals('', $row->UDH);
			$this->assertEquals(1, $row->Class);
			$this->assertEquals($post_data['message'], $row->TextDecoded);
			$this->assertEquals($i + 1, $row->ID);
			$this->assertEquals(FALSE, db_boolean_to_php_bool($db_engine, $row->MultiPart));
			$this->assertEquals($post_data['validity'], $row->RelativeValidity);
			$this->assertEquals('', $row->SenderID);
			$this->assertEquals($delivery_report, $row->DeliveryReport); //as per user configuration
			$this->assertEquals('🦃 Kalkun ' . $this->CI->config->item('kalkun_version'), $row->CreatorID);
			$row = $result->next_row();

			$result_user_outbox = $this->CI->db
					->where('id_outbox', ($i + 1))
					->get('user_outbox');
			$this->assertEquals($userid, $result_user_outbox->row()->id_user);
		}
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_compose_process_sendoption4($db_engine)
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

		$delivery_report = 'yes';
		$userid = 2;
		$this->request->addCallable($dbsetup->insert('user', ['delivery_report' => $delivery_report, 'id_user' => $userid, 'country_code' => 'FR'])->closure());

		$post_data = [
			'sendoption' => 'sendoption4',
			// For sendoption1
			//'personvalue' => '',
			//'msg_id' => '',
			// For sendoption3
			//'manualvalue' => '',
			// For sendoption4
			'import_value_count' => '2',
			'Number' => '0699999999,0688888888',
			'Name' => 'recipient1,recipient2',
			// For reply
			//'reply_value' => '',
			// For resend
			//'resend_value' => '',
			//'resend_delete_original' => '',
			//'orig_msg_id' => '',
			// For pbk_groups
			//'id_pbk' => '',

			'senddateoption' => 'option1', // now
			// For senddateoption == option2
			'datevalue' => '', // if senddateoption == option2
			'hour' => '00',
			'minute' => '00',
			// For senddateoption == option3
			'delayhour' => '00',   // if senddateoption == option3
			'delayminute' => '00', // if senddateoption == option3

			'smstype' => 'normal', // normal, flash, waplink
			'message' => 'Message content',
			'validity' => '-1',
			//'ncpr' => '',
			'url' => '',

			'sms_loop' => '1', // Is set to 1 in form when SMS bomber is not enabled.
		];

		$output = $this->request('POST', 'messages/compose_process', $post_data);
		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($output);
		$data_decoded = json_decode($output, TRUE);
		$this->assertEquals('Copy of the message was placed in the outbox and is ready for delivery.', $data_decoded['msg']);
		$this->assertEquals('info', $data_decoded['type']);

		$result = $this->CI->db->get('outbox');
		$row = $result->row();
		$numbers = array_map('trim', explode(',', $post_data['Number']));
		for ($i = 0; $i < count($numbers); $i++)
		{
			$this->assertDateEqualsWithDelta(date('Y-m-d H:i:s'), $row->SendingDateTime);
			$this->assertEquals('', $row->Text);
			$this->assertEquals(phone_format_e164($numbers[$i]), $row->DestinationNumber);
			$this->assertEquals('Default_No_Compression', $row->Coding);
			$this->assertEquals('', $row->UDH);
			$this->assertEquals(1, $row->Class);
			$this->assertEquals($post_data['message'], $row->TextDecoded);
			$this->assertEquals($i + 1, $row->ID);
			$this->assertEquals(FALSE, db_boolean_to_php_bool($db_engine, $row->MultiPart));
			$this->assertEquals($post_data['validity'], $row->RelativeValidity);
			$this->assertEquals('', $row->SenderID);
			$this->assertEquals($delivery_report, $row->DeliveryReport); //as per user configuration
			$this->assertEquals('🦃 Kalkun ' . $this->CI->config->item('kalkun_version'), $row->CreatorID);
			$row = $result->next_row();

			$result_user_outbox = $this->CI->db
					->where('id_outbox', ($i + 1))
					->get('user_outbox');
			$this->assertEquals($userid, $result_user_outbox->row()->id_user);
		}
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_compose_process_reply($db_engine)
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

		$delivery_report = 'yes';
		$userid = 2;
		$this->request->addCallable($dbsetup->insert('user', ['delivery_report' => $delivery_report, 'id_user' => $userid, 'country_code' => 'FR'])->closure());

		$post_data = [
			'sendoption' => 'reply',
			// For sendoption1
			//'personvalue' => '',
			//'msg_id' => '',
			// For sendoption3
			//'manualvalue' => '',
			// For sendoption4
			//'import_value_count' => '',
			//'Number' => '',
			// For reply
			'reply_value' => '+33699999999',
			// For resend
			//'resend_value' => '',
			//'resend_delete_original' => '',
			//'orig_msg_id' => '',
			// For pbk_groups
			//'id_pbk' => '',

			'senddateoption' => 'option1', // now
			// For senddateoption == option2
			'datevalue' => '', // if senddateoption == option2
			'hour' => '00',
			'minute' => '00',
			// For senddateoption == option3
			'delayhour' => '00',   // if senddateoption == option3
			'delayminute' => '00', // if senddateoption == option3

			'smstype' => 'normal', // normal, flash, waplink
			'message' => 'Message content',
			'validity' => '-1',
			//'ncpr' => '',
			'url' => '',

			'sms_loop' => '1', // Is set to 1 in form when SMS bomber is not enabled.
		];

		$output = $this->request('POST', 'messages/compose_process', $post_data);
		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($output);
		$data_decoded = json_decode($output, TRUE);
		$this->assertEquals('Copy of the message was placed in the outbox and is ready for delivery.', $data_decoded['msg']);
		$this->assertEquals('info', $data_decoded['type']);

		$result = $this->CI->db->get('outbox');
		$row = $result->row();

		$this->assertDateEqualsWithDelta(date('Y-m-d H:i:s'), $row->SendingDateTime);
		$this->assertEquals('', $row->Text);
		$this->assertEquals($post_data['reply_value'], $row->DestinationNumber);
		$this->assertEquals('Default_No_Compression', $row->Coding);
		$this->assertEquals('', $row->UDH);
		$this->assertEquals(1, $row->Class);
		$this->assertEquals($post_data['message'], $row->TextDecoded);
		$this->assertEquals(1, $row->ID);
		$this->assertEquals(FALSE, db_boolean_to_php_bool($db_engine, $row->MultiPart));
		$this->assertEquals($post_data['validity'], $row->RelativeValidity);
		$this->assertEquals('', $row->SenderID);
		$this->assertEquals($delivery_report, $row->DeliveryReport); //as per user configuration
		$this->assertEquals('🦃 Kalkun ' . $this->CI->config->item('kalkun_version'), $row->CreatorID);

		$result_user_outbox = $this->CI->db
				->where('id_outbox', 1)
				->get('user_outbox');
		$this->assertEquals($userid, $result_user_outbox->row()->id_user);
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_compose_process_resend($db_engine)
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

		$delivery_report = 'yes';
		$userid = 2;
		$number = '+33699999999';
		$this->request->addCallable($dbsetup
				->insert('user', ['delivery_report' => $delivery_report, 'id_user' => $userid, 'country_code' => 'FR'])
				->insert('sentitems', ['DestinationNumber' => $number])
				->insert('user_sentitems', ['id_user' => $userid])
				->closure());

		$post_data = [
			'sendoption' => 'resend',
			// For sendoption1
			//'personvalue' => '',
			//'msg_id' => '',
			// For sendoption3
			//'manualvalue' => '',
			// For sendoption4
			//'import_value_count' => '',
			//'Number' => '',
			// For reply
			//'reply_value' => '',
			// For resend
			'resend_value' => $number,
			//'resend_delete_original' => 'on', // missing or 'on'
			'orig_msg_id' => '1',
			// For pbk_groups
			//'id_pbk' => '',

			'senddateoption' => 'option1', // now
			// For senddateoption == option2
			'datevalue' => '', // if senddateoption == option2
			'hour' => '00',
			'minute' => '00',
			// For senddateoption == option3
			'delayhour' => '00',   // if senddateoption == option3
			'delayminute' => '00', // if senddateoption == option3

			'smstype' => 'normal', // normal, flash, waplink
			'message' => 'Message content',
			'validity' => '-1',
			//'ncpr' => '',
			'url' => '',

			'sms_loop' => '1', // Is set to 1 in form when SMS bomber is not enabled.
		];

		$output = $this->request('POST', 'messages/compose_process', $post_data);
		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($output);
		$data_decoded = json_decode($output, TRUE);
		$this->assertEquals('Copy of the message was placed in the outbox and is ready for delivery.', $data_decoded['msg']);
		$this->assertEquals('info', $data_decoded['type']);

		$result = $this->CI->db->get('outbox');
		$row = $result->row();

		$this->assertDateEqualsWithDelta(date('Y-m-d H:i:s'), $row->SendingDateTime);
		$this->assertEquals('', $row->Text);
		$this->assertEquals($post_data['resend_value'], $row->DestinationNumber);
		$this->assertEquals('Default_No_Compression', $row->Coding);
		$this->assertEquals('', $row->UDH);
		$this->assertEquals(1, $row->Class);
		$this->assertEquals($post_data['message'], $row->TextDecoded);
		$this->assertEquals(1, $row->ID);
		$this->assertEquals(FALSE, db_boolean_to_php_bool($db_engine, $row->MultiPart));
		$this->assertEquals($post_data['validity'], $row->RelativeValidity);
		$this->assertEquals('', $row->SenderID);
		$this->assertEquals($delivery_report, $row->DeliveryReport); //as per user configuration
		$this->assertEquals('🦃 Kalkun ' . $this->CI->config->item('kalkun_version'), $row->CreatorID);

		$result_user_outbox = $this->CI->db
				->where('id_outbox', 1)
				->get('user_outbox');
		$this->assertEquals($userid, $result_user_outbox->row()->id_user);

		$result_sentitems = $this->CI->db
				->where('ID', 1)
				->get('sentitems');
		$this->assertEquals(1, $result_sentitems->num_rows());

		$result_user_sentitems = $this->CI->db
				->where('id_sentitems', 1)
				->where('id_user', $userid)
				->get('user_sentitems');
		$this->assertEquals(1, $result_user_sentitems->num_rows());
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_compose_process_resend_delete_orig($db_engine)
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

		$delivery_report = 'yes';
		$userid = 2;
		$number = '+33699999999';
		$this->request->addCallable($dbsetup
				->insert('user', ['delivery_report' => $delivery_report, 'id_user' => $userid, 'country_code' => 'FR'])
				->insert('sentitems', ['DestinationNumber' => $number])
				->insert('user_sentitems', ['id_user' => $userid])
				->closure());

		$post_data = [
			'sendoption' => 'resend',
			// For sendoption1
			//'personvalue' => '',
			//'msg_id' => '',
			// For sendoption3
			//'manualvalue' => '',
			// For sendoption4
			//'import_value_count' => '',
			//'Number' => '',
			// For reply
			//'reply_value' => '',
			// For resend
			'resend_value' => $number,
			'resend_delete_original' => 'on', // missing or 'on'
			'orig_msg_id' => '1',
			// For pbk_groups
			//'id_pbk' => '',

			'senddateoption' => 'option1', // now
			// For senddateoption == option2
			'datevalue' => '', // if senddateoption == option2
			'hour' => '00',
			'minute' => '00',
			// For senddateoption == option3
			'delayhour' => '00',   // if senddateoption == option3
			'delayminute' => '00', // if senddateoption == option3

			'smstype' => 'normal', // normal, flash, waplink
			'message' => 'Message content',
			'validity' => '-1',
			//'ncpr' => '',
			'url' => '',

			'sms_loop' => '1', // Is set to 1 in form when SMS bomber is not enabled.
		];

		$output = $this->request('POST', 'messages/compose_process', $post_data);
		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($output);
		$data_decoded = json_decode($output, TRUE);
		$this->assertEquals('Copy of the message was placed in the outbox and is ready for delivery.', $data_decoded['msg']);
		$this->assertEquals('info', $data_decoded['type']);

		$result = $this->CI->db->get('outbox');
		$row = $result->row();

		$this->assertDateEqualsWithDelta(date('Y-m-d H:i:s'), $row->SendingDateTime);
		$this->assertEquals('', $row->Text);
		$this->assertEquals($post_data['resend_value'], $row->DestinationNumber);
		$this->assertEquals('Default_No_Compression', $row->Coding);
		$this->assertEquals('', $row->UDH);
		$this->assertEquals(1, $row->Class);
		$this->assertEquals($post_data['message'], $row->TextDecoded);
		$this->assertEquals(1, $row->ID);
		$this->assertEquals(FALSE, db_boolean_to_php_bool($db_engine, $row->MultiPart));
		$this->assertEquals($post_data['validity'], $row->RelativeValidity);
		$this->assertEquals('', $row->SenderID);
		$this->assertEquals($delivery_report, $row->DeliveryReport); //as per user configuration
		$this->assertEquals('🦃 Kalkun ' . $this->CI->config->item('kalkun_version'), $row->CreatorID);

		$result_user_outbox = $this->CI->db
				->where('id_outbox', 1)
				->get('user_outbox');
		$this->assertEquals($userid, $result_user_outbox->row()->id_user);

		$result_sentitems = $this->CI->db
				->where('ID', 1)
				->get('sentitems');
		$this->assertEquals(0, $result_sentitems->num_rows());

		$result_user_sentitems = $this->CI->db
				->where('id_sentitems', 1)
				->where('id_user', $userid)
				->get('user_sentitems');
		$this->assertEquals(0, $result_user_sentitems->num_rows());
	}

	public static function compose_process_member_Provider()
	{
		return DBSetup::prepend_db_engine([['user'], ['admin']]);
	}
	/**
	 * @dataProvider compose_process_member_Provider
	 *
	 */
	#[DataProvider('compose_process_member_Provider')]
	public function test_compose_process_member($db_engine, $user_level)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$dbsetup->install_plugin($this, 'sms_member');

		$this->request->setCallablePreConstructor(
			function () use ($user_level) {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '2';
				$_SESSION['level'] = $user_level;
				$_SESSION['username'] = 'user1';
			}
		);

		$delivery_report = 'yes';
		$userid = 2;

		// Add user
		$dbsetup->insert('user', ['delivery_report' => $delivery_report, 'id_user' => $userid, 'level' => $user_level]);

		// Add contacts to phonebook and pbk group
		$recipients = 5; // 1st is defined by number, following is defined by group. There will be 4 contacts in the pbk group

		for ($i = 1; $i <= $recipients; $i++)
		{
			$dbsetup->insert('plugin_sms_member', ['phone_number' => '+336'.str_repeat($i, 8)]);
		}

		$this->request->addCallable($dbsetup->closure());

		$post_data = [
			'sendoption' => 'member',
			// For sendoption1
			//'personvalue' => '',
			//'msg_id' => '',
			// For sendoption3
			//'manualvalue' => '',
			// For sendoption4
			//'import_value_count' => '',
			//'Number' => '',
			// For reply
			//'reply_value' => '',
			// For resend
			//'resend_value' => '',
			//'resend_delete_original' => '',
			//'orig_msg_id' => '',
			// For pbk_groups
			//'id_pbk' => '1',

			'senddateoption' => 'option1', // now
			// For senddateoption == option2
			'datevalue' => '', // if senddateoption == option2
			'hour' => '00',
			'minute' => '00',
			// For senddateoption == option3
			'delayhour' => '00',   // if senddateoption == option3
			'delayminute' => '00', // if senddateoption == option3

			'smstype' => 'normal', // normal, flash, waplink
			'message' => 'Message content',
			'validity' => '-1',
			//'ncpr' => '',
			'url' => '',

			'sms_loop' => '1', // Is set to 1 in form when SMS bomber is not enabled.
		];

		$output = $this->request('POST', 'messages/compose_process', $post_data);

		if ($user_level !== 'admin')
		{
			$this->assertResponseCode(403);
			return;
		}

		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($output);
		$data_decoded = json_decode($output, TRUE);
		$this->assertEquals('Copy of the message was placed in the outbox and is ready for delivery.', $data_decoded['msg']);
		$this->assertEquals('info', $data_decoded['type']);

		$result = $this->CI->db->get('outbox');
		$row = $result->row();
		$this->assertEquals($recipients, $result->num_rows());
		for ($i = 1; $i <= $recipients; $i++)
		{
			$this->assertDateEqualsWithDelta(date('Y-m-d H:i:s'), $row->SendingDateTime);
			$this->assertEquals('', $row->Text);
			$this->assertEquals('+336' . str_repeat($i, 8), $row->DestinationNumber);
			$this->assertEquals('Default_No_Compression', $row->Coding);
			$this->assertEquals('', $row->UDH);
			$this->assertEquals(1, $row->Class);
			$this->assertEquals($post_data['message'], $row->TextDecoded);
			$this->assertEquals($i, $row->ID);
			$this->assertEquals(FALSE, db_boolean_to_php_bool($db_engine, $row->MultiPart));
			$this->assertEquals($post_data['validity'], $row->RelativeValidity);
			$this->assertEquals('', $row->SenderID);
			$this->assertEquals($delivery_report, $row->DeliveryReport); //as per user configuration
			$this->assertEquals('🦃 Kalkun ' . $this->CI->config->item('kalkun_version'), $row->CreatorID);
			$row = $result->next_row();

			$result_user_outbox = $this->CI->db
					->where('id_outbox', ($i))
					->get('user_outbox');
			$this->assertEquals($userid, $result_user_outbox->row()->id_user);
		}
		// Check that there isn't another message (eg. for a contact that doesn't belong to the group)
		$this->assertEmpty($result->next_row());

		Pluginss_test::reset_plugins_lib_static_members();
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_compose_process_pbk_groups($db_engine)
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

		$delivery_report = 'yes';
		$userid = 2;

		// Add user
		$dbsetup->insert('user', ['delivery_report' => $delivery_report, 'id_user' => $userid]);

		// Add contacts to phonebook and pbk group
		$recipients = 5; // 1st is defined by number, following is defined by group. There will be 4 contacts in the pbk group
		$dbsetup->insert('pbk_groups');
		for ($i = 1; $i <= $recipients; $i++)
		{
			$dbsetup->insert('pbk', [
				'ID' => $i,
				'Name' => 'matching contact ' . $i,
				'Number' => '+336' . str_repeat($i, 8),
				'id_user' => $userid, ])
					->insert('user_group', [
						'id_group' => $i,
						'id_pbk' => $i,
						'id_pbk_groups' => 1,
						'id_user' => $userid, ]);
		}
		// Add a contact that is not in the group
		$dbsetup->insert('pbk', [
			'ID' => $recipients + 1,
			'Name' => 'matching contact ' . ($recipients + 1),
			'Number' => '+336' . str_repeat($recipients + 1, 8),
			'id_user' => $userid, ]);

		// Add a contact that is in another group
		$dbsetup->insert('pbk_groups', ['ID' => 2, 'Name' => 'Group2'])
				->insert('pbk', [
					'ID' => $recipients + 2,
					'Name' => 'matching contact ' . ($recipients + 2),
					'Number' => '+336' . str_repeat($recipients + 2, 8),
					'id_user' => $userid, ])
				->insert('user_group', [
					'id_group' => $recipients + 2,
					'id_pbk' => $recipients + 2,
					'id_pbk_groups' => 2,
					'id_user' => $userid, ]);

		$this->request->addCallable($dbsetup->closure());

		$post_data = [
			'sendoption' => 'pbk_groups',
			// For sendoption1
			//'personvalue' => '',
			//'msg_id' => '',
			// For sendoption3
			//'manualvalue' => '',
			// For sendoption4
			//'import_value_count' => '',
			//'Number' => '',
			// For reply
			//'reply_value' => '',
			// For resend
			//'resend_value' => '',
			//'resend_delete_original' => '',
			//'orig_msg_id' => '',
			// For pbk_groups
			'id_pbk' => '1',

			'senddateoption' => 'option1', // now
			// For senddateoption == option2
			'datevalue' => '', // if senddateoption == option2
			'hour' => '00',
			'minute' => '00',
			// For senddateoption == option3
			'delayhour' => '00',   // if senddateoption == option3
			'delayminute' => '00', // if senddateoption == option3

			'smstype' => 'normal', // normal, flash, waplink
			'message' => 'Message content',
			'validity' => '-1',
			//'ncpr' => '',
			'url' => '',

			'sms_loop' => '1', // Is set to 1 in form when SMS bomber is not enabled.
		];

		$output = $this->request('POST', 'messages/compose_process', $post_data);
		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($output);
		$data_decoded = json_decode($output, TRUE);
		$this->assertEquals('Copy of the message was placed in the outbox and is ready for delivery.', $data_decoded['msg']);
		$this->assertEquals('info', $data_decoded['type']);

		$result = $this->CI->db->get('outbox');
		$row = $result->row();
		$this->assertEquals($recipients, $result->num_rows());
		for ($i = 1; $i <= $recipients; $i++)
		{
			$this->assertDateEqualsWithDelta(date('Y-m-d H:i:s'), $row->SendingDateTime);
			$this->assertEquals('', $row->Text);
			$this->assertEquals('+336' . str_repeat($i, 8), $row->DestinationNumber);
			$this->assertEquals('Default_No_Compression', $row->Coding);
			$this->assertEquals('', $row->UDH);
			$this->assertEquals(1, $row->Class);
			$this->assertEquals($post_data['message'], $row->TextDecoded);
			$this->assertEquals($i, $row->ID);
			$this->assertEquals(FALSE, db_boolean_to_php_bool($db_engine, $row->MultiPart));
			$this->assertEquals($post_data['validity'], $row->RelativeValidity);
			$this->assertEquals('', $row->SenderID);
			$this->assertEquals($delivery_report, $row->DeliveryReport); //as per user configuration
			$this->assertEquals('🦃 Kalkun ' . $this->CI->config->item('kalkun_version'), $row->CreatorID);
			$row = $result->next_row();

			$result_user_outbox = $this->CI->db
					->where('id_outbox', ($i))
					->get('user_outbox');
			$this->assertEquals($userid, $result_user_outbox->row()->id_user);
		}
		// Check that there isn't another message (eg. for a contact that doesn't belong to the group)
		$this->assertEmpty($result->next_row());
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_compose_process_all_contacts($db_engine)
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

		$delivery_report = 'yes';
		$userid = 2;

		// Add user
		$dbsetup->insert('user', ['delivery_report' => $delivery_report, 'id_user' => $userid]);

		// Add contacts to phonebook and pbk group
		$recipients = 5; // 1st is defined by number, following is defined by group. There will be 4 contacts in the pbk group
		$dbsetup->insert('pbk_groups');
		for ($i = 1; $i <= $recipients; $i++)
		{
			$dbsetup->insert('pbk', [
				'ID' => $i,
				'Name' => 'matching contact ' . $i,
				'Number' => '+336' . str_repeat($i, 8),
				'id_user' => $userid, ])
					->insert('user_group', [
						'id_group' => $i,
						'id_pbk' => $i,
						'id_pbk_groups' => 1,
						'id_user' => $userid, ]);
		}
		// Add a contact that is not in the group
		$dbsetup->insert('pbk', [
			'ID' => $recipients + 1,
			'Name' => 'matching contact ' . ($recipients + 1),
			'Number' => '+336' . str_repeat($recipients + 1, 8),
			'id_user' => $userid, ]);

		// Add a contact that is in another group
		$dbsetup->insert('pbk_groups', ['ID' => 2, 'Name' => 'Group2'])
				->insert('pbk', [
					'ID' => $recipients + 2,
					'Name' => 'matching contact ' . ($recipients + 2),
					'Number' => '+336' . str_repeat($recipients + 2, 8),
					'id_user' => $userid, ])
				->insert('user_group', [
					'id_group' => $recipients + 2,
					'id_pbk' => $recipients + 2,
					'id_pbk_groups' => 2,
					'id_user' => $userid, ]);

		$this->request->addCallable($dbsetup->closure());

		$post_data = [
			'sendoption' => 'all_contacts',
			// For sendoption1
			//'personvalue' => '',
			//'msg_id' => '',
			// For sendoption3
			//'manualvalue' => '',
			// For sendoption4
			//'import_value_count' => '',
			//'Number' => '',
			// For reply
			//'reply_value' => '',
			// For resend
			//'resend_value' => '',
			//'resend_delete_original' => '',
			//'orig_msg_id' => '',
			// For pbk_groups
			//'id_pbk' => '',

			'senddateoption' => 'option1', // now
			// For senddateoption == option2
			'datevalue' => '', // if senddateoption == option2
			'hour' => '00',
			'minute' => '00',
			// For senddateoption == option3
			'delayhour' => '00',   // if senddateoption == option3
			'delayminute' => '00', // if senddateoption == option3

			'smstype' => 'normal', // normal, flash, waplink
			'message' => 'Message content',
			'validity' => '-1',
			//'ncpr' => '',
			'url' => '',

			'sms_loop' => '1', // Is set to 1 in form when SMS bomber is not enabled.
		];

		$output = $this->request('POST', 'messages/compose_process', $post_data);
		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($output);
		$data_decoded = json_decode($output, TRUE);
		$this->assertEquals('Copy of the message was placed in the outbox and is ready for delivery.', $data_decoded['msg']);
		$this->assertEquals('info', $data_decoded['type']);

		$result = $this->CI->db->get('outbox');
		$row = $result->row();
		$this->assertEquals($recipients + 2, $result->num_rows());
		for ($i = 1; $i <= $recipients + 2; $i++)
		{
			$this->assertDateEqualsWithDelta(date('Y-m-d H:i:s'), $row->SendingDateTime);
			$this->assertEquals('', $row->Text);
			$this->assertEquals('+336' . str_repeat($i, 8), $row->DestinationNumber);
			$this->assertEquals('Default_No_Compression', $row->Coding);
			$this->assertEquals('', $row->UDH);
			$this->assertEquals(1, $row->Class);
			$this->assertEquals($post_data['message'], $row->TextDecoded);
			$this->assertEquals($i, $row->ID);
			$this->assertEquals(FALSE, db_boolean_to_php_bool($db_engine, $row->MultiPart));
			$this->assertEquals($post_data['validity'], $row->RelativeValidity);
			$this->assertEquals('', $row->SenderID);
			$this->assertEquals($delivery_report, $row->DeliveryReport); //as per user configuration
			$this->assertEquals('🦃 Kalkun ' . $this->CI->config->item('kalkun_version'), $row->CreatorID);
			$row = $result->next_row();

			$result_user_outbox = $this->CI->db
					->where('id_outbox', ($i))
					->get('user_outbox');
			$this->assertEquals($userid, $result_user_outbox->row()->id_user);
		}
		// Check that there isn't another message (eg. for a contact that doesn't belong to the group)
		$this->assertEmpty($result->next_row());
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_compose_process_send_at_date($db_engine)
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

		$delivery_report = 'yes';
		$userid = 2;
		$this->request->addCallable($dbsetup->insert('user', ['delivery_report' => $delivery_report, 'id_user' => $userid])->closure());

		$post_data = [
			'sendoption' => 'sendoption3',
			// For sendoption1
			//'personvalue' => '',
			//'msg_id' => '',
			// For sendoption3
			'manualvalue' => '+33633333333, +33622222222',
			// For sendoption4
			//'import_value_count' => '',
			//'Number' => '',
			// For reply
			//'reply_value' => '',
			// For resend
			//'resend_value' => '',
			//'resend_delete_original' => '',
			//'orig_msg_id' => '',
			// For pbk_groups
			//'id_pbk' => '',

			'senddateoption' => 'option2', // now
			// For senddateoption == option2
			'datevalue' => date('Y-m-d', strtotime('+1 day')), // if senddateoption == option2
			'hour' => '9',
			'minute' => '7',
			// For senddateoption == option3
			'delayhour' => '00',   // if senddateoption == option3
			'delayminute' => '00', // if senddateoption == option3

			'smstype' => 'normal', // normal, flash, waplink
			'message' => 'Message content',
			'validity' => '-1',
			//'ncpr' => '',
			'url' => '',

			'sms_loop' => '1', // Is set to 1 in form when SMS bomber is not enabled.
		];

		$output = $this->request('POST', 'messages/compose_process', $post_data);
		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($output);
		$data_decoded = json_decode($output, TRUE);
		$this->assertEquals('Copy of the message was placed in the outbox and is ready for delivery.', $data_decoded['msg']);
		$this->assertEquals('info', $data_decoded['type']);

		$result = $this->CI->db->get('outbox');
		$row = $result->row();
		$numbers = array_map('trim', explode(',', $post_data['manualvalue']));
		for ($i = 0; $i < count($numbers); $i++)
		{
			$date = date('Y-m-d H:i:s', strtotime($post_data['datevalue'] . ' ' . $post_data['hour'] . ':' . $post_data['minute']));
			$this->assertEquals($date, $row->SendingDateTime);
			$this->assertEquals('', $row->Text);
			$this->assertEquals(phone_format_e164($numbers[$i]), $row->DestinationNumber);
			$this->assertEquals('Default_No_Compression', $row->Coding);
			$this->assertEquals('', $row->UDH);
			$this->assertEquals(1, $row->Class);
			$this->assertEquals($post_data['message'], $row->TextDecoded);
			$this->assertEquals($i + 1, $row->ID);
			$this->assertEquals(FALSE, db_boolean_to_php_bool($db_engine, $row->MultiPart));
			$this->assertEquals($post_data['validity'], $row->RelativeValidity);
			$this->assertEquals('', $row->SenderID);
			$this->assertEquals($delivery_report, $row->DeliveryReport); //as per user configuration
			$this->assertEquals('🦃 Kalkun ' . $this->CI->config->item('kalkun_version'), $row->CreatorID);
			$row = $result->next_row();

			$result_user_outbox = $this->CI->db
					->where('id_outbox', ($i + 1))
					->get('user_outbox');
			$this->assertEquals($userid, $result_user_outbox->row()->id_user);
		}
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_compose_process_send_delay($db_engine)
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

		$delivery_report = 'yes';
		$userid = 2;
		$this->request->addCallable($dbsetup->insert('user', ['delivery_report' => $delivery_report, 'id_user' => $userid])->closure());

		$post_data = [
			'sendoption' => 'sendoption3',
			// For sendoption1
			//'personvalue' => '',
			//'msg_id' => '',
			// For sendoption3
			'manualvalue' => '+33633333333, +33622222222',
			// For sendoption4
			//'import_value_count' => '',
			//'Number' => '',
			// For reply
			//'reply_value' => '',
			// For resend
			//'resend_value' => '',
			//'resend_delete_original' => '',
			//'orig_msg_id' => '',
			// For pbk_groups
			//'id_pbk' => '',

			'senddateoption' => 'option3', // now
			// For senddateoption == option2
			'datevalue' => '', // if senddateoption == option2
			'hour' => '00',
			'minute' => '00',
			// For senddateoption == option3
			'delayhour' => '2',   // if senddateoption == option3
			'delayminute' => '9', // if senddateoption == option3

			'smstype' => 'normal', // normal, flash, waplink
			'message' => 'Message content',
			'validity' => '-1',
			//'ncpr' => '',
			'url' => '',

			'sms_loop' => '1', // Is set to 1 in form when SMS bomber is not enabled.
		];

		$output = $this->request('POST', 'messages/compose_process', $post_data);
		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($output);
		$data_decoded = json_decode($output, TRUE);
		$this->assertEquals('Copy of the message was placed in the outbox and is ready for delivery.', $data_decoded['msg']);
		$this->assertEquals('info', $data_decoded['type']);

		$result = $this->CI->db->get('outbox');
		$row = $result->row();
		$numbers = array_map('trim', explode(',', $post_data['manualvalue']));
		for ($i = 0; $i < count($numbers); $i++)
		{
			$date = date('Y-m-d H:i:s', strtotime('now +  ' . $post_data['delayhour'] . ' hours + ' . $post_data['delayminute'] . 'minutes'));
			$this->assertDateEqualsWithDelta($date, $row->SendingDateTime);
			$this->assertEquals('', $row->Text);
			$this->assertEquals(phone_format_e164($numbers[$i]), $row->DestinationNumber);
			$this->assertEquals('Default_No_Compression', $row->Coding);
			$this->assertEquals('', $row->UDH);
			$this->assertEquals(1, $row->Class);
			$this->assertEquals($post_data['message'], $row->TextDecoded);
			$this->assertEquals($i + 1, $row->ID);
			$this->assertEquals(FALSE, db_boolean_to_php_bool($db_engine, $row->MultiPart));
			$this->assertEquals($post_data['validity'], $row->RelativeValidity);
			$this->assertEquals('', $row->SenderID);
			$this->assertEquals($delivery_report, $row->DeliveryReport); //as per user configuration
			$this->assertEquals('🦃 Kalkun ' . $this->CI->config->item('kalkun_version'), $row->CreatorID);
			$row = $result->next_row();

			$result_user_outbox = $this->CI->db
					->where('id_outbox', ($i + 1))
					->get('user_outbox');
			$this->assertEquals($userid, $result_user_outbox->row()->id_user);
		}
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_compose_process_flash($db_engine)
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

		$delivery_report = 'yes';
		$userid = 2;
		$this->request->addCallable($dbsetup->insert('user', ['delivery_report' => $delivery_report, 'id_user' => $userid])->closure());

		$post_data = [
			'sendoption' => 'sendoption3',
			// For sendoption1
			//'personvalue' => '',
			//'msg_id' => '',
			// For sendoption3
			'manualvalue' => '+33633333333, +33622222222',
			// For sendoption4
			//'import_value_count' => '',
			//'Number' => '',
			// For reply
			//'reply_value' => '',
			// For resend
			//'resend_value' => '',
			//'resend_delete_original' => '',
			//'orig_msg_id' => '',
			// For pbk_groups
			//'id_pbk' => '',

			'senddateoption' => 'option1', // now
			// For senddateoption == option2
			'datevalue' => '', // if senddateoption == option2
			'hour' => '00',
			'minute' => '00',
			// For senddateoption == option3
			'delayhour' => '00',   // if senddateoption == option3
			'delayminute' => '00', // if senddateoption == option3

			'smstype' => 'flash', // normal, flash, waplink
			'message' => 'Message content',
			'validity' => '-1',
			//'ncpr' => '',
			'url' => '',

			'sms_loop' => '1', // Is set to 1 in form when SMS bomber is not enabled.
		];

		$output = $this->request('POST', 'messages/compose_process', $post_data);
		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($output);
		$data_decoded = json_decode($output, TRUE);
		$this->assertEquals('Copy of the message was placed in the outbox and is ready for delivery.', $data_decoded['msg']);
		$this->assertEquals('info', $data_decoded['type']);

		$result = $this->CI->db->get('outbox');
		$row = $result->row();
		$numbers = array_map('trim', explode(',', $post_data['manualvalue']));
		for ($i = 0; $i < count($numbers); $i++)
		{
			$this->assertDateEqualsWithDelta(date('Y-m-d H:i:s'), $row->SendingDateTime);
			$this->assertEquals('', $row->Text);
			$this->assertEquals(phone_format_e164($numbers[$i]), $row->DestinationNumber);
			$this->assertEquals('Default_No_Compression', $row->Coding);
			$this->assertEquals('', $row->UDH);
			$this->assertEquals(0, $row->Class);
			$this->assertEquals($post_data['message'], $row->TextDecoded);
			$this->assertEquals($i + 1, $row->ID);
			$this->assertEquals(FALSE, db_boolean_to_php_bool($db_engine, $row->MultiPart));
			$this->assertEquals($post_data['validity'], $row->RelativeValidity);
			$this->assertEquals('', $row->SenderID);
			$this->assertEquals($delivery_report, $row->DeliveryReport); //as per user configuration
			$this->assertEquals('🦃 Kalkun ' . $this->CI->config->item('kalkun_version'), $row->CreatorID);
			$row = $result->next_row();

			$result_user_outbox = $this->CI->db
					->where('id_outbox', ($i + 1))
					->get('user_outbox');
			$this->assertEquals($userid, $result_user_outbox->row()->id_user);
		}
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_compose_process_waplink($db_engine)
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

		$delivery_report = 'yes';
		$userid = 2;
		$this->request->addCallable($dbsetup->insert('user', ['delivery_report' => $delivery_report, 'id_user' => $userid])->closure());

		$post_data = [
			'sendoption' => 'sendoption3',
			// For sendoption1
			//'personvalue' => '',
			//'msg_id' => '',
			// For sendoption3
			'manualvalue' => '+33633333333, +33622222222',
			// For sendoption4
			//'import_value_count' => '',
			//'Number' => '',
			// For reply
			//'reply_value' => '',
			// For resend
			//'resend_value' => '',
			//'resend_delete_original' => '',
			//'orig_msg_id' => '',
			// For pbk_groups
			//'id_pbk' => '',

			'senddateoption' => 'option1', // now
			// For senddateoption == option2
			'datevalue' => '', // if senddateoption == option2
			'hour' => '00',
			'minute' => '00',
			// For senddateoption == option3
			'delayhour' => '00',   // if senddateoption == option3
			'delayminute' => '00', // if senddateoption == option3

			'smstype' => 'waplink', // normal, flash, waplink
			'message' => 'Waplink message',
			'validity' => '-1',
			//'ncpr' => '',
			'url' => 'https://www.github.com',

			'sms_loop' => '1', // Is set to 1 in form when SMS bomber is not enabled.
		];

		$output = $this->request('POST', 'messages/compose_process', $post_data);
		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($output);
		$data_decoded = json_decode($output, TRUE);
		$this->assertEquals('Copy of the message was placed in the outbox and is ready for delivery.', $data_decoded['msg']);
		$this->assertEquals('info', $data_decoded['type']);

		$result = $this->CI->db->get('outbox');
		$row = $result->row();
		$numbers = array_map('trim', explode(',', $post_data['manualvalue']));
		for ($i = 0; $i < count($numbers); $i++)
		{
			$this->assertDateEqualsWithDelta(date('Y-m-d H:i:s'), $row->SendingDateTime);
			$this->assertNotEmpty($row->Text);
			$this->assertEquals(phone_format_e164($numbers[$i]), $row->DestinationNumber);
			$this->assertEquals('8bit', $row->Coding);
			$this->assertNotEmpty($row->UDH);
			$this->assertEquals(1, $row->Class);
			$this->assertEquals('', $row->TextDecoded);
			$this->assertEquals($i + 1, $row->ID);
			$this->assertEquals(FALSE, db_boolean_to_php_bool($db_engine, $row->MultiPart));
			$this->assertEquals($post_data['validity'], $row->RelativeValidity);
			$this->assertEquals('', $row->SenderID);
			$this->assertEquals($delivery_report, $row->DeliveryReport); //as per user configuration
			$this->_assertStringContainsString('Gammu ', $row->CreatorID);
			$row = $result->next_row();

			$result_user_outbox = $this->CI->db
					->where('id_outbox', ($i + 1))
					->get('user_outbox');
			$this->assertEquals($userid, $result_user_outbox->row()->id_user);
		}
	}

	/**
	 * @dataProvider database_Provider
	 * Both annotations below are required to so that we can set
	 * DNDcheck function before it is declared by Kalkun.
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 */
	#[DataProvider('database_Provider')]
	#[RunInSeparateProcess]
	#[PreserveGlobalState(FALSE)]
	public function test_compose_process_ncpr($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$testSuiteFile = new TestSuiteFile(APPPATH . 'helpers/tester_helper.php');
		$content = '<?php';
		$content .= '
function DNDcheck($mobileno)
{
	if ($mobileno === \'+33633333333\')
		return TRUE;
	return FALSE;
}
';
		$testSuiteFile->write($content);

		$this->request->addCallable(function ($CI) {
			$CI->load->helper('tester');
		});

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

		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = '<?php '
					. '$config[\'ncpr\'] = \'TRUE\';';
		$configFile->write($content);
		$this->resetInstance();

		$delivery_report = 'yes';
		$userid = 2;
		$this->request->addCallable($dbsetup->insert('user', ['delivery_report' => $delivery_report, 'id_user' => $userid])->closure());

		$post_data = [
			'sendoption' => 'sendoption3',
			// For sendoption1
			//'personvalue' => '',
			//'msg_id' => '',
			// For sendoption3
			'manualvalue' => '+33611111111, +33633333333, +33622222222',
			// For sendoption4
			//'import_value_count' => '',
			//'Number' => '',
			// For reply
			//'reply_value' => '',
			// For resend
			//'resend_value' => '',
			//'resend_delete_original' => '',
			//'orig_msg_id' => '',
			// For pbk_groups
			//'id_pbk' => '',

			'senddateoption' => 'option1', // now
			// For senddateoption == option2
			'datevalue' => '', // if senddateoption == option2
			'hour' => '00',
			'minute' => '00',
			// For senddateoption == option3
			'delayhour' => '00',   // if senddateoption == option3
			'delayminute' => '00', // if senddateoption == option3

			'smstype' => 'normal', // normal, flash, waplink
			'message' => 'Message content',
			'validity' => '-1',
			'ncpr' => 'ncpr',
			'url' => '',

			'sms_loop' => '1', // Is set to 1 in form when SMS bomber is not enabled.
		];

		$output = $this->request('POST', 'messages/compose_process', $post_data);
		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($output);
		$data_decoded = json_decode($output, TRUE);
		$this->assertEquals('Copy of the message was placed in the outbox and is ready for delivery.', $data_decoded['msg']);
		$this->assertEquals('info', $data_decoded['type']);

		$result = $this->CI->db->where('DestinationNumber', '+33633333333')->get('outbox');
		$this->assertEquals(0, $result->num_rows());

		$result = $this->CI->db->get('outbox');
		$numbers = array_map('trim', explode(',', $post_data['manualvalue']));
		$this->assertEquals(count($numbers) - 1, $result->num_rows());
		$row = $result->row();
		$id = 0;
		for ($i = 0; $i < count($numbers); $i++)
		{
			$id = $id + 1;
			if (phone_format_e164($numbers[$i]) === '+33633333333')
			{
				// This number is in DND, so there won't be a message for it.
				$id = $id - 1;
				continue;
			}
			$this->assertDateEqualsWithDelta(date('Y-m-d H:i:s'), $row->SendingDateTime);
			$this->assertEquals('', $row->Text);
			$this->assertEquals(phone_format_e164($numbers[$i]), $row->DestinationNumber);
			$this->assertEquals('Default_No_Compression', $row->Coding);
			$this->assertEquals('', $row->UDH);
			$this->assertEquals(1, $row->Class);
			$this->assertEquals($post_data['message'], $row->TextDecoded);
			$this->assertEquals($id, $row->ID);
			$this->assertEquals(FALSE, db_boolean_to_php_bool($db_engine, $row->MultiPart));
			$this->assertEquals($post_data['validity'], $row->RelativeValidity);
			$this->assertEquals('', $row->SenderID);
			$this->assertEquals($delivery_report, $row->DeliveryReport); //as per user configuration
			$this->assertEquals('🦃 Kalkun ' . $this->CI->config->item('kalkun_version'), $row->CreatorID);
			$row = $result->next_row();

			$result_user_outbox = $this->CI->db
					->where('id_outbox', ($id))
					->get('user_outbox');
			$this->assertEquals($userid, $result_user_outbox->row()->id_user);
		}
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_compose_process_append_username($db_engine)
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

		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = '<?php '
					. '$config[\'append_username\'] = \'TRUE\';'
					. '$config[\'append_username_message\'] = \'Sender: @username\';';
		$configFile->write($content);

		$delivery_report = 'default';
		$userid = 2;
		$this->request->addCallable($dbsetup->insert('user', ['delivery_report' => $delivery_report, 'id_user' => $userid])->closure());

		$post_data = [
			'sendoption' => 'sendoption3',
			// For sendoption1
			//'personvalue' => '',
			//'msg_id' => '',
			// For sendoption3
			'manualvalue' => '+33633333333, +33622222222',
			// For sendoption4
			//'import_value_count' => '',
			//'Number' => '',
			// For reply
			//'reply_value' => '',
			// For resend
			//'resend_value' => '',
			//'resend_delete_original' => '',
			//'orig_msg_id' => '',
			// For pbk_groups
			//'id_pbk' => '',

			'senddateoption' => 'option1', // now
			// For senddateoption == option2
			'datevalue' => '', // if senddateoption == option2
			'hour' => '00',
			'minute' => '00',
			// For senddateoption == option3
			'delayhour' => '00',   // if senddateoption == option3
			'delayminute' => '00', // if senddateoption == option3

			'smstype' => 'normal', // normal, flash, waplink
			'message' => 'Message content',
			'validity' => '-1',
			//'ncpr' => '',
			'url' => '',

			'sms_loop' => '1', // Is set to 1 in form when SMS bomber is not enabled.
		];

		$output = $this->request('POST', 'messages/compose_process', $post_data);
		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($output);
		$data_decoded = json_decode($output, TRUE);
		$this->assertEquals('Copy of the message was placed in the outbox and is ready for delivery.', $data_decoded['msg']);
		$this->assertEquals('info', $data_decoded['type']);

		$result = $this->CI->db->get('outbox');
		$row = $result->row();
		$numbers = array_map('trim', explode(',', $post_data['manualvalue']));
		for ($i = 0; $i < count($numbers); $i++)
		{
			$this->assertDateEqualsWithDelta(date('Y-m-d H:i:s'), $row->SendingDateTime);
			$this->assertEquals('', $row->Text);
			$this->assertEquals(phone_format_e164($numbers[$i]), $row->DestinationNumber);
			$this->assertEquals('Default_No_Compression', $row->Coding);
			$this->assertEquals('', $row->UDH);
			$this->assertEquals(1, $row->Class);
			$this->assertEquals($post_data['message'] . "\n" .'Sender: @user1', $row->TextDecoded);
			$this->assertEquals($i + 1, $row->ID);
			$this->assertEquals(FALSE, db_boolean_to_php_bool($db_engine, $row->MultiPart));
			$this->assertEquals($post_data['validity'], $row->RelativeValidity);
			$this->assertEquals('', $row->SenderID);
			$this->assertEquals($delivery_report, $row->DeliveryReport); //as per user configuration
			$this->assertEquals('🦃 Kalkun ' . $this->CI->config->item('kalkun_version'), $row->CreatorID);
			$row = $result->next_row();

			$result_user_outbox = $this->CI->db
					->where('id_outbox', ($i + 1))
					->get('user_outbox');
			$this->assertEquals($userid, $result_user_outbox->row()->id_user);
		}
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_compose_process_append_username_resend($db_engine)
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

		$delivery_report = 'yes';
		$userid = 2;
		$number = '+33699999999';
		$this->request->addCallable($dbsetup
				->insert('user', ['delivery_report' => $delivery_report, 'id_user' => $userid, 'country_code' => 'FR'])
				->insert('sentitems', ['DestinationNumber' => $number])
				->insert('user_sentitems', ['id_user' => $userid])
				->closure());

		$post_data = [
			'sendoption' => 'resend',
			// For sendoption1
			//'personvalue' => '',
			//'msg_id' => '',
			// For sendoption3
			//'manualvalue' => '',
			// For sendoption4
			//'import_value_count' => '',
			//'Number' => '',
			// For reply
			//'reply_value' => '',
			// For resend
			'resend_value' => $number,
			//'resend_delete_original' => 'on', // missing or 'on'
			'orig_msg_id' => '1',
			// For pbk_groups
			//'id_pbk' => '',

			'senddateoption' => 'option1', // now
			// For senddateoption == option2
			'datevalue' => '', // if senddateoption == option2
			'hour' => '00',
			'minute' => '00',
			// For senddateoption == option3
			'delayhour' => '00',   // if senddateoption == option3
			'delayminute' => '00', // if senddateoption == option3

			'smstype' => 'normal', // normal, flash, waplink
			'message' => 'Message content',
			'validity' => '-1',
			//'ncpr' => '',
			'url' => '',

			'sms_loop' => '1', // Is set to 1 in form when SMS bomber is not enabled.
		];

		$output = $this->request('POST', 'messages/compose_process', $post_data);
		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($output);
		$data_decoded = json_decode($output, TRUE);
		$this->assertEquals('Copy of the message was placed in the outbox and is ready for delivery.', $data_decoded['msg']);
		$this->assertEquals('info', $data_decoded['type']);

		$result = $this->CI->db->get('outbox');
		$row = $result->row();

		$this->assertDateEqualsWithDelta(date('Y-m-d H:i:s'), $row->SendingDateTime);
		$this->assertEquals('', $row->Text);
		$this->assertEquals($post_data['resend_value'], $row->DestinationNumber);
		$this->assertEquals('Default_No_Compression', $row->Coding);
		$this->assertEquals('', $row->UDH);
		$this->assertEquals(1, $row->Class);
		$this->assertEquals($post_data['message'], $row->TextDecoded);
		$this->assertEquals(1, $row->ID);
		$this->assertEquals(FALSE, db_boolean_to_php_bool($db_engine, $row->MultiPart));
		$this->assertEquals($post_data['validity'], $row->RelativeValidity);
		$this->assertEquals('', $row->SenderID);
		$this->assertEquals($delivery_report, $row->DeliveryReport); //as per user configuration
		$this->assertEquals('🦃 Kalkun ' . $this->CI->config->item('kalkun_version'), $row->CreatorID);

		$result_user_outbox = $this->CI->db
				->where('id_outbox', 1)
				->get('user_outbox');
		$this->assertEquals($userid, $result_user_outbox->row()->id_user);

		$result_sentitems = $this->CI->db
				->where('ID', 1)
				->get('sentitems');
		$this->assertEquals(1, $result_sentitems->num_rows());

		$result_user_sentitems = $this->CI->db
				->where('id_sentitems', 1)
				->where('id_user', $userid)
				->get('user_sentitems');
		$this->assertEquals(1, $result_user_sentitems->num_rows());
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_compose_process_sms_advertise($db_engine)
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

		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = '<?php '
					. '$config[\'sms_advertise\'] = \'TRUE\';'
					. '$config[\'sms_advertise_message\'] = \'This is ads message\';';
		$configFile->write($content);

		$delivery_report = 'yes';
		$userid = 2;
		$this->request->addCallable($dbsetup->insert('user', ['delivery_report' => $delivery_report, 'id_user' => $userid])->closure());

		$post_data = [
			'sendoption' => 'sendoption3',
			// For sendoption1
			//'personvalue' => '',
			//'msg_id' => '',
			// For sendoption3
			'manualvalue' => '+33633333333, +33622222222',
			// For sendoption4
			//'import_value_count' => '',
			//'Number' => '',
			// For reply
			//'reply_value' => '',
			// For resend
			//'resend_value' => '',
			//'resend_delete_original' => '',
			//'orig_msg_id' => '',
			// For pbk_groups
			//'id_pbk' => '',

			'senddateoption' => 'option1', // now
			// For senddateoption == option2
			'datevalue' => '', // if senddateoption == option2
			'hour' => '00',
			'minute' => '00',
			// For senddateoption == option3
			'delayhour' => '00',   // if senddateoption == option3
			'delayminute' => '00', // if senddateoption == option3

			'smstype' => 'normal', // normal, flash, waplink
			'message' => 'Message content',
			'validity' => '-1',
			//'ncpr' => '',
			'url' => '',

			'sms_loop' => '1', // Is set to 1 in form when SMS bomber is not enabled.
		];

		$output = $this->request('POST', 'messages/compose_process', $post_data);
		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($output);
		$data_decoded = json_decode($output, TRUE);
		$this->assertEquals('Copy of the message was placed in the outbox and is ready for delivery.', $data_decoded['msg']);
		$this->assertEquals('info', $data_decoded['type']);

		$result = $this->CI->db->get('outbox');
		$row = $result->row();
		$numbers = array_map('trim', explode(',', $post_data['manualvalue']));
		for ($i = 0; $i < count($numbers); $i++)
		{
			$this->assertDateEqualsWithDelta(date('Y-m-d H:i:s'), $row->SendingDateTime);
			$this->assertEquals('', $row->Text);
			$this->assertEquals(phone_format_e164($numbers[$i]), $row->DestinationNumber);
			$this->assertEquals('Default_No_Compression', $row->Coding);
			$this->assertEquals('', $row->UDH);
			$this->assertEquals(1, $row->Class);
			$this->assertEquals($post_data['message'] . "\n" . 'This is ads message', $row->TextDecoded);
			$this->assertEquals($i + 1, $row->ID);
			$this->assertEquals(FALSE, db_boolean_to_php_bool($db_engine, $row->MultiPart));
			$this->assertEquals($post_data['validity'], $row->RelativeValidity);
			$this->assertEquals('', $row->SenderID);
			$this->assertEquals($delivery_report, $row->DeliveryReport); //as per user configuration
			$this->assertEquals('🦃 Kalkun ' . $this->CI->config->item('kalkun_version'), $row->CreatorID);
			$row = $result->next_row();

			$result_user_outbox = $this->CI->db
					->where('id_outbox', ($i + 1))
					->get('user_outbox');
			$this->assertEquals($userid, $result_user_outbox->row()->id_user);
		}
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_compose_process_sms_advertise_resend($db_engine)
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

		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = '<?php '
					. '$config[\'sms_advertise\'] = \'TRUE\';'
					. '$config[\'sms_advertise_message\'] = \'This is ads message\';';
		$configFile->write($content);

		$delivery_report = 'yes';
		$userid = 2;
		$number = '+33699999999';
		$this->request->addCallable($dbsetup
				->insert('user', ['delivery_report' => $delivery_report, 'id_user' => $userid, 'country_code' => 'FR'])
				->insert('sentitems', ['DestinationNumber' => $number])
				->insert('user_sentitems', ['id_user' => $userid])
				->closure());

		$post_data = [
			'sendoption' => 'resend',
			// For sendoption1
			//'personvalue' => '',
			//'msg_id' => '',
			// For sendoption3
			//'manualvalue' => '',
			// For sendoption4
			//'import_value_count' => '',
			//'Number' => '',
			// For reply
			//'reply_value' => '',
			// For resend
			'resend_value' => $number,
			//'resend_delete_original' => 'on', // missing or 'on'
			'orig_msg_id' => '1',
			// For pbk_groups
			//'id_pbk' => '',

			'senddateoption' => 'option1', // now
			// For senddateoption == option2
			'datevalue' => '', // if senddateoption == option2
			'hour' => '00',
			'minute' => '00',
			// For senddateoption == option3
			'delayhour' => '00',   // if senddateoption == option3
			'delayminute' => '00', // if senddateoption == option3

			'smstype' => 'normal', // normal, flash, waplink
			'message' => 'Message content',
			'validity' => '-1',
			//'ncpr' => '',
			'url' => '',

			'sms_loop' => '1', // Is set to 1 in form when SMS bomber is not enabled.
		];

		$output = $this->request('POST', 'messages/compose_process', $post_data);
		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($output);
		$data_decoded = json_decode($output, TRUE);
		$this->assertEquals('Copy of the message was placed in the outbox and is ready for delivery.', $data_decoded['msg']);
		$this->assertEquals('info', $data_decoded['type']);

		$result = $this->CI->db->get('outbox');
		$row = $result->row();

		$this->assertDateEqualsWithDelta(date('Y-m-d H:i:s'), $row->SendingDateTime);
		$this->assertEquals('', $row->Text);
		$this->assertEquals($post_data['resend_value'], $row->DestinationNumber);
		$this->assertEquals('Default_No_Compression', $row->Coding);
		$this->assertEquals('', $row->UDH);
		$this->assertEquals(1, $row->Class);
		$this->assertEquals($post_data['message'], $row->TextDecoded);
		$this->assertEquals(1, $row->ID);
		$this->assertEquals(FALSE, db_boolean_to_php_bool($db_engine, $row->MultiPart));
		$this->assertEquals($post_data['validity'], $row->RelativeValidity);
		$this->assertEquals('', $row->SenderID);
		$this->assertEquals($delivery_report, $row->DeliveryReport); //as per user configuration
		$this->assertEquals('🦃 Kalkun ' . $this->CI->config->item('kalkun_version'), $row->CreatorID);

		$result_user_outbox = $this->CI->db
				->where('id_outbox', 1)
				->get('user_outbox');
		$this->assertEquals($userid, $result_user_outbox->row()->id_user);

		$result_sentitems = $this->CI->db
				->where('ID', 1)
				->get('sentitems');
		$this->assertEquals(1, $result_sentitems->num_rows());

		$result_user_sentitems = $this->CI->db
				->where('id_sentitems', 1)
				->where('id_user', $userid)
				->get('user_sentitems');
		$this->assertEquals(1, $result_user_sentitems->num_rows());
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_compose_process_disable_outgoing($db_engine)
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

		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = '<?php '
					. '$config[\'disable_outgoing\'] = \'TRUE\';';
		$configFile->write($content);

		$delivery_report = 'yes';
		$userid = 2;
		$this->request->addCallable($dbsetup->insert('user', ['delivery_report' => $delivery_report, 'id_user' => $userid])->closure());

		$post_data = [
			'sendoption' => 'sendoption3',
			// For sendoption1
			//'personvalue' => '',
			//'msg_id' => '',
			// For sendoption3
			'manualvalue' => '+33633333333, +33622222222',
			// For sendoption4
			//'import_value_count' => '',
			//'Number' => '',
			// For reply
			//'reply_value' => '',
			// For resend
			//'resend_value' => '',
			//'resend_delete_original' => '',
			//'orig_msg_id' => '',
			// For pbk_groups
			//'id_pbk' => '',

			'senddateoption' => 'option1', // now
			// For senddateoption == option2
			'datevalue' => '', // if senddateoption == option2
			'hour' => '00',
			'minute' => '00',
			// For senddateoption == option3
			'delayhour' => '00',   // if senddateoption == option3
			'delayminute' => '00', // if senddateoption == option3

			'smstype' => 'normal', // normal, flash, waplink
			'message' => 'Message content',
			'validity' => '-1',
			//'ncpr' => '',
			'url' => '',

			'sms_loop' => '1', // Is set to 1 in form when SMS bomber is not enabled.
		];

		$output = $this->request('POST', 'messages/compose_process', $post_data);
		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($output);
		$data_decoded = json_decode($output, TRUE);
		$this->assertEquals('Outgoing SMS disabled.', $data_decoded['msg']);
		$this->assertEquals('error', $data_decoded['type']);

		$result = $this->CI->db->get('outbox');
		$this->assertEquals(0, $result->num_rows());
		$result = $this->CI->db->get('user_outbox');
		$this->assertEquals(0, $result->num_rows());
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_compose_process_hook_message_outgoing($db_engine)
	{
		// Used by blacklist_number & whitelist_number
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$dbsetup->install_plugin($this, 'blacklist_number');

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

		$delivery_report = 'yes';
		$userid = 2;

		// Add user
		$dbsetup->insert('user', ['delivery_report' => $delivery_report, 'id_user' => $userid, 'level' => 'user']);
		$dbsetup->insert('plugin_blacklist_number', ['phone_number' => '+33611122233']);
		$this->request->addCallable($dbsetup->closure());

		$post_data = [
			'sendoption' => 'sendoption3',
			// For sendoption1
			//'personvalue' => '',
			//'msg_id' => '',
			// For sendoption3
			'manualvalue' => '+33633333333, +33622222222, +33611122233',
			// For sendoption4
			//'import_value_count' => '',
			//'Number' => '',
			// For reply
			//'reply_value' => '',
			// For resend
			//'resend_value' => '',
			//'resend_delete_original' => '',
			//'orig_msg_id' => '',
			// For pbk_groups
			//'id_pbk' => '',

			'senddateoption' => 'option1', // now
			// For senddateoption == option2
			'datevalue' => '', // if senddateoption == option2
			'hour' => '00',
			'minute' => '00',
			// For senddateoption == option3
			'delayhour' => '00',   // if senddateoption == option3
			'delayminute' => '00', // if senddateoption == option3

			'smstype' => 'normal', // normal, flash, waplink
			'message' => 'Message content',
			'validity' => '-1',
			//'ncpr' => '',
			'url' => '',

			'sms_loop' => '1', // Is set to 1 in form when SMS bomber is not enabled.
		];

		$output = $this->request('POST', 'messages/compose_process', $post_data);
		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($output);
		$data_decoded = json_decode($output, TRUE);
		$this->assertEquals('Copy of the message was placed in the outbox and is ready for delivery.', $data_decoded['msg']);
		$this->assertEquals('info', $data_decoded['type']);

		$result = $this->CI->db->get('outbox');
		$row = $result->row();
		$numbers = array_map('trim', explode(',', $post_data['manualvalue']));
		// 3rd number should have been excluded by blacklist_number
		$this->assertEquals(2, $result->num_rows());
		for ($i = 0; $i < count($numbers) - 1; $i++)
		{
			$this->assertDateEqualsWithDelta(date('Y-m-d H:i:s'), $row->SendingDateTime);
			$this->assertEquals('', $row->Text);
			$this->assertEquals(phone_format_e164($numbers[$i]), $row->DestinationNumber);
			$this->assertEquals('Default_No_Compression', $row->Coding);
			$this->assertEquals('', $row->UDH);
			$this->assertEquals(1, $row->Class);
			$this->assertEquals($post_data['message'], $row->TextDecoded);
			$this->assertEquals($i + 1, $row->ID);
			$this->assertEquals(FALSE, db_boolean_to_php_bool($db_engine, $row->MultiPart));
			$this->assertEquals($post_data['validity'], $row->RelativeValidity);
			$this->assertEquals('', $row->SenderID);
			$this->assertEquals($delivery_report, $row->DeliveryReport); //as per user configuration
			$this->assertEquals('🦃 Kalkun ' . $this->CI->config->item('kalkun_version'), $row->CreatorID);
			$row = $result->next_row();

			$result_user_outbox = $this->CI->db
					->where('id_outbox', ($i + 1))
					->get('user_outbox');
			$this->assertEquals($userid, $result_user_outbox->row()->id_user);
		}

		Pluginss_test::reset_plugins_lib_static_members();
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_compose_process_hook_message_outgoing_all($db_engine)
	{
		// Used by sms_credit
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$dbsetup->install_plugin($this, 'sms_credit');

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

		$delivery_report = 'yes';
		$userid = 2;

		// Add user
		$dbsetup->insert('user', ['delivery_report' => $delivery_report, 'id_user' => $userid, 'level' => 'user'])
		->insert('plugin_sms_credit_template')
		->insert('plugin_sms_credit')
		->insert('sms_used');
		$this->request->addCallable($dbsetup->closure());

		$post_data = [
			'sendoption' => 'sendoption3',
			// For sendoption1
			//'personvalue' => '',
			//'msg_id' => '',
			// For sendoption3
			'manualvalue' => '+33633333333, +33622222222, +33611122233',
			// For sendoption4
			//'import_value_count' => '',
			//'Number' => '',
			// For reply
			//'reply_value' => '',
			// For resend
			//'resend_value' => '',
			//'resend_delete_original' => '',
			//'orig_msg_id' => '',
			// For pbk_groups
			//'id_pbk' => '',

			'senddateoption' => 'option1', // now
			// For senddateoption == option2
			'datevalue' => '', // if senddateoption == option2
			'hour' => '00',
			'minute' => '00',
			// For senddateoption == option3
			'delayhour' => '00',   // if senddateoption == option3
			'delayminute' => '00', // if senddateoption == option3

			'smstype' => 'normal', // normal, flash, waplink
			'message' => 'Message content',
			'validity' => '-1',
			//'ncpr' => '',
			'url' => '',

			'sms_loop' => '1', // Is set to 1 in form when SMS bomber is not enabled.
		];

		$output = $this->request('POST', 'messages/compose_process', $post_data);
		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($output);
		$data_decoded = json_decode($output, TRUE);
		$this->assertEquals('Sorry, your sms credit limit exceeded.', $data_decoded['msg']);
		$this->assertEquals('error', $data_decoded['type']);

		$result = $this->CI->db->get('outbox');
		$row = $result->row();
		$numbers = array_map('trim', explode(',', $post_data['manualvalue']));
		$this->assertEquals(0, $result->num_rows());

		Pluginss_test::reset_plugins_lib_static_members();
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_compose_process_hook_message_outgoing_dest_data($db_engine)
	{
		// Used by stop_manager
		// Used by sms_credit
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$dbsetup->install_plugin($this, 'stop_manager');

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

		$delivery_report = 'yes';
		$userid = 2;

		// Add user
		$dbsetup->insert('user', ['delivery_report' => $delivery_report, 'id_user' => $userid, 'level' => 'user'])
		->insert('plugin_stop_manager');
		$this->request->addCallable($dbsetup->closure());

		$post_data = [
			'sendoption' => 'sendoption3',
			// For sendoption1
			//'personvalue' => '',
			//'msg_id' => '',
			// For sendoption3
			'manualvalue' => '+33633333333, +33622222222, +33789789789',
			// For sendoption4
			//'import_value_count' => '',
			//'Number' => '',
			// For reply
			//'reply_value' => '',
			// For resend
			//'resend_value' => '',
			//'resend_delete_original' => '',
			//'orig_msg_id' => '',
			// For pbk_groups
			//'id_pbk' => '',

			'senddateoption' => 'option1', // now
			// For senddateoption == option2
			'datevalue' => '', // if senddateoption == option2
			'hour' => '00',
			'minute' => '00',
			// For senddateoption == option3
			'delayhour' => '00',   // if senddateoption == option3
			'delayminute' => '00', // if senddateoption == option3

			'smstype' => 'normal', // normal, flash, waplink
			'message' => 'Message content',
			'validity' => '-1',
			//'ncpr' => '',
			'url' => '',

			'sms_loop' => '1', // Is set to 1 in form when SMS bomber is not enabled.
		];

		$output = $this->request('POST', 'messages/compose_process', $post_data);
		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($output);
		$data_decoded = json_decode($output, TRUE);
		$this->assertEquals('Copy of the message was placed in the outbox and is ready for delivery.', $data_decoded['msg']);
		$this->assertEquals('info', $data_decoded['type']);

		$result = $this->CI->db->get('outbox');
		$row = $result->row();
		$numbers = array_map('trim', explode(',', $post_data['manualvalue']));
		$this->assertEquals(2, $result->num_rows());

		Pluginss_test::reset_plugins_lib_static_members();
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_compose_process_replace_fields($db_engine)
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

		$delivery_report = 'yes';
		$userid = 2;
		$this->request->addCallable($dbsetup->insert('user', ['delivery_report' => $delivery_report, 'id_user' => $userid, 'country_code' => 'FR'])->closure());

		$post_data = [
			'sendoption' => 'sendoption4',
			// For sendoption1
			//'personvalue' => '',
			//'msg_id' => '',
			// For sendoption3
			//'manualvalue' => '',
			// For sendoption4
			'import_value_count' => '3',
			'Number' => '+33699999999, +33688888888, +33677777777',
			'Field1' => 'recipient1 ,recip ient2, r3',
			'Field2' => ',€,',
			// For reply
			//'reply_value' => '',
			// For resend
			//'resend_value' => '',
			//'resend_delete_original' => '',
			//'orig_msg_id' => '',
			// For pbk_groups
			//'id_pbk' => '',

			'senddateoption' => 'option1', // now
			// For senddateoption == option2
			'datevalue' => '', // if senddateoption == option2
			'hour' => '00',
			'minute' => '00',
			// For senddateoption == option3
			'delayhour' => '00',   // if senddateoption == option3
			'delayminute' => '00', // if senddateoption == option3

			'smstype' => 'normal', // normal, flash, waplink
			'message' => 'Message content Number:[[Number]] and Field1: [[Field1]] Field2: [[Field2]], [[MissingField]].',
			'validity' => '-1',
			//'ncpr' => '',
			'url' => '',

			'sms_loop' => '1', // Is set to 1 in form when SMS bomber is not enabled.
		];

		$output = $this->request('POST', 'messages/compose_process', $post_data);
		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($output);
		$data_decoded = json_decode($output, TRUE);
		$this->assertEquals('Copy of the message was placed in the outbox and is ready for delivery.', $data_decoded['msg']);
		$this->assertEquals('info', $data_decoded['type']);

		$result = $this->CI->db->get('outbox');
		$row = $result->row();
		$numbers = array_map('trim', explode(',', $post_data['Number']));
		$field1 = explode(',', $post_data['Field1']);
		$field2 = explode(',', $post_data['Field2']);
		for ($i = 0; $i < count($numbers); $i++)
		{
			switch ($i)
			{
				case 0:
					$message = 'Message content Number:+33699999999 and Field1: recipient1  Field2: , [[MissingField]].';
					break;
				case 1:
					$message = 'Message content Number: +33688888888 and Field1: recip ient2 Field2: €, [[MissingField]].';
					break;
				case 2:
					$message = 'Message content Number: +33677777777 and Field1:  r3 Field2: , [[MissingField]].';
					break;
			}
			$this->assertDateEqualsWithDelta(date('Y-m-d H:i:s'), $row->SendingDateTime);
			$this->assertEquals('', $row->Text);
			$this->assertEquals(phone_format_e164($numbers[$i]), $row->DestinationNumber);
			$this->assertEquals('Default_No_Compression', $row->Coding);
			$this->assertEquals('', $row->UDH);
			$this->assertEquals(1, $row->Class);
			$this->assertEquals($message, $row->TextDecoded);
			$this->assertEquals($i + 1, $row->ID);
			$this->assertEquals(FALSE, db_boolean_to_php_bool($db_engine, $row->MultiPart));
			$this->assertEquals($post_data['validity'], $row->RelativeValidity);
			$this->assertEquals('', $row->SenderID);
			$this->assertEquals($delivery_report, $row->DeliveryReport); //as per user configuration
			$this->assertEquals('🦃 Kalkun ' . $this->CI->config->item('kalkun_version'), $row->CreatorID);
			$row = $result->next_row();

			$result_user_outbox = $this->CI->db
					->where('id_outbox', ($i + 1))
					->get('user_outbox');
			$this->assertEquals($userid, $result_user_outbox->row()->id_user);
		}
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_compose_process_nothing_to_send($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$dbsetup->install_plugin($this, 'blacklist_number');

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

		$delivery_report = 'yes';
		$userid = 2;

		// Add user
		$dbsetup->insert('user', ['delivery_report' => $delivery_report, 'id_user' => $userid, 'level' => 'user']);
		$dbsetup->insert('plugin_blacklist_number', ['phone_number' => '+33611122233']);
		$this->request->addCallable($dbsetup->closure());

		$post_data = [
			'sendoption' => 'sendoption3',
			// For sendoption1
			//'personvalue' => '',
			//'msg_id' => '',
			// For sendoption3
			'manualvalue' => '+33611122233',
			// For sendoption4
			//'import_value_count' => '',
			//'Number' => '',
			// For reply
			//'reply_value' => '',
			// For resend
			//'resend_value' => '',
			//'resend_delete_original' => '',
			//'orig_msg_id' => '',
			// For pbk_groups
			//'id_pbk' => '',

			'senddateoption' => 'option1', // now
			// For senddateoption == option2
			'datevalue' => '', // if senddateoption == option2
			'hour' => '00',
			'minute' => '00',
			// For senddateoption == option3
			'delayhour' => '00',   // if senddateoption == option3
			'delayminute' => '00', // if senddateoption == option3

			'smstype' => 'normal', // normal, flash, waplink
			'message' => 'Message content',
			'validity' => '-1',
			//'ncpr' => '',
			'url' => '',

			'sms_loop' => '1', // Is set to 1 in form when SMS bomber is not enabled.
		];

		$output = $this->request('POST', 'messages/compose_process', $post_data);
		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($output);
		$data_decoded = json_decode($output, TRUE);
		$this->assertEquals('No number found. SMS not sent.', $data_decoded['msg']);
		$this->assertEquals('error', $data_decoded['type']);

		$result = $this->CI->db->get('outbox');
		$this->assertEquals(0, $result->num_rows());

		$result = $this->CI->db->get('user_outbox');
		$this->assertEquals(0, $result->num_rows());

		Pluginss_test::reset_plugins_lib_static_members();
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_compose_process_sms_bomber_disabled($db_engine)
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

		$delivery_report = 'yes';
		$userid = 2;
		$this->request->addCallable($dbsetup->insert('user', ['delivery_report' => $delivery_report, 'id_user' => $userid])->closure());

		$post_data = [
			'sendoption' => 'sendoption3',
			// For sendoption1
			//'personvalue' => '',
			//'msg_id' => '',
			// For sendoption3
			'manualvalue' => '+33633333333, +33622222222',
			// For sendoption4
			//'import_value_count' => '',
			//'Number' => '',
			// For reply
			//'reply_value' => '',
			// For resend
			//'resend_value' => '',
			//'resend_delete_original' => '',
			//'orig_msg_id' => '',
			// For pbk_groups
			//'id_pbk' => '',

			'senddateoption' => 'option1', // now
			// For senddateoption == option2
			'datevalue' => '', // if senddateoption == option2
			'hour' => '00',
			'minute' => '00',
			// For senddateoption == option3
			'delayhour' => '00',   // if senddateoption == option3
			'delayminute' => '00', // if senddateoption == option3

			'smstype' => 'normal', // normal, flash, waplink
			'message' => 'Message content',
			'validity' => '-1',
			//'ncpr' => '',
			'url' => '',

			'sms_loop' => '5', // Is set to 1 in form when SMS bomber is not enabled.
		];

		$output = $this->request('POST', 'messages/compose_process', $post_data);
		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($output);
		$data_decoded = json_decode($output, TRUE);
		$this->assertEquals('Copy of the message was placed in the outbox and is ready for delivery.', $data_decoded['msg']);
		$this->assertEquals('info', $data_decoded['type']);

		$numbers = array_map('trim', explode(',', $post_data['manualvalue']));
		$result = $this->CI->db->get('outbox');
		$this->assertEquals(count($numbers), $result->num_rows());

		$result = $this->CI->db->get('user_outbox');
		$this->assertEquals(count($numbers), $result->num_rows());
		return;
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_compose_process_sms_bomber_enabled($db_engine)
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

		$delivery_report = 'yes';
		$userid = 2;
		$this->request->addCallable($dbsetup->insert('user', ['delivery_report' => $delivery_report, 'id_user' => $userid])->closure());

		$post_data = [
			'sendoption' => 'sendoption3',
			// For sendoption1
			//'personvalue' => '',
			//'msg_id' => '',
			// For sendoption3
			'manualvalue' => '+33633333333, +33622222222',
			// For sendoption4
			//'import_value_count' => '',
			//'Number' => '',
			// For reply
			//'reply_value' => '',
			// For resend
			//'resend_value' => '',
			//'resend_delete_original' => '',
			//'orig_msg_id' => '',
			// For pbk_groups
			//'id_pbk' => '',

			'senddateoption' => 'option1', // now
			// For senddateoption == option2
			'datevalue' => '', // if senddateoption == option2
			'hour' => '00',
			'minute' => '00',
			// For senddateoption == option3
			'delayhour' => '00',   // if senddateoption == option3
			'delayminute' => '00', // if senddateoption == option3

			'smstype' => 'normal', // normal, flash, waplink
			'message' => 'Message content',
			'validity' => '-1',
			//'ncpr' => '',
			'url' => '',

			'sms_loop' => '5', // Is set to 1 in form when SMS bomber is not enabled.
		];

		$output = $this->request('POST', 'messages/compose_process', $post_data);
		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($output);
		$data_decoded = json_decode($output, TRUE);
		$this->assertEquals('Copy of the message was placed in the outbox and is ready for delivery.', $data_decoded['msg']);
		$this->assertEquals('info', $data_decoded['type']);

		$numbers = array_map('trim', explode(',', $post_data['manualvalue']));
		$result = $this->CI->db->get('outbox');
		$this->assertEquals(count($numbers) * $post_data['sms_loop'], $result->num_rows());

		$result = $this->CI->db->get('user_outbox');
		$this->assertEquals(count($numbers) * $post_data['sms_loop'], $result->num_rows());
		return;
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_compose_process_max_sms_by_minute($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = '<?php '
					. '$config[\'max_sms_sent_by_minute\'] = 1;';
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

		$delivery_report = 'yes';
		$userid = 2;
		$this->request->addCallable($dbsetup->insert('user', ['delivery_report' => $delivery_report, 'id_user' => $userid])->closure());

		$post_data = [
			'sendoption' => 'sendoption3',
			// For sendoption1
			//'personvalue' => '',
			//'msg_id' => '',
			// For sendoption3
			'manualvalue' => '+33633333333, +33622222222, +33611111111',
			// For sendoption4
			//'import_value_count' => '',
			//'Number' => '',
			// For reply
			//'reply_value' => '',
			// For resend
			//'resend_value' => '',
			//'resend_delete_original' => '',
			//'orig_msg_id' => '',
			// For pbk_groups
			//'id_pbk' => '',

			'senddateoption' => 'option2', // now
			// For senddateoption == option2
			'datevalue' => date('Y-m-d', strtotime('+1 day')), // if senddateoption == option2
			'hour' => '9',
			'minute' => '7',
			// For senddateoption == option3
			'delayhour' => '00',   // if senddateoption == option3
			'delayminute' => '00', // if senddateoption == option3

			'smstype' => 'normal', // normal, flash, waplink
			'message' => 'Message content',
			'validity' => '-1',
			//'ncpr' => '',
			'url' => '',

			'sms_loop' => '1', // Is set to 1 in form when SMS bomber is not enabled.
		];

		$output = $this->request('POST', 'messages/compose_process', $post_data);
		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($output);
		$data_decoded = json_decode($output, TRUE);
		$this->assertEquals('Copy of the message was placed in the outbox and is ready for delivery.', $data_decoded['msg']);
		$this->assertEquals('info', $data_decoded['type']);

		$result = $this->CI->db->get('outbox');
		$row = $result->row();
		$numbers = array_map('trim', explode(',', $post_data['manualvalue']));
		for ($i = 0; $i < count($numbers); $i++)
		{
			$date = date('Y-m-d H:i:s', strtotime($post_data['datevalue'] . ' ' . $post_data['hour'] . ':' . $post_data['minute'] . ' + '.($i + 1).' minutes'));
			$this->assertEquals($date, $row->SendingDateTime);
			$this->assertEquals('', $row->Text);
			$this->assertEquals(phone_format_e164($numbers[$i]), $row->DestinationNumber);
			$this->assertEquals('Default_No_Compression', $row->Coding);
			$this->assertEquals('', $row->UDH);
			$this->assertEquals(1, $row->Class);
			$this->assertEquals($post_data['message'], $row->TextDecoded);
			$this->assertEquals($i + 1, $row->ID);
			$this->assertEquals(FALSE, db_boolean_to_php_bool($db_engine, $row->MultiPart));
			$this->assertEquals($post_data['validity'], $row->RelativeValidity);
			$this->assertEquals('', $row->SenderID);
			$this->assertEquals($delivery_report, $row->DeliveryReport); //as per user configuration
			$this->assertEquals('🦃 Kalkun ' . $this->CI->config->item('kalkun_version'), $row->CreatorID);
			$row = $result->next_row();

			$result_user_outbox = $this->CI->db
					->where('id_outbox', ($i + 1))
					->get('user_outbox');
			$this->assertEquals($userid, $result_user_outbox->row()->id_user);
		}
	}

	/**
	 * @dataProvider database_Provider
	 *
	 */
	#[DataProvider('database_Provider')]
	public function test_compose_process_multi_modem($db_engine)
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

		$configFile = new ConfigFile(APPPATH . 'config/testing/kalkun_settings.php');
		$content = '<?php '
					. '$config[\'multiple_modem_state\'] = TRUE;';
		$configFile->write($content);

		$delivery_report = 'yes';
		$userid = 2;
		$this->request->addCallable($dbsetup->insert('user', ['delivery_report' => $delivery_report, 'id_user' => $userid])->closure());

		$post_data = [
			'sendoption' => 'sendoption3',
			// For sendoption1
			//'personvalue' => '',
			//'msg_id' => '',
			// For sendoption3
			'manualvalue' => '+33633333333, +33622222222',
			// For sendoption4
			//'import_value_count' => '',
			//'Number' => '',
			// For reply
			//'reply_value' => '',
			// For resend
			//'resend_value' => '',
			//'resend_delete_original' => '',
			//'orig_msg_id' => '',
			// For pbk_groups
			//'id_pbk' => '',

			'senddateoption' => 'option1', // now
			// For senddateoption == option2
			'datevalue' => '', // if senddateoption == option2
			'hour' => '00',
			'minute' => '00',
			// For senddateoption == option3
			'delayhour' => '00',   // if senddateoption == option3
			'delayminute' => '00', // if senddateoption == option3

			'smstype' => 'normal', // normal, flash, waplink
			'message' => 'Message content',
			'validity' => '-1',
			//'ncpr' => '',
			'url' => '',

			'sms_loop' => '1', // Is set to 1 in form when SMS bomber is not enabled.
		];

		$output = $this->request('POST', 'messages/compose_process', $post_data);
		$this->assertResponseHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->assertJson($output);
		$data_decoded = json_decode($output, TRUE);
		$this->assertEquals('Copy of the message was placed in the outbox and is ready for delivery.', $data_decoded['msg']);
		$this->assertEquals('info', $data_decoded['type']);

		$result = $this->CI->db->get('outbox');
		$row = $result->row();
		$numbers = array_map('trim', explode(',', $post_data['manualvalue']));
		for ($i = 0; $i < count($numbers); $i++)
		{
			$this->assertDateEqualsWithDelta(date('Y-m-d H:i:s'), $row->SendingDateTime);
			$this->assertEquals('', $row->Text);
			$this->assertEquals(phone_format_e164($numbers[$i]), $row->DestinationNumber);
			$this->assertEquals('Default_No_Compression', $row->Coding);
			$this->assertEquals('', $row->UDH);
			$this->assertEquals(1, $row->Class);
			$this->assertEquals($post_data['message'], $row->TextDecoded);
			$this->assertEquals($i + 1, $row->ID);
			$this->assertEquals(FALSE, db_boolean_to_php_bool($db_engine, $row->MultiPart));
			$this->assertEquals($post_data['validity'], $row->RelativeValidity);
			$this->assertEquals('sierra', $row->SenderID);
			$this->assertEquals($delivery_report, $row->DeliveryReport); //as per user configuration
			$this->assertEquals('🦃 Kalkun ' . $this->CI->config->item('kalkun_version'), $row->CreatorID);
			$row = $result->next_row();

			$result_user_outbox = $this->CI->db
					->where('id_outbox', ($i + 1))
					->get('user_outbox');
			$this->assertEquals($userid, $result_user_outbox->row()->id_user);
		}
	}
}
