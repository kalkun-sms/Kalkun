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

class Gammu_model_get_conversation_test extends KalkunTestCase {

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

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_get_conversation_invalid_type($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$this->resetInstance();

		$this->CI->load->library('session');
		$_SESSION['loggedin'] = 'TRUE';
		$_SESSION['id_user'] = '1';
		$_SESSION['level'] = 'admin';
		$_SESSION['username'] = 'kalkun';

		$this->CI->load->database();
		$this->CI->load->model('gateway/Gammu_model');
		$this->obj = $this->CI->Gammu_model;

		$i = 1;
		$senders = 2;
		$messages_per_sender = 5;
		for ($sender = 1; $sender <= $senders; $sender++)
		{
			for ($i; $i <= $messages_per_sender * $sender; $i++)
			{
				$msg_number = ($i - 1) % $messages_per_sender + 1;
				$dbsetup->insert(
					'inbox',
					['ReceivingDateTime' => date('Y-m-d H:i:s', strtotime('-' . $i . ' minutes')),
						'TextDecoded' => 'message ' . $msg_number . ' from sender ' . $sender,
						'ID' => $i,
						'id_folder' => 1,
						'id_user' => 1,
						'SenderNumber' => '+336' . sprintf('%08d', $sender),
					]
				);
				$dbsetup->insert(
					'user_inbox',
					['id_user' => 1,
						'id_inbox' => $i, // same as ID in inbox
						'trash' => 0,
					]
				);
			}
		}
		$dbsetup->execute($this->CI);

		$options = [
			'type' => 'invalid',
		];
		$this->expectException(CIPHPUnitTestShowErrorException::class);

		$this->_expectExceptionMessageMatches('/Invalid type request on class Gammu_model function get_conversation/');

		$output = $this->obj->get_conversation($options);
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_get_conversation_wrong_user($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$this->resetInstance();

		$this->CI->load->library('session');
		$_SESSION['loggedin'] = 'TRUE';
		$_SESSION['id_user'] = '1';
		$_SESSION['level'] = 'admin';
		$_SESSION['username'] = 'kalkun';

		$this->CI->load->database();
		$this->CI->load->model('gateway/Gammu_model');
		$this->obj = $this->CI->Gammu_model;

		$i = 1;
		$senders = 2;
		$messages_per_sender = 5;
		for ($sender = 1; $sender <= $senders; $sender++)
		{
			for ($i; $i <= $messages_per_sender * $sender; $i++)
			{
				$msg_number = ($i - 1) % $messages_per_sender + 1;
				$dbsetup->insert(
					'inbox',
					['ReceivingDateTime' => date('Y-m-d H:i:s', strtotime('-' . $i . ' minutes')),
						'TextDecoded' => 'message ' . $msg_number . ' from sender ' . $sender,
						'ID' => $i,
						'id_folder' => 1,
						'id_user' => 2,
						'SenderNumber' => '+336' . sprintf('%08d', $sender),
					]
				);
				$dbsetup->insert(
					'user_inbox',
					['id_user' => 2,
						'id_inbox' => $i, // same as ID in inbox
						'trash' => 0,
					]
				);
			}
		}
		$dbsetup->insert('user');
		$dbsetup->execute($this->CI);

		$options = [
			'type' => 'inbox',
		];
		$output = $this->obj->get_conversation($options);

		$this->assertEquals(0, $output->num_rows());
	}

	public static function get_conversation_inbox_no_limit_no_offset_Provider()
	{
		return DBSetup::prepend_db_engine([
			'not to inbox trash' => FALSE,
			'to inbox trash' => TRUE]);
	}

	/**
	 * @dataProvider get_conversation_inbox_no_limit_no_offset_Provider
	 */
	#[DataProvider('get_conversation_inbox_no_limit_no_offset_Provider')]
	public function test_get_conversation_inbox_no_limit_no_offset($db_engine, $in_trash)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$this->resetInstance();

		$this->CI->load->library('session');
		$_SESSION['loggedin'] = 'TRUE';
		$_SESSION['id_user'] = '1';
		$_SESSION['level'] = 'admin';
		$_SESSION['username'] = 'kalkun';

		$this->CI->load->database();
		$this->CI->load->model('gateway/Gammu_model');
		$this->obj = $this->CI->Gammu_model;

		if ($in_trash === TRUE)
		{
			$id_folder = '5';
			$trash = 1;
		}
		else
		{
			$id_folder = '1';
			$trash = 0;
		}

		$i = 1;
		$senders = 25;
		$messages_per_sender = 5;
		for ($sender = 1; $sender <= $senders; $sender++)
		{
			for ($i; $i <= $messages_per_sender * $sender; $i++)
			{
				$msg_number = ($i - 1) % $messages_per_sender + 1;
				$dbsetup->insert(
					'inbox',
					['ReceivingDateTime' => date('Y-m-d H:i:s', strtotime('-' . $i . ' minutes')),
						'TextDecoded' => 'message ' . $msg_number . ' from sender ' . $sender,
						'ID' => $i,
						'id_folder' => $id_folder,
						'id_user' => 1,
						'SenderNumber' => '+336' . sprintf('%08d', $sender),
					]
				);
				$dbsetup->insert(
					'user_inbox',
					['id_user' => 1,
						'id_inbox' => $i, // same as ID in inbox
						'trash' => $trash,
					]
				);
			}
		}

		$dbsetup->execute($this->CI);

		$options = [
			'type' => 'inbox',
			//'limit' => -1,
			//'offset' => 0,
		];
		if ($in_trash === TRUE)
		{
			// Leave id_folder away if not working on trash
			$options = array_merge($options, ['id_folder' => $id_folder]);
		}
		$output = $this->obj->get_conversation($options);

		$this->assertEquals($senders, $output->num_rows());

		for ($sender = 1; $sender <= $senders; $sender++)
		{
			$this->assertEquals((($sender - 1) * $messages_per_sender) + 1, $output->row($sender - 1)->ID);
			$this->assertEquals('+336' . sprintf('%08d', $sender), $output->row($sender - 1)->SenderNumber);
		}
	}

