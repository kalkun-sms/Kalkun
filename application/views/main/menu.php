<ul>
<li><?php echo anchor('',lang('kalkun_dashboard')); ?></li>
<li><a href="#" id="compose_sms_normal"><?php echo lang('kalkun_compose');?></a></li>
<li>
	<span style="color: #FFF;"><?php echo lang('kalkun_folder');?></span>
	<div id="f_child_menu">
	<ul>
	<li>
	<?php echo anchor('messages/folder/inbox', lang('kalkun_inbox'));?> 
	<span class="unread_inbox_notif">
	<?php 
	$tmp_unread = $this->Message_model->getUnread();
	if($tmp_unread > 0) echo " (".$tmp_unread.")";
	?>
	</span></li>
	<li><?php echo anchor('messages/folder/outbox',lang('kalkun_outbox')); ?></li>
	<li><?php echo anchor('messages/folder/sentitems',lang('kalkun_sentitems')); ?></li>
	<li><?php echo anchor('messages/my_folder/inbox/5',lang('kalkun_trash')); ?></li>					
	</ul>
	</div>
</li>
<li>
	<div style="float: left"><span style="color: #FFF;"><?php echo lang('kalkun_myfolder');?></span></div>
	<div style="float: right"><sup><a id="addfolder" href="#" title="Add a new folder">Add</a></sup></div>
	<div class="clear">&nbsp;</div>
	<div id="mf_child_menu">
	<ul>
	<?php foreach($this->Kalkun_model->getFolders('all')->result() as $folder):?>
	<li>
	<?php echo anchor('messages/my_folder/inbox/'.$folder->id_folder, $folder->name); 
	$tmp_unread = $this->Message_model->getUnread($folder->id_folder);
	if($tmp_unread > 0) echo " (".$tmp_unread.")";
	?>
	</li><?php endforeach;?>
	</ul>
	</div>
</li>
<li><?php echo  anchor('phonebook',lang('kalkun_phonebook')); ?></li>
<?php 
$level = $this->session->userdata('level');
if($level=='admin'):?>
	<li><?php echo anchor('users','Users'); ?></li>
	<?php if($this->config->item('sms_content')): ?> 
	<li id="bottom"><?php echo anchor('member','Member'); ?></li>
	<?php endif; ?>
<?php endif; ?>
</ul>
