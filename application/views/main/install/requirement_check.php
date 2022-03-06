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
		<td>>= 7.0</td>
		<td><?php echo PHP_VERSION;?></td>
		<td class="right">
			<?php
			if (version_compare(PHP_VERSION, '7.0', '>='))
			{
				echo '<span class="green">'.tr('Ok').'</span>';
			}
			else
			{
				if (version_compare(PHP_VERSION, '5.3.6', '>='))
				{
					// CI3 recommends 5.6+ (or at very least 5.3.6). We recommend >= 7.0
					echo '<span class="orange">'.tr('Ok').'</span>';
				}
				else
				{
					echo '<span class="red">'.tr('Missing').'</span>';
					$error++;
				}
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
</table>

<p>&nbsp;</p>

<?php if ($error > 0): ?>
<div>
	<p>Unfortunately, your system does not meet the minimum requirements to run Kalkun. Please update your system to meet the above requirements and refresh this page.</p>
</div>

<?php else: ?>
<div>
	<p>Your system is compatible with Kalkun.</p>
	<p>
		<?php
	echo form_open('install', 'style="display:inline"');
	echo form_hidden('idiom', $idiom);
	echo form_submit('submit', '‹ '.tr('Previous'), 'class="button"');
	echo form_close();
?>
		<?php
	echo form_open('install/database_setup', 'style="display:inline"');
	echo form_hidden('idiom', $idiom);
	echo form_submit('submit', tr('Next').' ›', 'class="button"');
	echo form_close();
?>
	</p>
</div>
<?php endif; ?>
