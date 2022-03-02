<?php
$this->load->helper('html');
echo doctype('xhtml1-trans');?>
<html>

<head>
	<title>Kalkun - <?php echo tr('Log in'); ?></title>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<meta name="generator" content="Geany 0.13" />
	<meta name="robots" content="noindex,nofollow">
	<?php echo link_tag($this->config->item('img_path').'icon.ico', 'shortcut icon', 'image/ico');?>
	<?php echo link_tag($this->config->item('css_path').'base.css');?>
	<script language="javascript" src="<?php echo $this->config->item('js_path');?>jquery-3.6.0.min.js"></script>
	<script language="javascript">
		$(document).ready(function() {
			$("#username").trigger('focus');
		});

	</script>
	<style type="text/css">
		@import url("<?php echo $this->config->item('css_path');?>blue.css");

	</style>
</head>

<body>
	<center>
		<div class="login_loading_container">&nbsp;
			<?php if ($this->session->flashdata('errorlogin')): ?>
			<span class="loading_area"><?php echo $this->session->flashdata('errorlogin');?></span>
			<?php endif; ?>
		</div>

		<div id="login_logo"><a href="<?php echo site_url().'?l='.$idiom ?>"><img src="<?php echo $this->config->item('img_path');?>logo.png" /></a></div>
		<div>
			<?php
				echo form_open('');
				echo form_dropdown('idiom', $language_list, $idiom, 'onchange="this.form.submit()"');
				echo form_hidden('change_language', 'true');
				echo form_close();
			?>
		</div>
		<div id="login_container">
			<?php echo form_open('login'); ?>
			<table id="login" cellpadding="3" cellspacing="2" border="0" class="rounded">
				<tr>
					<td><i><?php echo tr('Please enter your username and password'); ?></i></td>
				</tr>
				<tr>
					<td><label><?php echo tr('Username'); ?></label><input type="text" name="username" id="username" style="width:95%" /></td>
				</tr>
				<tr>
					<td><label><?php echo tr('Password'); ?></label><input type="password" name="password" style="width:95%" /></td>
				</tr>
				<tr>
					<td>
						<div style="float: left">
							<input type="checkbox" id="remember_me" name="remember_me" /><label for="remember_me"><?php echo tr('Remember me'); ?></label>
						</div>
						<div align="center" style="float: right; padding-right: 3px"><input type="submit" id="submit" value="<?php echo tr('Log in') ?>" /></div>
					</td>
				</tr>
				<tr>
					<td align="center"><a style="color: #fff" href="<?php echo site_url('login/forgot_password')."?l=${idiom}"; ?>"><?php echo tr('Forgot your password?'); ?></a></td>
				</tr>
			</table>
			<?php echo form_hidden('idiom', $idiom); ?>
			<?php echo form_close();?>
		</div>

	</center>
</body>

</html>
