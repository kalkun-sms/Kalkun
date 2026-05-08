<h1><?php echo tr('Final configuration steps'); ?></h1>
<p>This is the last step of the installation process.</p>
<p>Check each item and update it if required.</p>

<h2 class="section">Kalkun configuration</h2>
<table class="formtable" style="width: 100%">
	<tr>
		<td>Encryption key</td>
		<td>
			<?php 	if ( ! $uses_default_encryption_key): ?>
			<strong class="green"><?php echo 'Different from kalkun\'s default'; ?></strong>
			<?php 	else: ?>
			<strong class="orange"><?php echo 'Security risk'; ?></strong><br />
			<small>You are using the default encryption_key shipped by Kalkun.
				<br />To improve security, it's highly recommended to change the <code>encryption_key</code>. Change its value in <code><?php echo realpath(APPPATH.'config/config.php'); ?></code>. <a href="https://github.com/kalkun-sms/Kalkun/wiki/Installation#change-the-default-encryption-key" target="_blank"><strong>See wiki</strong></a> for details.</small>
			<?php	 endif; ?>
		</td>
	</tr>

	<tr>
		<td>Path to gammu</td>
		<td>
			<?php 	if (file_exists($config_gammu_path)): ?>
			<strong class="green"><?php echo tr('Found'); ?></strong>
			<?php 	else: ?>
			<strong class="red"><?php echo 'Missing'; ?></strong>
			<?php endif; ?>
			<br /><small><code><?php echo htmlentities($config_gammu_path, ENT_QUOTES); ?></code></small>
			<?php 	if ( ! file_exists($config_gammu_path)): ?>
			<br /><small>As per <code><?php echo realpath(FCPATH.'application/config/kalkun_settings.php') ?></code>.</small>
			<br /><small>This is mandatory to send Wap links.</small>
			<?php endif; ?>
		</td>
	</tr>
	<tr>
		<td>Path to gammu-smsd-inject</td>
		<td>
			<?php 	if (file_exists($config_gammu_sms_inject)): ?>
			<strong class="green"><?php echo tr('Found'); ?></strong>
			<?php 	else: ?>
			<strong class="red"><?php echo 'Missing'; ?></strong>
			<?php endif; ?>
			<br /><small><code><?php echo htmlentities($config_gammu_sms_inject, ENT_QUOTES); ?></code></small>
			<?php 	if ( ! file_exists($config_gammu_path)): ?>
			<br /><small>As per <code><?php echo realpath(FCPATH.'application/config/kalkun_settings.php') ?></code>.</small>
			<br /><small>This is mandatory to send Wap links.</small>
			<?php endif; ?>
		</td>
	</tr>
	<tr>
		<td>Path to gammu config file</td>
		<td>
			<?php 	if (file_exists($config_gammu_config)): ?>
			<strong class="green"><?php echo tr('Found'); ?></strong>
			<?php 	else: ?>
			<strong class="red"><?php echo 'Missing'; ?></strong>
			<?php endif; ?>
			<br /><small><code><?php echo htmlentities($config_gammu_config, ENT_QUOTES); ?></code></small>
			<?php 	if ( ! file_exists($config_gammu_path)): ?>
			<br /><small>As per <code><?php echo realpath(FCPATH.'application/config/kalkun_settings.php') ?></code>.</small>
			<br /><small>This is mandatory to send Wap links.</small>
			<?php endif; ?>
		</td>
	</tr>

</table>

<h2 class="section">Daemon script configuration</h2>

<p>Configuring the daemon scripts of Kalkun is mandatory to see the incoming messages in your inbox. See <a href="https://github.com/kalkun-sms/Kalkun/wiki/Installation#configure-daemon--outbox_queue-scripts" target="_blank"><strong>instructions on the wiki</strong></a>.</p>

<p>The daemon scripts are provided in the <code>scripts</code> directory of the kalkun archive. In case they are still located at the root of your webserver (ie. along the <code>application</code> directory), <strong>it is advised to move them to another location</strong>.</p>

