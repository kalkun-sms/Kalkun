<script language="javascript" src="<?php echo $this->config->item('js_path');?>jquery-plugin/jquery.validate.min.js"></script>
<?php $this->load->view('js_server_alert');?>

<!-- Add Alert dialog -->	
<div id="alert-dialog" title="Add Server Alert" class="dialog">
	<p id="validateTips">All form fields are required.</p>
	<?php echo form_open('plugin/server_alert', array('class' => 'addserveralertform')); ?>
	<fieldset>
		<label for="alert_name">Alert Name</label>
		<input type="text" name="alert_name" id="alert_name" class="text ui-widget-content ui-corner-all" />
		<label for="ip_address">Host</label>
		<input type="text" name="ip_address" id="ip_address" class="text ui-widget-content ui-corner-all" />
		<div class="note">(Can be IP address or Hostname)</div><br />				
		<label for="port_number">Service Port</label>
		<input type="text" name="port_number" id="port_number" class="text ui-widget-content ui-corner-all" />		
		<label for="timeout">Connect Timeout</label>		
		<input type="text" name="timeout" id="timeout" value="30" class="text ui-widget-content ui-corner-all" />	
		<div class="note">(In seconds, default value is 30 seconds, increase this for busy server)</div><br />
		<label for="phone_number">Phone Number</label>
		<input type="text" name="phone_number" id="phone_number" class="text ui-widget-content ui-corner-all" />
		<div class="note">(Person in charge to receive the alert message)</div><br />						
		<label for="respond_message">Respond Message</label>
		<textarea style="width: 96%" name="respond_message" id="respond_message" class="text ui-widget-content ui-corner-all" maxlength=10 ></textarea>
		<div class="note">(Maximum 100 character)</div><br />		
	</fieldset>
	<?php echo form_close(); ?>
</div>


<!-- Edit Alert dialog -->
<div id="editalert-dialog" title="Edit Server Alert"  class="dialog">
	<p id="validateTips">All form fields are required.</p>
	<?php echo form_open('plugin/server_alert', array('class' => 'editserveralertform')); ?>
	<fieldset>
		<input type="hidden" name="editid_server_alert" id="editid_server_alert" />
		<label for="editalert_name">Alert Name</label>
		<input type="text" name="editalert_name" id="editalert_name" class="text ui-widget-content ui-corner-all" />
		<label for="editip_address">Host</label>
		<input type="text" name="editip_address" id="editip_address" class="text ui-widget-content ui-corner-all" />			
		<label for="editport_number">Service Port</label>		
		<input type="text" name="editport_number" id="editport_number" class="text ui-widget-content ui-corner-all" />	
		<label for="edittimeout">Connect Timeout</label>		
		<input type="text" name="edittimeout" id="edittimeout" class="text ui-widget-content ui-corner-all" />			
		<label for="editphone_number">Phone Number</label>
		<input type="text" name="editphone_number" id="editphone_number" class="text ui-widget-content ui-corner-all" />				
		<label for="editrespond_message">Respond Message</label>
		<textarea style="width: 96%" name="editrespond_message" id="editrespond_message" class="text ui-widget-content ui-corner-all" ></textarea>
	</fieldset>
	<?php echo form_close(); ?>
	</form>
</div>

<div id="space_area">
<h3 style="float: left">Server Alert</h3> 
<div style="float: right">
<a href="#" id="addalertbutton" class="nicebutton">&#43; Add Server Alert</a>
</div>

	<table class="nice-table" cellpadding="0" cellspacing="0">
		<tr>
			<th class="nice-table-left">No.</th>
			<th>Alert Name</th>
			<th>Host</th>
			<th>Service Port</th>
			<th class="hidden">Connect Timeout</th>	
			<th>Phone Number</th>
			<th class="hidden">Respond Message</th>			
			<th align="center" class="nice-table-right" colspan="3">Control</th>
		</tr>
	    
		<?php 
		if($alert->num_rows()==0)
		{
			echo "<tr><td colspan=\"8\" style=\"border-left: 1px solid #000; border-right: 1px solid #000;\">No alert found.</td></tr>";
		}
		else
		{
			foreach($alert->result() as $tmp):
			?>
			<tr id="<?php echo $tmp->id_server_alert;?>">
				<td class="nice-table-left"><?php echo $number;?></td>
				<td class="alert_name"><?php echo $tmp->alert_name;?></td>
				<td class="ip_address"><?php echo $tmp->ip_address;?></td>	
				<td class="port_number"><?php echo $tmp->port_number;?></td>
				<td class="timeout hidden"><?php echo $tmp->timeout;?></td>
				<td class="phone_number"><?php echo $tmp->phone_number;?></td>	
				<td class="respond_message hidden"><?php echo $tmp->respond_message;?></td>	
				<?php if($tmp->status=='false'):?>
				<td><a href="<?php echo site_url();?>/plugin/server_alert/change_state/<?php echo $tmp->id_server_alert;?>" class="release"><img class="ui-icon ui-icon-locked" title="Release state" /></a></td>
				<?php 
				else: echo "<td>&nbsp;</td>";
				endif; 
				?>
				<td><a href="#" class="edit"><img class="ui-icon ui-icon-pencil" title="Edit" /></a></td>				
				<td class="nice-table-right"><a href="<?php echo site_url();?>/plugin/server_alert/delete/<?php echo $tmp->id_server_alert;?>"><img class="ui-icon ui-icon-close" title="Delete" /></a></td>
			</tr>
			
			<?php 
			$number++;
			endforeach;
		} 
		?>
		<tr>
			<th colspan="8" class="nice-table-footer"><div id="simplepaging"><?php echo $this->pagination->create_links();?></div></th>	
		</tr>
		
	</table>
	<br />
	<?php echo "<div class=\"note\">Total Time Interval : ".$time_interval." seconds</div>"; ?>
</div>
