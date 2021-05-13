<?php 
$this->load->helper('kalkun');
// echo lang('tni_status').": ";
// Get signal and battery value
$signal = $this->Kalkun_model->get_gammu_info('phone_signal')->row('Signal'); 
//$battery = $this->Kalkun_model->get_gammu_info('phone_battery')->row('Battery');

$status = $this->Kalkun_model->get_gammu_info('last_activity')->row('UpdatedInDB');
if($status!=NULL) {
	$status = get_modem_status($status, $this->config->item("modem_tolerant"));
	
// 	if($status=="connect") echo "<span class=\"good\">".lang('tni_connected')."</span>";
// 	else echo "<span class=\"warning\">".lang('tni_disconnected')."</span>";
if ($status=="connect") {
	$siglevel = 1;
	if ($signal > 75) $siglevel = 4;
	elseif ($signal > 50) $siglevel = 3;
  elseif ($signal > 25) $siglevel = 2;
}
else $siglevel = 0;
echo "<div role=\"img\" alt=\"Signal Strength: ".$signal."%\" class=\"signal-icon signal".$siglevel."\">";
	?>
  <div class="signal-bar bar1"></div>
  <div class="signal-bar bar2"></div>
  <div class="signal-bar bar3"></div>
  <div class="signal-bar bar4"></div>
  <div class="sizer"></div>
</div>
<?php
}
else echo "Unknown";
?>

<?php
//if($status)
// echo "Signal: ".$signal."%  Battery: ".$battery."%";
?>