<?php if ($this->config->item('enable_emoticons')) : ?>
<script language="javascript" src="<?php echo $this->config->item('js_path');?>jquery-plugin/jquery.emoticons.min.js"></script>
<?php endif; ?>
<link type="text/css" rel="stylesheet" href="<?php echo $this->config->item('css_path');?>jquery-plugin/jquerycssmenu.css" />
<?php if ($this->uri->segment(2) != 'conversation' && $this->uri->segment(2) != 'search')
{
	$this->load->view('js_init/message/js_function');
} ?>

<!-- Move To Dialog -->
<div id="movetodialog" title="<?php echo tr('Select folder');?>" class="dialog" style="background: #cce9f2;">
	<?php
if ($this->uri->segment(2) == 'my_folder')
{
	$folder = $this->Kalkun_model->get_folders('exclude', $this->uri->segment(4));
}
else
{
	$folder = $this->Kalkun_model->get_folders('all');
}
?>
	<?php foreach ($folder->result() as $folder):?>
	<div class="move_to" id="<?php echo $folder->id_folder;?>"><a href="javascript:void(0);"><?php echo htmlentities($folder->name, ENT_QUOTES);?></a></div>
	<?php endforeach;?>
</div>

<?php $this->load->view('main/messages/navigation', array('place' => 'top')); ?>

<?php
// my folder view
if ($this->uri->segment(2) == 'my_folder')
{ ?>

<!-- Rename Folder Dialog -->
<div id="renamefolderdialog" title="<?php echo tr('Rename folder');?>" class="dialog">
	<?php
	$this->load->helper('form');
	echo form_open('kalkun/rename_folder', array('class' => 'renamefolderform'));
?>
	<label for="name"><?php echo tr('Folder name');?></label>
	<input type="hidden" name="id_folder" value="<?php echo htmlentities($this->uri->segment(4), ENT_QUOTES);?>" />
	<input type="hidden" name="source_url" value="<?php echo htmlentities($this->uri->uri_string(), ENT_QUOTES);?>" />
	<input type="text" name="edit_folder_name" id="edit_folder_name" class="text ui-widget-content ui-corner-all" />
	<?php echo form_close(); ?>
</div>

<!-- Delete Folder Confirmation -->
<div class="dialog" id="deletefolderdialog" title="<?php echo tr('Delete folder');?>">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
		<?php echo tr('This folder and all messages in it will be deleted permanently and cannot be recovered. Are you sure?');?></p>
</div>
<!-- Delete All Confirmation -->
<div class="dialog" id="deletealldialog" title="<?php echo tr('Delete all');?>">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
		<?php echo tr('Are you sure? This will affect all conversations.');?></p>
</div>

<div id="two_column_container" class="tabbing">
	<div id="left_column" class="two_column_medium">
		<?php
		$folder_name = $this->Kalkun_model->get_folders('name', $this->uri->segment(4))->row('name');
		switch ($folder_name)
		{
			case 'Trash': //Trash
				$folder_name = tr('Trash');
				break;
			case 'Spam': //Spam
				$folder_name = tr('Spam');
				break;
		}
		echo '<span class="folder_name">'.htmlentities($folder_name, ENT_QUOTES).'</span>';?>
		<?php if ($this->uri->segment(4) == '5' || $this->uri->segment(4) == '6'):?>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:void(0)" style="padding: 0; cursor: pointer; font-weight: normal;" id="delete-all-link"><?php echo tr('Delete all messages now');?></a>
		<?php endif; ?>

		<?php if ($this->uri->segment(4) != '5' && $this->uri->segment(4) != '6'):?>
		<sup>[
			<a href="javascript:void(0);" title="<?php echo tr('Click to rename this folder');?>" style="padding: 0; cursor: pointer; font-weight: normal;" id="renamefolder"><?php echo tr('Rename');?></a> -
			<a href="javascript:void(0);" title="<?php echo tr('Click to delete this folder');?>" style="padding: 0; cursor: pointer; font-weight: normal;" id="deletefolder"><?php echo tr('Delete');?></a> ]
		</sup>
		<?php endif; ?>
	</div>
	<?php if ($this->uri->segment(4) != '6'):?>
	<div id="right_column">
		<?php
			if ($this->uri->segment(3) == 'inbox')
			{
				echo '<span class="currenttab">'.tr('Inbox').'</span>';
			}
			else
			{
				echo anchor('messages/my_folder/inbox/'.$this->uri->segment(4).'', tr('Inbox'));
			}

			if ($this->uri->segment(3) == 'sentitems')
			{
				echo '<span class="currenttab">'.tr('Sent items').'</span>';
			}
			else
			{
				echo anchor('messages/my_folder/sentitems/'.htmlentities($this->uri->segment(4), ENT_QUOTES).'', tr('Sent items'));
			}?>
	</div>
	<?php endif; ?>
</div>
<?php } ?>

<div id="message_holder">
	<?php
	if ($this->uri->segment(2) == 'conversation' || $this->uri->segment(2) == 'search')
	{
		$this->load->view('main/messages/conversation');
		$this->load->view('js_init/message/js_conversation');
	}
	else
	{
		$this->load->view('main/messages/message_list');
	}
	?>
</div>

<?php $this->load->view('main/messages/navigation', array('place' => 'bottom')); ?>
