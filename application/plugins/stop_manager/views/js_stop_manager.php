<script type="text/javascript">
	$(document).ready(function() {

		// background
		$("tr:odd").addClass('hover_color');

		// Add STOP dialog
		$("#stop-dialog").dialog({
			closeText: "<?php echo tr_addcslashes('"', 'Close'); ?>",
			bgiframe: true,
			autoOpen: false,
			modal: true,
			buttons: {
				"<?php echo tr_addcslashes('"', 'Save'); ?>": function() {
					$("form.addstopform").trigger('submit');
				},
				"<?php echo tr_addcslashes('"', 'Cancel'); ?>": function() {
					$(this).dialog('close');
				}
			}
		});

		// Add STOP button
		$('#addstopbutton').on("click", function() {
			$('#stop-dialog').dialog('open');
		});

		// validation
		$("#addStopForm").validate({
			rules: {
				destination_number: {
					required: true,
					remote: {
						url: "<?php echo site_url('kalkun/phone_number_validation'); ?>",
						type: "post",
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
					required: "<?php echo tr_addcslashes('"', 'Field required.');?>",
				},
			}
		});

	});

</script>
