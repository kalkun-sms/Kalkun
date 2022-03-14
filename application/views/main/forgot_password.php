<?php
$this->load->helper('html');
echo doctype('xhtml1-trans');?>
<html>

<head>
	<title>Kalkun - <?php echo tr('Forgot your password?'); ?></title>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<meta name="generator" content="Geany 0.13" />
	<meta name="robots" content="noindex,nofollow">
	<meta name="viewport" content="width=device-width, initial-scale=1">
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
			<span class="loading_area"><?php echo htmlentities($this->session->flashdata('errorlogin'), ENT_QUOTES);?></span>
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
			<?php echo form_open('login/forgot_password'); ?>
			<table id="login" cellpadding="3" cellspacing="2" border="0" class="rounded">
				<tr>
					<td><big><?php echo tr('Forgot your password?'); ?></big></td>
				</tr>
				<tr>
					<td><label><?php echo tr('Username'); ?></label><input type="text" name="username" id="username" style="width:95%" /></td>
				</tr>
				<tr>
					<td>-- <?php echo strtoupper(tr('or')); ?> --</td>
				</tr>
				<tr>
					<td><label><?php echo tr('Phone number'); ?></label><input type="text" name="phone" style="width:95%" /></td>
				</tr>
				<tr>
					<td>
						<div align="center" style="float: right; padding-right: 3px"><input type="submit" id="submit" value="<?php echo tr('Submit', 'form') ?>" /></div>
					</td>
				</tr>
			</table>
			<?php echo form_hidden('idiom', $idiom); ?>
			<?php echo form_close();?>
		</div>

	</center>
</body>

</html>
