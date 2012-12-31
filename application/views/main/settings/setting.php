<script type="text/javascript">
$(document).ready(function(){ 	

// Get current page for styling/css	
$("#window_sub_header").find("a[href='"+window.location.href+"']").each(function(){
	$(this).addClass("current");
});	
	
});	
</script>

<div id="window_container">
<div id="window_title"><?php echo lang('tni_set_title'); ?></div> 
<div id="window_sub_header">
<ul>
<li><?php echo anchor('settings/general', lang('tni_set_general'));?></li>
<li><?php echo anchor('settings/personal', lang('tni_set_personal'));?></li>
<!--<li><?php echo anchor('settings/appearance', 'Appearance');?></li>-->
<li><?php echo anchor('settings/password', lang('tni_user_password'));?></li>
<li><?php echo anchor('settings/filters', lang('kalkun_filters'));?></li>
</ul>
</div>
<div id="window_content">
<?php if(!empty($notif)): echo "<div class=\"notif\">".$notif."</div>"; endif;?>
<?php if($type != 'main/settings/filters'):?>
<?php
echo form_open('settings/save', array('id' => 'settingsForm')); 
$this->load->view($type);
?>
<br />
<div align="center"><input type="submit" id="submit" value="<?php echo lang('kalkun_save'); ?>" /></div>
<?php echo form_close();?>

<?php else:?>
<?php $this->load->view($type);?>
<?php endif;?>
</div>
