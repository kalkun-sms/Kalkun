<script language="javascript">
$(document).ready(function() {

// Add group
$('#addpbkgroup, a.editpbkgroup').bind('click', function() {
if($(this).hasClass('editpbkgroup'))
{
	var id = $(this).parents("tr:first").attr("id");
	var public = $(this).parents("tr:first").attr("public");
	var dialog_title = '<?php echo lang('tni_group_manage'); ?>';
	var groupname = $(this).parents("div:eq(1)").find("span.groupname").text();
	$('input#group_name').val(groupname);
	$('input.pbkgroup_id').val(id);
	if(public=="true") $("input#is_public").attr('checked', true);
	else $("input#is_public").attr('checked', false);
}
else
{
	var dialog_title = '<?php echo lang('tni_group_add'); ?>';
	$('input#group_name').val("");
	$('input.pbkgroup_id').val("");
}

$("#addgroupdialog").dialog({
	bgiframe: true,
	title: dialog_title,
	autoOpen: false,
	height: 175,
	modal: true,
	buttons: {
		'<?php echo lang('kalkun_save')?>': function() {
			$("form.addgroupform").submit();
		},
		'<?php echo lang('kalkun_cancel')?>': function() {
			$(this).dialog('close');
		}
	},
	open: function() {
		$("#group_name").focus();
	}
});		
$('#addgroupdialog').dialog('open');
});
		
// Delete group
$("a.delete_contact").click(action_delete = function(){
var count = $("input.select_group:checkbox:checked").length;
var dest_url = '<?php echo site_url('phonebook/delete_group') ?>';
if(count==0) { 
	$('.notification_area').text("<?php echo lang('tni_group_no_selected')?>");
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
		'<?php echo lang('kalkun_cancel')?>': function() {
			$(this).dialog('close');
		},			
		'<?php echo lang('tni_group_del_button')?>': function() {
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
	$("#compose_sms_container").html("<div align=\"center\"> Loading...</div>");
	$("#compose_sms_container").load('<?php echo site_url('messages/compose')?>', { 'type': "pbk_groups", 'param1': id_group }, function() {
	  $(this).dialog({
	    modal:true,
		width: 550,
		show: 'fade',
		hide: 'fade',
	    buttons: {
		'<?php echo lang('tni_send_message')?>': function() {
			if($("#composeForm").valid()) {
			 $('.ui-dialog-buttonpane :button').each(function(){ if($(this).text() == '<?php echo lang('tni_send_message'); ?>') $(this).html('<?php echo lang('tni_sending_message'); ?> <img src="<?php echo $this->config->item('img_path').'processing.gif' ?>" height="12" style="margin:0px; padding:0px;">');   });
			$.post("<?php echo site_url('messages/compose_process') ?>", $("#composeForm").serialize(), function(data) {
				$("#compose_sms_container").html(data);
				$("#compose_sms_container" ).dialog( "option", "buttons", { "Okay": function() { $(this).dialog("destroy"); } } );
				setTimeout(function() {$("#compose_sms_container").dialog('destroy')} , 1500);
			});
			}
		},
		'<?php echo lang('kalkun_cancel')?>': function() { $(this).dialog('destroy');}
	    }
	  });
	});
	$("#compose_sms_container").dialog('open');
	return false;
});
	
// select all
$("a.select_all").click(select_all = function(){
$(".select_group").attr('checked', true);
$(".contact_list").addClass("messagelist_hover");
return false;
});

// clear all
$("a.clear_all").click(clear_all = function(){
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