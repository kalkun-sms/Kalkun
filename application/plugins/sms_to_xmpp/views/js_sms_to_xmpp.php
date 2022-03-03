<script type="text/javascript">
	$(document).ready(function() {

		// Add blacklist dialog
		$("#xmpp-dialog").dialog({
			closeText: "<?php echo tr_addcslashes('"', 'Close'); ?>",
			bgiframe: true,
			autoOpen: false,
			modal: true,
			buttons: {
				"<?php echo tr_addcslashes('"', 'Save'); ?>": function() {
					$("form.addxmppform").trigger('submit');
				},
				"<?php echo tr_addcslashes('"', 'Cancel'); ?>": function() {
					$(this).dialog('close');
				}
			}
		});

		// Add blacklist button	
		$('#addxmppbutton').on("click", function() {
			$('#xmpp-dialog').dialog('open');
		});

	});

</script>
