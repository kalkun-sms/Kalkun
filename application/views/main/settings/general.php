<table width="100%" cellpadding="5">
	<tr valign="top">
		<td width="175px"><?php echo tr('Language'); ?></td>
		<td>
			<?php
$lang = $this->lang->kalkun_supported_languages();
$lang_act = $this->Kalkun_model->get_setting()->row('language');
echo form_dropdown('language', $lang, $lang_act);
?>
		</td>
	</tr>

	<tr valign="top">
		<td><?php echo tr('Country calling code'); ?></td>
		<td>
			<?php
$dial_code = getCountryDialCode();
$dial_code_act = $this->Kalkun_model->get_setting()->row('country_code');
echo form_dropdown('dial_code', $dial_code, $dial_code_act);
?>
		</td>
	</tr>

	<tr valign="top">
		<td><?php echo tr('Conversation sort'); ?></td>
		<td>
			<?php
$conv = array('asc' => tr('Oldest first'), 'desc' => tr('Newest first'));
$conv_act = $this->Kalkun_model->get_setting()->row('conversation_sort');
echo form_dropdown('conversation_sort', $conv, $conv_act);
?>
		</td>
	</tr>

	<tr valign="top">
		<td><?php echo tr('Data per page'); ?></td>
		<td>
			<?php
$paging = array('10' => '10', '15' => '15', '20' => '20', '25' => '25');
$paging_act = $this->Kalkun_model->get_setting()->row('paging');
echo form_dropdown('paging', $paging, $paging_act);
?>
			<small>&nbsp;&nbsp;<?php echo tr('Used for paging in message and phonebook'); ?></small>
		</td>
	</tr>

	<tr valign="top">
		<td><?php echo tr('Permanent delete'); ?></td>
		<td>
			<?php $permanent_act = $this->Kalkun_model->get_setting()->row('permanent_delete');?>
			<input type="radio" id="permanent_delete_false" name="permanent_delete" value="false" <?php if ($permanent_act == 'false')
{
	echo 'checked="checked"';
} ?> />
			<label for="permanent_delete_false"><?php echo tr('Disable'); ?></label> <small><?php echo tr(' - Always move to trash first'); ?></small><br />
			<input type="radio" id="permanent_delete_true" name="permanent_delete" value="true" <?php if ($permanent_act == 'true')
{
	echo 'checked="checked"';
} ?> />
			<label for="permanent_delete_true"><?php echo tr('Enable'); ?></label>
		</td>
	</tr>

	<tr valign="top">
		<td><?php echo tr('Delivery Report'); ?></td>
		<td>
			<?php
$report = array('default' => tr('Default'), 'yes' => tr('Yes'), 'no' => tr('No'));
$report_act = $this->Kalkun_model->get_setting()->row('delivery_report');
echo form_dropdown('delivery_report', $report, $report_act);
?>
		</td>
	</tr>

</table>
<input type="hidden" name="option" value="general" />
