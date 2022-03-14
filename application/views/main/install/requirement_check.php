<?php $error = 0; ?>
<h2><?php echo tr('Requirements check'); ?></h2>
<p>This page checks if your system is compatible with Kalkun.</p>

<table border="0" cellspacing="0" cellpadding="0" class="simpletable">
	<tr>
		<th>Component</th>
		<th>Required</th>
		<th>Installed</th>
		<th class="right">Status</th>
	</tr>

	<tr>
		<td>PHP</td>
		<td>≥ 5.6</td>
		<td><?php echo PHP_VERSION;?></td>
		<td class="right">
			<?php
			if (version_compare(PHP_VERSION, '5.6', '>='))
			{
				echo '<span class="green">'.tr('Ok').'</span>';
			}
			else
			{
				echo '<span class="red">'.tr('Missing').'</span>';
				$error++;
			}
			?>
		</td>
	</tr>
	<tr>
		<td colspan="4" style="background-color: #cce9f2" class="right"><b>PHP extension/module</b></td>
	</tr>

	<tr>
		<td colspan="3">
			<?php echo $db_property['human']; ?> <i>(Read from database configuration)</i>
		</td>
		<td class="right">
			<?php
			if (extension_loaded($db_property['driver']))
			{
				$db_msg = '';
			}

			if (isset($db_msg))
			{
				echo '<span class="green">'.tr('Found').'</span>';
			}
			else
			{
				echo '<span class="red">'.tr('Missing').'</span>';
				$error++;
			}
		?>
		</td>
	</tr>

	<tr>
		<td colspan="3">Ctype - Character type checking</td>
		<td class="right"><?php if (extension_loaded('ctype'))
		{
			echo '<span class="green">'.tr('Found').'</span>';
		}
		else
		{
			echo '<span class="red">'.tr('Missing').'</span>';
			$error++;
		}?></td>
	</tr>

	<tr>
		<td colspan="3">cURL - Client URL Library</td>
		<td class="right"><?php if (extension_loaded('curl'))
		{
			echo '<span class="green">'.tr('Found').'</span>';
		}
		else
		{
			echo '<span class="red">'.tr('Missing').'</span>';
			$error++;
		}?></td>
	</tr>

	<tr>
		<td colspan="3">HASH - HASH Message Digest Framework</td>
		<td class="right"><?php if (extension_loaded('hash'))
		{
			echo '<span class="green">'.tr('Found').'</span>';
		}
		else
		{
			echo '<span class="red">'.tr('Missing').'</span>';
			$error++;
		}?></td>
	</tr>

	<tr>
		<td colspan="3">Intl - Internationalization</td>
		<td class="right"><?php if (extension_loaded('intl'))
		{
			echo '<span class="green">'.tr('Found').'</span>';
		}
		else
		{
			echo '<span class="red">'.tr('Missing').'</span>';
			$error++;
		}?></td>
	</tr>

	<tr>
		<td colspan="3">JSON - JavaScript Object Notation</td>
		<td class="right"><?php if (extension_loaded('json'))
		{
			echo '<span class="green">'.tr('Found').'</span>';
		}
		else
		{
			echo '<span class="red">'.tr('Missing').'</span>';
			$error++;
		}?></td>
	</tr>

	<tr>
		<td colspan="3">MBString - Multibyte String</td>
		<td class="right"><?php if (extension_loaded('mbstring'))
		{
			echo '<span class="green">'.tr('Found').'</span>';
		}
		else
		{
			echo '<span class="red">'.tr('Missing').'</span>';
			$error++;
		}?></td>
	</tr>

	<tr>
		<td colspan="3">Session - Session Handling</td>
		<td class="right"><?php if (extension_loaded('session'))
		{
			echo '<span class="green">'.tr('Found').'</span>';
		}
		else
		{
			echo '<span class="red">'.tr('Missing').'</span>';
			$error++;
		}?></td>
	</tr>

	<?php if (extension_loaded('session') && $this->config->item('sess_driver') === 'files'): ?>
	<tr>
		<td colspan="3" class="bottom">Session save path: <code><?php echo $sess_save_path; ?></code><br />
			<?php if ( ! is_writable($sess_save_path)): ?>
			→ Set a correct value for '<code>sess_save_path</code>' in the '<code>config.php</code>' file.
			<?php endif; ?></td>
		<td colspan="1" class="bottom right">
			<?php if (is_writable($sess_save_path)):
		echo '<span class="green">'.tr('Writable').'</span>';
		else:
		echo '<span class="red">'.tr('Read-only').'</span>';
		$error++;
		endif;
		?>
		</td>
	</tr>
	<?php endif; ?></td>

</table>

<p>&nbsp;</p>

<?php if ($error > 0): ?>
<div>
	<p>Unfortunately, your system does not meet the minimum requirements to run Kalkun. Please update your system to meet the above requirements. Then click on button to check again.</p>
	<div align="center">
		<?php
	echo form_open('install/requirement_check');
	echo form_hidden('idiom', $idiom);
	echo '<input type="submit" name="submit" value="'.tr('Check again').'" class="button" />';
	echo form_close();
?>
	</div>
	<p>&nbsp;</p>
	<?php
	echo form_open('install', 'style="display:inline"');
	echo form_hidden('idiom', $idiom);
	echo '<input type="submit" name="submit" value="‹ '.tr('Previous').'" class="button" />';
	echo form_close();
?>
</div>

<?php else: ?>
<div>
	<p>Your system is compatible with Kalkun.</p>
	<p>&nbsp;</p>
	<p>
		<?php
	echo form_open('install', 'style="display:inline"');
	echo form_hidden('idiom', $idiom);
	echo '<input type="submit" name="submit" value="‹ '.tr('Previous').'" class="button" />';
	echo form_close();
?>
		<?php
	echo form_open('install/database_setup', 'style="display:inline"');
	echo form_hidden('idiom', $idiom);
	echo '<input type="submit" name="submit" value="'.tr('Next').' ›" class="button" />';
	echo form_close();
?>
	</p>
</div>
<?php endif; ?>
