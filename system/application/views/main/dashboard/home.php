<div id="space_area">

<div id="dash_box_titlebar"><?php echo "Statistic";?></div>
<div id="dash_box">
	<?php $this->load->view('main/dashboard/statistic');?>
</div>
<br />

<?php if($this->session->userdata('level')=='admin'): ?>
<div id="dash_box_titlebar"><?php echo lang('kalkun_system_information');?></div>
<div id="dash_box">
<table>
    <tr>
        <td width="125px"><?php echo lang('kalkun_operating_system');?></td>
        <td width="25px">:</td>
        <td><?php echo  filter_data(PHP_OS); ?></td>
    </tr>
    <tr valign="top">
        <td><?php echo lang('kalkun_gammu_version');?></td>
        <td>:</td>
        <td><?php echo  filter_data($this->Kalkun_model->getGammuInfo('gammu_version')->row('Client')); ?></td>
    </tr>
    <tr valign="top">
        <td><?php echo lang('kalkun_gammu_db_schema');?></td>
        <td>:</td>
        <td><?php echo  filter_data($this->Kalkun_model->getGammuInfo('db_version')->row('Version')); ?></td>
    </tr>       
    <tr valign="top">
        <td><?php echo lang('kalkun_phone_imei');?></td>
        <td>:</td>
        <td><?php echo  filter_data($this->Kalkun_model->getGammuInfo('phone_imei')->row('IMEI')); ?></td>
    </tr>        
</table>
</div>
</div>
<?php endif;?>