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
				<td><?php echo tr('See {0} page', NULL, '<a class="base_color underline_link" href="https://raw.githubusercontent.com/kalkun-sms/Kalkun/devel/CREDITS" target="_blank">CREDITS</a>'); ?></td>
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
				<td><a class="base_color underline_link" href="https://spdx.org/licenses/GPL-3.0-or-later.html">GPL-3.0-or-later</a></td>
			<tr>
				<td><b><?php echo tr('Homepage'); ?>:</b></td>
				<td>&nbsp;</td>
				<td><a class="base_color underline_link" href="https://github.com/kalkun-sms/Kalkun/" target="_blank">https://github.com/kalkun-sms/Kalkun/</a></td>
			</tr>
		</table>
		<br />
		<!--center>
			<a class="underline_link" href="http://kalkun.sourceforge.net/contribute.php"><b>~ DONATE THIS PROJECT ~</b></a>
		</center-->
		<hr style="border-style: solid; border-color: #86C0D2;" />
		<p>If you find an issue, please report it on the <a class="base_color underline_link" href="https://github.com/kalkun-sms/Kalkun/issues" target="_blank">issue page of the project</a> and add the information below:</p>
		<p>
			<b>* Kalkun version:</b>
			`<?php echo $this->config->item('kalkun_version');?> [Lang: <?php echo $this->Kalkun_model->get_setting()->row('language');?>] [CountryCode: <?php echo $this->Kalkun_model->get_setting()->row('country_code');?>]`
			<br /><b>* Operating system:</b>
			`<?php echo php_uname(); ?>`
			<br /><b>* PHP Version:</b>
			`<?php echo phpversion(); ?>`
			<br /><b>* DB Backend:</b>
			`<?php
				$this->load->helper('kalkun_helper');
				$db_name_human = get_database_property($this->db->platform())['human'];
				echo $db_name_human, ' ', $this->db->version(), ' (', $this->db->platform(), ')'; ?>`
			<br /><b>* Gammu version:</b>
			`<?php echo  filter_data($this->Kalkun_model->get_gammu_info('gammu_version')->row('Client')); ?>`
			<br /><b>* Gammu DB schema:</b>
			`<?php echo  filter_data($this->Kalkun_model->get_gammu_info('db_version')->row('Version')); ?>`
			<br /><b>* Browser:</b>
			`<?php
					$this->load->library('user_agent');
					echo $this->agent->browser(), ' ', $this->agent->version() ; ?>`
			<br /><b>* Plugins:</b>
			`<?php
					$this->load->model('Plugin_model');
					$installed_plugins = array_column($this->Plugin_model->get_plugins()->result_array(), 'plugin_system_name');
					echo implode(', ', $installed_plugins);
					?>`
		</p>
	</div>
</div>

<!-- Add Folder Dialog -->
<div id="addfolderdialog" title="<?php echo tr('Add folder');?>" class="dialog">
	<form class="addfolderform" method="post" action="<?php echo  site_url();?>/kalkun/add_folder">
		<label for="name"><?php echo tr('Folder name');?></label>
		<input type="hidden" name="source_url" value="<?php echo $this->uri->uri_string();?>" />
		<input type="hidden" name="id_user" value="<?php echo $this->session->userdata('id_user');?>" />
		<input type="text" name="folder_name" id="folder_name" class="text ui-widget-content ui-corner-all" />
	</form>
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
			<td align="right"><label for="a_search_from_to"><b><?php echo tr('From/To');?></b></label></td>
			<td colspan="3"><input style="width: 95%" type="text" id="a_search_from_to" name="a_search_from_to" /></td>
		</tr>
		<tr>
			<td align="right"><label for="a_search_query"><b><?php echo tr('Query');?></b></label></td>
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
					foreach ($my_folders->result() as $my_folder):
					echo "<option value=\"{$my_folder->id_folder}\">{$my_folder->name}</option>";
					endforeach;
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td align="right"><label for="a_search_date_from"><b><?php echo tr('Date From');?></b></label></td>
			<td><input type="text" id="a_search_date_from" name="a_search_date_from" /></td>
			<td><label for="a_search_date_to"><b><?php echo tr('Date To');?></b></label></td>
			<td><input type="text" id="a_search_date_to" name="a_search_date_to" /></td>
		</tr>
		<tr>
			<td align="right"><label for="a_search_sentitems_status"><b><?php echo tr('Status');?></b></label></td>
			<td colspan="3">
				<select name="a_search_sentitems_status" style="width: 98%">
					<option><?php echo tr('Any');?></option>
					<option><?php echo tr('Delivered');?></option>
					<option><?php echo tr('Sending failed');?></option>
				</select>
			</td>
		</tr>
		<tr>
			<td align="right"><label for="a_search_paging"><b><?php echo tr('Paging');?></b></label></td>
			<td colspan="3">
				<select name="a_search_paging" style="width: 98%">
					<option value="10">10 <?php echo tr('per page');?></option>
					<option value="20">20 <?php echo tr('per page');?></option>
					<option value="30">30 <?php echo tr('per page');?></option>
					<option value="40">40 <?php echo tr('per page');?></option>
					<option value="50">50 <?php echo tr('per page');?></option>
					<option value="all"><?php echo tr('No paging');?></option>
				</select>
			</td>
		</tr>
	</table>
	<?php echo form_close();?>
</div>
