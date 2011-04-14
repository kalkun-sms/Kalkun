<?php if($canned_list->num_rows()==0):?>
	<div><p><i>There is canned response created yet.</i></p></div>
<?php else:?>
<?php foreach($canned_list->result() as $list):?>
	<div class="small_two_column_container contact_list">
		<div class="left_column"><strong><?php echo $list->Name;?></strong></div>
		<div class="right_column" style="font-size: 10px">
			<a href="javascript:insert('<?php echo $list->Name;?>')">Insert</a> | 
			<a href="javascript:save('<?php echo $list->Name;?>')">Save</a> | 
			<a href="javascript:del('<?php echo $list->Name;?>')">Delete</a>
		</div>
	</div>
<?php endforeach;?>
<?php endif;?>