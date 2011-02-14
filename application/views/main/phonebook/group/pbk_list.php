<?php
if($group->num_rows()==0):
echo "<p><i>Group is empty.</i></p>";
else: ?>
<table>
<?php foreach($group->result() as $tmp): ?>
<tr id="<?php echo $tmp->ID;?>">
<td>
<div class="two_column_container contact_list">
	<div class="left_column">
	<div id="pbkname">
	<input type="checkbox" class="select_group" />
	<span class="groupname" style="font-weight: bold;"><?php echo $tmp->GroupName;?></span>
	</div>
</div>
<div class="right_column">
<span class="pbk_menu hidden">
<a class="editpbkgroup simplelink" href="#">Edit</a>
<img src="<?php echo $this->config->item('img_path')?>circle.gif" />
<a class="sendmessage simplelink" href="#">Send message</a>
</span>
</td></tr>
<?php endforeach;?>
</table>
<?php endif; ?>
