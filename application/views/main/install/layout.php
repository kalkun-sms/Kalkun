<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
<title>Kalkun &rsaquo; Installation</title>
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<meta name="generator" content="Geany 0.13" />
<link rel="shortcut icon" href="<?php echo $this->config->item('img_path');?>icon.ico" type="image/x-icon" />
<link type="text/css" rel="stylesheet" href="<?php echo $this->config->item('css_path');?>install.css" />
<script language="javascript" src="<?php echo $this->config->item('js_path');?>jquery-1.6.2.min.js"></script>
<script language="javascript">

$(document).ready(function(){
var left = $('div#left-container').height();
var right = $('div#right-container').height();

if(left>right) 
{
	$('div#right-container').height(left);
}

// Step highlight
var step = '<?php echo $this->uri->segment(2);?>';
if(step=='')
{
	$('li#step1').addClass("active");	
}
else if(step=='requirement_check')
{
	$('li.active').removeClass("active");
	$('li#step2').addClass("active");			
}
else if(step=='database_setup')
{
	$('li.active').removeClass("active");
	$('li#step3').addClass("active");				
}
else if(step=="run_install")
{
	$('li.active').removeClass("active");
	$('li#step4').addClass("active");			
}
});
</script>
</head>

<body>
<center>
	<div id="logo_only"><img src="<?php echo $this->config->item('img_path');?>logo.png"</div>
	<div id="arrow">&nbsp;</div>
	<div id="container">
		<div id="left-container"><?php $this->load->view($main);?></div>
		<div id="right-container">
		<h3 style="padding-left: 20px">Installation steps:</h3>
		<ul>
			<li id="step1">Welcome screen</li>
			<li id="step2">Requirement check</li>
			<li id="step3">Database setup</li>
			<li id="step4">Installation result</li>
		</ul>
		</div>
	</div>
	<div id="footer">Powered by Kalkun <?php echo $this->config->item('kalkun_version');?></div>
</center>
</body>
</html>