	/**
	 * @dataProvider get_conversation_inbox_no_limit_no_offset_Provider
	 */
	#[DataProvider('get_conversation_inbox_no_limit_no_offset_Provider')]
	public function test_get_conversation_inbox_multipart_no_limit_no_offset($db_engine, $in_trash)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$this->resetInstance();

		$this->CI->load->library('session');
		$_SESSION['loggedin'] = 'TRUE';
		$_SESSION['id_user'] = '1';
		$_SESSION['level'] = 'admin';
		$_SESSION['username'] = 'kalkun';

		$this->CI->load->database();
		$this->CI->load->model('gateway/Gammu_model');
		$this->obj = $this->CI->Gammu_model;

		if ($in_trash === TRUE)
		{
			$id_folder = '5';
			$trash = 1;
		}
		else
		{
			$id_folder = '1';
			$trash = 0;
		}

		$i = 1;
		$senders = 25;
		$messages_per_sender = 5;
		$nb_of_parts_per_message = 4; // as per insert('inbox_multipart')
		for ($sender = 1; $sender <= $senders; $sender++)
		{
			for ($i; $i <= $messages_per_sender * $sender * $nb_of_parts_per_message; $i = $i + $nb_of_parts_per_message)
			{
				$msg_number = ($i - 1) % $messages_per_sender + 1;
				$dbsetup->insert(
					'inbox_multipart',
					['ReceivingDateTime' => date('Y-m-d H:i:s', strtotime('-' . $i . ' minutes')),
						'TextDecoded' => 'message ' . $msg_number . ' from sender ' . $sender,
						'ID' => $i,
						'id_folder' => $id_folder,
						'id_user' => 1,
						'SenderNumber' => '+336' . sprintf('%08d', $sender),
					]
				);
				$dbsetup->insert(
					'user_inbox',
					['id_user' => 1,
						'id_inbox' => $i, // same as ID in inbox
						'trash' => $trash,
					]
				);
			}
		}

