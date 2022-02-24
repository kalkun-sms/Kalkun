<ul>
	<li><?php echo anchor('', tr('Dashboard')); ?></li>
	<li><a href="#" id="compose_sms_normal"><?php echo tr('Compose');?></a></li>
	<li>
		<span style="color: #FFF;"><?php echo tr('Folders');?></span>
		<div id="f_child_menu">
			<ul>
				<li>
					<?php echo anchor('messages/folder/inbox', tr('Inbox'));?>
					<span class="unread_inbox_notif">
						<?php
	$tmp_unread = $this->Message_model->get_messages(array('readed' => FALSE, 'uid' => $this->session->userdata('id_user')))->num_rows();
	if ($tmp_unread > 0)
	{
		echo ' ('.$tmp_unread.')';
	}
	?>
					</span>
				</li>
				<li><?php echo anchor('messages/folder/outbox', tr('Outbox')); ?></li>
				<li><?php echo anchor('messages/folder/sentitems', tr('Sent items')); ?> </li>
				<?php if ($this->uri->segment(3) == 'sentitems' || $this->uri->segment(4) == 'sentitems') : ?>
				<li style="list-style: none;"><?php echo anchor('messages/conversation/folder/sentitems/sending_error', tr('Sending error')); ?> </li>
				<?php endif; ?>
				<li><?php echo anchor('messages/my_folder/inbox/6', tr('Spam')); ?>
					<span class="unread_spam_notif">
						<?php
	$tmp_unread = $this->Message_model->get_messages(array('readed' => FALSE, 'id_folder' => '6', 'uid' => $this->session->userdata('id_user')))->num_rows();
	if ($tmp_unread > 0)
	{
		echo ' ('.$tmp_unread.')';
	}
	?>
					</span>
				</li>
				<li><?php echo anchor('messages/my_folder/inbox/5', tr('Trash')); ?></li>

			</ul>
		</div>
	</li>
	<li>
		<div style="float: left"><span style="color: #FFF;"><?php echo tr('My folders');?></span></div>
		<div style="float: right"><sup><a id="addfolder" href="#" title="Add a new folder"><?php echo tr('Add'); ?></a></sup></div>
		<div class="clear">&nbsp;</div>
		<div id="mf_child_menu">
			<ul>
				<?php foreach ($this->Kalkun_model->get_folders('all')->result() as $folder):?>
				<li>
					<?php echo anchor('messages/my_folder/inbox/'.$folder->id_folder, $folder->name);
	$tmp_unread = $this->Message_model->get_messages(array('readed' => FALSE, 'id_folder' => $folder->id_folder))->num_rows();
	if ($tmp_unread > 0)
	{
		echo ' ('.$tmp_unread.')';
	}
	?>
				</li><?php endforeach;?>
			</ul>
		</div>
	</li>
	<li><?php echo  anchor('phonebook', tr('Phonebook')); ?></li>
	<?php
$level = $this->session->userdata('level');
if ($level === 'admin'):?>
	<li><?php echo anchor('users', tr('Users')); ?></li>
	<?php if ($this->config->item('sms_content')): ?>
	<li id="bottom"><?php echo anchor('member', 'Member'); ?></li>
	<?php endif; ?>
	<li><?php echo anchor('pluginss', 'Plugins'); ?></li>
	<?php endif; ?>
</ul>
