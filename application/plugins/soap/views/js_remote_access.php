<?php
/**
 *	@Author: bullshit "oskar@biglan.at"
 *	@Copyright: bullshit, 2010
 *	@License: GNU General Public License
*/
?>
<script type="text/javascript">
$(document).ready(function() {

	// validation
	$(".addremoteaccessform").validate({
		rules: {
			access_name: {
				required: true			
			},
			ip_address: {
				required: true
			},
		},
		messages: {
			access_name: "Please enter access name",
			ip_address: "Please enter host",
		}
	});

   	$(".addnotificationform").validate({
		rules: {
   			notifynumber: {
				required: true,
				number:true,		
			},
			notifyvalue: {
				required: true,
				number:true,
			},
		},
		messages: {
			notifynumber: "Please enter a notification number",
			notifyvalue: "Please enter a notification value",
		}
	});	

	// background
    $("tr:odd").addClass('hover_color');

 	// Add remote access dialog
	$("#remoteaccess-dialog").dialog({
		bgiframe: true,
		autoOpen: false,
		height: 350,
		modal: true,
		buttons: {
			'Save': function() {
				$("form.addremoteaccessform").submit();
			},
			Cancel: function() {
				$(this).dialog('close');
			}
		}
	});

	$("#notification-dialog").dialog({
		bgiframe: true,
		autoOpen: false,
		height: 350,
		modal: true,
		buttons: {
			'Save': function() {
				$("form.addnotificationform").submit();
			},
			Cancel: function() {
				$(this).dialog('close');
			}
		}
	});

	// Add remote acces button	
	$('#addremotebutton').click(function() {
		$('#remoteaccess-dialog').dialog('open');
	});	

	$('#addnotificationbutton').click(function() {
		$('#notification-dialog').dialog('open');
	});	

	// Edit remote access dialog
	$("#editremoteaccess-dialog").dialog({
		bgiframe: true,
		autoOpen: false,
		height: 350,
		modal: true,
		buttons: {
			'Save Changes': function() {
				$("form.editremoteaccessform").submit();
			},
			Cancel: function() {
				$(this).dialog('close');
			}
		}
	});

	// Edit blacklist - get data
	$('a.edit').click(function() {
		var editid_remote_access = $(this).parents("tr:first").attr("id");
		$("#editid_remote_access").val(editid_remote_access);		
		var editaccess_name = $(this).parents("tr:first").children("td.access_name").text();
		$("#editaccess_name").val(editaccess_name);		
		var editip_address = $(this).parents("tr:first").children("td.ip_address").text();
		$("#editip_address").val(editip_address);				
		var edittoken = $(this).parents("tr:first").children("td.token").text();
		$("#edittoken").val(edittoken);		

		// FIXME
		//var editstatus = $('#statusBox').attr('checked');
		var editstatus = $(this).parents("tr:first").children("td.status").children("input.statusbox").attr('checked');
		$("#editstatus").attr('checked', editstatus);

		$('#editremoteaccess-dialog').dialog('open');
	});
});		
</script>
