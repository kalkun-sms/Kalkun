<?php if($canned_list->num_rows()==0):?>
	<div><p><i><?php echo lang('kalkun_canned_empty');?>.</i></p></div>
<?php else:?>
<?php foreach($canned_list->result() as $list):?>
	<div class="small_two_column_container canned_list">
		<div class="left_column"><strong><?php echo $list->Name;?></strong></div>
		<div class="right_column" style="font-size: 10px">
			<a href="javascript:insert_canned_response('<?php echo $list->Name;?>')"><?php echo lang('kalkun_insert');?></a> | 
			<a href="javascript:save_canned_response('<?php echo $list->Name;?>')"><?php echo lang('kalkun_save');?></a> | 
			<a href="javascript:delete_canned_response('<?php echo $list->Name;?>')"><?php echo lang('kalkun_delete');?></a>
		</div>
	</div>
<?php endforeach;?>
<?php endif;?>