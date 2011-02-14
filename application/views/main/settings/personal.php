<table width="100%" cellpadding="5">
<tr valign="top">
<td width="175px">Name</td>
<td>
<input type="text" name="realname" value="<?php echo $settings->row('realname');?>" />
</td>
</tr>

<tr valign="top">
<td>Username</td>
<td>
<input type="text" name="username" value="<?php echo $settings->row('username');?>" />
</td>
</tr>	

<tr valign="top">
<td>Phone number</td>
<td>
<input type="text" name="phone_number" value="<?php echo $settings->row('phone_number');?>" />
</td>
</tr> 

<tr valign="top">
<td>Signature<br /><small>Max. 50 characters</small></td>
<td>
<?php list($sig_option, $sig) = explode(';',$settings->row('signature'));?>
<input type="radio" id="signature_off" name="signatureoption" value="false" 
<?php if($sig_option=='false') echo "checked=\"checked\""; ?>  /> 
<label for="signature_off">Signature Off </label><br />
<input type="radio" id="signature_on" name="signatureoption" value="true"
<?php if($sig_option=='true') echo "checked=\"checked\""; ?> />
<label for="signature_on">Signature On </label><br />
<textarea name="signature" rows="5" cols="40"><?php echo $sig; ?></textarea>
<div class="note">Signature will take place at the end of your message</div>
</td>    
</tr>    
</table>
<input type="hidden" name="option" value="personal" /> 