<?php 
$this->load->helper('kalkun');
echo "<strong>".lang('tni_status').": ";
// Get signal and battery value
$signal = $this->Kalkun_model->get_gammu_info('phone_signal')->row('Signal'); 
//$battery = $this->Kalkun_model->get_gammu_info('phone_battery')->row('Battery');

$status = $this->Kalkun_model->get_gammu_info('last_activity')->row('UpdatedInDB');
if($status!=NULL) {
	$status = get_modem_status($status, $this->config->item("modem_tolerant"));
	
	if($status=="connect") echo "<span class=\"good\">".lang('tni_connected')."</span>";
	else echo "<span class=\"warning\">".lang('tni_disconnected')."</span>";
}
else echo "<span class=\"yellow\">Unknown</span>";
echo " | Signal: ";
if ($signal >= 80) echo '<span class="good">%'.$signal.'</span>';
elseif ($signal >= 50 AND $signal <= 79) echo '<span class="yellow">%'.$signal.'</span>';
elseif ($signal <= 49) echo '<span class="warning">%'.$signal.'</span>';
echo "</strong>";
?>
