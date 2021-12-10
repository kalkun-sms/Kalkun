<?php $this->load->view('js_init/users/js_add_user');?>

<!-- User dialog -->
<div id="dialog" class="dialog" style="display: block">
	<p id="validateTips"><?php echo tr('All form fields are required'); ?></p>
	<?php echo form_open('phonebook/add_user_process', array('id' => 'addUser'));?>
	<fieldset>
		<label for="name"><?php echo tr('Name'); ?></label>
		<input type="text" name="realname" id="realname" value="<?php if (isset($users))
{
	echo $users->row('realname');
}?>" class="text ui-widget-content ui-corner-all" />
		<label for="number"><?php echo tr('Phone number'); ?></label>
		<input type="text" name="phone_number" id="phone_number" value="<?php if (isset($users))
{
	echo $users->row('phone_number');
}?>" class="text ui-widget-content ui-corner-all" />
		<label for="name"><?php echo tr('Username'); ?></label>
		<input type="text" name="username" id="username" value="<?php if (isset($users))
{
	echo $users->row('username');
}?>" class="text ui-widget-content ui-corner-all" />

		<?php if ( ! isset($users)): ?>
		<label for="password"><?php echo tr('Password'); ?></label>
		<input type="password" name="password" id="password" value="" class="text ui-widget-content ui-corner-all" />
		<label for="confirm_password"><?php echo tr('Confirm password'); ?></label>
		<input type="password" name="confirm_password" id="confirm_password" value="" class="text ui-widget-content ui-corner-all" />
		<?php endif;?>

		<label for="level"><?php echo tr('Level'); ?></label>
		<?php
$level = array('admin' => tr('Administrator'), 'user' => tr('User'));
$level_act = (isset($users)) ? $users->row('level') : '';
$option = 'class="text ui-widget-content ui-corner-all"';
echo form_dropdown('level', $level, $level_act, $option);
?>

		<?php if (isset($users)): ?>
		<input type="hidden" name="id_user" id="id_user" value="<?php echo $users->row('id_user');?>" />
		<?php endif;?>
	</fieldset>
	<?php echo form_close();?>
</div>
