<h2>Installation result</h2>
<p>This is the last step of the installation process.</p>
<h4>Database setup:</h4>
<?php if ($error === 0): ?>
<p>Status: <span class="green">Successful</span></p>
<?php else: ?>
<p>Status: <span class="red">Failed</span></p>
<p>Consider manual installation, read the README instruction file.</p>
<?php endif; ?>

<?php if ($error === 0): ?>
<p>&nbsp;</p>

<h3>Remaining manual steps</h3>
<?php
	if (file_exists('./install') && is_writable(dirname('./install'))):
		$rm = unlink('./install');
		$needs_manual_install_file_deletion = FALSE;

	else:
		$realpath = realpath('./install');
		$needs_manual_install_file_deletion = TRUE;
?>
<h4>Remove Installation file</h4>
<p>Before lauching Kalkun, you have to remove the <code>install</code> file located at the root of Kalkun directory.</p>
<p>Location: <code><?php echo $realpath; ?></code></p>
<?php	endif; ?>

<h4>Configure Kalkun daemon</h4>
<p>Please note that you also must configure the PHP daemon script of Kalkun. Otherwise you can't get your inbox, see instructions on README file.</p>
<p>The daemon scripts are provided in the <code>scripts</code> directory of the kalkun archive. In case they are still located at the root of your webserver (ie. along the <code>application</code> directory), <strong>it is advised to move them to another location</strong>.</p>

<h4>Change encryption key</h4>
<p>To improve security, it's higly recommended to change the <code>encryption_key</code> in <code>application/config/config.php</code>.</p>

<h4>Configure kalkun internals</h4>
<p>You may change some parameters in the <code>application/config/kalkun_settings.php</code> file. For example:</p>
<ul>
	<li>Gammu path &amp; gammu config file. Required to send Wap links.</li>
	<li>Gateway engine (aka backend) in case you want to use an external service provider for your SMS (experimental).</li>
</ul>

<h4>Default credentials</h4>
<p>If this is your first setup, please note that default login &amp; password are 'kalkun'.</p>

<p>&nbsp;</p>
<p align="center"><a href="<?php echo site_url();?>" class="button">Log-in to kalkun</a>
	<?php if ($needs_manual_install_file_deletion): ?>
	<br>💡 Have you deleted install file?
	<?php endif; ?>
</p>

<?php endif; ?>
<p>&nbsp;</p>
