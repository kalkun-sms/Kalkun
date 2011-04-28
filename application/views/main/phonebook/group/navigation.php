<div class="two_column_container toolbar">
<div class="left_column">
<?php if(isset($public_group) && !$public_group):?>
	<a href="#" class="select_all nicebutton"><?php echo lang('kalkun_select_all');?></a>	
	<a href="#" class="clear_all nicebutton"><?php echo lang('kalkun_clear_all');?></a>
	<a href="#" class="delete_contact nicebutton"><?php echo lang('kalkun_delete');?></a>
<?php endif;?>			
</div>
<div class="right_column">
	<?php if(empty($_POST));?><div id="simplepaging" class="paging_grey"><?php echo $this->pagination->create_links();?></div>
</div>
</div>