<?php $this->load->view('js_blacklist_number');?>

<!-- Add Blacklist dialog -->	
<div id="blacklist-dialog" title="Add Blacklist Number" class="dialog">
	<p id="validateTips">All form fields are required.</p>
	<?php echo form_open('plugin/blacklist_number', array('class' => 'addblacklistnumberform')); ?>
	<fieldset>
		<label for="phone_number">Phone Number</label>
		<input type="text" name="phone_number" id="phone_number" class="text ui-widget-content ui-corner-all" />
		<label for="reason">Reason For Blacklisted</label>
		<input type="text" name="reason" id="reason" value="" class="text ui-widget-content ui-corner-all" />
	</fieldset>
	<?php echo form_close(); ?>
</div>


<!-- Edit Blacklist dialog -->
<div id="editblacklist-dialog" title="Edit Blacklist Number"  class="dialog">
	<p id="validateTips">All form fields are required.</p>
	<?php echo form_open('plugin/blacklist_number', array('class' => 'editblacklistnumberform')); ?>
	<fieldset>
		<input type="hidden" name="editid_blacklist_number" id="editid_blacklist_number" />
		<label for="editphone_number">Phone Number</label>
		<input type="text" name="editphone_number" id="editphone_number" class="text ui-widget-content ui-corner-all" />
		<label for="editreason">Reason For Blacklisted</label>
		<input type="text" name="editreason" id="editreason" value="" class="text ui-widget-content ui-corner-all" />
	</fieldset>
	<?php echo form_close(); ?>
	</form>
</div>

<div id="space_area">
<h3 style="float: left">Blacklist Number</h3> 
<div style="float: right">
<a href="#" id="addblacklistbutton" class="nicebutton">&#43; Add Blacklist Number</a>
</div>

	<table class="nice-table" cellpadding="0" cellspacing="0">
		<tr>
			<th class="nice-table-left">No.</th>
			<th>Phone Number</th>
			<th>Reason For Blacklisted</th>
			<th class="nice-table-right" colspan="2">Control</th>
		</tr>
	    
		<?php 
		if($blacklist->num_rows()==0)
		{
			echo "<tr><td colspan=\"5\" style=\"border-left: 1px solid #000; border-right: 1px solid #000;\">No blacklist number found.</td></tr>";
		}
		else
		{
			foreach($blacklist->result() as $tmp):
			?>
			<tr id="<?php echo $tmp->id_blacklist_number;?>">
				<td class="nice-table-left"><?php echo $number;?></td>
				<td class="phone_number"><?php echo $tmp->phone_number;?></td>
				<td class="reason"><?php echo $tmp->reason;?></td>
				<td><a href="#" class="edit"><img class="ui-icon ui-icon-pencil" title="Edit" /></a></td>
				<td class="nice-table-right"><a href="<?php echo site_url();?>/plugin/blacklist_number/delete/<?php echo $tmp->id_blacklist_number;?>"><img class="ui-icon ui-icon-close" title="Delete" /></a></td>
			</tr>
			
			<?php 
			$number++;
			endforeach;
		} 
		?>
		<tr>
			<th colspan="5" class="nice-table-footer"><div id="simplepaging"><?php echo $this->pagination->create_links();?></div></th>	
		</tr>
		
	</table>
	<br />
</div>
