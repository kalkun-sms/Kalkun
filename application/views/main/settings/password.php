<script language="javascript" src="<?php echo $this->config->item('js_path');?>jquery-plugin/jquery.validate.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
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
		required: "<?php echo lang('kalkun_setting_passwd_current_enter');?>"	
	},
	new_password: {
		required: "<?php echo lang('kalkun_setting_passwd_new_enter');?>",
		minlength: "<?php echo lang('tni_error_toshort');?>"
	},
	confirm_password: { 
		equalTo: "<?php echo lang('tni_error_password_nomatch');?>"
	}
}
});
});
</script>

<table width="100%" cellpadding="5">
<tr valign="top">
<td width="175px" valign="top"><?php echo lang('kalkun_setting_passwd_current');?></td>
<td>
<input type="password" id="current_password" name="current_password" />
<div class="note hidden"><a href="#"><?php echo lang('kalkun_setting_passwd_forgot');?></a></div>
</td>
</tr> 

<tr valign="top">
<td><?php echo lang('kalkun_setting_passwd_new');?></td>
<td><input type="password" id="new_password" name="new_password" /><br />
<small><?php echo lang('kalkun_setting_passwd_valid_rule');?></small></td>
</tr>

<tr valign="top">
<td><?php echo lang('tni_user_conf_password');?></td>
<td><input type="password" id="confirm_password" name="confirm_password" /></td>
</tr>	
</table>
<input type="hidden" name="option" value="password" /> 