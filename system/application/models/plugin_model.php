<?php
Class Plugin_model extends Model {
	
	function Plugin_model()
	{
		parent::Model();
		$this->load->database();
	}
	
	function getPluginStatus($name)
	{
		$this->db->select('plugin_status');
		$this->db->where('plugin_name', $name);
		return $this->db->get('plugin')->row('plugin_status');
	}
	

	//=================================================================
	// BLACKLIST NUMBER
	//=================================================================	
	
	function getBlacklistNumber($option=NULL, $limit=NULL, $offset=NULL)
	{
		switch($option)
		{
			case 'all':
				return $this->db->get('plugin_blacklist_number');
			break;
			
			case 'paginate':
				return $this->db->get('plugin_blacklist_number', $limit, $offset);		
			break;
			
			case 'count':
				$this->db->select('count(*) as count');
				return $this->db->get('plugin_blacklist_number')->row('count');
			break;
		}
	}
	
	function addBlacklistNumber()
	{
		$data = array (
				'phone_number' => trim($this->input->post('phone_number',TRUE)),
				'reason' => trim($this->input->post('reason',TRUE)),
					);
		$this->db->insert('plugin_blacklist_number',$data);			
	}

	function updateBlacklistNumber()
	{
		$data = array (
				'phone_number' => trim($this->input->post('editphone_number',TRUE)),
				'reason' => $this->input->post('editreason',TRUE),
					);
		$this->db->where('id_blacklist_number', $this->input->post('editid_blacklist_number',TRUE));			
		$this->db->update('plugin_blacklist_number',$data);
	}	
	
	function delBlacklistNumber($id)
	{
		$this->db->delete('plugin_blacklist_number', array('id_blacklist_number' => $id)); 
	}
	


	//=================================================================
	// SERVER ALERT
	//=================================================================	
	
	function getServerAlert($option=NULL, $limit=NULL, $offset=NULL)
	{
		switch($option)
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
	
	function addServerAlert()
	{
		$data = array (
				'alert_name' => $this->input->post('alert_name',TRUE),
				'ip_address' => $this->input->post('ip_address',TRUE),
				'port_number' => trim($this->input->post('port_number',TRUE)),
				'timeout' => trim($this->input->post('timeout',TRUE)),				
				'phone_number' => trim($this->input->post('phone_number',TRUE)),
				'respond_message' => $this->input->post('respond_message',TRUE),
					);
		$this->db->insert('plugin_server_alert',$data);			
	}

	function updateServerAlert()
	{
		$data = array (
				'alert_name' => $this->input->post('editalert_name',TRUE),
				'ip_address' => $this->input->post('editip_address',TRUE),
				'port_number' => trim($this->input->post('editport_number',TRUE)),
				'timeout' => trim($this->input->post('edittimeout',TRUE)),								
				'phone_number' => trim($this->input->post('editphone_number',TRUE)),
				'respond_message' => $this->input->post('editrespond_message',TRUE),
					);
		$this->db->where('id_server_alert', $this->input->post('editid_server_alert',TRUE));			
		$this->db->update('plugin_server_alert',$data);
	}	
	
	function delServerAlert($id)
	{
		$this->db->delete('plugin_server_alert', array('id_server_alert' => $id)); 
	}
	
	
	function changeState($id, $state)
	{
		$data = array('status' => $state);
		$this->db->where('id_server_alert', $id);
		$this->db->update('plugin_server_alert', $data);
	}
	
	
	function getTimeInterval()
	{
		$this->db->select('sum(timeout) as timeout');
		return $this->db->get('plugin_server_alert')->row('timeout');	
	}
}
?>