<p>The checks below search for the <code>scripts</code> directory either next to the <code>index.php</code> file at the root of Kalkun, or one level above it.</p>

<table class="formtable" style="width: 100%">
	<tr>
		<td>Script path</td>
		<td>
			<?php if ($daemon_path !== FALSE): ?>
			<strong class="green"><?php echo tr('Found'); ?></strong>
			<br /><small><code><?php echo htmlentities($daemon_path, ENT_QUOTES); ?></code></small>
			<?php else: ?>
			<strong class="red"><?php echo 'Not found'; ?></strong>
			<br /><small>You probably installed the file at a non standard location. Configure as per the <a href="https://github.com/kalkun-sms/Kalkun/wiki/Installation#configure-daemon--outbox_queue-scripts" target="_blank">guidance on the wiki</a>.</small>
			<?php endif; ?>
		</td>
	</tr>
	<?php if ($daemon_path !== FALSE): ?>
	<?php if ($is_windows === FALSE): ?>
	<tr>
		<td>Script is executable</td>
		<td>
			<?php if ($daemon_path_is_executable): ?>
			<strong class="green"><?php echo tr('Yes'); ?></strong>
			<?php else: ?>
			<strong class="red"><?php echo tr('No'); ?></strong>
			<br /><small>The script has to be marked as executable for proper functionning.</small>
			<?php endif; ?>
		</td>
		<?php endif; ?>
	</tr>
	<tr>
		<td>Path to PHP command</td>
		<td>
			<?php if ($daemon_php_path_exists): ?>
			<strong class="green"><?php echo tr('Found'); ?></strong>
			<?php else: ?>
			<strong class="red"><?php echo 'Doesn\'t exist'; ?></strong>
			<?php endif; ?>
			<br /><small><code><?php echo htmlentities($daemon_php_path, ENT_QUOTES); ?></code></small>
			<?php if ( ! $daemon_php_path_exists): ?>
			<br /><small>Path to PHP command is not correct. Fix it and refresh the page to check again.</small>
			<?php endif; ?>
		</td>
	</tr>
	<tr>
		<td>Path to PHP file</td>
		<td>
			<?php if ($daemon_daemon_path_exists): ?>
			<strong class="green"><?php echo tr('Found'); ?></strong>
			<?php else: ?>
			<strong class="red"><?php echo 'Doesn\'t exist'; ?></strong>
			<?php endif; ?>
			<br /><small><code><?php echo htmlentities($daemon_daemon_path, ENT_QUOTES); ?></code></small>
			<?php if ( ! $daemon_daemon_path_exists): ?>
			<br /><small>Path to PHP file is not correct. Fix it and refresh the page to check again.</small>
			<?php endif; ?>
		</td>
	</tr>
	<tr>
		<td>Kalkun URL</td>
		<td>
			<?php if ($daemon_url_matches_config): ?>
			<strong class="green"><?php echo 'Matches current URL'; ?></strong>
			<?php else: ?>
			<strong class="red"><?php echo 'Doesn\'t match the current URL of kalkun'; ?></strong>
			<?php endif; ?>
			<br /><small><code><?php echo htmlentities($daemon_url, ENT_QUOTES); ?></code></small>
			<?php if ( ! $daemon_url_matches_config): ?>
			<br /><small>URL in the PHP file doesn't match the URL of this kalkun instance. Fix it and refresh the page to check again.</small>
			<?php endif; ?>
		</td>
	</tr>
	<?php endif; ?>

</table>

