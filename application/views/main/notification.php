
<?php 
echo "Status: ";
// Get signal and battery value
//$signal = $this->Kalkun_model->getGammuInfo('phone_signal')->row('Signal'); 
//$battery = $this->Kalkun_model->getGammuInfo('phone_battery')->row('Battery');

$status = $this->Kalkun_model->getGammuInfo('last_activity')->row('UpdatedInDB');
if($status!=NULL) {
	$status = get_modem_status($status, $this->config->item("modem_tolerant"));
	
	if($status=="connect") echo "<span style=\"color: green\">Connected</span>";
	else echo "<span style=\"color: red\">Disconnected</span>";
}
else echo "Unknown";
?>

<?php
//if($status)
//echo "Signal: ".$signal."%  Battery: ".$battery."%";
?>