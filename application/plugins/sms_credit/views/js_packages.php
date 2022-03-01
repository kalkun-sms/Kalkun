<script type="text/javascript">
	$(document).ready(function() {

		// Add/ Edit packages	
		$('#addpackagesbutton, .editpackagesbutton').on("click", function() {
			var title = "<?php echo tr_addcslashes('"', 'Add packages'); ?>";

			// Edit mode
			if ($(this).hasClass('editpackagesbutton')) {
				title = "<?php echo tr_addcslashes('"', 'Edit packages'); ?>";
				var id_package = $(this).parents('div:eq(1)').find('span.id_package').text();
				var package_name = $(this).parents('div:eq(1)').find('span.package_name').text();
				var sms_amount = $(this).parents('div:eq(1)').find('span.sms_amount').text();
				$('#id_package').val(id_package);
				$('#package_name').val(package_name);
				$('#sms_amount').val(sms_amount);
			}

			$("#packages-dialog").dialog({
				closeText: "<?php echo tr_addcslashes('"', 'Close'); ?>",
				bgiframe: true,
				autoOpen: false,
				modal: true,
				title: title,
				buttons: {
					"<?php echo tr_addcslashes('"', 'Save'); ?>": function() {
						$("form#addpackagesform").trigger('submit');
					},
					"<?php echo tr_addcslashes('"', 'Cancel'); ?>": function() {
						$(this).dialog('destroy');
					}
				}
			});

			$('#packages-dialog').dialog('open');
		});

		// Search onBlur onFocus
		if ($('input.search_packages').val() == '') {
			$('input.search_packages').val("<?php echo tr_addcslashes('"', 'Search'); ?>");
		}

		$('input.search_packages').on("blur", function() {
			$(this).val('Search Packages');
		});

		$('input.search_packages').on("focus", function() {
			$(this).val('');
		});

		// Delete package
		$("a.deletepackagesbutton").on("click", function(e) {

			e.preventDefault();
			var url = $(this).attr('href');

			// confirm first
			$("#confirm_delete_package_dialog").dialog({
				closeText: "<?php echo tr_addcslashes('"', 'Close'); ?>",
				bgiframe: true,
				autoOpen: false,
				modal: true,
				buttons: {
					"<?php echo tr_addcslashes('"', 'Cancel')?>": function() {
						$(this).dialog('close');
					},
					"<?php echo tr_addcslashes('"', 'Yes')?>": function() {
						window.location.href = url;
						$(this).dialog('close');
					}
				}
			});
			$('#confirm_delete_package_dialog').dialog('open');
		});
	});

</script>
