<h1><?php echo tr('Database setup'); ?></h1>
<p>This step sets your database up for Kalkun.</p>
<h2 class="section">Database backend engine and gammu database version.</h2>
<table class="formtable">
	<tr>
		<td>Database engine</td>
		<td>
			<strong><?php echo $db_property['human']; ?></strong>
			<br><small>As per your database configuration.</small>
		</td>
	</tr>
	<?php if ($exception === NULL): ?>
	<tr>
		<td>Gammu DB schema</td>
		<td>
			<?php if ($has_smsd_database): ?>
			<strong class="green"><?php echo tr('Found'); ?></strong>
			<?php else: ?>
			<strong class="red"><?php echo tr('Missing'); ?></strong><br>
			<small>Please create the Gammu tables of the database first. Refer to Gammu's documentation.</small>
			<?php endif; ?>
		</td>
	</tr>

	<?php if ($has_smsd_database): ?>
	<tr>
		<td>Gammu DB version</td>
		<td><strong><?php echo htmlentities($this->Kalkun_model->get_gammu_info('db_version')->row('Version'), ENT_QUOTES); ?></strong>
			<br><small>As per the version stored in gammu database.</small>
		</td>
	</tr>
	<tr>
		<td>Gammu phonebook table</td>
		<td>
			<?php 	if ($has_table_pbk): ?>
			<strong class="green"><?php echo tr('Found'); ?></strong>
			<?php 	else: ?>
			<strong class="orange"><?php echo tr('Missing'); ?></strong><br>
			<small>Click 'Run Database Setup' below to install it.</small>
			<?php	 endif; ?>
		</td>
	</tr>

	<tr>
		<td>Kalkun DB</td>
		<?php	if ($has_gammu_database): ?>
		<td><strong class="green"><?php echo tr('Found'); ?></strong></td>
		<?php	else: ?>
		<td><strong class="orange"><?php echo tr('Missing'); ?></strong><br><small>Click 'Run Database Setup' below to install it.</small></td>
		<?php	endif; ?>
	</tr>

	<?php	if ($has_gammu_database): ?>
	<tr>
		<td>Kalkun DB version</td>
		<?php switch ($type):
				case 'upgrade_not_supported':
					$message = 'Upgrade of your version of kalkun database is not supported. You will need to proceed manually.';
					break;
				case 'upgrade':
					$message = 'Click \'Run Database Setup\' below to upgrade.';
					break;
				default:
					$message = '';
					break;
			endswitch; ?>
		<td>
			<strong class="<?php echo ($message !== '') ? 'orange' : ''; ?>"><?php echo $detected_db_version; ?></strong>
			<br><small><?php echo $message ?></small>
		</td>
	</tr>
	<?php	endif; ?>

	<?php endif; ?>
	<?php else: /* $exception !== NULL */ ?>
	<tr>
		<td colspan="2">
			<p class="red">There was a problem when trying to load the database.</p>
			<p>Reported error is:</p>
			<p><code><?php echo htmlentities($exception, ENT_QUOTES); ?></code></p>
			<p>Please check your database configuration in <code><?php echo realpath(APPPATH.'config/database.php'); ?></code>. Then click on button to check again.</p>
		</td>
	</tr>
	<?php endif; ?>
</table>
<p>&nbsp;</p>

<div style="margin-left: auto; margin-right: auto; width: fit-content;">
	<?php if ($exception !== NULL || ! $has_smsd_database): ?>
	<?php
	echo form_open('install/database_setup');
	echo form_hidden('idiom', $idiom);
	echo form_submit('submit', tr_raw('Check again'), 'class="button"');
	echo form_close();
else:
	if ($type === 'install' OR $type === 'upgrade' OR ! $has_table_pbk):
		if ($type !== 'upgrade_not_supported'):
			echo form_open('install/database_setup');
			echo form_hidden('idiom', $idiom);
			echo form_hidden('action', 'run_db_setup');
			echo form_submit('submit', 'Run Database Setup', 'class="button"');
			echo form_close();
		endif;
	endif;
endif; ?>
</div>

<?php if ($this->input->post('action') === 'run_db_setup'): ?>
<h2 class="section"><?php echo tr('Database setup'); ?></h2>
<?php  if ($error === 0): ?>
<p><?php echo tr('Status'); ?>: <span class="green"><?php echo tr('Successful'); ?></span></p>
<?php else: ?>
<p><?php echo tr('Status'); ?>: <span class="red"><?php echo tr('Failed'); ?></span></p>
<p>Consider manual installation, read the README instruction file.</p>
<?php endif; ?>
<?php endif; ?>

<p>&nbsp;</p>
<div>
	<?php
	echo form_open('install/requirement_check', 'style="display:inline"');
	echo form_hidden('idiom', $idiom);
	echo form_submit('submit', '‹ '.tr_raw('Previous'), 'class="button"');
	echo form_close();

	if ($exception === NULL && $has_smsd_database && $type !== 'install' && $type !== 'upgrade' && $has_table_pbk && $type !== 'upgrade_not_supported'):
		echo form_open('install/config_setup', 'style="display:inline"');
		echo form_hidden('idiom', $idiom);
		echo form_submit('submit', tr_raw('Continue').' ›', 'class="button"');
		echo form_close();
	endif; ?>
</div>
