<script type="text/javascript">
$(document).ready(function() {
    
    // background
    //$("div.unreaded").addClass('hover_color');
    
	// languange support
	var save = '<?php echo lang('kalkun_save')?>';
	var cancel = '<?php echo lang('kalkun_cancel')?>';
	var delete_folder = '<?php echo lang('kalkun_delete_folder')?>';
	var first = '<?php echo lang('kalkun_first')?>';
	var last = '<?php echo lang('kalkun_last')?>';
		
	$('div.ui-dialog-buttonpane:eq(1) button:eq(1)').text(cancel);
	$('div.ui-dialog-buttonpane:eq(1) button:eq(0)').text(save);
	$('div.ui-dialog-buttonpane:eq(2) button:eq(0)').text(cancel);
	$('div.ui-dialog-buttonpane:eq(2) button:eq(1)').text(delete_folder);
	
	$("div#paging a:contains('First')").text('< ' + first);	
	$("div#paging a:contains('Last')").text(last + ' >');	
    
});    
</script>
