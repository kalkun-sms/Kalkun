<script type="text/javascript">
	$(document).ready(function() {

		// Add WP Blog dialog
		$("#wp-dialog").dialog({
			closeText: "<?php echo tr('Close'); ?>",
			bgiframe: true,
			autoOpen: false,
			modal: true,
			buttons: {
				'<?php echo tr('Save'); ?>': function() {
					$("form.addwpblogform").trigger('submit');
				},
				'<?php echo tr('Cancel'); ?>': function() {
					$(this).dialog('close');
				}
			}
		});

		// Add WP Blog button
		$('#addwpblogbutton').on("click", function() {
			$('#wp-dialog').dialog('open');
		});

	});

</script>
