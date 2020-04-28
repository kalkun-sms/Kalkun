<?php
$this->load->helper('html');
echo doctype('html5');?>
<html>
<head>
<title>Kalkun / Info message</title>
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<?php echo link_tag($this->config->item('img_path').'icon.ico', 'shortcut icon', 'image/ico');?>
<?php echo link_tag($this->config->item('css_path').'base.css');?>
<style>
@import url("<?php echo $this->config->item('css_path');?>blue.css");
</style>
</head>
<body>
<p>
<?php echo $result; ?>
</p>
</body>
</html>
