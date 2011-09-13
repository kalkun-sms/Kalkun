<?php 
$this->load->helper('kalkun');
echo lang('tni_status').": ";
// Get signal and battery value
//$signal = $this->Kalkun_model->get_gammu_info('phone_signal')->row('Signal'); 
//$battery = $this->Kalkun_model->get_gammu_info('phone_battery')->row('Battery');

$status = $this->Kalkun_model->get_gammu_info('last_activity')->row('UpdatedInDB');
if($status!=NULL) {
	$status = get_modem_status($status, $this->config->item("modem_tolerant"));
	
	if($status=="connect") echo "<span class=\"good\">".lang('tni_connected')."</span>";
	else echo "<span class=\"warning\">".lang('tni_disconnected')."</span>";
}
else echo "Unknown";
?>

<?php
//if($status)
//echo "Signal: ".$signal."%  Battery: ".$battery."%";
?>