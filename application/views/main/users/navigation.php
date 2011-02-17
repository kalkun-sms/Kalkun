<div class="two_column_container toolbar">
<div class="left_column">
	<a href="#" class="select_all nicebutton"><?php echo lang('kalkun_select_all');?></a>	
	<a href="#" class="clear_all nicebutton"><?php echo lang('kalkun_clear_all');?></a>
	<a href="#" class="delete_user nicebutton"><?php echo lang('kalkun_delete');?></a>		
</div>
<div class="right_column">
	<?php if(empty($_POST));?><div id="simplepaging" class="paging_grey"><?php echo $this->pagination->create_links();?></div>
</div>
</div>