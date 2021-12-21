<script type="text/javascript">
	$(document).ready(function() {

		// Add blacklist dialog
		$("#xmpp-dialog").dialog({
			bgiframe: true,
			autoOpen: false,
			height: 405,
			modal: true,
			buttons: {
				'Save': function() {
					$("form.addxmppform").trigger('submit');
				},
				Cancel: function() {
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