		$dbsetup->execute($this->CI);

		$options = [
			'type' => 'inbox',
			//'limit' => -1,
			//'offset' => 0,
		];
		if ($in_trash === TRUE)
		{
			// Leave id_folder away if not working on trash
			$options = array_merge($options, ['id_folder' => $id_folder]);
		}
		$output = $this->obj->get_conversation($options);

		$this->assertEquals($senders, $output->num_rows());

		for ($sender = 1; $sender <= $senders; $sender++)
		{
			$expected_id = (($sender - 1) * $messages_per_sender * $nb_of_parts_per_message) + 1;
			$this->assertEquals($expected_id, $output->row($sender - 1)->ID);
			$this->assertEquals('+336' . sprintf('%08d', $sender), $output->row($sender - 1)->SenderNumber);
		}
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_get_conversation_inbox_limit_15_offset_15($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$this->resetInstance();

		$this->CI->load->library('session');
		$_SESSION['loggedin'] = 'TRUE';
		$_SESSION['id_user'] = '1';
		$_SESSION['level'] = 'admin';
		$_SESSION['username'] = 'kalkun';

		$this->CI->load->database();
		$this->CI->load->model('gateway/Gammu_model');
		$this->obj = $this->CI->Gammu_model;

		$i = 1;
		$senders = 25;
		$messages_per_sender = 5;
		for ($sender = 1; $sender <= $senders; $sender++)
		{
			for ($i; $i <= $messages_per_sender * $sender; $i++)
			{
				$msg_number = ($i - 1) % $messages_per_sender + 1;
				$dbsetup->insert(
					'inbox',
					['ReceivingDateTime' => date('Y-m-d H:i:s', strtotime('-' . $i . ' minutes')),
						'TextDecoded' => 'message ' . $msg_number . ' from sender ' . $sender,
						'ID' => $i,
						'id_folder' => 1,
						'id_user' => 1,
						'SenderNumber' => '+336' . sprintf('%08d', $sender),
					]
				);
				$dbsetup->insert(
					'user_inbox',
					['id_user' => 1,
						'id_inbox' => $i, // same as ID in inbox
						'trash' => 0,
					]
				);
			}
		}

		$dbsetup->execute($this->CI);

		$limit = 15;
		$offset = 12;
		$options = [
			'type' => 'inbox',
			'limit' => $limit,
			'offset' => $offset,
		];
		$output = $this->obj->get_conversation($options);

		$this->assertEquals(min($limit, $senders - $offset), $output->num_rows());

		for ($row = 0; $row < min($limit, $senders - $offset); $row++)
		{
			$sender = $row + $offset + 1;
			$this->assertEquals((($sender - 1) * $messages_per_sender) + 1, $output->row($row)->ID);
			$this->assertEquals('+336' . sprintf('%08d', $sender), $output->row($row)->SenderNumber);
		}
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_get_conversation_inbox_folder11_no_limit_no_offset($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$this->resetInstance();

		$this->CI->load->library('session');
		$_SESSION['loggedin'] = 'TRUE';
		$_SESSION['id_user'] = '1';
		$_SESSION['level'] = 'admin';
		$_SESSION['username'] = 'kalkun';

		$this->CI->load->database();
		$this->CI->load->model('gateway/Gammu_model');
		$this->obj = $this->CI->Gammu_model;

		$id_folder = 11;
		$trash = 0;

		$i = 1;
		$senders = 25;
		$messages_per_sender = 5;
		for ($sender = 1; $sender <= $senders; $sender++)
		{
			for ($i; $i <= $messages_per_sender * $sender; $i++)
			{
				$msg_number = ($i - 1) % $messages_per_sender + 1;
				$dbsetup->insert(
					'inbox',
					['ReceivingDateTime' => date('Y-m-d H:i:s', strtotime('-' . $i . ' minutes')),
						'TextDecoded' => 'message ' . $msg_number . ' from sender ' . $sender,
						'ID' => $i,
						'id_folder' => $id_folder,
						'id_user' => 1,
						'SenderNumber' => '+336' . sprintf('%08d', $sender),
					]
				);
				$dbsetup->insert(
					'user_inbox',
					['id_user' => 1,
						'id_inbox' => $i, // same as ID in inbox
						'trash' => $trash,
					]
				);
			}
		}

		$dbsetup->insert('user_folders');

		$dbsetup->execute($this->CI);

		$options = [
			'type' => 'inbox',
			'id_folder' => $id_folder,
			//'limit' => -1,
			//'offset' => 0,
		];
		$output = $this->obj->get_conversation($options);

		$this->assertEquals($senders, $output->num_rows());

		for ($sender = 1; $sender <= $senders; $sender++)
		{
			$this->assertEquals((($sender - 1) * $messages_per_sender) + 1, $output->row($sender - 1)->ID);
			$this->assertEquals('+336' . sprintf('%08d', $sender), $output->row($sender - 1)->SenderNumber);
		}
	}

	/**
	 * @dataProvider database_Provider
	 */
	#[DataProvider('database_Provider')]
	public function test_get_conversation_outbox_no_limit_no_offset($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$this->resetInstance();

		$this->CI->load->library('session');
		$_SESSION['loggedin'] = 'TRUE';
		$_SESSION['id_user'] = '1';
		$_SESSION['level'] = 'admin';
		$_SESSION['username'] = 'kalkun';

		$this->CI->load->database();
		$this->CI->load->model('gateway/Gammu_model');
		$this->obj = $this->CI->Gammu_model;

		$i = 1;
		$senders = 25;
		$messages_per_sender = 5;
		for ($sender = 1; $sender <= $senders; $sender++)
		{
			for ($i; $i <= $messages_per_sender * $sender; $i++)
			{
				$msg_number = ($i - 1) % $messages_per_sender + 1;
				$dbsetup->insert(
					'outbox',
					['SendingDateTime' => date('Y-m-d H:i:s', strtotime('-' . $i . ' minutes')),
						'TextDecoded' => 'message ' . $msg_number . ' to recipient ' . $sender,
						'ID' => $i,
						'DestinationNumber' => '+336' . sprintf('%08d', $sender),
					]
				);
				$dbsetup->insert(
					'user_outbox',
					['id_user' => 1,
						'id_outbox' => $i, // same as ID in outbox
					]
				);
			}
		}

		$dbsetup->execute($this->CI);

		$options = [
			'type' => 'outbox',
			//'limit' => -1,
			//'offset' => 0,
		];
		$output = $this->obj->get_conversation($options);

		$this->assertEquals($senders, $output->num_rows());

		for ($sender = 1; $sender <= $senders; $sender++)
		{
			$this->assertEquals((($sender - 1) * $messages_per_sender) + 1, $output->row($sender - 1)->ID);
			$this->assertEquals('+336' . sprintf('%08d', $sender), $output->row($sender - 1)->DestinationNumber);
		}
	}

	/**
	 * @dataProvider get_conversation_inbox_no_limit_no_offset_Provider
	 */
	#[DataProvider('get_conversation_inbox_no_limit_no_offset_Provider')]
	public function test_get_conversation_sentitems_no_limit_no_offset($db_engine, $in_trash)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$this->resetInstance();

		$this->CI->load->library('session');
		$_SESSION['loggedin'] = 'TRUE';
		$_SESSION['id_user'] = '1';
		$_SESSION['level'] = 'admin';
		$_SESSION['username'] = 'kalkun';

		$this->CI->load->database();
		$this->CI->load->model('gateway/Gammu_model');
		$this->obj = $this->CI->Gammu_model;

		if ($in_trash === TRUE)
		{
			$id_folder = '5';
			$trash = 1;
		}
		else
		{
			$id_folder = '3';
			$trash = 0;
		}

		$i = 1;
		$senders = 25;
		$messages_per_sender = 5;
		for ($sender = 1; $sender <= $senders; $sender++)
		{
			for ($i; $i <= $messages_per_sender * $sender; $i++)
			{
				$msg_number = ($i - 1) % $messages_per_sender + 1;
				$dbsetup->insert(
					'sentitems',
					['SendingDateTime' => date('Y-m-d H:i:s', strtotime('-' . $i . ' minutes')),
						'TextDecoded' => 'message ' . $msg_number . ' to recipient ' . $sender,
						'ID' => $i,
						'id_folder' => $id_folder,
						'id_user' => 1,
						'DestinationNumber' => '+336' . sprintf('%08d', $sender),
					]
				);
				$dbsetup->insert(
					'user_sentitems',
					['id_user' => 1,
						'id_sentitems' => $i, // same as ID in sentitems
						'trash' => $trash,
					]
				);
			}
		}

		$dbsetup->execute($this->CI);

		$options = [
			'type' => 'sentitems',
			//'limit' => -1,
			//'offset' => 0,
		];
		if ($in_trash === TRUE)
		{
			// Leave id_folder away if not working on trash
			$options = array_merge($options, ['id_folder' => $id_folder]);
		}
		$output = $this->obj->get_conversation($options);

		$this->assertEquals($senders, $output->num_rows());

		for ($sender = 1; $sender <= $senders; $sender++)
		{
			$this->assertEquals((($sender - 1) * $messages_per_sender) + 1, $output->row($sender - 1)->ID);
			$this->assertEquals('+336' . sprintf('%08d', $sender), $output->row($sender - 1)->DestinationNumber);
		}
	}