<h2 class="section">Outbox queue script configuration</h2>
<table class="formtable" style="width: 100%">
	<tr>
		<td>Script path</td>
		<td>
			<?php if ($outbox_queue_path !== FALSE): ?>
			<strong class="green"><?php echo tr('Found'); ?></strong>
			<br /><small><code><?php echo htmlentities($outbox_queue_path, ENT_QUOTES); ?></code></small>
			<?php else: ?>
			<strong class="red"><?php echo 'Not found'; ?></strong>
			<br /><small>You probably installed the file at a non standard location. Configure as per the <a href="https://github.com/kalkun-sms/Kalkun/wiki/Installation#configure-daemon--outbox_queue-scripts" target="_blank">guidance on the wiki</a>.</small>
			<?php endif; ?>
		</td>
	</tr>
	<?php if ($outbox_queue_path !== FALSE): ?>
	<?php if ($is_windows === FALSE): ?>
	<tr>
		<td>Script is executable</td>
		<td>
			<?php if ($outbox_queue_path_is_executable): ?>
			<strong class="green"><?php echo tr('Yes'); ?></strong>
			<?php else: ?>
			<strong class="red"><?php echo tr('No'); ?></strong>
			<br /><small>The script has to be marked as executable for proper functionning.</small>
			<?php endif; ?>
		</td>
		<?php endif; ?>
	<tr>
		<td>Path to PHP command</td>
		<td>
			<?php if ($outbox_queue_php_path_exists): ?>
			<strong class="green"><?php echo tr('Found'); ?></strong>
			<?php else: ?>
			<strong class="red"><?php echo 'Doesn\'t exist'; ?></strong>
			<?php endif; ?>
			<br /><small><code><?php echo htmlentities($outbox_queue_php_path, ENT_QUOTES); ?></code></small>
			<?php if ( ! $outbox_queue_php_path_exists): ?>
			<br /><small>Path to PHP command is not correct. Fix it and refresh the page to check again.</small>
			<?php endif; ?>
		</td>
	</tr>
	<tr>
		<td>Path to PHP file</td>
		<td>
			<?php if ($outbox_queue_daemon_path_exists): ?>
			<strong class="green"><?php echo tr('Found'); ?></strong>
			<?php else: ?>
			<strong class="red"><?php echo 'Doesn\'t exist'; ?></strong>
			<?php endif; ?>
			<br /><small><code><?php echo htmlentities($outbox_queue_daemon_path, ENT_QUOTES); ?></code></small>
			<?php if ( ! $outbox_queue_daemon_path_exists): ?>
			<br /><small>Path to PHP file is not correct. Fix it and refresh the page to check again.</small>
			<?php endif; ?>
		</td>
	</tr>
	<tr>
		<td>Kalkun URL</td>
		<td>
			<?php if ($outbox_queue_url_matches_config): ?>
			<strong class="green"><?php echo 'Matches current URL'; ?></strong>
			<?php else: ?>
			<strong class="red"><?php echo 'Doesn\'t match the current URL of kalkun'; ?></strong>
			<?php endif; ?>
			<br /><small><code><?php echo htmlentities($outbox_queue_url, ENT_QUOTES); ?></code></small>
			<?php if ( ! $outbox_queue_url_matches_config): ?>
			<br /><small>URL in the PHP file doesn't match the URL of this kalkun instance. Fix it and refresh the page to check again.</small>
			<?php endif; ?>
		</td>
	</tr>
	<?php endif; ?>

</table>

<h2 class="section">Gammu-smsd configuration</h2>
<table class="formtable" style="width: 100%">
	<tr>
		<td>Gammu-smsd configuration</td>
		<td><strong class="orange"><?php echo 'Unknown state'; ?></strong>
			<br /><small>In the configuration file of gammu-smsd, you have to set the <code>RunOnReceive</code> directive and set its value to the path of the daemon script. <code>RunOnReceive</code> must be in the <code>[smsd]</code> section of the configuration file.
				<br />The wiki explains <a href="https://github.com/kalkun-sms/Kalkun/wiki/Installation#configure-gammu-smsd" target="_blank">how to configure gammu-smsd</a> more in details.
			</small>
		</td>
	</tr>

</table>

