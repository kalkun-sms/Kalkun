<?php
$this->load->helper('form');
if ($this->uri->segment(1) == 'phonebook') :
echo form_open('phonebook', array('class' => 'sms_search_form')); ?>
<table border="0" cellpadding="0" cellspacing="0">
	<tr valign="top">
		<td><input type="text" name="search_name" id="search" value="<?php if (isset($search_string))
{
	echo htmlentities($search_string, ENT_QUOTES);
}?>" class="ui-corner-left" /></td>
		<td><input type="submit" value="<?php echo tr('Search contacts'); ?>" /></td>
	</tr>
</table>
<?php echo form_close();
else:
echo form_open('messages/query', array('class' => 'sms_search_form')); ?>
<table border="0" cellpadding="0" cellspacing="0">
	<tr valign="top">
		<td><input type="text" name="search_sms" id="search" value="<?php if (isset($search_string))
{
	echo htmlentities(urldecode($search_string), ENT_QUOTES);
}?>" class="ui-corner-left" /></td>
		<td><input type="submit" value="<?php echo tr('Search messages');?>" /></td>
		<td valign="middle">
			<div style="margin-left: 5px"><small><a style="text-decoration: underline" id="a_search" href="#"><?php echo tr('Advanced search');?></a></small></div>
		</td>
	</tr>
</table>
<?php echo form_close(); endif; ?>
