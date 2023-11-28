<!-- About dialog -->
<div id="about" title="<?php echo tr('About {0}', NULL, 'Kalkun');?>" class="dialog">
	<div class="mascot" style="float: left;">
		<img src="<?php echo $this->config->item('img_path');?>mascot.png" />
	</div>

	<div class="detail" style="float: left">
		<center>
			<div class="base_bg rounded"><img src="<?php echo $this->config->item('img_path');?>logo.png" /></div>
			<h1><?php echo tr('PHP Frontend for gammu-smsd'); ?></h1>
		</center>
		<table>
			<tr valign="top">
				<td><b><?php echo tr('Authors'); ?>:</b></td>
				<td>&nbsp;</td>
				<td><?php echo tr_raw('See {0} page', NULL, '<a class="base_color underline_link" href="https://raw.githubusercontent.com/kalkun-sms/Kalkun/devel/docs/CREDITS" target="_blank">CREDITS</a>'); ?></td>
			</tr>
			<tr>
				<td><b><?php echo tr('Version'); ?>:</b></td>
				<td>&nbsp;</td>
				<td><?php echo $this->config->item('kalkun_version').' ('.$this->config->item('kalkun_codename').')';?></td>
			</tr>
			<tr>
				<td><b><?php echo tr('Released'); ?>:</b></td>
				<td>&nbsp;</td>
				<td><?php echo $this->config->item('kalkun_release_date');?></td>
			<tr>
				<td><b><?php echo tr('License'); ?>:</b></td>
				<td>&nbsp;</td>
				<td><a class="base_color underline_link" href="https://spdx.org/licenses/GPL-2.0-or-later.html">GPL-2.0-or-later</a></td>
			<tr>
				<td><b><?php echo tr('Homepage'); ?>:</b></td>
				<td>&nbsp;</td>
				<td><a class="base_color underline_link" href="https://kalkun.sourceforge.io/" target="_blank">https://kalkun.sourceforge.io/</a></td>
			</tr>
		</table>
		<br />
		<!--center>
			<a class="underline_link" href="https://kalkun.sourceforge.io/contribute.php"><b>~ DONATE THIS PROJECT ~</b></a>
		</center-->
		<hr style="border-style: solid; border-color: #86C0D2;" />
		<p>If you find an issue, please report it on the <a class="base_color underline_link" href="https://github.com/kalkun-sms/Kalkun/issues" target="_blank">issue page of the project</a> and add the information below:</p>
		<p>
			<b>* Kalkun version:</b>
			`<?php echo $this->config->item('kalkun_version');?> [Lang: <?php echo htmlentities($this->Kalkun_model->get_setting()->row('language'), ENT_QUOTES);?>] [CountryCode: <?php echo htmlentities($this->Kalkun_model->get_setting()->row('country_code'), ENT_QUOTES);?>]`
			<br /><b>* Operating system:</b>
			`<?php echo htmlentities(php_uname(), ENT_QUOTES); ?>`
			<br /><b>* PHP Version:</b>
			`<?php echo htmlentities(phpversion(), ENT_QUOTES); ?>`
			<br /><b>* DB Backend:</b>
			`<?php
				$this->load->helper('kalkun_helper');
				$db_name_human = get_database_property($this->db->platform())['human'];
				echo $db_name_human, ' ', $this->db->version(), ' (', $this->db->platform(), ')'; ?>`
			<br /><b>* Gammu version:</b>
			`<?php echo  filter_data(htmlentities(strval($this->Kalkun_model->get_gammu_info('gammu_version')->row('Client'))), ENT_QUOTES); ?>`
			<br /><b>* Gammu DB schema:</b>
			`<?php echo  filter_data(htmlentities($this->Kalkun_model->get_gammu_info('db_version')->row('Version')), ENT_QUOTES); ?>`
			<br /><b>* Browser:</b>
			`<?php
					$this->load->library('user_agent');
					echo htmlentities($this->agent->browser(), ENT_QUOTES), ' ', htmlentities($this->agent->version(), ENT_QUOTES) ; ?>`
			<br /><b>* Plugins:</b>
			`<?php
					$this->load->model('Plugin_model');
					$installed_plugins = array_column($this->Plugin_model->get_plugins()->result_array(), 'plugin_system_name');
					echo htmlentities(implode(', ', $installed_plugins), ENT_QUOTES);
					?>`
		</p>
	</div>
</div>

<!-- Add Folder Dialog -->
<div id="addfolderdialog" title="<?php echo tr('Add folder');?>" class="dialog">
	<?php
	$this->load->helper('form');
	echo form_open('kalkun/add_folder', array('class' => 'addfolderform'));
