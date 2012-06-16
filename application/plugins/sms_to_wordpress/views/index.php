<?php $this->load->view('js_sms_to_wordpress');?>

<!-- Add Wordpress dialog -->	
<div id="wp-dialog" title="Add Wordpress Blog" class="dialog">
	<p id="validateTips">All form fields are required.</p>
	<?php echo form_open('plugin/sms_to_wordpress/add', array('class' => 'addwpblogform')); ?>
	<fieldset>
		<label for="wp_url">Wordpress URL</label>
		<input type="text" name="wp_url" id="wp_url" class="text ui-widget-content ui-corner-all" />
		<label for="wp_username">Wordpress Username</label>
		<input type="text" name="wp_username" id="wp_username" value="" class="text ui-widget-content ui-corner-all" />
		<label for="wp_password">Wordpress Password</label>
		<input type="password" name="wp_password" id="wp_password" value="" class="text ui-widget-content ui-corner-all" />
	</fieldset>
	<?php echo form_close(); ?>
</div>

<div id="window_container">
<div id="window_title"><?php echo $title; ?></div>
<div id="window_content">
<?php if (!$status):?>
<a href="#" class="nicebutton" id="addwpblogbutton">&#43; Add Wordpress blog</a>
<?php else:?>
<a href="<?php echo site_url('plugin/sms_to_wordpress/delete')?>" class="nicebutton">&#43; Delete Wordpress blog</a>
<?php endif;?>

<?php if($wp):?>
<h4>Blog URL:</h4>
<p><?php echo $wp['wp_url'];?></p>
<?php endif;?>
</div>