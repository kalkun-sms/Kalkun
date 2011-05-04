<div id="window_container">
<div id="window_title">
	<div id="window_title_left"><?php echo $title; ?></div>
	<div id="window_title_right">
		<?php echo form_open('pluginss', array('class' => 'search_form')); ?>
		<input type="text" name="search_name" size="20" class="search_name" value="" />
		<?php echo form_close(); ?>
		&nbsp;
		<a href="<?php echo site_url('pluginss/index/installed');?>" id="addpbkcontact_wizard" class="nicebutton"><?php echo "Installed"; ?></a>
		<a href="<?php echo site_url('pluginss/index/available');?>" id="addpbkcontact_wizard" class="nicebutton"><?php echo "Available"; ?></a>
	</div>
</div>

<div id="window_content">
	<?php
	if(count($plugins)>0):
		foreach($plugins as $tmp):
	?>
	<h3><?php echo anchor('plugin/'.$tmp['plugin_system_name'], $tmp['plugin_name']); ?></h3>
	<div><small>
	<strong>Version:</strong> <?php echo $tmp['plugin_version'];?>&nbsp;&nbsp;
	<strong>Author:</strong> <?php echo anchor($tmp['plugin_author_uri'], $tmp['plugin_author']);?>
	</small></div>
	<p><?php echo $tmp['plugin_description'];?></p>
	<hr />
	<?php endforeach;?>
	
	<?php else: ?>
	<p>There is no installed plugin.</p>
	<?php endif;?>
</div>
