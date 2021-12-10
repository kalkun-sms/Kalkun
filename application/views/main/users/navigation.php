<div class="two_column_container toolbar">
	<div class="left_column">
		<a href="#" class="select_all nicebutton"><?php echo tr('Select all');?></a>
		<a href="#" class="clear_all nicebutton"><?php echo tr('Clear all');?></a>
		<a href="#" class="delete_user nicebutton"><?php echo tr('Delete');?></a>
	</div>
	<div class="right_column">
		<?php if (empty($_POST));?><div id="simplepaging" class="paging_grey"><?php echo $this->pagination->create_links();?></div>
	</div>
</div>
