<script type="text/javascript">
	$(document).ready(function() {

		// background
		$("tr:odd").addClass('hover_color');

		// Add whitelist dialog
		$("#whitelist-dialog").dialog({
			bgiframe: true,
			autoOpen: false,
			height: 250,
			modal: true,
			buttons: {
				'Save': function() {
					$("form.addwhitelistnumberform").trigger('submit');
				},
				Cancel: function() {
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
			bgiframe: true,
			autoOpen: false,
			height: 250,
			modal: true,
			buttons: {
				'Save Changes': function() {
					$("form.editwhitelistnumberform").trigger('submit');
				},
				Cancel: function() {
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

	});

</script>
