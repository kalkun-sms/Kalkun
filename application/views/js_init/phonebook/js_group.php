<script language="javascript">
	$(document).ready(function() {

		// Add group
		$('#addpbkgroup, a.editpbkgroup').on('click', null, function() {
			if ($(this).hasClass('editpbkgroup')) {
				var id = $(this).parents("tr:first").attr("id");
				var public = $(this).parents("tr:first").attr("public");
				var dialog_title = '<?php echo tr('Manage group'); ?>';
				var groupname = $(this).parents("div:eq(1)").find("span.groupname").text();
				$('input#group_name').val(groupname);
				$('input.pbkgroup_id').val(id);
				if (public == "true") $("input#is_public").prop('checked', true);
				else $("input#is_public").prop('checked', false);
			} else {
				var dialog_title = '<?php echo tr('Create group'); ?>';
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
					'<?php echo tr('Save')?>': function() {
						$("form.addgroupform").trigger('submit');
					},
					'<?php echo tr('Cancel')?>': function() {
						$(this).dialog('close');
					}
				},
				open: function() {
					$("#group_name").trigger('focus');
				}
			});
			$('#addgroupdialog').dialog('open');
		});

		// Delete group
		$("a.delete_contact").on("click", action_delete = function() {
			var count = $("input.select_group:checkbox:checked").length;
			var dest_url = '<?php echo site_url('phonebook/delete_group') ?>';
			if (count == 0) {
				$('.notification_area').text("<?php echo tr('No group selected.')?>");
				$('.notification_area').show();
			} else {
				// confirm first
				$("#confirm_delete_group_dialog").dialog({
					bgiframe: true,
					autoOpen: false,
					height: 200,
					modal: true,
					buttons: {
						'<?php echo tr('Cancel')?>': function() {
							$(this).dialog('close');
						},
						'<?php echo tr('Yes, delete selected group(s).')?>': function() {
							$("input.select_group:checked").each(function() {
								var row = $(this).parents('tr');
								var id = row.attr('id');
								$.post(dest_url, {
									id: id
								}, function() {
									$(row).slideUp("slow");
								});
							});
							$(this).dialog('close');
						}
					}
				});
				$('#confirm_delete_group_dialog').dialog('open');
			}
		});

		// Compose SMS
		$('.sendmessage').on('click', null, function() {
			var row = $(this).parents('tr');
			var id_group = row.attr('id');
			$("#compose_sms_container").html("<div align=\"center\"> Loading...</div>");
			$("#compose_sms_container").load('<?php echo site_url('messages/compose')?>', {
				'type': "pbk_groups",
				'param1': id_group
			}, function() {
				$(this).dialog({
					modal: true,
					width: 550,
					show: 'fade',
					hide: 'fade',
					buttons: {
						'<?php echo tr('Send message')?>': function() {
							if ($("#composeForm").valid()) {
								$('.ui-dialog-buttonpane :button').each(function() {
									if ($(this).text() == '<?php echo tr('Send message'); ?>') $(this).html('<?php echo tr('Sending'); ?> <img src="<?php echo $this->config->item('img_path').'processing.gif' ?>" height="12" style="margin:0px; padding:0px;">');
								});
								$.post("<?php echo site_url('messages/compose_process') ?>", $("#composeForm").serialize(), function(data) {
									$("#compose_sms_container").html(data);
									$("#compose_sms_container").dialog("option", "buttons", {
										"Okay": function() {
											$(this).dialog("destroy");
										}
									});
									setTimeout(function() {
										if ($("#compose_sms_container").hasClass('ui-dialog-content')) {
											$("#compose_sms_container").dialog('destroy')
										}
									}, 1500);
								});
							}
						},
						'<?php echo tr('Cancel')?>': function() {
							$(this).dialog('destroy');
						}
					}
				});
				$("#compose_sms_container").dialog('open');
			});
			return false;
		});

		// select all
		$("a.select_all").on("click", select_all = function() {
			$(".select_group").prop('checked', true);
			$(".contact_list").addClass("messagelist_hover");
			return false;
		});

		// clear all
		$("a.clear_all").on("click", clear_all = function() {
			$(".select_group").prop('checked', false);
			$(".contact_list").removeClass("messagelist_hover");
			return false;
		});

		// input checkbox
		$("input.select_group").on("click", function() {
			if ($(this).prop('checked') == true) $(this).parents('div:eq(2)').addClass("messagelist_hover");
			else $(this).parents('div:eq(2)').removeClass("messagelist_hover");
		});

	});

</script>
