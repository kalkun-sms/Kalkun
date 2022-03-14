<?php $this->load->view('js_init/phonebook/js_phonebook');?>

<div id="contact_container" class="hidden"></div>

<!-- Delete Contact Dialog -->
<div class="dialog" id="confirm_delete_contact_dialog" title="<?php echo tr('Delete contact(s) confirmation');?>">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
		<?php echo tr('Are you sure you want to delete {0} contact(s)?', NULL, '<span id="contact-delete-count"></span>');?> </p>
</div>

<!-- Add contact wizard dialog -->
<div id="pbk_add_wizard_dialog" title="<?php echo tr('Select add contact method');?>" class="dialog">
	<div align="left">
		<p><a href="javascript:void(0);" id="addpbkcontact" class="addpbkcontact"><big><strong><?php echo tr('Manual input');?></strong></big><br />
				<?php echo tr('Manually add contact using contact form');?>
			</a></p>

		<p><a href="javascript:void(0);" id="importpbk"><big><strong><?php echo tr('From CSV file');?></strong></big><br />
				<?php echo tr('Import contact from CSV format file');?>
			</a></p>
	</div>
</div>

<!-- Import Phonebook dialog -->
<div id="pbkimportdialog" title="<?php echo tr('From CSV file');?>" class="dialog">
	<p id="validateTips"><?php echo tr('All form fields are required.'); ?></p>
	<?php
	$this->load->helper('form');
	echo form_open_multipart('phonebook/import_phonebook', array('class' => 'importpbkform'));
?>
	<fieldset>
		<input type="hidden" name="pbk_id_user" id="pbk_id_user" value="<?php echo $this->session->userdata('id_user');?>" />
		<label for="csvfile"><?php echo tr('CSV file');?></label>
		<input type="file" name="csvfile" id="csvfile" class="text ui-widget-content ui-corner-all" />
		<p><small><?php echo tr('The CSV file must be in valid format').':';?> <a href="<?php echo $this->config->item('csv_path');?>contact_sample.csv"><b><?php echo tr('valid example');?></b></a></small></p>
		<p><input type="checkbox" name="is_public" id="is_public" style="display: inline" <?php if (isset($contact) && $contact->row('is_public') == 'true')
{
	echo 'checked="checked"';
}?> />
			<label for="is_public" style="display: inline"><?php echo tr('Set as public contact');?></label>
		</p>
		<label for="group"><?php echo tr('Groups');?></label>
		<select id="importgroupvalue" name="importgroupvalue">
			<option value="">-- <?php echo tr('Select group name');?> --</option>
			<?php
		foreach ($pbkgroup as $tmp):
		echo '<option value="'.$tmp->ID.'">'.htmlentities($tmp->GroupName, ENT_QUOTES).'</option>';
		endforeach;
		?>
		</select>
	</fieldset>
	<?php echo form_close(); ?>
</div>


<div id="window_container">
	<div id="window_title">
		<div id="window_title_left"><?php echo $title; ?></div>
		<div id="window_title_right">
			<a href="javascript:void(0);" id="sendallcontact" class="nicebutton">&#43; <?php echo tr('Send to all contacts'); ?></a>
			<a href="javascript:void(0);" id="addpbkcontact_wizard" class="nicebutton">&#43; <?php echo tr('Add contact'); ?></a>
			<?php if ($public_contact) : ?>
			<a href="<?php echo site_url('phonebook/');?>" class="nicebutton">&#43; <?php echo tr('My contacts');?></a>
			<?php else: ?>
			<a href="<?php echo site_url('phonebook/index/public');?>" class="nicebutton">&#43; <?php echo tr('Public contacts');?></a>
			<?php endif; ?>
			<a href="<?php echo site_url('phonebook/group');?>" id="addpbkgroup" class="nicebutton">&#43; <?php echo tr('Manage groups'); ?></a>
		</div>
	</div>

	<div id="window_content">
		<?php $this->load->view('main/phonebook/contact/navigation');?>
		<div id="pbk_list"><?php $this->load->view('main/phonebook/contact/pbk_list');?></div>
		<?php $this->load->view('main/phonebook/contact/navigation');?>
	</div>
</div>
