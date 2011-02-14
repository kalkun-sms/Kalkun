<?php $this->load->view('js_init/phonebook/js_phonebook');
if($phonebook->num_rows()==0):
	if($_POST) echo "<p><i>Contact not found.</i></p>";
	else echo "<p><i>Contact is empty.</i></p>";
else: ?>
<table>
<?php foreach($phonebook->result() as $tmp): ?>
<tr id="<?php echo $tmp->id_pbk;?>">
<td>
<div class="two_column_container contact_list">
	<div class="left_column">
	<div id="pbkname">
	<input type="checkbox" class="select_contact" />&nbsp;<span style="font-weight: bold;"><?php echo $tmp->Name;?></span></div>	
	<div class="hidden" id="pbknumber"><?php echo $tmp->Number;?></div>
</div>
<div class="right_column">
<span class="pbk_menu hidden">
<a class="editpbkcontact simplelink" href="#">Edit</a>
<img src="<?php echo $this->config->item('img_path')?>circle.gif" />
<a class="sendmessage simplelink" href="#">Send message</a>
</span>
</td></tr>
<?php endforeach;?>
</table>
<?php endif; ?>
