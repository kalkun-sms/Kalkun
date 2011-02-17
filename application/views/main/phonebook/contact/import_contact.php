<!-- Import Phonebook dialog -->
<div id="importdialog" title="Import Phonebook"  class="dialog">
	<p id="validateTips">All form fields are required.</p>
	<form class="importpbkform" method="post" enctype="multipart/form-data" action="<?php echo  site_url();?>/kalkun/import_phonebook">
	<fieldset>
		<label for="csvfile">CSV File</label>
		<input type="file" name="csvfile" id="csvfile" class="text ui-widget-content ui-corner-all" />
		<label for="group">Group</label>
    	<select id="importgroupvalue" name="importgroupvalue">
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