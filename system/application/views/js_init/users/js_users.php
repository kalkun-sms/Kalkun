<script language="javascript">
$(document).ready(function() {

var inbox_master = '<?php echo $this->config->item('inbox_owner_id');?>';
	
// Add/Edit Contact
$('.addpbkcontact, .edit_user').bind('click', function() {
if($(this).hasClass('addpbkcontact')) {
	var user_title = 'Add User';
	var type = 'normal';
	var param1 = '';
}	
else if($(this).hasClass('edit_user')) {
	var user_title = 'Edit User';
	var type = 'edit';
	var param1 = $(this).parents("tr:first").attr("id");
}

$("#users_container").load('<?php echo site_url('users/add_user')?>', { 'type': type, 'param1': param1 }, function() {
$(this).dialog({
	title: user_title,
	modal: true,
	show: 'fade',
	hide: 'fade',
	buttons: {
	'Save': function() {
		if($("#addUser").valid()) {
		$.post("<?php echo site_url('users/add_user_process') ?>", $("#addUser").serialize(), function(data) {
		$("#users_container").html(data);
		$("#users_container").dialog({ buttons: { "Okay": function() { $(this).dialog("close"); } } });
		setTimeout(function() {$("#users_container").dialog('close')} , 1500);
		});
		}
	}, Cancel: function() { $(this).dialog('close');} }
	});
});

$("#users_container").dialog('open');
return false;
});	

// select all
$("a.select_all").click(function(){
$(".select_user").attr('checked', true);
$(".contact_list").addClass("messagelist_hover");
return false;
});

// clear all
$("a.clear_all").click(function(){
$(".select_user").attr('checked', false);
$(".contact_list").removeClass("messagelist_hover");
return false;
}); 

// input checkbox
$("input.select_user").click(function(){
if($(this).attr('checked')==true) $(this).parents('div:eq(2)').addClass("messagelist_hover");
else $(this).parents('div:eq(2)').removeClass("messagelist_hover");
});

// Delete user
$("a.delete_user").click(function(){
var count = $("input:checkbox:checked").length;
var dest_url = '<?php echo site_url('users/del_users') ?>';
if(count==0) { 
	$('.notification_area').text("No user selected");
	$('.notification_area').show();
}		
else {
	// confirm first
	$("#confirm_delete_user_dialog").dialog({
	bgiframe: true,
	autoOpen: false,
	height: 175,
	modal: true,
	buttons: {
		Cancel: function() {
			$(this).dialog('close');
		},			
		'Yes, Delete selected user': function() {
			$("input.select_user:checked").each( function () {
			var row = $(this).parents('tr');
			var id = row.attr('id');
			if(id==inbox_master)
			{
				$('.notification_area').text("Can't delete Inbox master");
				$('.notification_area').show();	
			}
			else {
				$.post(dest_url, {id_user: id}, function() {
					$(row).slideUp("slow");
				});
			}
			});
		$(this).dialog('close');
		}
	}
	});			
	$('#confirm_delete_user_dialog').dialog('open');
}
});

// Show menu on hover
$("tr").hover(function() {
	$(this).find("span.pbk_menu").show();
},function() {
 	$(this).find("span.pbk_menu").hide();
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
$('input.search_name').val('Search User');

$('input.search_name').blur(function(){
	$(this).val('Search User');
});

$('input.search_name').focus(function(){
	$(this).val('');
});
	  
});    
</script>