<script language="javascript">
	$(document).ready(function() {

		var inbox_master = '<?php echo $this->config->item('inbox_owner_id')[0];?>';

		// Add/Edit Contact
		$('.addpbkcontact, .edit_user').on('click', null, function() {
			if ($(this).hasClass('addpbkcontact')) {
				var user_title = '<?php echo tr('Add user');?>';
				var type = 'normal';
				var param1 = '';
			} else if ($(this).hasClass('edit_user')) {
				var user_title = '<?php echo tr('Edit user');?>';
				var type = 'edit';
				var param1 = $(this).parents("tr:first").attr("id");
			}

			$("#users_container").load('<?php echo site_url('users/add_user')?>', {
				'type': type,
				'param1': param1
			}, function() {
				$(this).dialog({
					title: user_title,
					modal: true,
					show: 'fade',
					hide: 'fade',
					buttons: {
						'<?php echo tr('Save');?>': function() {
							if ($("#addUser").valid()) {
								$.post("<?php echo site_url('users/add_user_process') ?>", $("#addUser").serialize(), function(data) {
									$("#users_container").html(data);
									$("#users_container").dialog({
										buttons: {
											"<?php echo tr('Close'); ?>": function() {
												$(this).dialog("close");
											}
										}
									});
									setTimeout(function() {
										$("#users_container").dialog('close')
									}, 1500);
								});
							}
						},
						'<?php echo tr('Cancel');?>': function() {
							$(this).dialog('close');
						}
					}
				});
				$("#users_container").dialog('open');
			});
			return false;
		});

		// select all
		$("a.select_all").on("click", select_all = function() {
			$(".select_user").prop('checked', true);
			$(".contact_list").addClass("messagelist_hover");
			return false;
		});

		// clear all
		$("a.clear_all").on("click", clear_all = function() {
			$(".select_user").prop('checked', false);
			$(".contact_list").removeClass("messagelist_hover");
			return false;
		});

		// input checkbox
		$("input.select_user").on("click", function() {
			if ($(this).prop('checked') == true) $(this).parents('div:eq(2)').addClass("messagelist_hover");
			else $(this).parents('div:eq(2)').removeClass("messagelist_hover");
		});

		// Delete user
		$("a.delete_user").on("click", action_delete = function() {
			var count = $("input:checkbox:checked").length;
			var dest_url = '<?php echo site_url('users/delete_user') ?>';
			if (count == 0) {
				$('.notification_area').text("<?php echo tr('No user selected'); ?>");
				$('.notification_area').show();
			} else {
				// confirm first
				$("#confirm_delete_user_dialog").dialog({
					bgiframe: true,
					autoOpen: false,
					height: 175,
					modal: true,
					buttons: {
						'<?php echo tr('Cancel'); ?>': function() {
							$(this).dialog('close');
						},
						'<?php echo tr('Delete'); ?>': function() {
							$("input.select_user:checked").each(function() {
								var row = $(this).parents('tr');
								var id = row.attr('id');
								if (id == inbox_master) {
									$('.notification_area').text("<?php echo tr('Action not allowed'); ?>");
									$('.notification_area').show();
								} else {
									$.post(dest_url, {
										id_user: id
									}, function() {
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

		// Contact import
		$('#pbkimport').on("click", function() {
			$("#pbkimportdialog").dialog({
				bgiframe: true,
				autoOpen: false,
				height: 300,
				modal: true,
			});
			$('#pbkimportdialog').dialog('open');
		});

		// Search onBlur onFocus
		$('input.search_name').val('<?php echo tr('Search user'); ?>');

		$('input.search_name').on("blur", function() {
			$(this).val('<?php echo tr('Search user'); ?>');
		});

		$('input.search_name').on("focus", function() {
			$(this).val('');
		});

	});

</script>
