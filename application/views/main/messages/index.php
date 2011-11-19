<?php if($this->config->item('enable_emoticons')) : ?> 
<script language="javascript" src="<?php echo $this->config->item('js_path');?>jquery-plugin/jquery.emoticons.min.js"></script>
<?php endif; ?>
<link type="text/css" rel="stylesheet" href="<?php echo $this->config->item('css_path');?>jquery-plugin/jquerycssmenu.css" />
<?php if($this->uri->segment(2)!='conversation' && $this->uri->segment(2)!='search') $this->load->view('js_init/message/js_function'); ?>

<!-- Move To Dialog -->
<div id="movetodialog" title="<?php echo lang('kalkun_select_folder');?>" class="dialog" style="background: #cce9f2;">
<?php 
if($this->uri->segment(2)=='my_folder') $folder = $this->Kalkun_model->get_folders('exclude', $this->uri->segment(4)); 
else $folder = $this->Kalkun_model->get_folders('all');
?>
<?php foreach($folder->result() as $folder):?>
<div class="move_to" id="<?php echo $folder->id_folder;?>"><a href="#"><?php echo $folder->name;?></a></div>
<?php endforeach;?>
</div>

<?php $this->load->view("main/messages/navigation",array('place'=>'top')); ?>		

<?php
// my folder view
if($this->uri->segment(2)=='my_folder')
{ ?>

<!-- Rename Folder Dialog -->
<div id="renamefolderdialog" title="<?php echo lang('kalkun_rename_folder');?>" class="dialog">
	<form class="renamefolderform" method="post" action="<?php echo site_url();?>/kalkun/rename_folder">
		<label for="name"><?php echo lang('kalkun_folder_name');?></label>
		<input type="hidden" name="id_folder" value="<?php echo $this->uri->segment(4);?>" />
		<input type="hidden" name="source_url" value="<?php echo $this->uri->uri_string();?>" />
		<input type="text" name="edit_folder_name" id="edit_folder_name" class="text ui-widget-content ui-corner-all" />
	</form>
</div>	
		
<!-- Delete Folder Confirmation -->
<div class="dialog" id="deletefolderdialog" title="<?php echo lang('kalkun_delete_folder_confirmation_header');?>">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
	<?php echo lang('kalkun_delete_folder_confirmation');?></p>
</div>		
<!-- Delete All Confirmation -->
<div class="dialog" id="deletealldialog" title="<?php echo lang('kalkun_delete_all_confirmation_header');?>">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
	<?php echo lang('kalkun_delete_all_confirmation');?></p>
</div>	

<div id="two_column_container" class="tabbing">
<div id="left_column" class="two_column_medium">
	<?php echo"<span class=\"folder_name\">".$this->Kalkun_model->get_folders('name', $this->uri->segment(4))->row('name')."</span>";?>
    <?php if($this->uri->segment(4)=='5' || $this->uri->segment(4)=='6'):?>
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:void(0)" style="padding: 0; cursor: pointer; font-weight: normal;" id="delete-all-link"><?php echo lang('kalkun_delete_all_message_now');?></a>
    <?php endif; ?>
    
	<?php if($this->uri->segment(4)!='5' && $this->uri->segment(4)!='6'):?>
	<sup>[ 
	<a href="#" title="<?php echo lang('kalkun_rename_folder_title');?>" style="padding: 0; cursor: pointer; font-weight: normal;" id="renamefolder"><?php echo lang('kalkun_rename');?></a> - 
			<a href="#" title="<?php echo lang('kalkun_delete_folder_title');?>" style="padding: 0; cursor: pointer; font-weight: normal;" id="deletefolder"><?php echo lang('kalkun_delete');?></a> ]
			</sup>
			<?php endif; ?>
		</div>
        <?php if( $this->uri->segment(4)!='6'):?>
		<div id="right_column">
			<?php
			if($this->uri->segment(3)=='inbox') echo "<span class=\"currenttab\">".lang('kalkun_inbox')."</span>"; 
			else echo anchor('messages/my_folder/inbox/'.$this->uri->segment(4).'', lang('kalkun_inbox'));
			
			if($this->uri->segment(3)=='sentitems') echo "<span class=\"currenttab\">".lang('kalkun_sentitems')."</span>";
			else echo anchor('messages/my_folder/sentitems/'.$this->uri->segment(4).'', lang('kalkun_sentitems'));?>
		</div>
        <?php endif; ?>
		</div>	
	<?php } ?>

	<div id="message_holder">
	<?php 
	if($this->uri->segment(2)=='conversation' || $this->uri->segment(2)=='search')
	{
		$this->load->view('main/messages/conversation');
		$this->load->view('js_init/message/js_object');
		$this->load->view('js_init/message/js_conversation');
	}
	else 
	{
		$this->load->view('main/messages/message_list');
		$this->load->view('js_init/message/js_object');
	}
	?>
	</div>

	<?php $this->load->view("main/messages/navigation",array('place'=>'bottom')); ?>