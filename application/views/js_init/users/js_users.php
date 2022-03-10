<script language="javascript">
	$(document).ready(function() {

		var inbox_master = '<?php echo $this->config->item('inbox_owner_id')[0];?>';

		// Add/Edit Contact
		$('.addpbkcontact, .edit_user').on('click', null, function() {
			if ($(this).hasClass('addpbkcontact')) {
				var user_title = <?php echo tr_js('Add user'); ?>;
				var type = 'normal';
				var param1 = '';
			} else if ($(this).hasClass('edit_user')) {
				var user_title = <?php echo tr_js('Edit user'); ?>;
				var type = 'edit';
				var param1 = $(this).parents("tr:first").attr("id");
			}

			$.get("<?php echo site_url('users/add_user')?>", {
					'type': type,
					'param1': param1
				})
				.done(function(responseText, textStatus, jqXHR) {
					$("#users_container").html(responseText);
					$("#users_container").dialog({
						title: user_title,
						closeText: <?php echo tr_js('Close'); ?>,
						modal: true,
						show: 'fade',
						hide: 'fade',
						buttons: {
							<?php echo tr_js('Save'); ?>: function() {
								if ($("#addUser").valid()) {
									$.post("<?php echo site_url('users/add_user_process') ?>", $("#addUser").serialize())
										.done(function(data) {
											show_notification(data.msg, data.type);
											$("#users_container").dialog("destroy");
										})
										.fail(function(data) {
											display_error_container(data);
										});

								}
								$("#users_list").load(window.location.href);
							},
							<?php echo tr_js('Cancel'); ?>: function() {
								$(this).dialog('close');
							}
						}
					});
					$("#users_container").dialog('open');
				})
				.fail(function(data) {
					display_error_container(data);
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
				show_notification(<?php echo tr_js('No user selected'); ?>);
			} else {
				// confirm first
				$("#confirm_delete_user_dialog").dialog({
					closeText: <?php echo tr_js('Close'); ?>,
					bgiframe: true,
					autoOpen: false,
					modal: true,
					buttons: {
						<?php echo tr_js('Cancel'); ?>: function() {
							$(this).dialog('close');
						},
						<?php echo tr_js('Delete'); ?>: function() {
							$("input.select_user:checked").each(function() {
								var row = $(this).parents('tr');
								var id = row.attr('id');
								if (id == inbox_master) {
									show_notification(<?php echo tr_js('Action not allowed'); ?>);
								} else {
									$.post(dest_url, {
											id_user: id,
											[csrf_name]: csrf_hash,
										})
										.done(function() {
											$(row).slideUp("slow");
										})
										.fail(function(data) {
											display_error_container(data);
										})
										.always(function(data) {
											update_csrf_hash();
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
				closeText: <?php echo tr_js('Close'); ?>,
				bgiframe: true,
				autoOpen: false,
				modal: true,
			});
			$('#pbkimportdialog').dialog('open');
		});

		// Search onBlur onFocus
		$('input.search_name').val(<?php echo tr_js('Search user'); ?>);

		$('input.search_name').on("blur", function() {
			$(this).val(<?php echo tr_js('Search user'); ?>);
		});

		$('input.search_name').on("focus", function() {
			$(this).val('');
		});

	});

</script>
