<div id="window_container">
<div id="window_title"><?php echo $title; ?></div>
<div id="window_content">
<?php echo form_open('plugin/sms_to_email/save', array('id' => 'settingsForm'));?>
<table width="100%" cellpadding="5">
<tr valign="top">
<td width="175px"><?php echo lang('tni_email_forward'); ?></td>
<td>
<?php 
$email_forward = array('true' => lang('tni_yes'), 'false' => lang('tni_no'));
if($settings->num_rows()==1)
{
	$email_forward_act = $settings->row('email_forward'); 
}
else
{
	$email_forward_act = 'false';	
}
echo form_dropdown('email_forward', $email_forward, $email_forward_act);  
?>
</td>
</tr>

<tr valign="top">
<td><?php echo lang('tni_email_address'); ?></td>
<td>
<input type="text" name="email_id" class="email" value="<?php if($settings->num_rows()==1) echo $settings->row('email_id');?>" />
</td>
</tr>
</table>
<br />
<input type="hidden" name="mode" value="<?php echo $mode;?>" /> 
<div align="center"><input type="submit" id="submit" value="<?php echo lang('kalkun_save'); ?>" /></div>
<?php echo form_close();?>

</div>