<?php
$this->load->helper('html');
echo doctype('xhtml1-trans');?>
<html>

<head><?php $this->load->view('main/header');?></head>

<body>
	<?php $this->load->view('main/base');?>

	<div class="loading_container"><span class="loading_area hidden"><?php echo tr('Loading');?>...</span></div>
	<div id="top_navigation"><?php $this->load->view('main/dock');?></div>

	<div id="main_container">
		<div id="header">
			<div id="header_left">
				<div id="logo"><a href="javascript:void(0);"><img src="<?php echo $this->config->item('img_path');?>logo.png" /></a></div>

			</div>
			<div id="header_right">
				<div id="top_link"><?php $this->load->view('main/search');?></div>
				<div class="clear">&nbsp;</div>
				<div class="notification_container" align="center"><span class="notification_area hidden"><?php echo tr('Loading');?>...</span>
					<?php if ($this->session->flashdata('notif')): ?>
					<span class="notification_area"><?php echo $this->session->flashdata('notif');?></span>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div id="container">
			<div id="menu"><?php $this->load->view('main/menu');?></div>
			<div id="content">
				<div id="compose_sms_container" title="<?php echo tr('Compose SMS'); ?>" class="hidden">&nbsp;</div>
				<?php $this->load->view($main);?>
			</div>
		</div>
		<div id="footer"><?php $this->load->view('main/footer');?></div>
	</div>

</body>

</html>