?>
	<label for="name"><?php echo tr('Folder name');?></label>
	<input type="hidden" name="id_user" value="<?php echo $this->session->userdata('id_user');?>" />
	<input type="text" name="folder_name" id="folder_name" class="text ui-widget-content ui-corner-all" />
	<?php echo form_close(); ?>
</div>

<!-- Shortcuts dialog -->
<div id="kbd" title="<?php echo tr('Keyboard shortcuts'); ?>" class="dialog">


	<div class="detail" style="float: left">
		<center>
			<h1><?php echo tr('Keyboard shortcuts'); ?></h1>
		</center>

		<table>
			<tr valign="top">
				<td>

					<table>
						<tr>
							<td colspan="2" align="center"><strong><?php echo tr('Jumping'); ?></strong> </td>
						</tr>
						<tr>
							<td class="align_right"><?php echo tr('{0} then {1}:', NULL, 'g', 'i'); ?></td>
							<td><?php echo tr('Go to {0}', NULL, tr('Inbox')); ?></td>
						</tr>
						<tr>
							<td class="align_right"><?php echo tr('{0} then {1}:', NULL, 'g', 'o'); ?></td>
							<td><?php echo tr('Go to {0}', NULL, tr('Outbox')); ?></td>
						</tr>
						<tr>
							<td class="align_right"><?php echo tr('{0} then {1}:', NULL, 'g', 's'); ?></td>
							<td><?php echo tr('Go to {0}', NULL, tr('Sent items')); ?></td>
						</tr>
						<tr>
							<td class="align_right"><?php echo tr('{0} then {1}:', NULL, 'g', 'p'); ?></td>
							<td><?php echo tr('Go to {0}', NULL, tr('Phonebook')); ?></td>
						</tr>

						<tr>
							<td colspan="2" align="center"><br /><strong><?php echo tr('Navigation'); ?></strong></td>
						</tr>
						<tr>
							<td class="align_right">u:</td>
							<td><?php echo tr('Back to conversation list'); ?></td>
						</tr>
						<tr>
							<td class="align_right">k / j:</td>
							<td><?php echo tr('Highlight prev/next'); ?></td>
						</tr>
						<tr>
							<td class="align_right">p / n:</td>
							<td><?php echo tr('Open prev/next (message only)'); ?></td>
						</tr>
						<tr>
							<td class="align_right"><?php echo tr('{0} or {1}:', NULL, 'o', 'ENTER'); ?></td>
							<td><?php echo tr('Open'); ?></td>
						</tr>
						<tr>
							<td colspan="2" align="center"><br /><strong><?php echo tr('Selection'); ?></strong></td>
						</tr>
						<tr>
							<td class="align_right">x:</td>
							<td><?php echo tr('Select'); ?></td>
						</tr>
						<tr>
							<td class="align_right"><?php echo tr('{0} then {1}:', NULL, '*', 'a'); ?></td>
							<td><?php echo tr('Select all'); ?></td>
						</tr>
						<tr>
							<td class="align_right"><?php echo tr('{0} then {1}:', NULL, '*', 'n'); ?></td>
							<td><?php echo tr('Deselect all'); ?></td>
						</tr>
					</table>

				</td>
				<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>

				<td>
					<table>
						<tr>
							<td colspan="2" align="center"><strong><?php echo tr('Actions'); ?></strong></td>
						</tr>

						<tr>
							<td class="align_right">m:</td>
							<td><?php echo tr('Move selected'); ?></td>
						</tr>
						<tr>
							<td class="align_right">#:</td>
							<td><?php echo tr('Delete selected'); ?></td>
						</tr>
						<tr>
							<td class="align_right">r:</td>
							<td><?php echo tr('Reply'); ?></td>
						</tr>
						<tr>
							<td class="align_right">f:</td>
							<td><?php echo tr('Forward'); ?></td>
						</tr>
						<tr>
							<td class="align_right"><?php echo tr('{0} then {1}:', NULL, 'TAB', 'ENTER'); ?></td>
							<td><?php echo tr('Send message'); ?></td>
						</tr>
						<tr>
							<td class="align_right">d:</td>
							<td><?php echo tr('Message details'); ?></td>
						</tr>

						<tr>
							<td colspan="2" align="center"> <br /><strong><?php echo tr('Application'); ?></strong>
							</td>
						</tr>
						<tr>
							<td class="align_right">c:</td>
							<td><?php echo tr('Compose'); ?></td>
						</tr>
						<tr>
							<td class="align_right">s:</td>
							<td><?php echo tr('Search'); ?></td>
						</tr>
						<tr>
							<td class="align_right">Shift + /:</td>
							<td><?php echo tr('Open shortcut help'); ?></td>
						</tr>
						<tr>
							<td></td>
							<td></td>
						</tr>
					</table>

				</td>
			</tr>
		</table>

		<br />

	</div>
