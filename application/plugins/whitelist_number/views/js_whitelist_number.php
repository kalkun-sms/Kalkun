<script type="text/javascript">
	$(document).ready(function() {

		// background
		$("tr:odd").addClass('hover_color');

		// Add whitelist dialog
		$("#whitelist-dialog").dialog({
			closeText: <?php echo tr_js('Close'); ?>,
			bgiframe: true,
			autoOpen: false,
			modal: true,
			buttons: {
				<?php echo tr_js('Save'); ?>: function() {
					$("form.addwhitelistnumberform").trigger('submit');
				},
				<?php echo tr_js('Cancel'); ?>: function() {
					$(this).dialog('close');
				}
			}
		});

		// Add whitelist button
		$('#addwhitelistbutton').on("click", function() {
			$('#whitelist-dialog').dialog('open');
		});

		// Edit whitelist dialog
		$("#editwhitelist-dialog").dialog({
			closeText: <?php echo tr_js('Close'); ?>,
			bgiframe: true,
			autoOpen: false,
			modal: true,
			buttons: {
				<?php echo tr_js('Save'); ?>: function() {
					$("form.editwhitelistnumberform").trigger('submit');
				},
				<?php echo tr_js('Cancel'); ?>: function() {
					$(this).dialog('close');
				}
			}
		});

		// Edit whitelist - get data
		$('a.edit').on("click", function() {
			var editid_whitelist = $(this).parents("tr:first").attr("id");
			$("#editid_whitelist").val(editid_whitelist);
			var editphone_number = $(this).parents("tr:first").children("td.phone_number").text();
			$("#editphone_number").val(editphone_number);
			var editreason = $(this).parents("tr:first").children("td.reason").text();
			$("#editreason").val(editreason);
			$('#editwhitelist-dialog').dialog('open');
		});

		// Delete
		$("a.delete").on('click', function() {
			var element = this;
			$.post("<?php echo site_url(); ?>/plugin/whitelist_number/delete", {
					id: $(element).parents("tr:first").attr("id"),
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
	});

</script>
