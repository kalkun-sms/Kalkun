<div class="two_column_container toolbar">
	<div class="left_column_big">
		<?php if (isset($public_contact) && ! $public_contact):?>
		<a href="#" class="select_all nicebutton"><?php echo tr('Select all');?></a>
		<a href="#" class="clear_all nicebutton"><?php echo tr('Clear all');?></a>
		<a href="javascript:void(0)" class="delete_contact nicebutton"><?php echo tr('Delete');?></a>

		<select name="grp_action" class="grp_action nicebutton" style="width: 100px;">
			<option value="do"><?php echo tr('Action');?></option>
			<option value="null">- <?php echo tr('Add to group');?> -</option>
			<?php
	$group = $this->Phonebook_model->get_phonebook(array('option' => 'group'));
	foreach ($group->result() as $tmp):
	echo "<option value='{$tmp->ID}'> {$tmp->GroupName} </option>";
	endforeach;
	?>
			<option value="null">- <?php echo tr('Delete from group');?> -</option>
			<?php
	$group = $this->Phonebook_model->get_phonebook(array('option' => 'group'));
	foreach ($group->result() as $tmp):
	echo "<option value='-{$tmp->ID}'> {$tmp->GroupName} </option>";
	endforeach;
	?>
		</select>
		<?php endif;?>
	</div>
	<div class="right_column">
		<?php if (empty($_POST));?><div id="simplepaging" class="paging_grey"><?php echo $this->pagination->create_links();?></div>
	</div>
</div>
