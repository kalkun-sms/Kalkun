<?php $this->load->view('js_init/phonebook/js_phonebook');?>

<div id="contact_container" class="hidden"></div>

<!-- Add contact wizard dialog -->
<div id="pbk_add_wizard_dialog" title="Select Add Contact Method" class="dialog">
	<div align="left">
	<p><a href="#" id="addpbkcontact" class="addpbkcontact"><big><strong>Using contact form</strong></big><br />
	Manually add contact using contact form
	</a></p>
	
	<p><a href="#" id="importpbk"><big><strong>Import CSV file</strong></big><br />
	Import contact from CSV format file
	</a></p>
	</div>
</div>

<!-- Import Phonebook dialog -->
<div id="pbkimportdialog" title="Import Phonebook"  class="dialog">
	<p id="validateTips"><?php echo lang('tni_form_fields_required'); ?></p>
	<form class="importpbkform" method="post" enctype="multipart/form-data" action="<?php echo site_url();?>/phonebook/import_phonebook">
	<fieldset>
		<input type="hidden" name="pbk_id_user" id="pbk_id_user" value="<?php echo $this->session->userdata('id_user');?>" />
		<label for="csvfile">CSV File</label>
		<input type="file" name="csvfile" id="csvfile" class="text ui-widget-content ui-corner-all" />
		<p><small>The CSV file must be in valid format, see <a href="<?php echo $this->config->item('csv_path');?>contact_sample.csv">Valid Example</a></small></p>
		<label for="group">Group</label>
    	<select id="importgroupvalue" name="importgroupvalue">
    	<option value="">-- Select Group --</option>
    	<?php
    	foreach($pbkgroup as $tmp):
    	echo "<option value=\"".$tmp->ID."\">".$tmp->GroupName."</option>";
    	endforeach; 
    	?>
    	</select>
	</fieldset>
	</form>
</div>

	
<div id="window_container">
<div id="window_title">
	<div id="window_title_left"><?php echo $title; ?></div>
	<div id="window_title_right">
	<a href="#" id="addpbkcontact_wizard" class="nicebutton">&#43; <?php echo lang('tni_contact_add'); ?></a>	
	<a href="<?php echo site_url('phonebook/group');?>" id="addpbkgroup" class="nicebutton">&#43; <?php echo lang('tni_groups_manage'); ?></a>
	</div>
</div>

<div id="window_content">
	<?php $this->load->view("main/phonebook/contact/navigation");?>
	<div id="pbk_list"><?php $this->load->view('main/phonebook/contact/pbk_list');?></div>
	<?php $this->load->view("main/phonebook/contact/navigation");?>
</div>
</div>
