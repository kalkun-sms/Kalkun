<?php $this->load->helper('html');?>
<link type="text/css" rel="stylesheet" href="<?php echo $this->config->item('css_path');?>jquery-plugin/token-input-facebook.css" />
<script language="javascript" src="<?php echo $this->config->item('js_path');?>jquery-plugin/jquery.validate.min.js"></script>
<script language="javascript" src="<?php echo $this->config->item('js_path');?>jquery-plugin/jquery.tokeninput.min.js"></script>
<script language="javascript" src="<?php echo $this->config->item('js_path');?>jquery-plugin/jquery.form.min.js"></script>
<style type="text/css">
	.left_aligned {
		margin-left: 0;
		padding-left: 0;
	}
	.form_option {
		display: flex
	}
	.opt {
		flex-grow: 1
	}

</style>
<?php $this->load->view('js_init/message/js_compose'); ?>

<?php echo form_open_multipart('messages/compose_process', array('id' => 'composeForm', 'class' => 'composeForm'));?>
<table width="100%">
	<?php
$type = array('inbox', 'sentitems');

// Reply to option
if ($val_type == 'reply'): ?>
	<tr>
		<td width="100px" align="right" class="form_label label"><?php echo tr('Send to'); ?>:</td>
		<td>
			<?php
$phone = $dest;
$qry = $this->Phonebook_model->get_phonebook(array('option' => 'bynumber', 'number' => $phone));
if ($qry->num_rows() !== 0):
echo $qry->row('Name').' <'.$phone.'>';
else:
echo $phone;
endif;
?>
			<input type="hidden" name="sendoption" value="reply" />
			<input type="hidden" name="reply_value" value="<?php echo $phone;?>" />
		</td>
	</tr>

	<?php /* Member */ elseif ($val_type == 'member'):?>
	<tr>
		<td width="100px" align="right" class="form_label label"><?php echo tr('Send to'); ?>:</td>
		<td><?php echo tr('Member');?><input type="hidden" name="sendoption" value="member" /></td>
	</tr>

	<?php /* Phonebook contact */ elseif ($val_type == 'pbk_contact'):?>
	<tr>
		<td width="100px" align="right" class="form_label label"><?php echo tr('Send to'); ?>:</td>
		<td>
			<?php
$qry = $this->Phonebook_model->get_phonebook(array('option' => 'bynumber', 'number' => $dest));
if ($qry->num_rows() !== 0):
echo $qry->row('Name').' <'.$dest.'>';
else:
echo $dest;
endif;
?>
			<input type="hidden" name="sendoption" value="reply" />
			<input type="hidden" name="reply_value" value="<?php echo $dest;?>" />
		</td>
	</tr>

	<?php /* Phonebook group */ elseif ($val_type == 'pbk_groups'):?>
	<tr>
		<td width="100px" align="right" class="form_label label"><?php echo tr('Send to'); ?>:</td>
		<td>
			<?php echo $this->Phonebook_model->get_phonebook(array('option' => 'groupname', 'id' => $dest))->row('GroupName');?>
			<input type="hidden" name="sendoption" value="pbk_groups" />
			<input type="hidden" name="id_pbk" value="<?php echo $dest;?>" />
		</td>
	</tr>

	<?php /* All Contacts */ elseif ($val_type == 'all_contacts'):?>
	<tr>
		<td width="100px" align="right" class="form_label label"><?php echo tr('Send to'); ?>:</td>
		<td>
			<?php echo tr('All contacts'); ?>
			<input type="hidden" name="sendoption" value="all_contacts" />
		</td>
	</tr>

	<?php /* Forward to option */ else: ?>
	<tr>
		<td width="100px" align="right" class="label">
			<?php
