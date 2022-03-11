<script type="text/javascript">
	$(document).ready(function() {

		// background
		$("tr:odd").addClass('hover_color');

		// Add blacklist dialog
		$("#blacklist-dialog").dialog({
			closeText: <?php echo tr_js('Close'); ?>,
			bgiframe: true,
			autoOpen: false,
			modal: true,
			buttons: {
				<?php echo tr_js('Save'); ?>: function() {
					$("form.addblacklistnumberform").trigger('submit');
				},
				<?php echo tr_js('Cancel'); ?>: function() {
					$(this).dialog('close');
				}
			}
		});

		// Add blacklist button
		$('#addblacklistbutton').on("click", function() {
			$('#blacklist-dialog').dialog('open');
		});

		// Edit blacklist dialog
		$("#editblacklist-dialog").dialog({
			closeText: <?php echo tr_js('Close'); ?>,
			bgiframe: true,
			autoOpen: false,
			modal: true,
			buttons: {
				<?php echo tr_js('Save'); ?>: function() {
					$("form.editblacklistnumberform").trigger('submit');
				},
				<?php echo tr_js('Cancel'); ?>: function() {
					$(this).dialog('close');
				}
			}
		});

		// Edit blacklist - get data
		$('a.edit').on("click", function() {
			var editid_blacklist_number = $(this).parents("tr:first").attr("id");
			$("#editid_blacklist_number").val(editid_blacklist_number);
			var editphone_number = $(this).parents("tr:first").children("td.phone_number").text();
			$("#editphone_number").val(editphone_number);
			var editreason = $(this).parents("tr:first").children("td.reason").text();
			$("#editreason").val(editreason);
			$('#editblacklist-dialog').dialog('open');
		});

		// Delete
		$("a.delete").on('click', function() {
			var element = this;
			$.post("<?php echo site_url(); ?>/plugin/blacklist_number/delete", {
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
