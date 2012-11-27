<script language="javascript" src="<?php echo $this->config->item('js_path');?>jquery-plugin/jquery.validate.min.js"></script>
<?php
/**
 *	@Author: bullshit "oskar@biglan.at"
 *	@Copyright: bullshit, 2010
 *	@License: GNU General Public License
*/

$this->load->view('js_remote_access');
?>
<!-- Add Remote Access dialog -->	
<div id="remoteaccess-dialog" title="Add Remote Access" class="dialog">
	<p id="validateTips">All form fields are required.</p>
	<?php echo form_open('plugin/soap', array('class' => 'addremoteaccessform')); ?>
	<fieldset>
		<label for="access_name">Access Name</label>
		<input type="text" name="access_name" id="access_name" class="text ui-widget-content ui-corner-all" />
		<label for="ip_address">IP Address</label>
		<input type="text" name="ip_address" id="ip_address" class="text ui-widget-content ui-corner-all" />			
		<!-- 
		<label for="token">Remote Token</label>		
		<input type="text" name="token" id="token" class="text ui-widget-content ui-corner-all" />	
		-->
	</fieldset>
	<?php echo form_close(); ?>
</div>

<!-- Edit Remote access dialog -->	
<div id="editremoteaccess-dialog" title="Edit Remote Access" class="dialog">
	<p id="validateTips">All form fields are required.</p>
	<?php echo form_open('plugin/soap', array('class' => 'editremoteaccessform')); ?>
	<fieldset>
		<input type="hidden" name="editid_remote_access" id="editid_remote_access" />
		<label for="editaccess_name">Access Name</label>
		<input type="text" name="editaccess_name" id="editaccess_name" class="text ui-widget-content ui-corner-all" />
		<label for="editip_address">Remote Host</label>
		<input type="text" name="editip_address" id="editip_address" class="text ui-widget-content ui-corner-all" />				
		<label for="editstatus">Active</label>
		<input type="checkbox" name="editstatus" id="editstatus" class="text ui-widget-content ui-corner-all"/>
		<label for="edittoken">Remote Token</label>		
		<input type="text" name="edittoken" id="edittoken" size="30" class="text ui-widget-content ui-corner-all" readonly/>
	</fieldset>
	<?php echo form_close(); ?>
</div>

<div id="space_area">
<h3 style="float: left">Remote Access</h3> 
<div style="float: right">
<a href="#" title="Add Remote access" id="addremotebutton" class="simplebutton">
<img src="<?php echo  $this->config->item('img_path');?>alert.png" />Add Rmote Access</a>	
</div>

	<table class="nice-table" cellpadding="0" cellspacing="0">
		<tr>
			<th class="nice-table-left">No.</th>
			<th>Access Name</th>
			<th>IP Address</th>
			<th>Remote Token</th>
			<th>Active</th>	
			<th align="center" class="nice-table-right" colspan="3">Control</th>
		</tr>
	    
		<?php 
		if($remote_access->num_rows()==0)
		{
			echo "<tr><td colspan=\"8\" style=\"border-left: 1px solid #000; border-right: 1px solid #000;\">No remote access found.</td></tr>";
		}
		else
		{
			foreach($remote_access->result() as $tmp):
			?>
			<tr id="<?php echo $tmp->id_remote_access;?>">
				<td class="nice-table-left"><?php echo $number;?></td>
				<td class="access_name"><?php echo $tmp->access_name;?></td>
				<td class="ip_address"><?php echo $tmp->ip_address;?></td>	
				<td class="token"><?php echo $tmp->token;?></td>
				<td class="status"><input type="checkbox" class="statusbox" <?php echo ($tmp->status == 'false')? '':'checked=\"checked\"'?> disabled/></td>
				<td>&nbsp;</td>	
				<td><a href="#" class="edit"><img class="ui-icon ui-icon-pencil" title="Edit" /></a></td>			
				<td class="nice-table-right"><a href="<?php echo site_url();?>/plugin/delete_remote_access/<?php echo $tmp->id_remote_access;?>"><img class="ui-icon ui-icon-close" title="Delete" /></a></td>
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
</div>



<!-- NOTIFICATION -->
<!-- Add Remote Access dialog -->	
<div id="notification-dialog" title="Add Notification" class="dialog">
	<p id="validateTips">All form fields are required.</p>
	<?php echo form_open('plugin/soap', array('class' => 'addnotificationform')); ?>
	<fieldset>
		<label for="notifynumber">Number</label>
		<input type="text" name="notifynumber" id="notifynumber" class="text ui-widget-content ui-corner-all" />
		<label for="notifyvalue">Value</label>
		<input type="text" name="notifyvalue" id="notifyvalue" class="text ui-widget-content ui-corner-all" />
		<input type="hidden" name="notifiy" id="notifiy" value="on" />			
	</fieldset>
	<?php echo form_close(); ?>
</div>

<!--  VIEW  -->
<div id="space_area">
<h3 style="float: left">Notification</h3>
<div style="float: right">
<a href="#" title="Add Notification" id="addnotificationbutton" class="simplebutton">
<img src="<?php echo  $this->config->item('img_path');?>alert.png" />Add Notification</a>	
</div>

	<table class="nice-table" cellpadding="0" cellspacing="0">
		<tr>
			<th class="nice-table-left">Number</th>
			<th>Notification Value</th>
			<th align="center" class="nice-table-right" colspan="2">Control</th>
		</tr>
		<?php 
		if(count($notification)==0)
		{
			echo "<tr><td colspan=\"4\" style=\"border-left: 1px solid #000; border-right: 1px solid #000;\">No Notification found.</td></tr>";
		}
		else
		{
			?>
			<tr id="notification">
				<td class="nice-table-left"><?php echo $notification['number'];?></td>
				<td class="notificationvalue"><?php echo $notification['value'];?></td>
				<td>&nbsp;</td>	
				<!-- <td><a href="#" class="edit"><img class="ui-icon ui-icon-pencil" title="Edit" /></a></td> -->	
				<td class="nice-table-right"><a href="<?php echo site_url();?>/plugin/delete_notification/"><img class="ui-icon ui-icon-close" title="Delete" /></a></td>
			</tr>
			
			<?php 
		} 
		?>
		<tr>
			<th colspan="8" class="nice-table-footer"><div id="simplepaging"></div></th>	
		</tr>
		</table>
		<br />
</div>
