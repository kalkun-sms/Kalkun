<table width="100%" cellpadding="5">
	<tr valign="top">
		<td width="175px"><?php echo tr('Theme'); ?></td>
		<td>
			<?php
$theme = array('blue' => 'Blue', 'dark' => 'Dark', 'green' => 'Green');
$theme_act = $this->Kalkun_model->get_setting()->row('theme');
echo form_dropdown('theme', $theme, $theme_act);
?>
		</td>
	</tr>

	<tr valign="top">
		<td><?php echo tr('Background image'); ?></td>
		<td>
			<?php list($bg_act_option, $bg_act) = explode(';', $settings->row('bg_image'));?>
			<input type="radio" id="bg_off" name="bg_image_option" value="false" <?php if ($bg_act_option === 'false')
{
	echo 'checked="checked"';
} ?> />
			<label for="bg_off"><?php echo tr('Disable'); ?></label> <br />
			<input type="radio" id="bg_on" name="bg_image_option" value="true" <?php if ($bg_act_option === 'true')
{
	echo 'checked="checked"';
} ?> />
			<label for="bg_on"><?php echo tr('Enable'); ?></label>
		</td>
	</tr>
</table>
<input type="hidden" name="option" value="appearance" />
