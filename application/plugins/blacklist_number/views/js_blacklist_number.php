<script type="text/javascript">
	$(document).ready(function() {

		// background
		$("tr:odd").addClass('hover_color');

		// Add blacklist dialog
		$("#blacklist-dialog").dialog({
			closeText: "<?php echo tr_addcslashes('"', 'Close'); ?>",
			bgiframe: true,
			autoOpen: false,
			modal: true,
			buttons: {
				"<?php echo tr_addcslashes('"', 'Save'); ?>": function() {
					$("form.addblacklistnumberform").trigger('submit');
				},
				"<?php echo tr_addcslashes('"', 'Cancel'); ?>": function() {
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
			closeText: "<?php echo tr_addcslashes('"', 'Close'); ?>",
			bgiframe: true,
			autoOpen: false,
			modal: true,
			buttons: {
				"<?php echo tr_addcslashes('"', 'Save'); ?>": function() {
					$("form.editblacklistnumberform").trigger('submit');
				},
				"<?php echo tr_addcslashes('"', 'Cancel'); ?>": function() {
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

	});

</script>
