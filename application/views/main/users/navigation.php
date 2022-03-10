<div class="two_column_container toolbar">
	<div class="left_column">
		<a href="javascript:void(0)" class="select_all nicebutton"><?php echo tr('Select all');?></a>
		<a href="javascript:void(0)" class="clear_all nicebutton"><?php echo tr('Deselect all');?></a>
		<a href="javascript:void(0)" class="delete_user nicebutton"><?php echo tr('Delete');?></a>
	</div>
	<div class="right_column">
		<?php if (empty($_POST));?><div id="simplepaging" class="paging_grey"><?php echo $pagination_links;?></div>
	</div>
</div>
