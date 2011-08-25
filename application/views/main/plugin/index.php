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
if(count($plugins)>0)
{
	foreach($plugins as $tmp)
	{
		if ($type=="installed" AND file_exists(APPPATH . "plugins/".$tmp['plugin_system_name']."/controllers/".$tmp['plugin_system_name'].".php"))
		{
			echo "<div style=\"float: left\"><h3>".anchor('plugin/'.$tmp['plugin_system_name'], $tmp['plugin_name'])."</h3></div>";
		}
		else
		{
			echo "<div style=\"float: left\"><h3 style=\"color: #000\">".$tmp['plugin_name']."</h3></div>";
		}		
?>
		<div style="float: right; margin-top: 15px;">
			<?php if ($type=="installed"):?>
			<a href="<?php echo site_url('pluginss/uninstall/'.$tmp['plugin_system_name']);?>" class="nicebutton">Uninstall</a>
			<?php else:?>
			<a href="<?php echo site_url('pluginss/install/'.$tmp['plugin_system_name']);?>" class="nicebutton">Install</a>
			<?php endif;?>
		</div>

		<div class="clear"><small>
		<strong>Version:</strong> <?php echo $tmp['plugin_version'];?>&nbsp;&nbsp;
		<strong>Author:</strong> <?php echo anchor($tmp['plugin_author_uri'], $tmp['plugin_author']);?>
		</small></div>
		<p><?php echo $tmp['plugin_description'];?></p>
		<hr />
<?php 
	}
}
else
{
?>
	<p>There is no <?php echo $type;?> plugin.</p>
<?php
}
?>
</div>
