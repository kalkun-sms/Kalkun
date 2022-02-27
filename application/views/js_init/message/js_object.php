<script type="text/javascript">
	$(document).ready(function() {

		// background
		//$("div.unreaded").addClass('hover_color');

		// languange support
		var save = '<?php echo tr('Save')?>';
		var cancel = '<?php echo tr('Cancel')?>';
		var delete_folder = '<?php echo tr('Delete')?>';

		$('div.ui-dialog-buttonpane:eq(1) button:eq(1)').text(cancel);
		$('div.ui-dialog-buttonpane:eq(1) button:eq(0)').text(save);
		$('div.ui-dialog-buttonpane:eq(2) button:eq(0)').text(cancel);
		$('div.ui-dialog-buttonpane:eq(2) button:eq(1)').text(delete_folder);
	});

</script>
