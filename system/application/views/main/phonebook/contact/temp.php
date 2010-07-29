<!-- Edit Phonebook dialog -->
<div id="editdialog" title="Edit Contact"  class="dialog">
	<p id="validateTips">All form fields are required.</p>
	<form>
	<fieldset>
		<input type="hidden" name="editid_pbk" id="editid_pbk" />
		<label for="name">Name</label>
		<input type="text" name="editname" id="editname" class="text ui-widget-content ui-corner-all" />
		<label for="number">Phone Number</label>
		<input type="text" name="editnumber" id="editnumber" value="" class="text ui-widget-content ui-corner-all" />
		<label for="group">Group</label>
    	<select id="editgroupvalue" name="editgroupvalue">
    	<option value="">-- Select Group --</option>
    	<?php
    	foreach($pbkgroup->result() as $tmp):
    	echo "<option value=\"".$tmp->ID."\">".$tmp->Name."</option>";
    	endforeach; 
    	?>
    	</select>
	</fieldset>
	</form>
</div>




<!-- Contact Import dialog -->
<div id="pbkimportdialog" title="Import Contact"  class="dialog">
	<p id="validateTips">Currently only CSV file supported, and the format should be: contact name, phone number.<br />
	See <a href="<?php echo base_url();?>/temp/test.csv">example file</a>.</p>
	<form class="contactimport" method="post" action="<?php echo site_url();?>/kalkun/import_phonebook">
	<fieldset>
		<input type="hidden" name="editid_pbk" id="editid_pbk" />
		<label for="file">Select File</label>
		<input type="file" name="cvsfile" id="cvsfile" class="text ui-widget-content ui-corner-all" />
    	<label for="group">Group</label>
    	<select id="editgroupvalue" name="editgroupvalue">
    	<option value="">-- Select Group --</option>
    	<?php
    	foreach($pbkgroup->result() as $tmp):
    	echo "<option value=\"".$tmp->ID."\">".$tmp->Name."</option>";
    	endforeach; 
    	?>
    	</select>
	</fieldset>
	</form>
</div>