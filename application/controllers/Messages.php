<?php
/**
 * Kalkun
 * An open source web based SMS Management
 *
 * @package		Kalkun
 * @author		Kalkun Dev Team
 * @license		http://kalkun.sourceforge.net/license.php
 * @link		http://kalkun.sourceforge.net
 */

// ------------------------------------------------------------------------

/**
 * Messages Class
 *
 * @package		Kalkun
 * @subpackage	Messages
 * @category	Controllers
 */
class Messages extends MY_Controller {

	/**
	 * Constructor
	 *
	 * @access	public
	 */
	function __construct()
	{
		parent::__construct();

		// session check
		if ($this->session->userdata('loggedin') === NULL)
		{
			redirect('login');
		}
		$param['uid'] = $this->session->userdata('id_user');

		$this->load->model('Phonebook_model');
		$this->load->library('Plugins');
	}

	// --------------------------------------------------------------------

	/**
	 * Compose
	 *
	 * Render compose window form
	 *
	 * @access	public
	 */
	function compose()
	{
		$this->load->helper(array('form', 'kalkun'));

		// register valid type
		$val_type = array('normal', 'reply', 'forward', 'member', 'pbk_contact', 'pbk_groups', 'all_contacts');
		$type = $this->input->post('type');
		if ( ! in_array($type, $val_type))
		{
			die('Invalid type on compose');
		}

		$data['val_type'] = $type;

		// Forward option
		if ($type === 'forward')
		{
			$source = $this->input->post('param1');
			$id = $this->input->post('param2');
			$data['source'] = $source;
			switch ($source)
			{
				case 'inbox':
					$tmp_number = 'SenderNumber';
					$param['type'] = 'inbox';
					$param['id_message'] = $id;
					$data['message'] = $this->Message_model->get_messages($param)->row('TextDecoded');
					$data['msg_id'] = $id;

					// check multipart
					$multipart['type'] = 'inbox';
					$multipart['option'] = 'check';
					$multipart['id_message'] = $id;
					$tmp_check = $this->Message_model->get_multipart($multipart);
					if ($tmp_check->row('UDH') !== '')
					{
						$multipart['option'] = 'all';
						$multipart['udh'] = substr($tmp_check->row('UDH'), 0, 8);
						$multipart['phone_number'] = $tmp_check->row('SenderNumber');
						foreach ($this->Message_model->get_multipart($multipart)->result() as $part)
						{
							$data['message'] .= $part->TextDecoded;
						}
					}
					break;

				case 'sentitems':
					$tmp_number = 'DestinationNumber';
					$param = array('type' => 'sentitems', 'id_message' => $id);
					$data['message'] = $this->Message_model->get_messages($param)->row('TextDecoded');

					// check multipart
					$multipart['type'] = 'sentitems';
					$multipart['option'] = 'check';
					$multipart['id_message'] = $id;
					$tmp_check = $this->Message_model->get_multipart($multipart);
					if ($tmp_check === TRUE)
					{
						$multipart['option'] = 'all';
						foreach ($this->Message_model->get_multipart($multipart)->result() as $part)
						{
							$data['message'] .= $part->TextDecoded;
						}
					}
					break;
			}
		}
		else
		{
			if ($type === 'reply')
			{
				$data['dest'] = $this->input->post('param1');
			}
			else
			{
				if ($type === 'pbk_contact')
				{
					$data['dest'] = $this->input->post('param1');
				}
				else
				{
					if ($type === 'pbk_groups')
					{
						$data['dest'] = $this->input->post('param1');
					}
				}
			}
		}

		$this->load->view('main/messages/compose', $data);
	}

	// --------------------------------------------------------------------