if ($val_type == 'forward')
{
	echo tr('Forward to').':';
}
else
{
	echo tr('Send to').':';
}
?>
		</td>
		<td>
			<div class="form_option">
				<div class="opt">
					<input type="radio" id="sendoption1" name="sendoption" value="sendoption1" checked="checked" class="left_aligned" style="border: none;" />
					<label for="sendoption1"><?php echo tr('Phonebook');?></label>
				</div>
				<div class="opt">
					<input type="radio" id="sendoption3" name="sendoption" value="sendoption3" style="border: none;" />
					<label for="sendoption3"><?php echo tr('Input manually');?> </label>
				</div>
				<div class="opt">
					<input type="radio" id="sendoption4" name="sendoption" value="sendoption4" style="border: none;" />
					<label for="sendoption4"><?php echo tr('Import from file');?></label>
				</div>
			</div>
		</td>
	</tr>

	<tr>
		<td>&nbsp;</td>
		<td>
			<div id="person">
				<textarea id="personvalue" style="width: 95%;" name="personvalue" /></textarea>
			</div>

			<div id="manually" class="hidden">
				<input style="width: 95%;" type="text" name="manualvalue" />
			</div>

			<div id="import" class="hidden"><input type="file" name="import_file" id="import_file" class="text ui-widget-content ui-corner-all" /></div>
			<input type="hidden" id="import_value_count" name="import_value_count" />
		</td>
	</tr>
	<?php endif; ?>

	<tr>
		<td align="right" class="label"><?php echo tr('Send date').':';?></td>
		<td>
			<input class="left_aligned" type="radio" id="option1" name="senddateoption" value="option1" checked="checked" style="border: none;" />
			<label for="option1"><?php  echo tr('Now');?></label>
			<input type="radio" id="option2" name="senddateoption" value="option2" style="border: none;" />
			<label for="option2"><?php  echo tr('At date and time');?></label>
			<input type="radio" id="option3" name="senddateoption" value="option3" style="border: none;" />
			<label for="option3"><?php  echo tr('After a delay');?></label>
		</td>
	</tr>

	<tr>
		<td>&nbsp;</td>
		<td>
			<div id="nowoption"></div>
			<div id="dateoption" class="hidden">
				<input type="text" name="datevalue" id="datevalue" class="datepicker" readonly="readonly" />
				<?php echo '&nbsp;&nbsp;';?>
				<select name="hour"><?php echo get_hour();?></select> :
				<select name="minute"><?php echo get_minute();?></select>
			</div>
			<div id="delayoption" class="hidden">
				<select name="delayhour"><?php echo get_hour();?></select> <?php echo tr('Hour'); ?>&nbsp;
				<select name="delayminute"><?php echo get_minute();?></select> <?php echo tr('Minutes'); ?>
			</div>
		</td>
	</tr>

	<tr>
		<td align="right" class="label"><?php echo tr('Validity'); ?></td>
		<td><select size="1" name="validity">
				<option value="-1"><?php echo tr('default'); ?></option>
				<option value="0"><?php echo tr('5 minutes'); ?></option>
				<option value="1"><?php echo tr('10 minutes'); ?></option>
				<option value="5"><?php echo tr('30 minutes'); ?></option>
				<option value="11"><?php echo tr('1 hour'); ?></option>
				<option value="23"><?php echo tr('2 hours'); ?></option>
				<option value="35"><?php echo tr('4 hours'); ?></option>
				<option value="143"><?php echo tr('12 hours'); ?></option>
				<option value="167"><?php echo tr('1 day'); ?></option>
				<option value="168"><?php echo tr('2 days'); ?></option>
				<option value="171"><?php echo tr('5 days'); ?></option>
				<option value="173"><?php echo tr('1 week'); ?></option>
				<option value="180"><?php echo tr('2 weeks'); ?></option>
				<option value="196"><?php echo tr('4 weeks'); ?></option>
				<option value="255"><?php echo tr('maximum'); ?></option>
			</select></td>
	</tr>

	<?php if ($this->config->item('sms_bomber')): ?>
	<tr valign="top">
		<td align="right" class="label"><?php echo tr('Amount').':';?></td>
		<td><input type="text" style="width: 25px" name="sms_loop" id="sms_loop" value="1" />&nbsp; <?php echo tr('times'); ?>
		</td>
	</tr>
	<?php else: ?>
	<input type="hidden" name="sms_loop" id="sms_loop" value="1" />
	<?php endif;?>

	<tr>
		<td align="right" class="label"><?php echo tr('SMS type');?></td>
		<td>
			<input class="left_aligned" type="radio" id="stype1" name="smstype" value="normal" checked="checked" style="border: none;" />
			<label for="stype1"><?php echo tr('Normal');?></label>
			<input type="radio" id="stype2" name="smstype" value="flash" style="border: none;" />
			<label for="stype2"><?php echo tr('Flash');?><?php //echo tr('Send as Flash SMS');?></label>
			<input type="radio" id="stype3" name="smstype" value="waplink" style="border: none;" />
			<label for="stype3"><?php echo tr('WAP push link');?></label>
			<div style="float: right; text-align: right; padding-right: 10px;"><a href="javascript:void(0)" id="canned_response"> <?php echo tr('Canned responses');?>...</a></div>

		</td>
	</tr>

	<tr style="display: none;" id="url-display">
		<td align="right" class="label"><?php echo tr('URL');?></td>
		<td><input type="text" style="width: 97%;" name="url" value="" /></td>
	</tr>

	<tr valign="top">
		<td align="right" class="label"><?php echo tr('Message').':';?></td>
		<td>
			<?php if ($val_type == 'forward' AND isset($msg_id)):?> <input type="hidden" name="msg_id" value="<?php echo $msg_id;?>" /> <?php endif;?>
			<textarea class="word_count" style="width: 400px; line-height: 16px; min-height: 50px;" id="message" name="message">
<?php
if ($val_type == 'forward')
{
	echo $message;
}
list($sig_option, $sig) = explode(';', $this->Kalkun_model->get_setting()->row('signature'));
if ($sig_option == 'true')
{
	echo "\n\n".$sig;
} ?>
</textarea>
			<div>
				<div style="float: left"><span class="counter"></span></div>
				<div style="float: right; padding-right: 5px;"><?php if ($this->config->item('ncpr')) { ?><input class="left_aligned" type="checkbox" value="ndnc" id="ncpr" name="ncpr" style="border: none;" /><label for="ncpr"><?php echo tr('Check DND');?> </label> <?php }?><input class="left_aligned" type="checkbox" value="unicode" id="unicode" name="unicode" style="border: none;" <?php if ($this->config->item('unicode'))
{
	echo 'checked="checked"';
}?> /><label for="unicode"><?php echo tr('Send as Unicode'); ?></label></div>
			</div>
		</td>
	</tr>
	<tr id="field_option" class="hidden">
		<td align="right" class="label">Select field :</td>
		<td>
			<div style="border: 1px solid #AAA; padding: 5px; width: 96%;">
				<input type="button" id="field_button" class="hidden field_button" value="Name" />
			</div>
		</td>
	</tr>
</table>
<br />
<?php  echo form_close();?>

<div id="iframe" style="width:0px; height:0px; visibility:none;"></div>
<div id="canned_response_container"> </div>
<?php
if ($this->config->item('sms_advertise'))
{
	echo '*'.tr('Ads is active');
}
?>
