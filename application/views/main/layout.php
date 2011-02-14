<?php echo doctype('xhtml1-trans');?>
<html>
<head><?php echo $this->load->view('main/header');?></head>
<body>
<?php echo $this->load->view('main/base');?>
<center>
	<div class="loading_container"><span class="loading_area hidden">Loading...</span></div>
	<div id="top_navigation"><?php echo $this->load->view('main/dock');?></div>
	
	<div style="clear: both">&nbsp;</div>
	
	<div id="header">
		<div id="header_left">
			<div id="logo"><img src="<?php echo $this->config->item('img_path');?>logo.png" /></div>
			<div id="app_meta">Open source web based SMS management</div>
		</div>
		<div class="notification_container"><span class="notification_area hidden">Loading...</span>
		<?php if($this->session->flashdata('notif')): ?>
		<span class="notification_area"><?php echo $this->session->flashdata('notif');?></span>
		<?php endif; ?>
		</div>
		<div id="top_link"><?php echo $this->load->view('main/search');?></div>
	</div>
	
	<div id="container">
		<div id="menu"><?php echo $this->load->view('main/menu');?></div>
		<div id="content">
			<div id="compose_sms_container"  title="Compose SMS" class="hidden">&nbsp;</div>
			<?php echo $this->load->view($main);?>
		</div>		
	</div>
	<div id="footer"><?php echo $this->load->view('main/footer');?></div>
</center>
</body>
</html>
