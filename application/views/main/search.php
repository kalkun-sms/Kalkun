<?php 
$this->load->helper('form');
if($this->uri->segment(1)=='phonebook') :
echo form_open("phonebook", array('class' => 'sms_search_form')); ?>
<table border="0" cellpadding="0" cellspacing="0">
<tr valign="top">
<td><input type="text" name="search_name"  id="search" value="<?php if (isset($search_string)) echo $search_string;?>" class="ui-corner-left" /></td> 
<td><input type="submit" value="<?php echo lang('tni_search_contacts'); ?>" /></td>
</tr>
</table>
<?php echo form_close();
else: 
echo form_open("messages/query", array('class' => 'sms_search_form')); ?>
<table border="0" cellpadding="0" cellspacing="0">
<tr valign="top">
<td><input type="text" name="search_sms" id="search"  value="<?php if (isset($search_string)) echo $search_string;?>" class="ui-corner-left" /></td> 
<td><input type="submit" value="<?php echo lang('tni_search_sms');?>" /></td>
<td valign="middle"><div style="margin-left: 5px"><small><a style="text-decoration: underline" id="a_search" href="#"><?php echo lang('kalkun_advanced_search');?></a></small></div></td>
</tr>
</table>
<?php echo form_close(); endif; ?>