<div class="two_column_container toolbar">
<div class="left_column_big">
<?php if(isset($public_contact) && !$public_contact):?>
	<a href="#" class="select_all nicebutton"><?php echo lang('kalkun_select_all');?></a>	
	<a href="#" class="clear_all nicebutton"><?php echo lang('kalkun_clear_all');?></a>
	<a href="javascript:void(0)" class="delete_contact nicebutton"><?php echo lang('kalkun_delete');?></a>	
 	
  	<select name="grp_action" class="grp_action nicebutton" style="width: 100px;">
	<option value="do"><?php echo lang('kalkun_action');?></option>
	<option value="null">- <?php echo lang('kalkun_add_to_group');?> -</option>
	<?php
	$group = $this->Phonebook_model->get_phonebook(array('option' => 'group'));
	foreach($group->result() as $tmp):
	echo "<option value='{$tmp->ID}'> {$tmp->GroupName} </option>";
	endforeach; 
	?>
	<option value="null">- <?php echo lang('kalkun_delete_from_group');?> -</option>
	<?php
	$group = $this->Phonebook_model->get_phonebook(array('option' => 'group'));
	foreach($group->result() as $tmp):
	echo "<option value='-{$tmp->ID}'> {$tmp->GroupName} </option>";
	endforeach; 
	?>
	</select>
<?php endif;?>
</div>
<div class="right_column">
	<?php if(empty($_POST));?><div id="simplepaging" class="paging_grey"><?php echo $this->pagination->create_links();?></div>
</div>
</div>