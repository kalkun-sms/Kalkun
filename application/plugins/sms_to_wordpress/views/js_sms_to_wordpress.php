<script type="text/javascript">
	$(document).ready(function() {

		// Add WP Blog dialog
		$("#wp-dialog").dialog({
			closeText: <?php echo tr_js('Close'); ?>,
			bgiframe: true,
			autoOpen: false,
			modal: true,
			buttons: {
				<?php echo tr_js('Save'); ?>: function() {
					$("form.addwpblogform").trigger('submit');
				},
				<?php echo tr_js('Cancel'); ?>: function() {
					$(this).dialog('close');
				}
			}
		});

		// Add WP Blog button
		$('#addwpblogbutton').on("click", function() {
			$('#wp-dialog').dialog('open');
		});

		// Delete
		$("a.delete").on('click', function() {
			var element = this;
			$.post("<?php echo site_url(); ?>/plugin/sms_to_wordpress/delete", {
					id: "", // Empty value so that we can detect in PHP that $_POST is not empty
					[csrf_name]: csrf_hash,
				})
				.done(function(data) {
					location.reload();
				})
				.fail(function(data) {
					display_error_container(data);
				})
				.always(function(data) {
					update_csrf_hash();
				});
		});

	});

</script>