<h2 class="section">HTTP server configuration</h2>
<table class="formtable" style="width: 100%">
	<tr>
		<td>CI_ENV environment variable</td>
		<td>
			<?php	if ($CI_ENV === 'production'): ?>
			Value: <code><?php echo htmlentities($CI_ENV, ENT_QUOTES); ?></code>. <strong class="green">OK</strong>
			<br /><small>Codeigniter will not report any errors. This can lead to blank screens in case of bugs. If this happens to you, set it to <code>development</code> and the error messages will be displayed. However, setting it to <code>development</code> decreases the security of your setup.</small>
			<?php	else: ?>
			Value: <code><?php echo ($CI_ENV === '') ? 'Unset' : htmlentities($CI_ENV, ENT_QUOTES); ?></code>. <strong class="orange">Security risk</strong>
			<br /><small>To improve security, it's recommended to set the CI_ENV variable in the configuration of your web server to <code>production</code>. However, when set to <code>production</code>, codeigniter will not report any errors. This can lead to blank screens in case of bugs.</small>
			<?php	endif; ?>
			<br /><small>
				<?php	if ($htaccess_location !== ''): ?>
				This configuration is set in the <code><?php echo htmlentities($htaccess_location, ENT_QUOTES); ?></code> file.
				<?php	else: ?>
				This configuration can be set in the <code><?php echo FCPATH.'.htaccess'; ?></code> file, or in the main configuration of your HTTP server.
				<?php	endif; ?>
				See more details in the <a href="https://codeigniter.com/userguide3/general/environments.html">CodeIgniter documentation</a></small>.
		</td>
	</tr>
</table>


<h2 class="section">Kalkun settings</h2>
<p>You may change additional settings in the configuration file of kalkun.
<table class="formtable" style="width: 100%">
	<tr>
		<td>Location</td>
		<td>
			<code><?php echo realpath(APPPATH.'config/kalkun_settings.php'); ?></code>
		</td>
	</tr>
</table>
<p>Find some suggestions of parameters you can change on the <a href="https://github.com/kalkun-sms/Kalkun/wiki/Configuration" target="_blank"><strong>configuration page of the wiki</strong></a>.</p>


<h2 class="section"><a id="install_file">Disable installation wizard</a></h2>
<p>There is a <code>install</code> file located at the root of Kalkun directory. As long as it is present, you can only access the installation wizard. As soon as this file is removed, the installation wizard can't be accessed anymore.</p>
<p>If the present configuration of Kalkun, as displayed, is satisfactory, you can now remove the <code>install</code> file located at the root of Kalkun directory.</p>

<table class="formtable" style="width: 100%">
	<?php if (file_exists($install_realpath)): ?>
	<tr>
		<td>Location</td>
		<td>
			<code><?php echo $install_realpath; ?></code>
		</td>
	</tr>
	<?php endif; ?>
	<tr>
		<td>Status</td>
		<td>
			<?php if (file_exists($install_realpath)): ?>
			<strong class="red">Present</strong>
			<?php if ($needs_manual_install_file_deletion || $this->input->post('remove_install_file') === 'remove'): ?>
			<br /><small>You must remove the file manually.</small>
			<?php endif; ?>
			<?php else: ?>
			<strong class="green"><?php echo 'Removed'; ?></strong>
			<?php endif; ?>
		</td>
	</tr>
	<?php if ( ! $needs_manual_install_file_deletion && ($this->input->post('remove_install_file') !== 'remove')): ?>
	<tr>
		<td colspan="2">
			<?php
		echo form_open('install/config_setup#install_file', 'style="display:block; text-align:center"');
		echo form_hidden('idiom', $idiom);
		echo form_hidden('remove_install_file', 'remove');
		echo form_submit('submit', 'Remove install file now', 'class="button"');
		echo form_close();
?>
		</td>
	</tr>
	<?php endif; ?>
</table>


<h2 class="section">Default credentials</h2>

<p>If this is your first setup, please note the default credentials.</p>
<ul>
	<li>login: <code>kalkun</code>
	<li>password: <code>kalkun</code>
</ul>
<p><strong>It is advised to change the default password on first login.</strong></p>

<p>&nbsp;</p>
<?php
	echo form_open(site_url(), 'style="display:block; text-align: center"');
	echo form_hidden('idiom', $idiom);
	echo form_submit('submit', tr_raw('Log in'), 'class="button"');
	if ($needs_manual_install_file_deletion):
		echo '<br>💡 Have you deleted install file?';
	endif;
	echo form_close();
?>

<p>&nbsp;</p>
