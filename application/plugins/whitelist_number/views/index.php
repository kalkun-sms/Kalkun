<script language="javascript" src="<?php echo $this->config->item('js_path');?>jquery-plugin/jquery.validate.min.js"></script>
<?php $this->load->view('js_whitelist_number');?>

<!-- Add Whitelist dialog -->
<div id="whitelist-dialog" title="Add Whitelist Number" class="dialog">
	<p id="validateTips">All form fields are required.</p>
	<?php echo form_open('plugin/whitelist_number', array('class' => 'addwhitelistnumberform', 'id' => 'addwhitelistnumberform')); ?>
	<fieldset>
		<label for="phone_number">Match pattern as required by PHP's preg_match()</label>
		<input type="text" name="match" id="phone_number" class="text ui-widget-content ui-corner-all" />
	</fieldset>
	<?php echo form_close(); ?>
</div>


<!-- Edit Whitelist dialog -->
<div id="editwhitelist-dialog" title="Edit Whitelist Number" class="dialog">
	<p id="validateTips">All form fields are required.</p>
	<?php echo form_open('plugin/whitelist_number', array('class' => 'editwhitelistnumberform', 'id' => 'editwhitelistnumberform')); ?>
	<fieldset>
		<input type="hidden" name="editid_whitelist" id="editid_whitelist" />
		<label for="editphone_number">Match pattern as required by PHP's preg_match()</label>
		<input type="text" name="editmatch" id="editphone_number" class="text ui-widget-content ui-corner-all" />
	</fieldset>
	<?php echo form_close(); ?>
	</form>
</div>

<div id="space_area">
	<h3 style="float: left">Match</h3>
	<div style="float: right">
		<a href="javascript:void(0);" id="addwhitelistbutton" class="nicebutton">&#43; Add match rule</a>
	</div>

	<table class="nice-table" cellpadding="0" cellspacing="0">
		<tr>
			<th class="nice-table-left">No.</th>
			<th>Match</th>
			<th class="nice-table-right" colspan="2">Control</th>
		</tr>

		<?php if ($whitelist->num_rows() === 0): ?>
		<tr>
			<td colspan="5" style="border-left: 1px solid #000; border-right: 1px solid #000;">No whitelist number found.</td>
		</tr>
		<?php else:
			foreach ($whitelist->result() as $tmp):
			?>
		<tr id="<?php echo htmlentities($tmp->id_whitelist, ENT_QUOTES); ?>">
			<td class="nice-table-left"><?php echo htmlentities($number, ENT_QUOTES); ?></td>
			<td class="phone_number"><?php echo htmlentities($tmp->match, ENT_QUOTES); ?></td>
			<td><a href="javascript:void(0);" class="edit"><img class="ui-icon ui-icon-pencil" title="<?php echo tr('Edit'); ?>" /></a></td>
			<td class="nice-table-right"><a href="javascript:void(0);" class="delete"><img class="ui-icon ui-icon-close" title="<?php echo tr('Delete'); ?>" /></a></td>
		</tr>

		<?php
			$number++;
			endforeach;
		endif;
		?>
		<tr>
			<th colspan="5" class="nice-table-footer">
				<div id="simplepaging"><?php echo $this->pagination->create_links();?></div>
			</th>
		</tr>

	</table>
	<br />
</div>
