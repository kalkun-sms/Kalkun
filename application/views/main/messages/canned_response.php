<?php if ($canned_list->num_rows() === 0):?>
<div>
	<p><i><?php echo tr('There are no canned responses. Continue to save your present message as canned response.');?></i></p>
</div>
<?php else:?>
<?php foreach ($canned_list->result() as $list):?>
<div class="small_two_column_container canned_list">
	<div class="left_column"><strong><?php echo htmlentities($list->Name, ENT_QUOTES);?></strong></div>
	<div class="right_column" style="font-size: 10px">
		<a href="javascript:void(0)" onClick="javascript:insert_canned_response(<?php echo htmlentities(json_protect($list->Name), ENT_QUOTES);?>)"><?php echo tr('Insert');?></a> |
		<a href="javascript:void(0)" onClick="javascript:save_canned_response(<?php echo htmlentities(json_protect($list->Name), ENT_QUOTES);?>)"><?php echo tr('Save');?></a> |
		<a href="javascript:void(0)" onClick="javascript:delete_canned_response(<?php echo htmlentities(json_protect($list->Name), ENT_QUOTES);?>)"><?php echo tr('Delete');?></a>
	</div>
</div>
<?php endforeach;?>
<?php endif;?>
