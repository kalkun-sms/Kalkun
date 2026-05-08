<div id="window_container">
	<div id="window_title"><?php echo $title; ?></div>
	<div id="window_content">
		<?php echo form_open('plugin/sms_to_email/save', array('id' => 'settingsForm'));?>
		<table class="sms_to_email">
			<tr>
				<td><?php echo tr('Enable email forwarding'); ?></td>
				<td>
					<?php
$email_forward = array('true' => tr('Yes'), 'false' => tr('No'));
if ($settings->num_rows() === 1)
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

			<tr>
				<td><?php echo tr('Email ID'); ?></td>
				<td>
					<input type="text" name="email_id" class="email" value="<?php if ($settings->num_rows() === 1)
{
	echo htmlentities($settings->row('email_id'), ENT_QUOTES);
}?>" />
				</td>
			</tr>
		</table>
		<br>
		<input type="hidden" name="mode" value="<?php echo htmlentities($mode, ENT_QUOTES);?>" />
		<div style="text-align: center"><input type="submit" id="submit" value="<?php echo tr('Save'); ?>" /></div>
		<?php echo form_close();?>

	</div>
</div>
