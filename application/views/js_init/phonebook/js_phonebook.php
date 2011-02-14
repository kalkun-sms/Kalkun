<script language="javascript">
$(document).ready(function() {
	
// Add/Edit Contact
$('.addpbkcontact, .editpbkcontact').bind('click', function() {
	
// check group
var group = '<?php echo count($pbkgroup);?>';
if(group==0)
{
	$('.notification_area').text("No group detected, add one first");
	$('.notification_area').show();	
}
else
{
	if($(this).hasClass('addpbkcontact')) {
		var pbk_title = 'Add Contact';
		var type = 'normal';
		var param1 = '';
	}	
	else if($(this).hasClass('editpbkcontact')) {
		var pbk_title = 'Edit Contact';
		var type = 'edit';
		var param1 = $(this).parents("tr:first").attr("id");
	}

	$("#contact_container").load('<?php echo site_url('phonebook/add_contact')?>', { 'type': type, 'param1': param1 }, function() {
	$(this).dialog({
		title: pbk_title,
		modal: true,
		show: 'fade',
		hide: 'fade',
		buttons: {
		'Save': function() {
			$.post("<?php echo site_url('phonebook/add_contact_process') ?>", $("#addContact").serialize(), function(data) {
			$("#contact_container").html(data);
			$("#contact_container").dialog({ buttons: { "Okay": function() { $(this).dialog("close"); } } });
			setTimeout(function() {$("#contact_container").dialog('close')} , 1500);
		});
		}, Cancel: function() { $(this).dialog('close');} }
		});
	});
	$("#contact_container").dialog('open');
}
return false;
});	

// select all
$("a.select_all").click(function(){
$(".select_contact").attr('checked', true);
$(".contact_list").addClass("messagelist_hover");
return false;
});

// clear all
$("a.clear_all").click(function(){
$(".select_contact").attr('checked', false);
$(".contact_list").removeClass("messagelist_hover");
return false;
}); 

// input checkbox
$("input.select_contact").click(function(){
if($(this).attr('checked')==true) $(this).parents('div:eq(2)').addClass("messagelist_hover");
else $(this).parents('div:eq(2)').removeClass("messagelist_hover");
});

// Delete contact
$("a.delete_contact").click(function(){
var count = $("input:checkbox:checked").length;
var dest_url = '<?php echo site_url('phonebook/del_phonebook') ?>';
if(count==0) { 
	$('.notification_area').text("No contact selected");
	$('.notification_area').show();
}
else {
	$("input.select_contact:checked").each( function () {
	var row = $(this).parents('tr');
	var id = row.attr('id');
	$.post(dest_url, {id: id}, function() {
		$(row).slideUp("slow");
	});
	});
}
});

// Show menu on hover
$("tr").hover(function() {
	$(this).find("span.pbk_menu").show();
},function() {
 	$(this).find("span.pbk_menu").hide();
});  


// Compose SMS
$('.sendmessage').bind('click', function() {
	var header = $(this).parents('div:eq(1)');
	var param1 = header.children('.left_column').children('#pbknumber').text();
	$("#compose_sms_container").load('<?php echo site_url('messages/compose')?>', { 'type': "pbk_contact", 'param1': param1 }, function() {
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
	
// Contact import
$('#pbkimport').click(function() {
	$("#pbkimportdialog").dialog({
		bgiframe: true,
		autoOpen: false,
		height: 300,
		modal: true,
	});		
	$('#pbkimportdialog').dialog('open');
});	
	
// Search onBlur onFocus
$('input.search_name').val('Search Contact');

$('input.search_name').blur(function(){
	$(this).val('Search Contact');
});

$('input.search_name').focus(function(){
	$(this).val('');
});
	  
});    
</script>