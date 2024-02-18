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
 * Whitelist_number_model Class
 *
 * Handle all plugin database activity
 *
 * @package		Kalkun
 * @subpackage	Plugin
 * @category	Models
 */
class Whitelist_number_model extends CI_Model {

	function get($option = NULL, $limit = NULL, $offset = NULL)
	{
		switch ($option)
		{
			case 'all':
				return $this->db->get('plugin_whitelist_number');
			break;

			case 'paginate':
				return $this->db->get('plugin_whitelist_number', $limit, $offset);
			break;

			case 'count':
				$this->db->select('count(*) as count');
				return $this->db->get('plugin_whitelist_number')->row('count');
			break;
		}
	}

	function add()
	{
		$data = array (
			'match' => trim($this->input->post('match')),
		);
		$this->db->insert('plugin_whitelist_number', $data);
	}

	function update()
	{
		$data = array (
			'match' => trim($this->input->post('editmatch')),
		);
		$this->db->where('id_whitelist', $this->input->post('editid_whitelist'));
		$this->db->update('plugin_whitelist_number', $data);
	}

	function delete($id)
	{
		$this->db->delete('plugin_whitelist_number', array('id_whitelist' => $id));
	}
}
