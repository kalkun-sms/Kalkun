<?php
if($group->num_rows()==0):
echo "<p><i>".lang('tni_group_no_group')."</i></p>";
else: ?>
<table>
<?php foreach($group->result() as $tmp): ?>
<tr id="<?php echo $tmp->ID;?>" public="<?php echo $tmp->is_public;?>">
<td>
<div class="two_column_container contact_list">
	<div class="left_column">
	<div id="pbkname">
	<input type="checkbox" class="select_group" />
	<span class="groupname" style="font-weight: bold;"><?php echo anchor('phonebook/group_contacts/'.$tmp->ID,$tmp->GroupName  , 'title="'.$tmp->GroupName .'"');?></span>
	</div>
</div>
<div class="right_column">
<span class="pbk_menu hidden">
<?php if(isset($public_group) && !$public_group):?>
<a class="editpbkgroup simplelink" href="#"><?php echo lang('tni_edit');?></a>
<img src="<?php echo $this->config->item('img_path')?>circle.gif" />
<?php endif;?>	
<a class="sendmessage simplelink" href="#"><?php echo lang('tni_send_message');?></a>
</span>
</td></tr>
<?php endforeach;?>
</table>
<?php endif; ?>
