<script type="text/javascript">
	$(document).ready(function() {

		// background
		//$("div.unreaded").addClass('hover_color');

		// languange support
		var save = '<?php echo tr('Save')?>';
		var cancel = '<?php echo tr('Cancel')?>';
		var delete_folder = '<?php echo tr('Delete')?>';
		var first = '<?php echo tr('First')?>';
		var last = '<?php echo tr('Last')?>';

		$('div.ui-dialog-buttonpane:eq(1) button:eq(1)').text(cancel);
		$('div.ui-dialog-buttonpane:eq(1) button:eq(0)').text(save);
		$('div.ui-dialog-buttonpane:eq(2) button:eq(0)').text(cancel);
		$('div.ui-dialog-buttonpane:eq(2) button:eq(1)').text(delete_folder);

		$("div#paging a:contains('First')").text('< ' + first);
		$("div#paging a:contains('Last')").text(last + ' >');

	});

</script>
