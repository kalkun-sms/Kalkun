
<div class="jquerycssmenu">
<ul>
	<?php 
	if($this->uri->segment(2)=='conversation'):
		if($this->uri->segment(3)=='folder'): ?>
		<li><?php echo anchor('messages/folder/'.$this->uri->segment(4),'&lsaquo;&lsaquo; Back to '.humanize($this->uri->segment(4)), array('class' => 'button'));?></li>
		<?php else: ?>
		<li><?php echo anchor('messages/my_folder/'.$this->uri->segment(4).'/'.$this->uri->segment(6),'&lsaquo;&lsaquo; Back to '.humanize($this->Kalkun_model->getFolders('name', $this->uri->segment(6))->row('name')), array('class' => 'button'));?></li>
		<li>&nbsp;</li>
		<?php endif;?>
	<?php endif;?>
	<li><a href="#" class="select_all_button button"><?php echo lang('kalkun_select_all');?></a></li>
	<li><a href="#" class="clear_all_button button"><?php echo lang('kalkun_clear_all');?></a></li>
	<?php 
	if($this->uri->segment(2)=='folder' && $this->uri->segment(3)=='outbox'): 
	elseif($this->uri->segment(2)=='conversation' && $this->uri->segment(4)=='outbox'):
	else:?>
	<li>&nbsp;</li>
	<li><a class="move_to_button button" href="#">Move To</a></li>	
	<?php endif; ?>
	<li><a class="global_delete button" href="#">
	<?php echo lang('kalkun_delete'); 
	if($this->uri->segment(4)=='5' or $this->uri->segment(6)=='5') echo " ".lang('kalkun_permanently');?></a></li>	
	<?php if($this->uri->segment(2)!='conversation'):?>
	<li>&nbsp;</li>
	<li><a href="#" class="refresh_button button"><?php echo lang('kalkun_refresh');?></a></li>			
	<?php if($this->pagination->create_links()!=''): ?>
	<li class="paging"><div id="paging"><?php echo $this->pagination->create_links();?></div></li>
	<?php endif;?>
	<?php endif;?>
</ul>
</div>	