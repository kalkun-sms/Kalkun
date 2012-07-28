<?php 
$this->load->helper('html');
echo doctype('xhtml1-trans');?>
<html>
<head>
<title>Kalkun / Password Reset</title>
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<meta name="generator" content="Geany 0.13" />
<meta name="robots" content="noindex,nofollow">
<?php echo link_tag($this->config->item('img_path').'icon.ico', 'shortcut icon', 'image/ico');?>
<?php echo link_tag($this->config->item('css_path').'base.css');?>
<script language="javascript" src="<?php echo $this->config->item('js_path');?>jquery-1.6.2.min.js"></script>
<script language="javascript">
$(document).ready(function(){
$("#new_password").focus();
});
</script>
<style type="text/css">
@import url("<?php echo $this->config->item('css_path');?>blue.css");
</style>	
</head>

<body>
<center>
<div class="login_loading_container">&nbsp;
<?php if($this->session->flashdata('errorlogin')): ?>
<span class="loading_area"><?php echo $this->session->flashdata('errorlogin');?></span>
<?php endif; ?>
</div>

<div id="login_logo"><img src="<?php echo $this->config->item('img_path');?>logo.png" /></div>
<div id="login_container">
<?php echo form_open('login/password_reset'); ?>
<table id="login" cellpadding="3" cellspacing="2" border="0"  class="rounded">
<tr><td><i>Choose your new password</i></td></tr>
<tr><td><label>New Password</label><input type="password" name="new_password" id="new_password" style="width:95%" /></td></tr>
<tr><td><label>Verify Password</label><input type="password" name="password" style="width:95%" /></td></tr>
<tr><td><div align="center" style="float: right; padding-right: 3px"><input type="submit" id="submit" value="Submit" /></div></td></tr>
</table>
<?php echo form_hidden('token', $token); ?>
<?php echo form_close();?>
</div>

</center>
</body>
</html>
