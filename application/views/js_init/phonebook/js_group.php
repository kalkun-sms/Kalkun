<script language="javascript">
	$(document).ready(function() {

		// Add group
		$('#addpbkgroup, a.editpbkgroup').on('click', null, function() {
			if ($(this).hasClass('editpbkgroup')) {
				var id = $(this).parents("tr:first").attr("id");
				var public = $(this).parents("tr:first").attr("public");
				var dialog_title = '<?php echo lang('tni_group_manage'); ?>';
				var groupname = $(this).parents("div:eq(1)").find("span.groupname").text();
				$('input#group_name').val(groupname);
				$('input.pbkgroup_id').val(id);
				if (public == "true") $("input#is_public").prop('checked', true);
				else $("input#is_public").prop('checked', false);
			} else {
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
						$("form.addgroupform").trigger('submit');
					},
					'<?php echo lang('kalkun_cancel')?>': function() {
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
				$('.notification_area').text("<?php echo lang('tni_group_no_selected')?>");
				$('.notification_area').show();
			} else {
				// confirm first
				$("#confirm_delete_group_dialog").dialog({
					bgiframe: true,
					autoOpen: false,
					height: 200,
					modal: true,
					buttons: {
						'<?php echo lang('kalkun_cancel')?>': function() {
							$(this).dialog('close');
						},
						'<?php echo lang('tni_group_del_button')?>': function() {
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
			compose_message('pbk_groups', false, '#message', id_group);
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
