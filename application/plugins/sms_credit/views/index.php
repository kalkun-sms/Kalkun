<?php $this->load->view('js_users');?>

<div id="window_container">
	<div id="window_title">
		<div id="window_title_left"><?php echo $title;?></div>
		<div id="window_title_right">
			<?php echo form_open('plugin/sms_credit', array('class' => 'search_form')); ?>
			<input type="text" name="search_name" size="20" class="search_name" value="<?php if (isset($query))
{
	echo htmlentities($query, ENT_QUOTES);
}?>" />
			<?php echo form_close(); ?>
			&nbsp;
			<a href="<?php echo site_url('plugin/sms_credit/packages');?>" class="nicebutton"><?php echo tr('Packages'); ?></a>
			<a href="#" class="nicebutton addpbkcontact">&#43; <?php echo tr('Add user');?></a>
		</div>
	</div>

	<div id="window_content">
		<?php $this->load->view('navigation');?>
		<table>
			<?php foreach ($users->result() as $tmp): ?>
			<tr id="<?php echo htmlentities($tmp->id_user, ENT_QUOTES);?>">
				<td>
					<div class="two_column_container contact_list">
						<div class="left_column">
							<div id="pbkname">
								<span style="font-weight: bold;"><?php echo htmlentities($tmp->realname, ENT_QUOTES);?></span>
								<?php if ( ! is_null($tmp->template_name)): echo '<sup>( '.htmlentities($tmp->template_name, ENT_QUOTES).' )</sup>'; ?>
								<?php else: echo '<sup>( '.tr('No package').' )</sup>'; ?>
								<?php endif;?>
							</div>
						</div>

						<div class="right_column">
							<span class="pbk_menu">
								<a class="delete_user simplelink" href="<?php echo site_url('plugin/sms_credit/delete_users/'.$tmp->id_user);?>"><?php echo tr('Delete'); ?></a>
								<img src="<?php echo $this->config->item('img_path')?>circle.gif" />
								<a class="edit_user simplelink" href="#"><?php echo tr('Edit'); ?></a>
							</span>
						</div>

						<div class="hidden">
							<span class="id_package"><?php echo htmlentities($tmp->id_credit_template, ENT_QUOTES);?></span>
							<span class="package_start"><?php echo htmlentities($tmp->valid_start, ENT_QUOTES);?></span>
							<span class="package_end"><?php echo htmlentities($tmp->valid_end, ENT_QUOTES);?></span>
						</div>
					</div>
				</td>
			</tr>
			<?php endforeach;?>
		</table>
		<?php $this->load->view('navigation');?>
	</div>
</div>

<!-- Add User dialog -->
<div id="users_container" class="dialog" style="display: none">
	<p id="validateTips"><?php echo tr('All form fields are required.'); ?></p>
	<?php echo form_open('plugin/sms_credit/add_users', array('id' => 'addUser'));?>
	<fieldset>
		<label for="name"><?php echo tr('Name'); ?></label>
		<input type="text" name="realname" id="realname" class="text ui-widget-content ui-corner-all" />
		<label for="number"><?php echo tr('Phone number'); ?></label>
		<input type="text" name="phone_number" id="phone_number" class="text ui-widget-content ui-corner-all" />
		<label for="name"><?php echo tr('Username'); ?></label>
		<input type="text" name="username" id="username" class="text ui-widget-content ui-corner-all" />
		<label for="password"><?php echo tr('Password'); ?></label>
		<input type="password" name="password" id="password" class="text ui-widget-content ui-corner-all" />
		<label for="confirm_password"><?php echo tr('Confirm password'); ?></label>
		<input type="password" name="confirm_password" id="confirm_password" class="text ui-widget-content ui-corner-all" />
		<label for="level"><?php echo tr('Role'); ?></label>
		<?php
$level = array('admin' => tr('Administrator'), 'user' => tr('User', 'credentials'));
$option = 'class="text ui-widget-content ui-corner-all"';
echo form_dropdown('level', $level, 'user', $option);
?>
		<br /><br />

		<label for="package"><?php echo tr('Package'); ?></label>
		<?php
foreach ($packages->result_array() as $row)
{
	$package[$row['id_credit_template']] = htmlentities($row['template_name'], ENT_QUOTES);
}
$option = 'class="text ui-widget-content ui-corner-all"';
echo form_dropdown('package', $package, '', $option);
?>
		<br /><br />

		<label for="package_start"><?php echo tr('Start date'); ?></label>
		<input type="text" style="display: inline; width: 80%" name="package_start" id="package_start" class="text datepicker ui-widget-content ui-corner-all" />

		<label for="package_end"><?php echo tr('End date'); ?></label>
		<input type="text" style="display: inline; width: 80%" name="package_end" id="package_end" class="text datepicker ui-widget-content ui-corner-all" />
	</fieldset>
	<?php echo form_close();?>
</div>


<!-- Edit User dialog -->
<div id="edit_users_container" class="dialog" style="display: none">
	<p id="validateTips"><?php echo tr('All form fields are required.'); ?></p>
	<?php echo form_open('plugin/sms_credit/add_users', array('id' => 'editUser'));?>
	<fieldset>
		<label for="package"><?php echo tr('Package'); ?></label>
		<?php
foreach ($packages->result_array() as $row)
{
	$package[$row['id_credit_template']] = htmlentities($row['template_name'], ENT_QUOTES);
}
$option = 'id="edit_id_package" class="text ui-widget-content ui-corner-all"';
echo form_dropdown('package', $package, '', $option);
?>
		<br /><br />

		<label for="package_start"><?php echo tr('Start date'); ?></label>
		<input type="text" style="display: inline; width: 80%" name="package_start" id="edit_package_start" class="text datepicker ui-widget-content ui-corner-all" />

		<label for="package_end"><?php echo tr('End date'); ?></label>
		<input type="text" style="display: inline; width: 80%" name="package_end" id="edit_package_end" class="text datepicker ui-widget-content ui-corner-all" />

		<input type="hidden" name="id_user" id="id_user" />
	</fieldset>
	<?php echo form_close();?>
</div>

<!-- Delete User Confirmation Dialog -->
<div class="dialog" id="confirm_delete_user_dialog" title="Delete Users Confirmation">
	<p>
		<span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
		<?php echo tr('Are you sure you want to delete this user?'); ?>
	</p>
</div>
