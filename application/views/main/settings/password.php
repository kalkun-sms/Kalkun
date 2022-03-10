<script language="javascript" src="<?php echo $this->config->item('js_path');?>jquery-plugin/jquery.validate.min.js"></script>
<script type="text/javascript">
	$(document).ready(function() {
		// validation
		$("#settingsForm").validate({
			rules: {
				current_password: {
					required: true
				},
				new_password: {
					required: true,
					minlength: 6
				},
				confirm_password: {
					equalTo: "#new_password"
				},
			},
			messages: {
				current_password: {
					required: "<?php echo tr_addcslashes('"', 'Field required.');?>",
				},
				new_password: {
					required: "<?php echo tr_addcslashes('"', 'Field required.');?>",
					minlength: "<?php echo tr_addcslashes('"', 'Value is too short.');?>"
				},
				confirm_password: {
					equalTo: "<?php echo tr_addcslashes('"', 'Passwords do not match.');?>"
				}
			}
		});
	});

</script>

<table width="100%" cellpadding="5">
	<tr valign="top">
		<td width="175px" valign="top"><?php echo tr('Current password');?></td>
		<td>
			<input type="password" id="current_password" name="current_password" />
			<div class="note hidden"><a href="javascript:void(0)"><?php echo tr('Forgot your password?');?></a></div>
		</td>
	</tr>

	<tr valign="top">
		<td><?php echo tr('New password');?></td>
		<td><input type="password" id="new_password" name="new_password" /><br />
			<small><?php echo tr('Must be at least 6 characters long');?></small>
		</td>
	</tr>

	<tr valign="top">
		<td><?php echo tr('Confirm password');?></td>
		<td><input type="password" id="confirm_password" name="confirm_password" /></td>
	</tr>
</table>
<input type="hidden" name="option" value="password" />
