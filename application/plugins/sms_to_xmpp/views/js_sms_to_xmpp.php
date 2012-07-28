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
				$("form.addxmppform").submit();
			},
			Cancel: function() {
				$(this).dialog('close');
			}
		}
	});
	
	// Add blacklist button	
	$('#addxmppbutton').click(function() {
		$('#xmpp-dialog').dialog('open');
	});

});
</script>
