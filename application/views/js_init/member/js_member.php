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
		$("#compose_sms_container").html("<div align=\"center\"> Loading...</div>");
		$("#compose_sms_container").load('<?php echo site_url('messages/compose/member')?>', { 'type': "member" }, function() {
		  $(this).dialog({
		    modal:true,		
			width: 550,
		    buttons: {
			'<?php echo lang('tni_send_message'); ?>': function() {
				if($("#composeForm").valid()) {
				    $('.ui-dialog-buttonpane :button').each(function(){ if($(this).text() == '<?php echo lang('tni_send_message'); ?>') $(this).html('<?php echo lang('tni_sending_message'); ?> <img src="<?php echo $this->config->item('img_path').'processing.gif' ?>" height="12" style="margin:0px; padding:0px;">');   });
				$.post("<?php echo site_url('messages/compose_process') ?>", $("#composeForm").serialize(), function(data) {
					$("#compose_sms_container").html(data);
					$("#compose_sms_container" ).dialog( "option", "buttons", { "Okay": function() { $(this).dialog("destroy"); } } );
					setTimeout(function() {$("#compose_sms_container").dialog('destroy')} , 1500);
				});
				}			},
			Cancel: function() { $(this).dialog('destroy');}
		    }
		  });
		});
		$("#compose_sms_container").dialog('open');
		return false;
		}
	});	
});
</script>