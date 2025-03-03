<?php $this->load->helper('kalkun'); ?>
<div id="space_area">

	<?php if (isset($alerts) && count($alerts) > 0): ?>
	<div class="dash_box_titlebar"><?php echo tr('Alerts');?></div>
	<div class="dash_box">
		<?php
foreach ($alerts as $msg):
   echo '<div class="warning">'.htmlentities($msg, ENT_QUOTES).'</div>';;
endforeach;
?>
	</div>
	<br />
	<?php endif; ?>

	<div class="dash_box_titlebar"><?php echo tr('Statistics');?></div>
	<div class="dash_box">
		<?php $this->load->view('main/dashboard/statistic');?>
	</div>
	<br />

	<?php if ($this->session->userdata('level') === 'admin'): ?>
	<div class="dash_box_titlebar"><?php echo tr('System information');?></div>
	<div class="dash_box">
		<table class="sysinfo">
			<tr>
				<td><?php echo tr('Operating system');?></td>
				<td>:</td>
				<td><?php echo  filter_data(PHP_OS); ?></td>
			</tr>
			<tr>
				<td><?php echo tr('Gammu version');?></td>
				<td>:</td>
				<td><?php echo  filter_data(htmlentities($this->Kalkun_model->get_gammu_info('gammu_version')->row('Client') !== NULL ? $this->Kalkun_model->get_gammu_info('gammu_version')->row('Client') : ''), ENT_QUOTES); ?></td>
			</tr>
			<tr>
				<td><?php echo tr('Gammu DB schema');?></td>
				<td>:</td>
				<td><?php echo  filter_data(htmlentities($this->Kalkun_model->get_gammu_info('db_version')->row('Version')), ENT_QUOTES); ?></td>
			</tr>
			<tr>
				<td><?php echo tr('Modem IMEI');?></td>
				<td>:</td>
				<td><?php echo  filter_data(htmlentities($this->Kalkun_model->get_gammu_info('phone_imei')->row('IMEI') !== NULL ? $this->Kalkun_model->get_gammu_info('phone_imei')->row('IMEI') : ''), ENT_QUOTES); ?></td>
			</tr>
		</table>
	</div>
</div>
<?php endif;?>
