<?php
/**
 * Kalkun
 * An open source web based SMS Management
 *
 * @package		Kalkun
 * @author		Kalkun Dev Team
 * @license		https://spdx.org/licenses/GPL-2.0-or-later.html
 * @link		https://kalkun.sourceforge.io/
 */

// ------------------------------------------------------------------------

/**
 * Server_alert_model Class
 *
 * Handle all plugin database activity
 *
 * @package		Kalkun
 * @subpackage	Plugin
 * @category	Models
 */
class Server_alert_model extends CI_Model {

	function __construct()
	{
		$this->load->helper('kalkun');
	}

	function get($option = NULL, $limit = NULL, $offset = NULL)
	{
		switch ($option)
		{
			case 'active':
				$this->db->where('status', 'true');
				return $this->db->get('plugin_server_alert');
			break;

			case 'paginate':
				return $this->db->get('plugin_server_alert', $limit, $offset);
			break;

			case 'count':
				$this->db->select('count(*) as count');
				return $this->db->get('plugin_server_alert')->row('count');
			break;
		}
	}

	function add()
	{
		$data = array (
			'alert_name' => $this->input->post('alert_name'),
			'ip_address' => $this->input->post('ip_address'),
			'port_number' => trim($this->input->post('port_number')),
			'timeout' => trim($this->input->post('timeout')),
			'phone_number' => trim(phone_format_e164($this->input->post('phone_number'))),
			'respond_message' => $this->input->post('respond_message'),
			'release_code' => '', // Not used for now (db requires NOT NULL)
		);
		$this->db->insert('plugin_server_alert', $data);
	}

	function update()
	{
		$data = array (
			'alert_name' => $this->input->post('editalert_name'),
			'ip_address' => $this->input->post('editip_address'),
			'port_number' => trim($this->input->post('editport_number')),
			'timeout' => trim($this->input->post('edittimeout')),
			'phone_number' => trim(phone_format_e164($this->input->post('editphone_number'))),
			'respond_message' => $this->input->post('editrespond_message'),
		);
		$this->db->where('id_server_alert', $this->input->post('editid_server_alert'));
		$this->db->update('plugin_server_alert', $data);
	}

	function delete($id)
	{
		$this->db->delete('plugin_server_alert', array('id_server_alert' => $id));
	}

	function change_state($id, $state)
	{
		$data = array('status' => $state);
		$this->db->where('id_server_alert', $id);
		$this->db->update('plugin_server_alert', $data);
	}

	function get_time_interval()
	{
		$this->db->select('sum(timeout) as timeout');
		$interval = $this->db->get('plugin_server_alert')->row('timeout');
		if (empty($interval))
		{
			$interval = 0;
		}
		return $interval;
	}
}
