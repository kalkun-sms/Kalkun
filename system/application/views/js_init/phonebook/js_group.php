<script language="javascript">
$(document).ready(function() {

// Add group
$('#addpbkgroup, a.editpbkgroup').bind('click', function() {
if($(this).hasClass('editpbkgroup'))
{
	var id = $(this).parents("tr:first").attr("id");
	var dialog_title = 'Edit group';
	var groupname = $(this).parents("div:eq(1)").find("span.groupname").text();
	$('input#group_name').val(groupname);
	$('input.pbkgroup_id').val(id);
}
else
{
	var dialog_title = 'Add group';
	$('input#group_name').val("");
	$('input.pbkgroup_id').val("");
}

$("#addgroupdialog").dialog({
	bgiframe: true,
	title: dialog_title,
	autoOpen: false,
	height: 100,
	modal: true,
	buttons: {
		'Save': function() {
			$("form.addgroupform").submit();
		},
		Cancel: function() {
			$(this).dialog('close');
		}
	}
});		
$('#addgroupdialog').dialog('open');
});
		
// Delete group
$("a.delete_contact").click(function(){
var count = $("input.select_group:checkbox:checked").length;
var dest_url = '<?php echo site_url('phonebook/del_group') ?>';
if(count==0) { 
	$('.notification_area').text("No group selected");
	$('.notification_area').show();
}
else {
	// confirm first
	$("#confirm_delete_group_dialog").dialog({
	bgiframe: true,
	autoOpen: false,
	height: 150,
	modal: true,
	buttons: {
		Cancel: function() {
			$(this).dialog('close');
		},			
		'Yes, Delete selected group': function() {
			$("input.select_group:checked").each( function () {	
			var row = $(this).parents('tr');
			var id = row.attr('id');
			$.post(dest_url, {id: id}, function() {
				$(row).slideUp("slow");
			});
			});
			$(this).dialog('close');
		} }
	});
	$('#confirm_delete_group_dialog').dialog('open');
}
});

// Compose SMS
$('.sendmessage').bind('click', function() {
	var row = $(this).parents('tr');
	var id_group = row.attr('id');
	$("#compose_sms_container").load('<?php echo site_url('messages/compose')?>', { 'type': "pbk_groups", 'param1': id_group }, function() {
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
	
// select all
$("a.select_all").click(function(){
$(".select_group").attr('checked', true);
$(".contact_list").addClass("messagelist_hover");
return false;
});

// clear all
$("a.clear_all").click(function(){
$(".select_group").attr('checked', false);
$(".contact_list").removeClass("messagelist_hover");
return false;
}); 

// input checkbox
$("input.select_group").click(function(){
if($(this).attr('checked')==true) $(this).parents('div:eq(2)').addClass("messagelist_hover");
else $(this).parents('div:eq(2)').removeClass("messagelist_hover");
});

// Show menu on hover
$("tr").hover(function() {
	$(this).find("span.pbk_menu").show();
},function() {
 	$(this).find("span.pbk_menu").hide();
});  

});    
</script>	