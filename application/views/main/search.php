<?php if($this->uri->segment(1) == 'messages'): ?>
<?php echo form_open("messages/search/results/all", array('class' => 'sms_search_form')); ?>
<table border="0" cellpadding="0" cellspacing="0">
<tr valign="top">
<td><input type="text" name="search_sms" value="<?php if (isset($search_string)) echo $search_string;?>" /></td>
<td><input type="submit" value="Search Message" /></td>
</tr>
</table>
<?php echo form_close(); ?>
<?php endif; ?>