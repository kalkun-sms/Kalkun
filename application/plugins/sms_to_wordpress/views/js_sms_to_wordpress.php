<script type="text/javascript">
$(document).ready(function() {
    
	// Add blacklist dialog
	$("#wp-dialog").dialog({
		bgiframe: true,
		autoOpen: false,
		height: 300,
		modal: true,
		buttons: {
			'Save': function() {
				$("form.addwpblogform").submit();
			},
			Cancel: function() {
				$(this).dialog('close');
			}
		}
	});
	
	// Add blacklist button	
	$('#addwpblogbutton').click(function() {
		$('#wp-dialog').dialog('open');
	});

});
</script>
