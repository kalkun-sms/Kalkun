<?php $this->load->view('js_whitelist_number');?>

<!-- Add Whitelist dialog -->
<div id="whitelist-dialog" title="Add Whitelist Number" class="dialog">
	<p id="validateTips">All form fields are required.</p>
	<?php echo form_open('plugin/whitelist_number', array('class' => 'addwhitelistnumberform')); ?>
	<fieldset>
		<label for="phone_number">Match</label>
		<input type="text" name="match" id="phone_number" class="text ui-widget-content ui-corner-all" />
	</fieldset>
	<?php echo form_close(); ?>
</div>


<!-- Edit Whitelist dialog -->
<div id="editwhitelist-dialog" title="Edit Whitelist Number" class="dialog">
	<p id="validateTips">All form fields are required.</p>
	<?php echo form_open('plugin/whitelist_number', array('class' => 'editwhitelistnumberform')); ?>
	<fieldset>
		<input type="hidden" name="editid_whitelist" id="editid_whitelist" />
		<label for="editphone_number">Match</label>
		<input type="text" name="editmatch" id="editphone_number" class="text ui-widget-content ui-corner-all" />
	</fieldset>
	<?php echo form_close(); ?>
	</form>
</div>

<div id="space_area">
	<h3 style="float: left">Match</h3>
	<div style="float: right">
		<a href="#" id="addwhitelistbutton" class="nicebutton">&#43; Add match rule</a>
	</div>

	<table class="nice-table" cellpadding="0" cellspacing="0">
		<tr>
			<th class="nice-table-left">No.</th>
			<th>Match</th>
			<th class="nice-table-right" colspan="2">Control</th>
		</tr>

		<?php
		if ($whitelist->num_rows() === 0)
		{
			echo '<tr><td colspan="5" style="border-left: 1px solid #000; border-right: 1px solid #000;">No whitelist number found.</td></tr>';
		}
		else
		{
			foreach ($whitelist->result() as $tmp):
			?>
		<tr id="<?php echo $tmp->id_whitelist; ?>">
			<td class="nice-table-left"><?php echo $number; ?></td>
			<td class="phone_number"><?php echo $tmp->match; ?></td>
			<td><a href="#" class="edit"><img class="ui-icon ui-icon-pencil" title="Edit" /></a></td>
			<td class="nice-table-right"><a href="<?php echo site_url(); ?>/plugin/whitelist_number/delete/<?php echo $tmp->id_whitelist; ?>"><img class="ui-icon ui-icon-close" title="Delete" /></a></td>
		</tr>

		<?php
			$number++;
			endforeach;
		}
		?>
		<tr>
			<th colspan="5" class="nice-table-footer">
				<div id="simplepaging"><?php echo $this->pagination->create_links();?></div>
			</th>
		</tr>

	</table>
	<br />
</div>
