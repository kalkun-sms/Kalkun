<script language="javascript" src="<?php echo $this->config->item('js_path');?>jquery-plugin/jquery.validate.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
// validation
$("#settingsForm").validate();
});
</script>
<table width="100%" cellpadding="5">
<tr valign="top">
<td width="175px"><?php echo lang('tni_contact_name'); ?></td>
<td>
<input type="text" name="realname" value="<?php echo $settings->row('realname');?>" />
</td>
</tr>

<tr valign="top">
<td><?php echo lang('tni_user_username'); ?></td>
<td>
<input type="text" name="username" value="<?php echo $settings->row('username');?>" />
</td>
</tr>	

<tr valign="top">
<td><?php echo lang('tni_contact_phonenumber'); ?></td>
<td>
<input type="text" name="phone_number" value="<?php echo $settings->row('phone_number');?>" />
</td>
</tr> 

<tr valign="top">
<td><?php echo lang('tni_signature'); ?><br /><small><?php echo lang('tni_signature_hint'); ?></small></td>
<td>
<?php list($sig_option, $sig) = explode(';',$settings->row('signature'));?>
<input type="radio" id="signature_off" name="signatureoption" value="false" 
<?php if($sig_option=='false') echo "checked=\"checked\""; ?>  /> 
<label for="signature_off"><?php echo lang('tni_signature_off'); ?></label><br />
<input type="radio" id="signature_on" name="signatureoption" value="true"
<?php if($sig_option=='true') echo "checked=\"checked\""; ?> />
<label for="signature_on"><?php echo lang('tni_signature_on'); ?></label><br />
<textarea name="signature" rows="5" cols="40"><?php echo $sig; ?></textarea>
<div class="note"><?php echo lang('tni_signature_hintb'); ?></div>
</td>    
</tr>    
</table>
<input type="hidden" name="option" value="personal" /> 