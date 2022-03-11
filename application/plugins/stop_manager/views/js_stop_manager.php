<script type="text/javascript">
	$(document).ready(function() {

		// background
		$("tr:odd").addClass('hover_color');

		// Add STOP dialog
		$("#stop-dialog").dialog({
			closeText: <?php echo tr_js('Close'); ?>,
			bgiframe: true,
			autoOpen: false,
			modal: true,
			buttons: {
				<?php echo tr_js('Save'); ?>: function() {
					$("form.addstopform").trigger('submit');
				},
				<?php echo tr_js('Cancel'); ?>: function() {
					$(this).dialog('close');
				}
			}
		});

		// Add STOP button
		$('#addstopbutton').on("click", function() {
			$('#stop-dialog').dialog('open');
		});

		// Delete
		$("a.delete").on('click', function() {
			var element = this;
			$.post("<?php echo site_url(); ?>/plugin/stop_manager/delete", {
					from: $(element).parents("tr:first").children(".destination_number").children(".dest_number_intl").text(),
					type: $(element).parents("tr:first").children(".stop_type").text(),
					[csrf_name]: csrf_hash,
				})
				.done(function(data) {
					$(element).parents("tr:first").slideUp("slow");
					show_notification(<?php echo tr_js('Item deleted.'); ?>, "info");
				})
				.fail(function(data) {
					display_error_container(data);
				})
				.always(function(data) {
					update_csrf_hash();
				});
		});

		// validation
		$("#addStopForm").validate({
			rules: {
				destination_number: {
					required: true,
					remote: {
						url: "<?php echo site_url('kalkun/phone_number_validation'); ?>",
						type: "get",
						data: {
							phone: function() {
								return $("#destination_number").val();
							},
						}
					}
				},
			},
			messages: {
				destination_number: {
					required: <?php echo tr_js('Field required.'); ?>,
				},
			}
		});

	});

</script>
