<script language="javascript">
$(document).ready(function() {
	
	// Add/Edit Contact
	$('.addpbkcontact, .editpbkcontact').bind('click', function() {
		
	// check group
	var group = '<?php echo count($pbkgroup);?>';
	if(group==0)
	{
		$('.notification_area').text("<?php echo lang('tni_group_no_group'); ?>");
		$('.notification_area').show();	
    setTimeout( "	$('.notification_area').fadeOut();", 2000);
	}
	else
	{
		if($(this).hasClass('addpbkcontact')) {
			var pbk_title = '<?php echo lang('tni_contact_add'); ?>';
			var type = 'normal';
			var param1 = '<?php echo (isset($group_id))? $group_id : "";?>';
		}	
		else if($(this).hasClass('editpbkcontact')) {
			var pbk_title = '<?php echo lang('tni_pbk_edit_contact'); ?>';
			var type = 'edit';
			var param1 = $(this).parents("tr:first").attr("id");
		}
	
		$("#contact_container").load('<?php echo site_url('phonebook/add_contact')?>', { 'type': type, 'param1': param1 }, function() {
		$(this).dialog({
			title: pbk_title,
			modal: true,
			show: 'fade',
			hide: 'fade',
			open: function() {
				$("#name").focus();
			},
			buttons: {
			'<?php echo lang('kalkun_save')?>': function() {
			 if($('#addContact').valid()){
				$.post("<?php echo site_url('phonebook/add_contact_process') ?>", $("#addContact").serialize(), function(data) {
				$("#contact_container").html(data);
				$("#contact_container").dialog({ buttons: { "Okay": function() { $(this).dialog("close"); } } });
				setTimeout(function() {$("#contact_container").dialog('close')} , 1500);
			});
            } else { return false;}
            
			}, <?php echo lang('kalkun_cancel')?>: function() { $(this).dialog('close');} }
			});
		});
		$("#contact_container").dialog('open');
	}
	return false;
	});	
	
	// select all
	$("a.select_all").click(select_all = function(){
	$(".select_contact").attr('checked', true);
	$(".contact_list").addClass("messagelist_hover");
	return false;
	});
	
	// clear all
	$("a.clear_all").click(clear_all = function(){
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
	$("a.delete_contact").click(action_delete = function(){
	var count = $("input:checkbox:checked:visible").length;
	var dest_url = '<?php echo site_url('phonebook/delete_contact') ?>';
	if(count==0) { 
		$('.notification_area').text("<?php echo lang('tni_pbk_no_contact_selected')?>");
		$('.notification_area').show();
    setTimeout( "	$('.notification_area').fadeOut();", 2000);
	}
	else {
    	$("#confirm_delete_contact_dialog").dialog({
          autoOpen: false,
          modal: true,
          buttons : {
            "Confirm" : function() {
              		$("input.select_contact:checked:visible").each( function () {
            		var row = $(this).parents('tr');
            		var id = row.attr('id');
            		$.post(dest_url, {id: id}, function() {
            			$(row).slideUp("slow");
            		});
            		});
                    $(this).dialog("close");
            },
            "<?php echo lang('kalkun_cancel'); ?>" : function() {
              $(this).dialog("close");
            }
          }
        });
        $("#contact-delete-count").html($("input.select_contact:checked:visible").length);
        $('#confirm_delete_contact_dialog').dialog('open');
	}
	});
  
  // Add/Remove from Group
	$("select.grp_action").change(function(){
	 
  var grp_id =  $(this).val();
  if(grp_id == 'null' || grp_id == 'do') return false;
  
	var count = $("input:checkbox:checked").length;
	var dest_url = '<?php echo site_url('phonebook/update_contact_group') ?>';
	if(count==0) { 
		$('.notification_area').text("<?php echo lang('tni_pbk_no_contact_selected')?>");
		$('.notification_area').show();
        setTimeout( "	$('.notification_area').fadeOut();", 2000);
	}
	else {
		$("input.select_contact:checked").each( function (i , val) {
		  var row = $(this).parents('tr');
		  var id = row.attr('id');
		  $.post(dest_url, {id_pbk: id , id_group :grp_id }, function (){
		      if( i ==  ($("input.select_contact:checked").length -1 )  ) // execute only after the last one.
              {
                $('.notification_area').text("Updated");
                $('.notification_area').show();
                setTimeout( "	$('.notification_area').fadeOut();", 2000);
              }
		  });
		});        
    
	}
    $(this).val('do');
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
		var param1 = header.children('.left_column').children('#pbkname').children('#pbknumber').text();
		$("#compose_sms_container").html("<div align=\"center\"> Loading...</div>");
		$("#compose_sms_container").load('<?php echo site_url('messages/compose')?>', { 'type': "pbk_contact", 'param1': param1 }, function() {
		  $(this).dialog({
		    modal:true,
			width: 550,
			show: 'fade',
			hide: 'fade',
		    buttons: {
			'<?php echo lang('tni_send_message'); ?>': function() {
				if($("#composeForm").valid()) {
                $('.ui-dialog-buttonpane :button').each(function(){ if($(this).text() == '<?php echo lang('tni_send_message'); ?>') $(this).html('<?php echo lang('tni_sending_message'); ?> <img src="<?php echo $this->config->item('img_path').'processing.gif' ?>" height="12" style="margin:0px; padding:0px;">');   });
				$.post("<?php echo site_url('messages/compose_process') ?>", $("#composeForm").serialize(), function(data) {
					$("#compose_sms_container").html(data);
					$("#compose_sms_container" ).dialog( "option", "buttons", { "Okay": function() { $(this).dialog("destroy"); } } );
					setTimeout(function() {$("#compose_sms_container").dialog('destroy')} , 1500);
				});
				}
			},
			'<?php echo lang('kalkun_cancel'); ?>': function() { $(this).dialog('destroy');}
		    }
		  });
		});
		$("#compose_sms_container").dialog('open');
		return false;
	});

	// Send to all
	$('#sendallcontact').bind('click', function() {
		$("#compose_sms_container").html("<div align=\"center\"> Loading...</div>");
		$("#compose_sms_container").load('<?php echo site_url('messages/compose')?>', { 'type': "all_contacts" }, function() {
		  $(this).dialog({
		    modal:true,
			width: 550,
			show: 'fade',
			hide: 'fade',
		    buttons: {
			'<?php echo lang('tni_send_message'); ?>': function() {
				if($("#composeForm").valid()) {
                $('.ui-dialog-buttonpane :button').each(function(){ if($(this).text() == '<?php echo lang('tni_send_message'); ?>') $(this).html('<?php echo lang('tni_sending_message'); ?> <img src="<?php echo $this->config->item('img_path').'processing.gif' ?>" height="12" style="margin:0px; padding:0px;">');   });
				$.post("<?php echo site_url('messages/compose_process') ?>", $("#composeForm").serialize(), function(data) {
					$("#compose_sms_container").html(data);
					$("#compose_sms_container" ).dialog( "option", "buttons", { "Okay": function() { $(this).dialog("destroy"); } } );
					setTimeout(function() {$("#compose_sms_container").dialog('destroy')} , 1500);
				});
				}
			},
			'<?php echo lang('kalkun_cancel'); ?>': function() { $(this).dialog('destroy');}
		    }
		  });
		});
		$("#compose_sms_container").dialog('open');
		return false;
	});
		
	// Contact import
	$('#importpbk').click(function() {
		$("#pbkimportdialog").dialog({
			bgiframe: true,
			autoOpen: false,
			height: 300,
			modal: true,
			buttons: {
				'Import': function() {
					$("form.importpbkform").submit();
				},
				"<?php echo lang('kalkun_cancel'); ?>": function() {
					$(this).dialog('close');
				}
			}
		});		
		$('#pbkimportdialog').dialog('open');
	});	

	// Add contact wizard
	$('#addpbkcontact_wizard').click(function() {
		$("#pbk_add_wizard_dialog").dialog({
			autoOpen: false,
			height: 250,
			modal: true,
			buttons: {
				'<?php echo lang('kalkun_cancel'); ?>': function() {
					$(this).dialog('close');
				}
			}
		});		
		$('#pbk_add_wizard_dialog').dialog('open');
	});	
	
		
	// Search onBlur onFocus
	/*$('input.search_name').val('<?php echo lang('tni_search_contacts'); ?>');
	
	$('input.search_name').blur(function(){
		$(this).val('<?php echo lang('tni_search_contacts'); ?>');
	});
	
	$('input.search_name').focus(function(){
		$(this).val('');
	});*/
	  
});    
</script>
