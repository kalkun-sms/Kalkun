<div id="contact_container" class="hidden"></div>
<?php
if (count($messages) == 0)
{
	if ($this->uri->segment(2) == 'my_folder')
	{
		echo '<p style="padding-left: 10px"><span class="ui-icon ui-icon-alert" style="float:left;"></span><i>'.tr('There is no message in this folder.').'.</i></p>';
	}
	else
	{
		if ($this->uri->segment(2) == 'search')
		{
			echo '<p style="padding-left: 10px"><span class="ui-icon ui-icon-alert" style="float:left;"></span><i>'.tr('No result.').'</i></p>';
		}
		else
		{
			$folder_type = '';
			switch ($this->uri->segment(3))
			{
				case 'inbox':
					$folder_type = tr('Inbox');
					break;
				case 'outbox':
					$folder_type = tr('Outbox');
					break;
				case 'sentitems':
					$folder_type = tr('Sent items');
					break;
			}
			if ($folder_type === '' && $this->uri->segment(4) === 'sentitems' && $this->uri->segment(5) === 'sending_error')
			{
				$folder_type = tr('Sending error');
			}
			echo '<p style="padding-left: 10px"><span class="ui-icon ui-icon-alert" style="float:left;"></span><i>'.tr('There is no message in {0}.', NULL, $folder_type).'</i></p>';
		}
	}
}
else
{
	// loop - begin
	foreach ($messages as $tmp):

	// initialization
	$type = $this->uri->segment(4);
	if ($tmp['source'] == 'inbox')
	{
		$qry = $this->Phonebook_model->get_phonebook(array('option' => 'bynumber', 'number' => $tmp['SenderNumber']));
		if ($qry->num_rows() !== 0)
		{
			$senderName = $qry->row('Name');
			$on_pbk = TRUE;
		}
		else
		{
			$senderName = phone_format_human($tmp['SenderNumber']);
			$on_pbk = FALSE;
		}

		$message_date = $tmp['ReceivingDateTime'];
		$number = $tmp['SenderNumber'];
		$arrow = 'arrow_left';
	}
	else
	{
		$qry = $this->Phonebook_model->get_phonebook(array('option' => 'bynumber', 'number' => $tmp['DestinationNumber']));
		if ($qry->num_rows() !== 0)
		{
			$senderName = $qry->row('Name');
			$on_pbk = TRUE;
		}
		else
		{
			$senderName = phone_format_human($tmp['DestinationNumber']);
			$on_pbk = FALSE;
		}

		$message_date = $tmp['SendingDateTime'];
		$number = $tmp['DestinationNumber'];
		if ($type == 'outbox')
		{
			$arrow = 'circle';
		}
		else
		{
			$arrow = 'arrow_right';
		}
	}

	// count string for message preview
	$char_per_line = 100 - strlen(kalkun_nice_date($message_date)) - strlen($senderName); ?>

<div class="messagelist conversation messagelist_conversation">
	<div class="message_container <?php echo $tmp['source']; ?>">
		<div class="message_header" style="color: #444; height: 20px; overflow: hidden">
			<input type="hidden" name="item_source<?php echo $tmp['ID']; ?>" id="item_source<?php echo $tmp['ID']; ?>" value="<?php echo $tmp['source']; ?>" />
			<input type="hidden" class="item_number" name="item_number<?php echo $tmp['ID']; ?>" id="item_number<?php echo $tmp['ID']; ?>" value="<?php echo htmlentities($number, ENT_QUOTES); ?>" />
			<input type="checkbox" id="<?php echo $tmp['ID']; ?>" class="select_message nicecheckbox" value="<?php echo $tmp['ID']; ?>" style="border: none;" />
			<span class="message_toggle" style="cursor: pointer">
				<span <?php  if ($tmp['source'] == 'inbox' && $tmp['readed'] == 'false')
	{
		echo 'style="font-weight: bold"';
	} ?>><?php echo kalkun_nice_date($message_date); ?>&nbsp;&nbsp;<img src="<?php echo $this->config->item('img_path').$arrow; ?>.gif" />
					&nbsp;&nbsp;<?php echo htmlentities($senderName, ENT_QUOTES); ?></span>
				<span class="message_preview">-&nbsp;<?php echo message_preview(htmlentities($tmp['TextDecoded'], ENT_QUOTES), $char_per_line); ?></span>
			</span>
		</div>


		<?php
if ($tmp['source'] == 'sentitems'):
	// check delivery status
	$status = check_delivery_report($tmp['Status']);
	$part_no = 1;
	//check multipart
	$multipart['type'] = 'sentitems';
	$multipart['option'] = 'check';
	$multipart['id_message'] = $tmp['ID'];
	if ($this->Message_model->get_multipart($multipart) != 0):
		$multipart['option'] = 'all';
	foreach ($this->Message_model->get_multipart($multipart)->result() as $part):
		$tmp['TextDecoded'] .= $part->TextDecoded;
	$part_no++;
	endforeach;
	endif;
	elseif ($tmp['source'] == 'outbox'):
	//check multipart
	$multipart['type'] = 'outbox';
	$multipart['option'] = 'check';
	$multipart['id_message'] = $tmp['ID'];
	if ($this->Message_model->get_multipart($multipart) === TRUE):
		$part_no = 1;
	$multipart['option'] = 'all';
	foreach ($this->Message_model->get_multipart($multipart)->result_array() as $part):
		$tmp['TextDecoded'] .= $part['TextDecoded'];
	$part_no++;
	endforeach;
	endif;
	elseif ($tmp['source'] == 'inbox'):
	$part_no = 1;
	// check multipart
	if ( ! empty($tmp['UDH'])):
		$multipart['type'] = 'inbox';
	$multipart['option'] = 'all';
	$multipart['udh'] = substr($tmp['UDH'], 0, 8);
	$multipart['phone_number'] = $tmp['SenderNumber'];
	foreach ($this->Message_model->get_multipart($multipart)->result_array() as $part):
		$tmp['TextDecoded'] .= $part['TextDecoded'];
	$part_no++;
	endforeach;
	endif;
	endif; ?>

		<div class="detail_area hidden <?php echo htmlentities($number, ENT_QUOTES); ?>">
			<table cellspacing="0" cellpadding="0" border="0">
				<tr>
					<td width="50px"><?php  if ($tmp['source'] == 'inbox')
	{
		echo tr('From');
	}
	else
	{
		echo tr('To');
	} ?></td>
					<td width="10px"> : </td>
					<td><?php echo htmlentities($number, ENT_QUOTES); ?></td>
				</tr>

				<?php  if ($tmp['source'] == 'outbox'): ?>
				<tr>
					<td><?php echo tr('Inserted'); ?></td>
					<td> : </td>
					<td><?php echo htmlentities(simple_date($tmp['InsertIntoDB']), ENT_QUOTES); ?></td>
				</tr>
				<?php  endif; ?>

				<tr>
					<td><?php echo tr('Date'); ?></td>
					<td> : </td>
					<td><?php echo htmlentities(simple_date($message_date), ENT_QUOTES); ?></td>
				</tr>

				<?php if ($tmp['source'] != 'outbox'): ?>
				<tr>
					<td><?php echo tr('SMSC'); ?></td>
					<td> : </td>
					<td><?php echo htmlentities($tmp['SMSCNumber'], ENT_QUOTES); ?></td>
				</tr>
				<?php endif; ?>

				<?php if ($tmp['source'] == 'sentitems' OR $tmp['source'] == 'inbox'): ?>
				<?php if ($part_no > 1): ?>
				<tr>
					<td><?php echo tr('Part'); ?></td>
					<td> : </td>
					<td><?php echo tr('{0} part messages', NULL, $part_no); ?></td>
				</tr>
				<?php endif; ?>
				<?php endif; ?>
				<?php if ($tmp['source'] == 'sentitems'): ?>
				<tr>
					<td><?php echo tr('Status'); ?></td>
					<td> : </td>
					<td><?php echo htmlentities($status, ENT_QUOTES); ?></td>
				</tr>
				<?php endif; ?>
			</table>
		</div>

		<?php echo '<div class="message_content hidden" style="padding: 5px 10px 5px 20px">'.showmsg($tmp['TextDecoded']).'</div>'; ?>

		<div class="optionmenu hidden" style="padding-left: 20px">
			<ul>
				<li><a class="detail_button" href="javascript:void(0);"><?php echo tr('Show details'); ?></a></li>

				<?php if ($tmp['source'] == 'inbox'): ?>
				<li><img src="<?php echo $this->config->item('img_path'); ?>circle.gif" /></li>
				<li><a href="javascript:void(0);" class="reply_button"><?php echo tr('Reply'); ?></a></li>
				<?php endif; ?>

				<?php if ($type != 'outbox'): ?>
				<li><img src="<?php echo $this->config->item('img_path'); ?>circle.gif" /></li>
				<li><a href="javascript:void(0);" class="forward_button"><?php echo tr('Forward'); ?></a></li>
				<?php endif; ?>

				<?php if ( ! $on_pbk): ?>
				<li><img src="<?php echo $this->config->item('img_path'); ?>circle.gif" /></li>
				<li><a href="javascript:void(0);" class="add_to_pbk"><?php echo tr('Add contact'); ?></a></li>
				<?php endif; ?>

				<?php if ($tmp['source'] == 'sentitems'): ?>
				<li><img src="<?php echo $this->config->item('img_path'); ?>circle.gif" /></li>
				<li><a href="javascript:void(0);" class="resend"><?php echo tr('Resend'); ?></a></li>
				<?php endif; ?>
			</ul>
		</div>

		<div class="message_metadata hidden">
			<span class="class"><?php echo htmlentities($tmp['Class'], ENT_QUOTES); ?></span>
		</div>

	</div>
</div>

<?php
	if ($tmp['source'] == 'inbox')
	{
		if ($tmp['readed'] == 'false')
		{
			$this->Message_model->update_read($tmp['ID']);
		}
	}
	endforeach;
}
?>
