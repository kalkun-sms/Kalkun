<h2><?php echo tr('Database setup'); ?></h2>
<p>This step sets your database up for Kalkun.</p>
<h4 align="center" style="padding-bottom: 5px; border-bottom: 1px solid #999">Database backend engine and gammu database version.</h4>
<table class="formtable">
	<tr valign="top">
		<td>Database engine</td>
		<td>
			<strong><?php echo $db_property['human']; ?></strong>
			<br /><small>Read from your database configuration.</small>
		</td>
	</tr>
	<?php if ($exception === NULL): ?>
	<tr valign="top">
		<td>Gammu DB schema</td>
		<td>
			<?php if ($has_smsd_database): ?>
			<strong class="green"><?php echo tr('Found'); ?></strong>
			<?php else: ?>
			<strong class="red"><?php echo tr('Missing'); ?></strong><br />
			<small>Please create the Gammu tables of the database first. Refer to Gammu's documentation.</small>
			<?php endif; ?>
		</td>
	</tr>

	<?php if ($has_smsd_database): ?>
	<tr valign="top">
		<td>Gammu DB version</td>
		<td><strong><?php echo htmlentities($this->Kalkun_model->get_gammu_info('db_version')->row('Version'), ENT_QUOTES); ?></strong>
			<br /><small>Read from your gammu database schema.</small>
		</td>
	</tr>
	<tr valign="top">
		<td>Gammu phonebook table</td>
		<td>
			<?php 	if ($this->Kalkun_model->has_table_pbk()): ?>
			<strong class="green"><?php echo tr('Found'); ?></strong>
			<?php 	else: ?>
			<strong class="orange"><?php echo tr('Missing'); ?></strong><br />
			<small>Click 'Run Database Setup' below to install it.</small>
			<?php	 endif; ?>
		</td>
	</tr>

	<tr valign="top">
		<td>Kalkun DB</td>
		<?php	if ($this->db->table_exists('user')): ?>
		<td><strong class="green"><?php echo tr('Found'); ?></strong></td>
		<?php	else: ?>
		<td><strong class="orange"><?php echo tr('Missing'); ?></strong><br />Click 'Run Database Setup' below to install it.</td>
		<?php	endif; ?>
	</tr>

	<tr valign="top">
		<td>Kalkun DB version</td>
		<?php	if ($this->db->table_exists('user')): ?>
		<td><strong><?php echo $detected_db_version; ?></strong></td>
		<?php	else: ?>
		<td><strong class="orange"><?php echo tr('Missing'); ?></strong><br />Click 'Run Database Setup' below to install it.</td>
		<?php	endif; ?>
	</tr>

	<tr valign="top">
		<td colspan="2"><br />
			<?php switch ($type):
				case 'install': ?>
			→ Kalkun database is not installed yet. Click 'Run Database Setup' below to install it.
			<?php		break;?>
			<?php	case 'upgrade_not_supported': ?>
			<strong class="red">→ Upgrade of your version of kalkun database is not supported. You will need to proceed manually.</strong>
			<?php		break;?>
			<?php	case 'upgrade': ?>
			→ Kalkun database is already installed in a former version (database schema of version <?php echo $detected_db_version;?> detected). Click 'Run Database Setup' below to upgrade it.
			<?php		break; ?>
			<?php	case 'up_to_date': ?>
			→ Kalkun database is already up-to-date (database schema of version <?php echo $detected_db_version;?> detected).
			<?php		break; ?>
			<?php endswitch; ?>
		</td>
	</tr>
	<?php endif; ?>
	<?php else: /* $exception !== NULL */ ?>
	<tr valign="top">
		<td colspan="2">
			<p class="red">There was a problem when trying to load the database.</p>
			<p>Reported error is:</p>
			<p><code><?php echo htmlentities($exception, ENT_QUOTES); ?></code></p>
			<p>Please check your database configuration in <code>application/config/database.php</code>. Then click on button to check again.</p>
		</td>
	</tr>
	<?php endif; ?>
</table>
<p>&nbsp;</p>

<?php if ($exception !== NULL): ?>
<div align="center">
	<?php
	echo form_open('install/database_setup');
	echo form_hidden('idiom', $idiom);
	echo form_submit('submit', tr_raw('Check again'), 'class="button"');
	echo form_close();
?>
</div>
<?php endif; ?>

<p>&nbsp;</p>
<div>
	<p>
		<?php
	echo form_open('install/requirement_check', 'style="display:inline"');
	echo form_hidden('idiom', $idiom);
	echo form_submit('submit', '‹ '.tr_raw('Previous'), 'class="button"');
	echo form_close();
?>
		<?php if ($exception === NULL): ?>
		<?php if ($type === 'install' OR $type === 'upgrade' OR ! $this->Kalkun_model->has_table_pbk()):
		$btn_text = 'Run Database Setup'.' ›';
	else:
		$btn_text = tr_raw('Continue').' ›';
	endif; ?>
		<?php if ($type !== 'upgrade_not_supported'): ?>
		<?php echo form_open('install/run_install', 'style="display:inline"');?>
		<?php echo form_hidden('idiom', $idiom); ?>
		<?php echo form_submit('submit', $btn_text, 'class="button"'); ?>
	</p>
	<?php echo form_close();?>
		<?php endif; ?>
		<?php endif; ?>
	</p>
</div>
