<div class="two_column_container toolbar">
	<div class="left_column">
		<?php if (isset($public_group) && ! $public_group):?>
		<a href="#" class="select_all nicebutton"><?php echo tr('Select all');?></a>
		<a href="#" class="clear_all nicebutton"><?php echo tr('Deselect all');?></a>
		<a href="#" class="delete_contact nicebutton"><?php echo tr('Delete');?></a>
		<?php endif;?>
	</div>
	<div class="right_column">
		<?php if (empty($_POST));?><div id="simplepaging" class="paging_grey"><?php echo $pagination_links;?></div>
	</div>
</div>
