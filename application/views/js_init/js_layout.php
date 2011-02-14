<script type="text/javascript">
var refreshId = setInterval(function() {
	$('.modem_status').load('<?php echo site_url('kalkun/notification')?>');
	//$('.unread_inbox').load('<?php echo site_url('kalkun/unread_inbox')?>');\
	//var current_title = $(document).attr('title');
	$.post("<?php echo site_url('kalkun/unread_inbox')?>", function(data) {
		$('span.unread_inbox_notif').text(data);
				
		var current_title = $(document).attr('title');
		var stopNumber = current_title.search('\\)');
		if(stopNumber!='-1') var title = current_title.substr(stopNumber+1);
		else var title = current_title;
			
		var newtitle = data + ' ' + title;
		$(document).attr('title', newtitle);
	});
}, 60000);

$(document).ready(function() {

// Get current page for styling/css	
$("#menu").find("a[href='"+window.location.href+"']").each(function(){
	$(this).addClass("current");
});

// Compose SMS
$('#compose_sms_normal').bind('click', function() {
	$("#compose_sms_container").load('<?php echo site_url('messages/compose')?>', { 'type': "normal" }, function() {
	  $(this).dialog({
	    modal:true,
		width: 550,
		show: 'fade',
		hide: 'fade',
	    buttons: {
		'Send Message': function() {
			if($("#composeForm").valid()) {
			$.post("<?php echo site_url('messages/compose_process') ?>", $("#composeForm").serialize(), function(data) {
				$("#compose_sms_container").html(data);
				$("#compose_sms_container").dialog({ buttons: { "Okay": function() { $(this).dialog("close"); } } });
				setTimeout(function() {$("#compose_sms_container").dialog('close')} , 1500);
			});
			}
		},
		Cancel: function() { $(this).dialog('close');}
	    }
	  });
	});
	$("#compose_sms_container").dialog('open');
	return false;
});	
	
// About
$('#about_button').click(function() {
	$("#about").dialog({
		bgiframe: true,
		autoOpen: false,
		height: 300,
		width: 300,
		modal: true,		
	});	
	$('#about').dialog('open');
	return false;
});		

// Add folder
$('#addfolder').click(function() {
	$("#addfolderdialog").dialog({
	bgiframe: true,
	autoOpen: false,
	height: 100,
	modal: true,
	buttons: {
		'Save': function() {
			$("form.addfolderform").submit();
		},
		Cancel: function() {
			$(this).dialog('close');
		}
	}
	});		
	$('#addfolderdialog').dialog('open');
	return false;
});	

// languange support
var save = '<?php echo lang('kalkun_save')?>';
var cancel = '<?php echo lang('kalkun_cancel')?>';
	
$('div.ui-dialog-buttonpane:eq(0) button:eq(1)').text(cancel);
$('div.ui-dialog-buttonpane:eq(0) button:eq(0)').text(save);
		
});
</script>
