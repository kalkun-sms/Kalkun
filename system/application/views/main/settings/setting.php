<script type="text/javascript">
$(document).ready(function(){ 	

// Get current page for styling/css	
$("#window_sub_header").find("a[href='"+window.location.href+"']").each(function(){
	$(this).addClass("current");
});	
	
});	
</script>

<div id="window_container">
<div id="window_title">User's settings</div> 
<div id="window_sub_header">
<ul>
<li><?php echo anchor('settings/general', 'General');?></li>
<li><?php echo anchor('settings/personal', 'Personal');?></li>
<!--<li><?php echo anchor('settings/appearance', 'Appearance');?></li>-->
<li><?php echo anchor('settings/password', 'Password');?></li>
</ul>
</div>
<div id="window_content">
<?php if(!empty($notif)): echo "<div class=\"notif\">".$notif."</div>"; endif;?>
<?php 
echo form_open('settings/save', array('id' => 'settingsForm')); 
$this->load->view($type);
?>
<br />
<div align="center"><input type="submit" id="submit" value="Save changes" /></div>
<?php echo form_close();?>

</div>
