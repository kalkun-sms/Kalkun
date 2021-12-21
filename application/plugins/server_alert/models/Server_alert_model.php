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
		parent::__construct();
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
			'alert_name' => $this->input->post('alert_name', TRUE),
			'ip_address' => $this->input->post('ip_address', TRUE),
			'port_number' => trim($this->input->post('port_number', TRUE)),
			'timeout' => trim($this->input->post('timeout', TRUE)),
			'phone_number' => trim($this->input->post('phone_number', TRUE)),
			'respond_message' => $this->input->post('respond_message', TRUE),
			'release_code' => '', // Not used for now (db requires NOT NULL)
		);
		$this->db->insert('plugin_server_alert', $data);
	}

	function update()
	{
		$data = array (
			'alert_name' => $this->input->post('editalert_name', TRUE),
			'ip_address' => $this->input->post('editip_address', TRUE),
			'port_number' => trim($this->input->post('editport_number', TRUE)),
			'timeout' => trim($this->input->post('edittimeout', TRUE)),
			'phone_number' => trim($this->input->post('editphone_number', TRUE)),
			'respond_message' => $this->input->post('editrespond_message', TRUE),
		);
		$this->db->where('id_server_alert', $this->input->post('editid_server_alert', TRUE));
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
