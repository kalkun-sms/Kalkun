<script type="text/javascript">
	$(document).ready(function() {

		// validation
		$(".addserveralertform").validate({
			rules: {
				alert_name: {
					required: true
				},
				ip_address: {
					required: true
				},
				port_number: {
					required: true,
					number: true
				},
				timeout: {
					required: true,
					number: true
				},
				phone_number: {
					required: true
				},
				respond_message: {
					required: true,
					maxlength: 100
				}
			},
			messages: {
				alert_name: {
					required: "<?php echo tr('Field required.');?>",
				},
				ip_address: {
					required: "<?php echo tr('Field required.');?>",
				},
				port_number: {
					required: "<?php echo tr('Field required.');?>",
					number: "<?php echo tr('Value must be a number.');?>",
				},
				timeout: {
					required: "<?php echo tr('Field required.');?>",
					number: "<?php echo tr('Value must be a number.');?>",
				},
				phone_number: {
					required: "<?php echo tr('Field required.');?>",
				},
				respond_message: {
					required: "<?php echo tr('Field required.');?>",
					maxlength: "<?php echo tr('Value is too long.');?>",
				},
			}
		});

		// background
		$("tr:odd").addClass('hover_color');

		// Add alert dialog
		$("#alert-dialog").dialog({
			bgiframe: true,
			autoOpen: false,
			height: 350,
			modal: true,
			buttons: {
				'<?php echo tr('Save'); ?>': function() {
					$("form.addserveralertform").trigger('submit');
				},
				'<?php echo tr('Cancel'); ?>': function() {
					$(this).dialog('close');
				}
			}
		});

		// Add alert button	
		$('#addalertbutton').on("click", function() {
			$('#alert-dialog').dialog('open');
		});

		// Edit alert dialog
		$("#editalert-dialog").dialog({
			bgiframe: true,
			autoOpen: false,
			height: 350,
			modal: true,
			buttons: {
				'<?php echo tr('Save'); ?>': function() {
					$("form.editserveralertform").trigger('submit');
				},
				'<?php echo tr('Cancel'); ?>': function() {
					$(this).dialog('close');
				}
			}
		});

		// Edit blacklist - get data
		$('a.edit').on("click", function() {
			var editid_server_alert = $(this).parents("tr:first").attr("id");
			$("#editid_server_alert").val(editid_server_alert);
			var editalert_name = $(this).parents("tr:first").children("td.alert_name").text();
			$("#editalert_name").val(editalert_name);
			var editip_address = $(this).parents("tr:first").children("td.ip_address").text();
			$("#editip_address").val(editip_address);
			var editport_number = $(this).parents("tr:first").children("td.port_number").text();
			$("#editport_number").val(editport_number);
			var edittimeout = $(this).parents("tr:first").children("td.timeout").text();
			$("#edittimeout").val(edittimeout);
			var editphone_number = $(this).parents("tr:first").children("td.phone_number").text();
			$("#editphone_number").val(editphone_number);
			var editrespond_message = $(this).parents("tr:first").children("td.respond_message").text();
			$("#editrespond_message").val(editrespond_message);
			$('#editalert-dialog').dialog('open');
		});

	});

</script>
