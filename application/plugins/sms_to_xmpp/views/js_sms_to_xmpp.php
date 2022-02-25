<script type="text/javascript">
	$(document).ready(function() {

		// Add blacklist dialog
		$("#xmpp-dialog").dialog({
			bgiframe: true,
			autoOpen: false,
			height: 405,
			modal: true,
			buttons: {
				'<?php echo tr('Save'); ?>': function() {
					$("form.addxmppform").trigger('submit');
				},
				'<?php echo tr('Cancel'); ?>': function() {
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