	/**
	 * Compose Process
	 *
	 * Process submitted form
	 *
	 * @access	public
	 */
	function compose_process()
	{
		$this->load->helper('kalkun');

		// We need POST variable
		if ( ! $_POST)
		{
			// Repost the form if we went through login process
			// Finally, after form submission (call to compose_process), redirect to a result page
			// that cannot be POSTed again in case of page refresh.
			if ($this->session->flashdata('bef_login_method') === 'post')
			{
				$this->load->view('main/messages/compose_repost_after_login');
			}
			return;
		}

		$dest = array();

		// Import value from file (currently only CSV)
		if (isset($_FILES['import_file']))
		{
			$this->load->library('CSVReader');
			$filePath = $_FILES['import_file']['tmp_name'];
			$csvData = $this->csvreader->parse_file($filePath, TRUE);
			$csvField = array_keys($csvData[0]);
			foreach ($csvData as $data)
			{
				foreach ($csvField as $field)
				{
					$tmp[$field][] = trim($data[$field]);
				}
			}
			foreach ($csvField as $field)
			{
				$csv[$field] = implode(',', $tmp[$field]);
			}
			$csv['Field'] = $csvField;
			echo json_encode($csv);
			return;
		}


		// Select send option
		switch ($this->input->post('sendoption'))
		{
			// Phonebook
			case 'sendoption1':
				$tmp_dest = explode(',', $this->input->post('personvalue'));
				foreach ($tmp_dest as $key => $tmp)
				{
					if (trim($tmp) !== '')
					{
						list ($id, $type) = explode(':', $tmp);
						// Person
						if ($type === 'c')
						{
							// Already sent, no need to send again
							if (in_array($id, $dest))
							{
								continue;
							}
							$dest[] = $id;
						}
						// Group
						else
						{
							if ($type === 'g')
							{
								$param = array('option' => 'bygroup', 'group_id' => $id);
								foreach ($this->Phonebook_model->get_phonebook($param)->result() as $group)
								{
									// Already sent, no need to send again
									if (in_array($group->Number, $dest))
									{
										continue;
									}
									$dest[] = $group->Number;
								}
							}
							// User, share mode
							else
							{
								if ($type === 'u')
								{
									// set share user id, process later
									$share_uid[] = $id;
								}
							}
						}
					}
				}
				break;

			// Input manually
			case 'sendoption3':
				$tmp_dest = explode(',', $this->input->post('manualvalue'));
				foreach ($tmp_dest as $key => $tmp)
				{
					$tmp = trim($tmp); // remove space
					if (trim($tmp) !== '')
					{
						$dest[$key] = $tmp;
					}
				}
				break;

			// Import from file  (CSV)
			case 'sendoption4':
				if (intval($this->input->post('import_value_count')) > 0)
				{
					$tmp_dest = explode(',', $this->input->post('Number'));
					foreach ($tmp_dest as $key => $tmp)
					{
						$tmp = trim($tmp); // remove space
						if (trim($tmp) !== '')
						{
							$dest[$key] = $tmp;
						}
					}
				}
				break;

			// Reply
			case 'reply':
				$dest[] = $this->input->post('reply_value');
				break;

			// Member
			case 'member':
				$this->load->model('sms_member/sms_member_model', 'Member_model');
				foreach ($this->Member_model->get_member('all')->result() as $tmp)
				{
					$dest[] = $tmp->phone_number;
				}
				break;

			// Phonebook group
			case 'pbk_groups':
				$param = array('option' => 'bygroup', 'group_id' => $this->input->post('id_pbk'));
				foreach ($this->Phonebook_model->get_phonebook($param)->result() as $tmp)
				{
					$dest[] = $tmp->Number;
				}
				break;

			// All contacts
			case 'all_contacts':
				$param = array('option' => 'all');
				foreach ($this->Phonebook_model->get_phonebook($param)->result() as $tmp)
				{
					$dest[] = $tmp->Number;
				}
				break;
		}

		// Select send date
		switch ($this->input->post('senddateoption'))
		{
			// Now
			case 'option1':
				$date = date('Y-m-d H:i:s');
				break;

			// Date and time
			case 'option2':
				$date = $this->input->post('datevalue').' '.$this->input->post('hour').':'.$this->input->post('minute').':00';
				break;

			// Delay
			case 'option3':
				$date = date('Y-m-d H:i:s', mktime(
					date('H') + $this->input->post('delayhour'),
					date('i') + $this->input->post('delayminute'),
					date('s'),
					date('m'),
					date('d'),
					date('Y')
				));
				break;
		}
		$data['type'] = $this->input->post('smstype') ;
		$data['class'] = ($data['type'] === 'flash') ? '0' : '1';
		$data['message'] = $this->input->post('message');
		$data['date'] = $date;
		$data['validity'] = $this->input->post('validity');
		$data['delivery_report'] = $this->Kalkun_model->get_setting()->row('delivery_report');
		$data['coding'] = ($this->input->post('unicode') === 'unicode') ? 'unicode' : 'default';
		$data['ncpr'] = ($this->input->post('ncpr') === 'ncpr') ? TRUE : FALSE;
		$data['uid'] = $this->session->userdata('id_user');
		$data['url'] = $this->input->post('url');

		// if append @username is active
		if ($this->config->item('append_username'))
		{
			$append_username_message = $this->config->item('append_username_message');
			$append_username_message = str_replace('@username', '@'.$this->session->userdata('username'), $append_username_message);
			$data['message'] .= "\n".$append_username_message;
		}

		// if ads is active
		if ($this->config->item('sms_advertise'))
		{
			$ads_message = $this->config->item('sms_advertise_message');
			$data['message'] .= "\n".$ads_message;
		}

		// if disable outgoing
		if ($this->config->item('disable_outgoing'))
		{
			unset($dest);
			$return_msg['type'] = 'error';
			$return_msg['msg'] = lang('kalkun_msg_outgoing_disabled');
		}

		// if ndnc filtering enabled
		$ndnc_msg = '';
		if ($this->config->item('ncpr'))
		{
			if ($data['ndnc'])
			{
				if (is_array($dest))
				{
					for ($i = 0 ; $i < count($dest);  $i++)
					{
						if (DNDcheck($dest[$i]))
						{
							unset($dest[$i]);
							$return_msg['type'] = 'error';
							$return_msg['msg'] = lang('kalkun_msg_number_in_DND');
						}
					}
				}
				else
				{
					if (DNDcheck($dest))
					{
						unset($dest);
						$return_msg['type'] = 'error';
						$return_msg['msg'] = lang('kalkun_msg_number_in_DND');
					}
				}
			}
		}

		// hook for outgoing message
		$dest = do_action('message.outgoing', $dest);
		$sms = do_action('message.outgoing_all', $data);

		$dest_data = do_action('message.outgoing_dest_data', array($dest, $data));
		if (isset($dest_data) && sizeof($dest_data) === 2)
		{
			$dest = $dest_data[0];
			$data = $dest_data[1];
		}

		// check for field
		$field_status = FALSE;
		preg_match_all('/\[\[(.*?)\]\]/', $data['message'], $field_count);
		if (count($field_count[1]) > 0)
		{
			$field_status = TRUE;
			$field_name = $field_count[1];
			foreach ($field_name as $field)
			{
				$$field = explode(',', $this->input->post($field));
			}
		}

		// Share message
		if (isset($share_uid) && is_array($share_uid))
		{
			foreach ($share_uid as $id)
			{
				$msg_id = $this->Message_model->copy_message($this->input->post('msg_id'));
				$this->Message_model->update_owner($msg_id, $id);
				$return_msg['type'] = 'info';
				$return_msg['msg'] = lang('kalkun_msg_delivered_to_user_inbox');
			}
		}

		// Send the message
		if ( ! empty($dest))
		{  // handles if empty numbers after any number removal process
			$n = 0;
			$sms_loop = $this->input->post('sms_loop');
			foreach ($dest as $dest)
			{
				$backup['message'] = $data['message'];
				$data['dest'] = $dest;
				$data['SenderID'] = NULL;
				$data['CreatorID'] = '';

				// change field to value
				if ($field_status)
				{
					foreach ($field_name as $field)
					{
						$field_tag = $$field;
						$data['message'] = str_replace("[[{$field}]]", $field_tag[$n], $data['message']);
					}
				}

				for ($i = 1;$i <= $sms_loop;$i++)
				{
					// Re-schedule if max sms limit occured
					if ($this->config->item('max_sms_sent_by_minute') !== 0)
					{
						$minute_added = floor(($n * $sms_loop + $i) / $this->config->item('max_sms_sent_by_minute'));
						$data['date'] = date('Y-m-d H:i:s', mktime(
							date('H'),
							date('i') + $minute_added,
							date('s'),
							date('m'),
							date('d'),
							date('Y')
						));
						;
					}

					// if multiple modem is active
					if ($this->config->item('multiple_modem_state'))
					{
						$data['SenderID'] = $data['CreatorID'] = $this->_multiple_modem_select($dest, $date, $n * $sms_loop + $i);
					}
					$this->Message_model->send_messages($data);
				}
				$data['message'] = $backup['message'];
				$n++;
			}
			$return_msg['type'] = 'info';
			$return_msg['msg'] = lang('kalkun_msg_moved_to_outbox');
		}
		if ( ! isset($return_msg))
		{
			$return_msg['type'] = 'error';
			$return_msg['msg'] = lang('kalkun_msg_no_numberfound');
		}

		// Display sending status
		switch ($return_msg['type'])
		{
			case 'error':
				echo '<div class="notif" style="color:red">'.$return_msg['msg'].'</div>';
				break;
			case 'info':
			default:
				echo '<div class="notif">'.$return_msg['msg'].'</div>';
				break;
		}

		if ($this->input->post('redirect_to_form_result') === '1')
		{
			switch ($return_msg['type'])
			{
				case 'error':
					$this->session->set_flashdata('notif', '<span style="color:red">'.$return_msg['msg'].'</span>');
					break;
				case 'info':
				default:
					$this->session->set_flashdata('notif', $return_msg['msg']);
					break;
			}
			redirect('messages/folder/outbox/');
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Folder
	 *
	 * List messages on folder (inbox, outbox, sentitems, trash)
	 *
	 * @access	public
	 */
	function folder($type = NULL, $offset = 0)
	{
		$this->load->helper('kalkun');

		if ($type === 'phonebook')
		{
			redirect('phonebook/');
		}

		// validate url
		$valid_type = array('inbox', 'sentitems', 'outbox');
		if ( ! in_array($type, $valid_type))
		{
			die('Invalid URL');
		}

		$data['folder'] = 'folder';
		$data['type'] = $type;
		$data['offset'] = $offset;
		$data['id_folder'] = '';

		// Pagination
		$this->load->library('pagination');
		$config['per_page'] = $this->Kalkun_model->get_setting()->row('paging');
		$config['cur_tag_open'] = '<span id="current">';
		$config['cur_tag_close'] = '</span>';

		if (is_ajax())
		{
			$param['type'] = $type;
			$param['limit'] = $config['per_page'];
			$param['offset'] = $offset;
			if ($this->config->item('conversation_grouping'))
			{
				$data['messages'] = $this->Message_model->get_conversation($param);
			}
			else
			{
				$param['order_by'] = ($type === 'inbox') ? 'ReceivingDateTime' : 'SendingDateTime';
				$param['order_by_type'] = $this->Kalkun_model->get_setting()->row('conversation_sort');
				$param['uid'] = $this->session->userdata('id_user');
				$data['messages'] = $this->Message_model->get_messages($param);
			}
			$this->load->view('main/messages/message_list', $data);
		}
		else
		{
			if ($this->config->item('conversation_grouping'))
			{
				$config['total_rows'] = $this->Message_model->get_conversation(array('type' => $type))->num_rows();
				$param['type'] = $type;
				$param['limit'] = $config['per_page'];
				$param['offset'] = $this->uri->segment(4, 0);
				$data['messages'] = $this->Message_model->get_conversation($param);
			}
			else
			{
				$config['total_rows'] = $this->Message_model->get_messages(array('type' => $type))->num_rows();
				$param['type'] = $type;
				$param['limit'] = $config['per_page'];
				$param['offset'] = $this->uri->segment(4, 0);
				$param['order_by'] = ($type === 'inbox') ? 'ReceivingDateTime' : 'SendingDateTime';
				$param['order_by_type'] = $this->Kalkun_model->get_setting()->row('conversation_sort');
				$param['uid'] = $this->session->userdata('id_user');
				$data['messages'] = $this->Message_model->get_messages($param);
			}

			$config['base_url'] = site_url('messages/folder/'.$type);
			$config['uri_segment'] = 4;
			$this->pagination->initialize($config);

			$data['main'] = 'main/messages/index';
			$this->load->view('main/layout', $data);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * My Folder
	 *
	 * List messages on custom folder (user created folder)
	 *
	 * @access	public
	 */
	function my_folder($type = NULL, $id_folder = NULL, $offset = 0)
	{
		$this->load->helper('kalkun');

		// validate url
		$valid = array('inbox', 'sentitems');
		if ( ! in_array($type, $valid))
		{
			die('Invalid URL');
		}

		$data['folder'] = 'my_folder';
		$data['type'] = $type;
		$data['offset'] = $offset;
		$data['id_folder'] = $id_folder;

		// Pagination
		$this->load->library('pagination');
		$config['per_page'] = $this->Kalkun_model->get_setting()->row('paging');
		$config['cur_tag_open'] = '<span id="current">';
		$config['cur_tag_close'] = '</span>';

		if (is_ajax())
		{
			$param['type'] = $type;
			$param['id_folder'] = $id_folder;
			$param['limit'] = $config['per_page'];
			$param['offset'] = $offset;

			if ($this->config->item('conversation_grouping'))
			{
				$data['messages'] = $this->Message_model->get_conversation($param);
			}
			else
			{
				$param['order_by'] = ($type === 'inbox') ? 'ReceivingDateTime' : 'SendingDateTime';
				$param['order_by_type'] = $this->Kalkun_model->get_setting()->row('conversation_sort');
				$param['uid'] = $this->session->userdata('id_user');
				$data['messages'] = $this->Message_model->get_messages($param);
			}
			$this->load->view('main/messages/message_list', $data);
		}
		else
		{
			$param['type'] = $type;
			$param['id_folder'] = $id_folder;
			if ($this->config->item('conversation_grouping'))
			{
				$config['total_rows'] = $this->Message_model->get_conversation($param)->num_rows();
				$param['limit'] = $config['per_page'];
				$param['offset'] = $this->uri->segment(5, 0);
				$data['messages'] = $this->Message_model->get_conversation($param);
			}
			else
			{
				$config['total_rows'] = $this->Message_model->get_messages($param)->num_rows();
				$param['limit'] = $config['per_page'];
				$param['offset'] = $this->uri->segment(5, 0);
				$param['order_by'] = ($type === 'inbox') ? 'ReceivingDateTime' : 'SendingDateTime';
				$param['order_by_type'] = $this->Kalkun_model->get_setting()->row('conversation_sort');
				$param['uid'] = $this->session->userdata('id_user');
				$data['messages'] = $this->Message_model->get_messages($param);
			}
			$config['base_url'] = site_url('/messages/my_folder/'.$type.'/'.$id_folder);
			$config['uri_segment'] = 5;
			$this->pagination->initialize($config);

			$data['main'] = 'main/messages/index';
			$this->load->view('main/layout', $data);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Conversation
	 *
	 * List messages on conversation (based on phone number)
	 *
	 * @access	public
	 */
	function conversation($source = NULL, $type = NULL, $number = NULL, $id_folder = NULL)
	{
		$this->load->helper('kalkun');

		// Pagination
		$this->load->library('pagination');
		$config['per_page'] = $this->Kalkun_model->get_setting()->row('paging');
		$config['cur_tag_open'] = '<span id="current">';
		$config['cur_tag_close'] = '</span>';

		if ($source === 'folder' && isset($type) && $type !== 'outbox' && $type !== 'phonebook')
		{
			$data['main'] = 'main/messages/index';
			$param['type'] = $type;
			$param['number'] = trim($number);

			$config['base_url'] = site_url('/messages/conversation/folder/'.$type.'/'.$number);
			$config['total_rows'] = $this->Message_model->get_messages($param)->num_rows();
			$config['uri_segment'] = 6;
			$this->pagination->initialize($config);


			if ($param['number'] === 'sending_error')
			{
				$param['type'] = 'sentitems';
				$param['number'] = trim($number);
				$param['order_by'] = 'SendingDateTime';
				$param['order_by_type'] = $this->Kalkun_model->get_setting()->row('conversation_sort');
				$param['uid'] = $this->session->userdata('id_user');
				$sentitems = $this->Message_model->get_messages($param)->result_array();

				// add global date for sorting
				foreach ($sentitems as $key => $tmp)
				{
					$sentitems[$key]['globaldate'] = $sentitems[$key]['SendingDateTime'];
					$sentitems[$key]['source'] = 'sentitems';
				}

				$data['messages'] = $sentitems;
			}
			else
			{
				$param['type'] = 'inbox';
				$param['limit'] = $config['per_page'];
				$param['offset'] = $this->uri->segment(6, 0);
				$param['order_by'] = 'ReceivingDateTime';
				$param['order_by_type'] = $this->Kalkun_model->get_setting()->row('conversation_sort');
				$param['uid'] = $this->session->userdata('id_user');
				$inbox = $this->Message_model->get_messages($param)->result_array();

				// add global date for sorting
				foreach ($inbox as $key => $tmp)
				{
					$inbox[$key]['globaldate'] = $inbox[$key]['ReceivingDateTime'];
					$inbox[$key]['source'] = 'inbox';
				}

				$param['type'] = 'sentitems';
				$param['number'] = trim($number);
				$param['order_by'] = 'SendingDateTime';
				$param['order_by_type'] = $this->Kalkun_model->get_setting()->row('conversation_sort');
				$sentitems = $this->Message_model->get_messages($param)->result_array();

				// add global date for sorting
				foreach ($sentitems as $key => $tmp)
				{
					$sentitems[$key]['globaldate'] = $sentitems[$key]['SendingDateTime'];
					$sentitems[$key]['source'] = 'sentitems';
				}

				$data['messages'] = $inbox;

				// merge inbox and sentitems
				foreach ($sentitems as $tmp)
				{
					$data['messages'][] = $tmp;
				}
			}

			// sort data
			$sort_option = $this->Kalkun_model->get_setting()->row('conversation_sort');
			usort($data['messages'], 'compare_date_'.$sort_option);

			if (is_ajax())
			{
				$this->load->view('main/messages/conversation', $data);
			}
			else
			{
				$this->load->view('main/layout', $data);
			}
		}
		else
		{
			if ($source === 'folder' && $type === 'outbox')
			{
				$data['main'] = 'main/messages/index';
				$param['type'] = 'outbox';
				$param['number'] = trim($number);
				$param['uid'] = $this->session->userdata('id_user');
				$config['base_url'] = site_url('/messages/conversation/folder/'.$type.'/'.$number);
				$config['total_rows'] = $this->Message_model->get_messages($param)->num_rows();
				$config['uri_segment'] = 6;
				$this->pagination->initialize($config);

				$param['limit'] = $config['per_page'];
				$param['offset'] = $this->uri->segment(6, 0);
				$param['order_by'] = 'SendingDateTime';
				$param['order_by_type'] = $this->Kalkun_model->get_setting()->row('conversation_sort');
				$outbox = $this->Message_model->get_messages($param)->result_array();

				foreach ($outbox as $key => $tmp)
				{
					$outbox[$key]['source'] = 'outbox';
				}
				$data['messages'] = $outbox;

				if (is_ajax())
				{
					$this->load->view('main/messages/conversation', $data);
				}
				else
				{
					$this->load->view('main/layout', $data);
				}
			}
			else
			{
				if ($source === 'my_folder')
				{ // my folder
					$data['main'] = 'main/messages/index';
					$param['type'] = 'inbox';
					$param['id_folder'] = $id_folder;
					$param['number'] = trim($number);
					$param['uid'] = $this->session->userdata('id_user');

					$config['base_url'] = site_url('/messages/conversation/my_folder/'.$type.'/'.$number.'/'.$id_folder);
					$config['total_rows'] = $this->Message_model->get_messages($param)->num_rows();
					$config['uri_segment'] = 7;
					$this->pagination->initialize($config);

					$param['limit'] = $config['per_page'];
					$param['offset'] = $this->uri->segment(7, 0);
					$param['order_by'] = 'ReceivingDateTime';
					$param['order_by_type'] = $this->Kalkun_model->get_setting()->row('conversation_sort');
					$inbox = $this->Message_model->get_messages($param)->result_array();

					// add global date for sorting
					foreach ($inbox as $key => $tmp)
					{
						$inbox[$key]['globaldate'] = $inbox[$key]['ReceivingDateTime'];
						$inbox[$key]['source'] = 'inbox';
					}

					$param['type'] = 'sentitems';
					$param['id_folder'] = $id_folder;
					$param['number'] = trim($number);
					$param['order_by'] = 'SendingDateTime';
					$param['order_by_type'] = $this->Kalkun_model->get_setting()->row('conversation_sort');
					$sentitems = $this->Message_model->get_messages($param)->result_array();

					// add global date for sorting
					foreach ($sentitems as $key => $tmp)
					{
						$sentitems[$key]['globaldate'] = $sentitems[$key]['SendingDateTime'];
						$sentitems[$key]['source'] = 'sentitems';
					}

					$data['messages'] = $inbox;

					// merge inbox and sentitems
					foreach ($sentitems as $tmp)
					{
						$data['messages'][] = $tmp;
					}

					// sort data
					$sort_option = $this->Kalkun_model->get_setting()->row('conversation_sort');
					usort($data['messages'], 'compare_date_'.$sort_option);

					if (is_ajax())
					{
						$this->load->view('main/messages/conversation', $data);
					}
					else
					{
						$this->load->view('main/layout', $data);
					}
				}
				else
				{ //all
					$data['main'] = 'main/messages/index';
					$param['number'] = $number;
					$param['uid'] = $this->session->userdata('id_user');
					$config['per_page'] = $this->Kalkun_model->get_setting()->row('paging');
					$config['cur_tag_open'] = '<span id="current">';
					$config['cur_tag_close'] = '</span>';
					$config['base_url'] = site_url('/messages/conversation/folder/'.$type.'/'.$number);
					$config['total_rows'] = $this->Message_model->search_messages($param)->total_rows;
					$config['uri_segment'] = 6;
					$this->pagination->initialize($config);
					$param['limit'] = $config['per_page'];
					$param['offset'] = $this->uri->segment(6, 0);
					$data['messages'] = $this->Message_model->search_messages($param)->messages;

					if (is_ajax())
					{
						$this->load->view('main/messages/conversation', $data);
					}
					else
					{
						$this->load->view('main/layout', $data);
					}
				}
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Search Conversation
	 *
	 * List messages based on search string
	 *
	 * @access	public
	 */
	function search()
	{
		// Get URI string
		$segment = $this->uri->segment_array();
		$segment_count = count($segment);
		$data['main'] = 'main/messages/index';
		$data['messages'] = array();

		// Pagination
		$this->load->library('pagination');
		$config['per_page'] = $this->Kalkun_model->get_setting()->row('paging');
		$config['cur_tag_open'] = '<span id="current">';
		$config['cur_tag_close'] = '</span>';

		switch ($segment[3])
		{
			case 'basic':
				$param_needed = 4; // minimal count of segment
				if ($segment_count >= $param_needed)
				{
					if ($segment[4] !== '_')
					{
						$param['search_string'] = urldecode($segment[4]);
					}
					$data['search_string'] = $segment[4];
					$config['total_rows'] = $this->Message_model->search_messages($param)->total_rows;
					$config['uri_segment'] = $param_needed + 1;
					$config['base_url'] = site_url(array_slice($segment, 0, $param_needed));
					$this->pagination->initialize($config);
					$param['limit'] = $config['per_page'];
					$param['offset'] = $this->uri->segment($param_needed + 1, 0);
					$param['uid'] = $this->session->userdata('id_user');
					$data['messages'] = $this->Message_model->search_messages($param)->messages;
				}
				break;

			case 'advanced':
				$param_needed = 10;
				if ($segment_count >= $param_needed)
				{
					if ($segment[4] !== '_')
					{
						$param['search_string'] = urldecode($segment[4]);
					}
					if ($segment[5] !== '_')
					{
						$param['number'] = $segment[5];
					}
					if ($segment[6] !== '_')
					{
						$param['date_from'] = $segment[6];
					}
					if ($segment[7] !== '_')
					{
						$param['date_to'] = $segment[7];
					}
					if ($segment[8] !== '_')
					{
						$param['status'] = $segment[8];
					}
					if ($segment[9] !== '_')
					{
						$param['id_folder'] = $segment[9];
						if ($segment[9] === '5' OR $segment[9] === 'all')
						{
							$param['trash'] = TRUE;
						}
					}
					$config['total_rows'] = $this->Message_model->search_messages($param)->total_rows;
					if ($segment[10] !== '_')
					{
						$config['per_page'] = ($segment[10] === 'all') ? $config['total_rows'] : $segment[10];
					}
					$config['uri_segment'] = $param_needed + 1;
					$config['base_url'] = site_url(array_slice($segment, 0, $param_needed));
					$this->pagination->initialize($config);
					$param['limit'] = $config['per_page'];
					$param['offset'] = $this->uri->segment($param_needed + 1, 0);
					$param['uid'] = $this->session->userdata('id_user');
					$data['messages'] = $this->Message_model->search_messages($param)->messages;
				}
				break;
		}
		$this->load->view('main/layout', $data);
	}

	function query()
	{
		// basic search
		if ($this->input->post('search_sms'))
		{
			$params[] = 'basic';
			$params[] = trim($this->input->post('search_sms'));
		}
		// advanced search
		else
		{
			if ($this->input->post('a_search_trigger'))
			{
				$params[] = 'advanced';
				$params[] = $this->input->post('a_search_query') ? $this->input->post('a_search_query') : '_';
				$params[] = $this->input->post('a_search_from_to') ? $this->input->post('a_search_from_to') : '_';
				$params[] = $this->input->post('a_search_date_from') ? $this->input->post('a_search_date_from') : '_';
				$params[] = $this->input->post('a_search_date_to') ? $this->input->post('a_search_date_to') : '_';
				$params[] = $this->input->post('a_search_sentitems_status') ? $this->input->post('a_search_sentitems_status') : '_';
				$params[] = $this->input->post('a_search_on') ? $this->input->post('a_search_on') : '_';
				$params[] = $this->input->post('a_search_paging') ? $this->input->post('a_search_paging') : '_';
			}
			else
			{
				// nothing to search
			}
		}

		$url = 'messages/search';
		foreach ($params as $param)
		{
			$url .= '/'.$param;
		}
		redirect($url);
	}


	// --------------------------------------------------------------------

	/**
	 * Move Message
	 *
	 * Move messages from a folder to another folder
	 *
	 * @access	public
	 */
	function move_message()
	{
		$param['current_folder'] = '';

		if ($this->input->post('type'))
		{
			$param['type'] = $this->input->post('type');
		}
		if ($this->input->post('current_folder'))
		{
			$param['current_folder'] = $this->input->post('current_folder');
		}
		if ($this->input->post('number'))
		{
			$param['number'] = $this->input->post('number');
		}
		if ($this->input->post('id_folder'))
		{
			$param['id_folder'] = $this->input->post('id_folder');
		}
		if ($this->input->post('folder'))
		{
			$param['folder'] = $this->input->post('folder');
		}
		if ($this->input->post('id_message'))
		{
			$param['id_message'][0] = $this->input->post('id_message');
		}

		if (isset($param['type']) && $param['type'] === 'single' && isset($param['folder']) && $param['folder'] === 'inbox')
		{
			$multipart = array('type' => 'inbox', 'option' => 'check', 'id_message' => $param['id_message'][0]);
			$tmp_check = $this->Message_model->get_multipart($multipart);
			if ($tmp_check->row('UDH') !== '')
			{
				$multipart = array('option' => 'all', 'udh' => substr($tmp_check->row('UDH'), 0, 8));
				$multipart['phone_number'] = $tmp_check->row('SenderNumber');
				foreach ($this->Message_model->get_multipart($multipart)->result() as $part)
				{
					$param['id_message'][] = $part->ID;
				}
			}
		}
		$this->Message_model->move_messages($param);
	}

	// --------------------------------------------------------------------

	/**
	 * Delete Message
	 *
	 * Delete messages, permanently or temporarily
	 *
	 * @access	public
	 */
	function delete_messages($source = NULL)
	{
		if ($this->input->post('type'))
		{
			$param['type'] = $this->input->post('type');
		}
		if ($this->input->post('current_folder'))
		{
			$param['current_folder'] = $this->input->post('current_folder');
		}
		if ($this->input->post('id'))
		{
			$param['id'][0] = $this->input->post('id');
		}
		if ( ! is_null($source))
		{
			$param['source'] = $source;
		}
		if ($this->input->post('number'))
		{
			$param['number'] = $this->input->post('number');
		}

		if ($param['source'] === 'outbox')
		{
			$param['option'] = 'outbox';
		}
		else
		{
			// check trash/permanent delete
			if ($this->Kalkun_model->get_setting()->row('permanent_delete') === 'true')
			{
				$param['option'] = 'permanent';
			}
			else
			{
				if (isset($param['current_folder']) && $param['current_folder'] === '5')
				{
					$param['option'] = 'permanent';
				}
				else
				{
					if (isset($param['current_folder']) && $param['current_folder'] === '6')
					{
						$param['option'] = 'permanent';
					}
					else
					{
						$param['option'] = 'temp';
					}
				}
			}
		}

		if ($param['option'] === 'permanent' && $this->config->item('only_admin_can_permanently_delete') && $this->session->userdata('level') !== 'admin')
		{
			echo lang('kalkun_msg_only_admin_can_permanently_delete');
			exit;
		}

		if ($param['type'] === 'single' && $param['source'] === 'inbox')
		{
			$multipart['type'] = 'inbox';
			$multipart['option'] = 'check';
			$multipart['id_message'] = $param['id'][0];
			$tmp_check = $this->Message_model->get_multipart($multipart);
			if ($tmp_check->row('UDH') !== '')
			{
				$multipart['option'] = 'all';
				$multipart['udh'] = substr($tmp_check->row('UDH'), 0, 8);
				$multipart['phone_number'] = $tmp_check->row('SenderNumber');
				foreach ($this->Message_model->get_multipart($multipart)->result() as $part)
				{
					$param['id'][] = $part->ID;
				}
			}
		}
		$this->Message_model->delete_messages($param);
	}

	// --------------------------------------------------------------------

	/**
	 * Check Folder Privileges
	 *
	 * Check folder privileges/permission
	 *
	 * @access	private
	 */
	function _check_folder_privileges($id_folder = NULL)
	{
		//$this->
	}

	// --------------------------------------------------------------------

	/**
	 * Canned Response a.k.a SMS Template
	 *
	 * Get/List/Save/Update/Delete Canned Response
	 *
	 * @access	public
	 */
	function canned_response($action = NULL)
	{
		$name = $this->input->post('name');
		$message = $this->input->post('message');

		if ($action === 'list')
		{
			$data['canned_list'] = $this->Message_model->canned_response($name, $message, $action);
			$this->load->view('main/messages/canned_response', $data);
		}
		else
		{
			$this->Message_model->canned_response($name, $message, $action);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Multiple modem
	 *
	 * Select modem to use based on multiple modem configuration
	 *
	 * @access	private
	 */
	function _multiple_modem_select($phone_number, $date, $n)
	{
		$modem_list = $this->config->item('multiple_modem');
		$strategies = explode(':', $this->config->item('multiple_modem_strategy'));
		$first_strategy = $strategies[0];
		$valid_second_strategy = array('round_robin', 'failover', 'recent');
		if (count($strategies) === 2)
		{
			$second_strategy = $strategies[1];
		}

		// find candidates
		switch ($first_strategy)
		{
			case 'scheduled_time':
				list($date, $time) = explode(' ', $date);
				foreach ($modem_list as $modem)
				{
					list($start_time, $end_time) = explode('-', $modem['value']);
					if ($time >= $start_time && $time <= $end_time)
					{
						$candidate_modem[] = $modem['id'];
					}
				}
				break;

			case 'scheduled_day':
				$this->load->helper('date');
				$day = date('w', human_to_unix($date));
				foreach ($modem_list as $modem)
				{
					list($start_day, $end_day) = explode('-', $modem['value']);
					if ($day >= $start_day && $day <= $end_day)
					{
						$candidate_modem[] = $modem['id'];
					}
				}
				break;

			case 'scheduled_date':
				list($date, $time) = explode(' ', $date);
				foreach ($modem_list as $modem)
				{
					list($start_date, $end_date) = explode(':', $modem['value']);
					if ($date >= $start_date && $date <= $end_date)
					{
						$candidate_modem[] = $modem['id'];
					}
				}
				break;

			case 'phone_number_prefix':
				foreach ($modem_list as $modem)
				{
					$prefix_number = $modem['value'];
					foreach ($prefix_number as $prefix)
					{
						if (strpos($phone_number, $prefix) !== FALSE)
						{
							$candidate_modem[] = $modem['id'];
						}
					}
				}
				break;

			case 'phone_number':
				foreach ($modem_list as $modem)
				{
					$dest_phone_number = $modem['value'];
					if (in_array($phone_number, $dest_phone_number))
					{
						$candidate_modem[] = $modem['id'];
					}
				}
				break;

			case 'user':
				$user_id = $this->session->userdata('id_user');
				foreach ($modem_list as $modem)
				{
					$allowed_users = $modem['value'];
					if (in_array($user_id, $allowed_users))
					{
						$candidate_modem[] = $modem['id'];
					}
				}
				break;

			case 'round_robin': // currently only works with multiple message, not a single message
				$candidate_modem[] = $this->_multiple_modem_round_robin($modem_list, $n);
				break;

			case 'failover':
				$candidate_modem[] = $this->_multiple_modem_failover($modem_list);
				break;

			case 'recent': // use latest active modem
				$candidate_modem[] = $this->_multiple_modem_recent($modem_list);
				break;
		}

		// if second strategy exist, and the first strategy is NOT one of them
		if (isset($second_strategy) && ! in_array($first_strategy, $valid_second_strategy))
		{
			switch ($second_strategy)
			{
				case 'round_robin':
					$selected_modem = $this->_multiple_modem_round_robin($candidate_modem, $n);
					break;

				case 'failover':
					$selected_modem = $this->_multiple_modem_failover($candidate_modem);
					break;

				case 'recent':
					$selected_modem = $this->_multiple_modem_recent($candidate_modem);
					break;
			}
		}
		else
		{
			if (isset($candidate_modem) && count($candidate_modem) >= 1)
			{
				$selected_modem = $candidate_modem[0];
			}
		}

		// Return selected modem, if not return first modem as default value
		if (isset($selected_modem))
		{
			return $selected_modem;
		}
		else
		{
			return $modem_list[0]['id'];
		}
	}

	function _multiple_modem_round_robin($modems, $n)
	{
		$modem_count = count($modems);
		$n = $n % $modem_count;
		if ($n === 0)
		{
			$n = $modem_count;
		}
		return $modems[$n - 1];

		// Currently not used
		// phpcs:disable CodeIgniter.Commenting.InlineComment.LongCommentWithoutSpacing
		//$available_modem = $this->Message_model->get_modem_list('count', 'asc');
		//foreach ($modems as $modem)
		//{
		//	if ($modem == $available_modem->row('ID'))
		//	{
		//		return $modem;
		//	}
		//}
	}

	function _multiple_modem_failover($modems)
	{
		// Not yet implemented
		// ...
	}

	function _multiple_modem_recent($modems)
	{
		$available_modem = $this->Message_model->get_modem_list('time', 'desc');
		foreach ($modems as $modem)
		{
			if ($modem === $available_modem->row('ID'))
			{
				return $modem;
			}
		}
	}

	// --------------------------------------------------------------------
	/**
	 * Messages::delete_all()
	 * empty trash/spam
	 * @param mixed $type
	 * @return
	 */
	function delete_all($type = NULL)
	{
		// register valid type
		$valid_type = array('trash', 'spam');
		// check if it's valid type
		if ( ! in_array($type, $valid_type))
		{
			die('Invalid Type');
		}

		switch ($type){
			case 'trash':
				$this->db->delete('inbox', array('id_folder' => '5'));
				$this->db->delete('sentitems', array('id_folder' => '5'));
				break;
			case 'spam':
				$this->db->delete('inbox', array('id_folder' => '6'));
				break;
		}
	}

	/**
	 * Messages::report_spam()
	 * Report spam mode
	 * @param mixed $type
	 * @return
	 */
	function report_spam($type = NULL)
	{
		if ($type === NULL OR ($type !== 'spam' && $type !== 'ham'))
		{
			show_404();
		}

		$ID = $this->input->post('id_message');

		if ( ! isset($ID) OR $ID === '')
		{
			show_error('Something is wrong with the request.', 400, '400 Bad Request');
		}

		$this->load->Model('Spam_model');
		$Text = $this->Message_model->get_messages(array('type' => 'inbox', 'id_message' => $ID))->row('TextDecoded');
		$params = array('ID' => $ID, 'Text' => $Text);
		if ($type === 'ham')
		{
			$this->Spam_model->report_ham($params);
		}
		else
		{
			$this->Spam_model->report_spam($params);
		}
	}
}
