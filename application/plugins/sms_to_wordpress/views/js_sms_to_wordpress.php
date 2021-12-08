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
				$("form.addwpblogform").trigger('submit');
			},
			Cancel: function() {
				$(this).dialog('close');
			}
		}
	});
	
	// Add blacklist button	
	$('#addwpblogbutton').on("click", function() {
		$('#wp-dialog').dialog('open');
	});

});
</script>
