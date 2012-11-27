<?php
Class Soap_model extends Model {
	
	function __construct()
	{
		parent::__construct();
	}
	
	function getRemoteAccess($option=NULL, $limit=NULL, $offset=NULL)
	{
		switch($option)
		{
			case 'active':
				$this->db->where('status', 'true');
				return $this->db->get('plugin_remote_access');
			break;
			
			case 'paginate':
				return $this->db->get('plugin_remote_access', $limit, $offset);
			break;
			
			case 'count':
				$this->db->select('count(*) as count');
				return $this->db->get('plugin_remote_access')->row('count');
			break;
		}
	}
	
	function addRemoteAccess()
	{
		$data = array (
				'access_name' => $this->input->post('access_name',TRUE),
				'ip_address' => $this->input->post('ip_address',TRUE),
				'token' => $this->generateUniqueHash(),
					);
		$this->db->insert('plugin_remote_access',$data); 		
	}

	function updateRemoteAccess()
	{
		$status = ($this->input->post('editstatus',TRUE) == 'on')? 'true':'false';
		$data = array (
				'access_name' => $this->input->post('editaccess_name',TRUE),
				'ip_address' => $this->input->post('editip_address',TRUE),
				'status'	=> $status
					);
		$this->db->where('id_remote_access', $this->input->post('editid_remote_access',TRUE));			
		$this->db->update('plugin_remote_access',$data);
	}	
	
	function delRemoteAccess($id)
	{
		$this->db->delete('plugin_remote_access', array('id_remote_access' => $id)); 
	}
	
	function generateUniqueHash() {
		$this->uniquehash->setLength(25);
		return $this->uniquehash->getHash();
	}
	
	function addNotification() {
		$nr = ($this->input->post('notifynumber',TRUE));
		$value = ($this->input->post('notifyvalue',TRUE));

		//TODO - save notify
	}
}
?>
