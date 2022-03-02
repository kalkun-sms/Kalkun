<?php $this->load->helper('kalkun'); ?>
<div id="space_area">

	<?php if (isset($alerts) && count($alerts) > 0): ?>
	<div id="dash_box_titlebar"><?php echo tr('Alerts');?></div>
	<div id="dash_box">
		<?php
foreach ($alerts as $msg):
   echo $msg;
endforeach;
?>
	</div>
	<br />
	<?php endif; ?>

	<div id="dash_box_titlebar"><?php echo tr('Statistics');?></div>
	<div id="dash_box">
		<?php $this->load->view('main/dashboard/statistic');?>
	</div>
	<br />

	<?php if ($this->session->userdata('level') === 'admin'): ?>
	<div id="dash_box_titlebar"><?php echo tr('System information');?></div>
	<div id="dash_box">
		<table>
			<tr>
				<td width="125px"><?php echo tr('Operating system');?></td>
				<td width="25px">:</td>
				<td><?php echo  filter_data(PHP_OS); ?></td>
			</tr>
			<tr valign="top">
				<td><?php echo tr('Gammu version');?></td>
				<td>:</td>
				<td><?php echo  filter_data($this->Kalkun_model->get_gammu_info('gammu_version')->row('Client')); ?></td>
			</tr>
			<tr valign="top">
				<td><?php echo tr('Gammu DB schema');?></td>
				<td>:</td>
				<td><?php echo  filter_data($this->Kalkun_model->get_gammu_info('db_version')->row('Version')); ?></td>
			</tr>
			<tr valign="top">
				<td><?php echo tr('Modem IMEI');?></td>
				<td>:</td>
				<td><?php echo  filter_data($this->Kalkun_model->get_gammu_info('phone_imei')->row('IMEI')); ?></td>
			</tr>
		</table>
	</div>
</div>
<?php endif;?>
