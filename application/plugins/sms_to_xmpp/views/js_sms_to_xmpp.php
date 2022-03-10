<script type="text/javascript">
	$(document).ready(function() {

		// Add blacklist dialog
		$("#xmpp-dialog").dialog({
			closeText: <?php echo tr_js('Close'); ?>,
			bgiframe: true,
			autoOpen: false,
			modal: true,
			buttons: {
				<?php echo tr_js('Save'); ?>: function() {
					$("form.addxmppform").trigger('submit');
				},
				<?php echo tr_js('Cancel'); ?>: function() {
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