</div>

<!-- Advanced Search Dialog -->
<div id="a_search_dialog" title="<?php echo tr('Advanced search');?>" class="dialog">
	<?php
	$this->load->helper('form');
	echo form_open('messages/query', array('id' => 'a_search_form'));
	echo form_hidden('a_search_trigger', TRUE);
	?>
	<table width="100%">
		<tr>
			<td align="right"><label for="a_search_from_to"><b><?php echo tr('Phone number');?></b></label></td>
			<td colspan="3"><input style="width: 95%" type="text" id="a_search_from_to" name="a_search_from_to" /></td>
		</tr>
		<tr>
			<td align="right"><label for="a_search_query"><b><?php echo tr('Content');?></b></label></td>
			<td colspan="3"><input style="width: 95%" type="text" id="a_search_query" name="a_search_query" /></td>
		</tr>
		<tr>
			<td align="right"><label for="a_search_on"><b><?php echo tr('Folder');?></b></label></td>
			<td colspan="3">
				<select name="a_search_on" style="width: 98%">
					<option value="all"><?php echo tr('All');?></option>
					<option value="1"><?php echo tr('Inbox');?></option>
					<option value="3"><?php echo tr('Sent items');?></option>
					<option value="6"><?php echo tr('Spam');?></option>
					<option value="5"><?php echo tr('Trash');?></option>
					<?php
					$my_folders = $this->Kalkun_model->get_folders('all');
					foreach ($my_folders->result() as $my_folder): ?>
					<option value="<?php echo htmlentities($my_folder->id_folder, ENT_QUOTES); ?>"><?php echo htmlentities($my_folder->name, ENT_QUOTES); ?></option>
					<?php
					endforeach; ?>
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td align="right"><label for="a_search_date_from"><b><?php echo tr('Date from');?></b></label></td>
			<td><input type="text" id="a_search_date_from" name="a_search_date_from" /></td>
			<td><label for="a_search_date_to"><b><?php echo tr('Date to');?></b></label></td>
			<td><input type="text" id="a_search_date_to" name="a_search_date_to" /></td>
		</tr>
		<tr>
			<td align="right"><label for="a_search_sentitems_status"><b><?php echo tr('Status');?></b></label></td>
			<td colspan="3">
				<select name="a_search_sentitems_status" style="width: 98%">
					<option value="all"><?php echo tr('All');?></option>
					<option value="delivered"><?php echo tr('Delivered');?></option>
					<option value="failed"><?php echo tr('Sending failed');?></option>
				</select>
			</td>
		</tr>
		<tr>
			<td align="right"><label for="a_search_paging"><b><?php echo tr('Paging');?></b></label></td>
			<td colspan="3">
				<select name="a_search_paging" style="width: 98%">
					<option value="10"><?php echo tr('{0} per page', NULL, 10);?></option>
					<option value="20"><?php echo tr('{0} per page', NULL, 20);?></option>
					<option value="30"><?php echo tr('{0} per page', NULL, 30);?></option>
					<option value="40"><?php echo tr('{0} per page', NULL, 40);?></option>
					<option value="50"><?php echo tr('{0} per page', NULL, 50);?></option>
					<option value="all"><?php echo tr('All');?></option>
				</select>
			</td>
		</tr>
	</table>
	<?php echo form_close();?>

	<!-- Add Error container Dialog -->
	<div id="error_container" title="<?php echo tr('Error'); ?>" class="dialog">
		<div id="error_container_delay_notif" class="notif" style="display: none;">
			<span id="retry-progress"><?php echo tr_raw('Retrying in {0} seconds.', NULL, '<span id="countdown-count">unset</span>'); ?></span>
			<span id="retry-now" style="display: none;"><?php echo tr('Retrying now'); ?></span>
		</div>
		<div id="error_container_main"></div>
	</div>

	<!-- POST or GET data container -->
	<div id="post_get_data" style="display: none;">
		<?php
	if ($this->input->post())
	{
		echo htmlentities(json_protect($this->input->post(), ENT_QUOTES));
	}
	else
	{
		if ($this->session->flashdata('bef_login_post_data'))
		{
			echo htmlentities(json_protect($this->session->flashdata('bef_login_post_data'), ENT_QUOTES));
		}
		else
		{
			if ($this->input->get())
			{
				echo htmlentities(json_protect($this->input->get(), ENT_QUOTES));
			}
			else
			{
				echo htmlentities(json_protect([], ENT_QUOTES));
			}
		}
	}

?>
	</div>
</div>
