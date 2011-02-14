<script type="text/javascript">
$(document).ready(function(){
		
	// Compose SMS
	$('#send_member').bind('click', function() {
		var member = '<?php echo $total_member;?>';
		if(member==0)
		{
			$('.notification_area').text("No member registered");
			$('.notification_area').show();	
		}
		else {
		$("#compose_sms_container").load('<?php echo site_url('messages/compose/member')?>', { 'type': "member" }, function() {
		  $(this).dialog({
		    modal:true,		
			width: 550,
		    buttons: {
			'Send Message': function() {
				if($("#composeForm").valid()) {
				$.post("<?php echo site_url('messages/compose_process') ?>", $("#composeForm").serialize(), function(data) {
					$("#compose_sms_container").html(data);
					$("#compose_sms_container").dialog({ buttons: { "Okay": function() { $(this).dialog("close"); } } });
					setTimeout(function() {$("#compose_sms_container").dialog('close')} , 1500);
				});
				}			},
			Cancel: function() { $(this).dialog('close');}
		    }
		  });
		});
		$("#compose_sms_container").dialog('open');
		return false;
		}
	});	
});
</script>