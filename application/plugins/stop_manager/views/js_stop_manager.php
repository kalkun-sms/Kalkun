<script type="text/javascript">
	$(document).ready(function() {

		// background
		$("tr:odd").addClass('hover_color');

		// Add STOP dialog
		$("#stop-dialog").dialog({
			bgiframe: true,
			autoOpen: false,
			height: 350,
			modal: true,
			buttons: {
				'<?php echo tr('Save'); ?>': function() {
					$("form.addstopform").trigger('submit');
				},
				'<?php echo tr('Cancel'); ?>': function() {
					$(this).dialog('close');
				}
			}
		});

		// Add STOP button
		$('#addstopbutton').on("click", function() {
			$('#stop-dialog').dialog('open');
		});

	});

</script>
