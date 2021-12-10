<?php $this->load->view('js_stop_manager');?>

<div id="window_container">
	<div id="window_title">
		<div id="window_title_left">Stop Manager records</div>
		<div id="window_title_right">
			<?php echo form_open('plugin/stop_manager', array('class' => 'search_form')); ?>
			<input type="text" name="search_name" size="20" class="search_name" placeholder="Search..." value="<?php echo $this->input->post('search_name');?>" />
			<?php echo form_close(); ?>
			&nbsp;
			<a href="<?php echo current_url();?>" class="nicebutton">Reset search</a>
			<a href="#" id="addstopbutton" class="nicebutton">&#43; Add STOP record</a>
		</div>
	</div>

	<div id="window_content">

		<table class="nice-table" cellpadding="0" cellspacing="0">
			<tr>
				<th class="nice-table-left">No.</th>
				<th>Phone Number</th>
				<th>Type</th>
				<th>Original opt-out SMS</th>
				<th>Insertion date</th>
				<th class="nice-table-right" colspan="1">Control</th>
			</tr>

			<?php
		if ($stoplist->num_rows() === 0)
		{
			echo '<tr><td colspan="6" style="border-left: 1px solid #000; border-right: 1px solid #000;">No STOP record found.</td></tr>';
		}
		else
		{
			foreach ($stoplist->result() as $tmp):
			?>
			<tr id="<?php echo $tmp->id_stop_manager; ?>">
				<td class="nice-table-left"><?php echo $number; ?></td>
				<td class="destination_number"><?php echo $tmp->destination_number; ?></td>
				<td class="stop_type"><?php echo $tmp->stop_type; ?></td>
				<td class="stop_message"><?php echo $tmp->stop_message; ?></td>
				<td class="reg_date"><?php echo $tmp->reg_date; ?></td>
				<td class="nice-table-right">
					<?php if ($tmp->destination_number && $tmp->stop_type) { ?>
					<a href="<?php echo site_url();?>/plugin/stop_manager/delete/<?php echo urlencode(base64_encode($tmp->destination_number));?>/<?php echo urlencode(base64_encode($tmp->stop_type));?>"><img class="ui-icon ui-icon-close" title="Delete" /></a>
					<?php } ?>
				</td>
			</tr>

			<?php
			$number++;
			endforeach;
		}
		?>
			<tr>
				<th colspan="6" class="nice-table-footer">
					<div id="simplepaging">
						<?php if (is_null($this->input->post('search_name')))
		{
			echo $this->pagination->create_links();
		}
				?>
					</div>
				</th>
			</tr>

		</table>
		<br />
	</div>
</div>
</div>


<!-- Add STOP dialog -->
<div id="stop-dialog" title="Add STOP record" class="dialog">
	<p id="validateTips">All form fields are required.</p>
	<?php echo form_open('plugin/stop_manager', array('class' => 'addstopform')); ?>
	<fieldset>
		<label for="destination_number">Phone Number</label>
		<input type="text" name="destination_number" id="destination_number" class="text ui-widget-content ui-corner-all" />
		<label for="stop_type">Type</label>
		<input type="text" name="stop_type" id="stop_type" class="text ui-widget-content ui-corner-all" />
		<label for="stop_message">Original opt-out SMS</label>
		<input type="text" name="stop_message" id="stop_message" class="text ui-widget-content ui-corner-all" />
	</fieldset>
	<?php echo form_close(); ?>
</div>
