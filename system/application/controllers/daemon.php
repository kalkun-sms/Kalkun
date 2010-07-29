<?php
Class Daemon extends Controller {
				
	function Daemon()
	{	
		// Commented this for allow access from other machine
		// if($_SERVER['REMOTE_ADDR']!='127.0.0.1') exit("Access Denied.");		
						
		parent::controller();
	}
	
	// ================================================================
	// SERVER ALERT
	// ================================================================
	
	function server_alert_engine()
	{
		// check plugin status
		$tmp_stat = $this->Plugin_model->getPluginStatus('server_alert');
		
		if($tmp_stat=='true')
		{
			$tmp_data = $this->Plugin_model->getServerAlert('active');
			foreach($tmp_data->result() as $tmp):
				$fp = fsockopen($tmp->ip_address, $tmp->port_number, $errno, $errstr, 60);
				if(!$fp)
				{
					$data['message'] = $tmp->respond_message."\n\nKalkun Server Alert";
					$data['date'] = date('Y-m-d H:i:s');
					$data['dest'] = $tmp->phone_number;
					$data['delivery_report'] = $this->Kalkun_model->getSetting('delivery_report', 'value')->row('value');
					$data['class'] = '1';
					
					$this->Message_model->sendMessages($data);
					log_message('info', 'Kalkun server alert=> Alert Name: '.$tmp->alert_name.', Dest: '.$tmp->phone_number);
					$this->Plugin_model->changeState($tmp->id_server_alert, 'false');
				} 
			endforeach;
		}
	}
}
?>
