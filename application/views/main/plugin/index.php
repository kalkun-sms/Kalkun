<div id="window_container">
	<div id="window_title">
		<div id="window_title_left"><?php echo $title; ?></div>
		<div id="window_title_right">
			<?php /* echo form_open('pluginss', array('class' => 'search_form'));
		echo '<input type="text" name="search_name" size="20" class="search_name" value="" />';
		echo form_close(); */?>
			&nbsp;
			<a href="<?php echo site_url('pluginss/index/installed');?>" id="addpbkcontact_wizard" class="nicebutton"><?php echo tr('Installed', 'Plural'); ?></a>
			<a href="<?php echo site_url('pluginss/index/available');?>" id="addpbkcontact_wizard" class="nicebutton"><?php echo tr('Available', 'Plural'); ?></a>
		</div>
	</div>

	<div id="window_content">
		<?php
if (count($plugins) > 0)
{
	foreach ($plugins as $tmp)
	{
		if ($type === 'installed'
			&& file_exists(APPPATH . 'plugins/'.$tmp['plugin_system_name'].'/controllers/'.ucfirst($tmp['plugin_system_name']).'.php')
			&& $tmp['plugin_controller_has_index'] === TRUE)
		{
			echo '<div style="float: left"><h3>'.anchor('plugin/'.rawurlencode($tmp['plugin_system_name']), htmlentities($tmp['plugin_name'], ENT_QUOTES)).'</h3></div>';
		}
		else
		{
			echo '<div style="float: left"><h3 style="color: #000">'.htmlentities($tmp['plugin_name'], ENT_QUOTES).'</h3></div>';
		} ?>
		<div style="float: right; margin-top: 15px;">
			<?php if ($type === 'installed'):?>
			<a href="<?php echo site_url('pluginss/uninstall/'.rawurlencode($tmp['plugin_system_name'])); ?>" class="nicebutton"><?php echo tr('Uninstall'); ?></a>
			<?php else:?>
			<a href="<?php echo site_url('pluginss/install/'.rawurlencode($tmp['plugin_system_name'])); ?>" class="nicebutton"><?php echo tr('Install'); ?></a>
			<?php endif; ?>
		</div>

		<div class="clear"><small>
				<strong><?php echo tr('Version'); ?>:</strong> <?php echo htmlentities($tmp['plugin_version'], ENT_QUOTES); ?>&nbsp;&nbsp;
				<strong><?php echo tr('Author'); ?>:</strong> <?php echo anchor(htmlentities($tmp['plugin_author_uri'], ENT_QUOTES), htmlentities($tmp['plugin_author'], ENT_QUOTES)); ?>
			</small></div>
		<p><?php echo htmlentities($tmp['plugin_description'], ENT_QUOTES); ?></p>
		<hr />
		<?php
	}
}
else
{
	?>
		<p>
			<?php if ($type === 'available'):
echo tr('No plugin available.');
	elseif ($type === 'installed'):
echo tr('No plugin installed.');
	endif; ?>
		</p>
		<?php
}
?>
	</div>
