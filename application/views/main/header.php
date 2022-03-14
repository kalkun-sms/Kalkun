<title><?php echo 'Kalkun'; if (isset($title)): echo ' / '.htmlentities($title, ENT_QUOTES); endif;?></title>
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<meta name="generator" content="Geany 0.13" />
<meta name="robots" content="noindex,nofollow">
<link rel="shortcut icon" href="<?php echo  $this->config->item('img_path');?>icon.ico" type="image/x-icon" />
<link type="text/css" rel="stylesheet" href="<?php echo $this->config->item('css_path');?>base.css" />
<style type="text/css">
	@import url("<?php echo $this->config->item('css_path');?>blue.css");

</style>
<link type="text/css" rel="stylesheet" href="<?php echo $this->config->item('css_path');?>jquery-ui/jquery-ui.min.css" />
<script defer language="javascript" src="<?php echo $this->config->item('js_path');?>modernizr.min.js"></script>
<script language="javascript" src="<?php echo $this->config->item('js_path');?>jquery-3.6.0.min.js"></script>
<script language="javascript" src="<?php echo $this->config->item('js_path');?>jquery-plugin/jquery.hotkeys.js"></script>
<script language="javascript" src="<?php echo $this->config->item('js_path');?>jquery-plugin/jquery.field.min.js"></script>
<script language="javascript" src="<?php echo $this->config->item('js_path');?>autosize-5.0.1.min.js"></script>
<script language="javascript" src="<?php echo $this->config->item('js_path');?>jquery-ui/jquery-ui.min.js"></script>
<?php
$jquery_datepicker_regional = $this->lang->get_jquery_datepicker_regional(APPPATH.'../media/js/jquery-ui/i18n');
$jquery_ui_i18n = FCPATH."media/js/jquery-ui/i18n/datepicker-${jquery_datepicker_regional}.js";
if ($jquery_datepicker_regional !== '' && file_exists($jquery_ui_i18n)):
?>
<script language="javascript" src="<?php echo $this->config->item('js_path');?>jquery-ui/i18n/datepicker-<?php echo rawurlencode($jquery_datepicker_regional); ?>.js"></script>
<?php endif; ?>
<!--[if IE]>
  <link type="text/css" rel="stylesheet" href="<?php echo $this->config->item('css_path');?>ie-fix.css" />
<![endif]-->
<?php

$this->load->view('js_init/js_layout');
$this->load->view('js_init/js_keyboard');
?>
