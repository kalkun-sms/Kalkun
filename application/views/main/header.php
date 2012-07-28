<title><?php echo "Kalkun"; if(isset($title)): echo " / ".$title; endif;?></title>
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<meta name="generator" content="Geany 0.13" />
<meta name="robots" content="noindex,nofollow">
<link rel="shortcut icon" href="<?php echo  $this->config->item('img_path');?>icon.ico" type="image/x-icon" />
<link type="text/css" rel="stylesheet" href="<?php echo $this->config->item('css_path');?>base.css" />
<style type="text/css">
@import url("<?php echo $this->config->item('css_path');?>blue.css");
</style>
<link type="text/css" rel="stylesheet" href="<?php echo $this->config->item('css_path');?>jquery-ui/ui.all.css" />	
<script language="javascript" src="<?php echo $this->config->item('js_path');?>jquery-1.6.2.min.js"></script>
<script language="javascript" src="<?php echo $this->config->item('js_path');?>jquery-plugin/jquery.hotkeys.js"></script>
<script language="javascript" src="<?php echo $this->config->item('js_path');?>jquery-plugin/jquery.field.min.js"></script>
<script language="javascript" src="<?php echo $this->config->item('js_path');?>jquery-plugin/jquery.autogrow-textarea.min.js"></script>
<script language="javascript" src="<?php echo $this->config->item('js_path');?>jquery-plugin/jquery.sound.min.js"></script>
<script language="javascript" src="<?php echo $this->config->item('js_path');?>jquery-ui/ui.core.min.js"></script>
<script language="javascript" src="<?php echo $this->config->item('js_path');?>jquery-ui/ui.draggable.min.js"></script>
<script language="javascript" src="<?php echo $this->config->item('js_path');?>jquery-ui/ui.dialog.min.js"></script>
<script language="javascript" src="<?php echo $this->config->item('js_path');?>jquery-ui/ui.datepicker.min.js"></script>
<script language="javascript" src="<?php echo $this->config->item('js_path');?>jquery-ui/effects.core.min.js"></script>
<script language="javascript" src="<?php echo $this->config->item('js_path');?>jquery-ui/effects.fade.min.js"></script>
<!--[if IE]>
  <link type="text/css" rel="stylesheet" href="<?php echo $this->config->item('css_path');?>ie-fix.css" />
<![endif]-->
<?php
//background image
list($bg_act_option, $bg_act) = explode(';',$this->Kalkun_model->get_setting()->row('bg_image'));
if($bg_act_option=='true'):?>
<style type="text/css">
body { 
	background-image: url('<?php echo $this->config->item('img_path').''.$bg_act;?>'); 
	background-repeat: repeat-x; 
	background-attachment: fixed;
}
</style>
	
<?php
endif;

$this->load->view('js_init/js_layout');
$this->load->view('js_init/js_keyboard');
?>	
