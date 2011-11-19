<?php $this->load->helper('inflector'); ?>

<div class="jquerycssmenu">
<ul>
	<?php 
	if($this->uri->segment(2)=='conversation'):
		if($this->uri->segment(3)=='folder'): 

			// _tni_ added this for translation on the inbox, outbox etc.
			$theFolder = $this->uri->segment(4);
			$theFname = "inbox";
			if($theFolder == "inbox") {
			$theFname = lang('kalkun_inbox');
			} else if($theFolder == "outbox") {
			$theFname = lang('kalkun_outbox');
			} else if($theFolder == "sentitems") {
			$theFname = lang('kalkun_sentitems');
			} else {
					//$theFname = $this->Kalkun_model->get_folders('name', $this->uri->segment(4))->row('name');
			 $theFname = $this->uri->segment(4);
			}
	?>
		<li><?php echo anchor('messages/folder/'.$this->uri->segment(4),'&lsaquo;&lsaquo; '.lang('tni_back_to').' '.$theFname, array('class' => 'button' , 'id' => 'back_threadlist'));?></li>
		<?php else: ?>
		<li><?php echo anchor('messages/my_folder/'.$this->uri->segment(4).'/'.$this->uri->segment(6),'&lsaquo;&lsaquo; '.lang('tni_back_to').' '.humanize($this->Kalkun_model->get_folders('name', $this->uri->segment(6))->row('name')), array('class' => 'button'));?></li>
		<li>&nbsp;</li>
		<?php endif;?>
	<?php endif;?>
	<li><a href="javascript:void(0);" class="select_all_button button"><?php echo lang('kalkun_select_all');?></a></li>
	<li><a href="javascript:void(0);" class="clear_all_button button"><?php echo lang('kalkun_clear_all');?></a></li>
    <li>&nbsp;</li>
    <?php if($this->uri->segment(2)=='conversation' && $this->uri->segment(4)=='inbox' ) :
            if(   $this->uri->segment(6)!='6' ) : ?>
    <li><a href="javascript:void(0);" class="spam_button button"><?php echo lang('kalkun_report_spam');?></a></li>
    <?php   else : ?>
    <li><a href="javascript:void(0);" class="ham_button button"><?php echo lang('kalkun_ham');?></a></li>
    <?php   endif; 
        endif;?>
	<?php 
	if($this->uri->segment(2)=='folder' && $this->uri->segment(3)=='outbox'): 
	elseif($this->uri->segment(2)=='conversation' && $this->uri->segment(4)=='outbox'):
	else:?>
	<li>&nbsp;</li>
    <?php if($this->uri->segment(4)=='5' or $this->uri->segment(6)=='5') : ?>
    <li><a href="javascript:void(0);" class="recover_button button"><?php echo lang('kalkun_recover');?></a></li>
    <?php endif; ?>
	<li><a class="move_to_button button" href="#"><?php echo lang('kalkun_move_to');?></a></li>	
	<?php endif; ?>
	<li><a class="global_delete button" href="javascript:void(0);">
	<?php echo lang('kalkun_delete'); 
	if($this->uri->segment(4)=='5' or $this->uri->segment(6)=='5' or $this->uri->segment(4)=='6' or $this->uri->segment(6)=='6') echo " ".lang('kalkun_permanently');?></a></li>	
	<?php if($this->uri->segment(2) != 'search'): ?>
	<li>&nbsp;</li>
	<li><a href="javascript:void(0);" class="refresh_button button"><?php echo lang('kalkun_refresh');?></a></li>	
    <?php endif; ?>		
    
	<?php if($this->uri->segment(2)=='conversation' && $this->uri->segment(4)=='sentitems'): ?>
	<li>&nbsp;</li>
	<li><a href="#" class="resend_bulk button"><?php echo lang('kalkun_resend');?></a></li>	
    <?php endif; ?>		    
    
	<?php if($this->pagination->create_links()!=''): ?>
	<li class="paging"><div id="paging"><?php  echo $this->pagination->create_links();?></div></li>
	<?php endif; ?>
    
</ul>
</div>	