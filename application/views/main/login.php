<?php echo doctype('xhtml1-trans');?>
<html>
<head>
<title>Kalkun / Login</title>
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<meta name="generator" content="Geany 0.13" />
<?php echo link_tag($this->config->item('img_path').'icon.ico', 'shortcut icon', 'image/ico');?>
<?php echo link_tag($this->config->item('css_path').'base.css');?>
<script language="javascript" src="<?php echo $this->config->item('js_path');?>jquery-1.3.2.min.js"></script>
<script language="javascript">
$(document).ready(function(){
$("#username").focus();
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
<?php echo form_open('login'); ?>
<table id="login" cellpadding="3" cellspacing="2" border="0"  class="rounded">
<tr><td><i>Please enter your username and password</i></td></tr>
<tr><td><label>Username</label><input type="text" name="username" id="username" style="width:95%" /></td></tr>
<tr><td><label>Password</label><input type="password" name="password" style="width:95%" /></td></tr>
<tr><td><div style="float: left" class="hidden">
<input type="checkbox" id="remember" name="remember" /><label for="remember">Remember me</label></div>
<div align="center"><input type="submit" id="submit" value="Login" /></div></td></tr>
</table>
<?php echo form_close();?>
</div>

</center>
</body>
</html>
