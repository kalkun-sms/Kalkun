<?php $this->load->view('js_init/phonebook/js_phonebook');?>

<div id="contact_container" class="hidden"></div>

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
	<div id="window_title_left"><?php echo lang('tni_contacts'); ?></div>
	<div id="window_title_right">
	<?php echo form_open('phonebook', array('class' => 'search_form')); ?>
	<input type="text" name="search_name" size="20" class="search_name" value="" />
	<?php echo form_close(); ?>	
	&nbsp;
	<a href="#" id="addpbkcontact" class="addpbkcontact nicebutton">&#43; <?php echo lang('tni_contact_add'); ?></a>	
	<a href="#" id="importpbk" class="nicebutton">&#43; Import</a>
	<a href="<?php echo site_url('phonebook/group');?>" id="addpbkgroup" class="nicebutton">&#43; <?php echo lang('tni_groups_manage'); ?></a>
	</div>
</div>

<div id="window_content">
	<?php $this->load->view("main/phonebook/contact/navigation");?>
	<div id="pbk_list"><?php $this->load->view('main/phonebook/contact/pbk_list');?></div>
	<?php $this->load->view("main/phonebook/contact/navigation");?>
</div>
</div>