	/**
	 * @dataProvider get_conversation_inbox_no_limit_no_offset_Provider
	 */
	#[DataProvider('get_conversation_inbox_no_limit_no_offset_Provider')]
	public function test_get_conversation_sentitems_multipart_no_limit_no_offset($db_engine, $in_trash)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$this->resetInstance();

		$this->CI->load->library('session');
		$_SESSION['loggedin'] = 'TRUE';
		$_SESSION['id_user'] = '1';
		$_SESSION['level'] = 'admin';
		$_SESSION['username'] = 'kalkun';

		$this->CI->load->database();
		$this->CI->load->model('gateway/Gammu_model');
		$this->obj = $this->CI->Gammu_model;

		if ($in_trash === TRUE)
		{
			$id_folder = '5';
			$trash = 1;
		}
		else
		{
			$id_folder = '3';
			$trash = 0;
		}

		$i = 1;
		$senders = 25;
		$messages_per_sender = 5;
		for ($sender = 1; $sender <= $senders; $sender++)
		{
			for ($i; $i <= $messages_per_sender * $sender; $i++)
			{
				$msg_number = ($i - 1) % $messages_per_sender + 1;
				$dbsetup->insert(
					'sentitems_multipart',
					['SendingDateTime' => date('Y-m-d H:i:s', strtotime('-' . $i . ' minutes')),
						'TextDecoded' => 'message ' . $msg_number . ' to recipient ' . $sender,
						'ID' => $i,
						'id_folder' => $id_folder,
						'id_user' => 1,
						'DestinationNumber' => '+336' . sprintf('%08d', $sender),
					]
				);
				$dbsetup->insert(
					'user_sentitems',
					['id_user' => 1,
						'id_sentitems' => $i, // same as ID in sentitems
						'trash' => $trash,
					]
				);
			}
		}

		$dbsetup->execute($this->CI);

		$options = [
			'type' => 'sentitems',
			//'limit' => -1,
			//'offset' => 0,
		];
		if ($in_trash === TRUE)
		{
			// Leave id_folder away if not working on trash
			$options = array_merge($options, ['id_folder' => $id_folder]);
		}
		$output = $this->obj->get_conversation($options);

		$this->assertEquals($senders, $output->num_rows());

		for ($sender = 1; $sender <= $senders; $sender++)
		{
			$this->assertEquals((($sender - 1) * $messages_per_sender) + 1, $output->row($sender - 1)->ID);
			$this->assertEquals('+336' . sprintf('%08d', $sender), $output->row($sender - 1)->DestinationNumber);
		}
	}
}
