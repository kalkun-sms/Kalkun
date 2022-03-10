<script language="javascript">
	$(document).ready(function() {

		// Add/Edit Contact
		$(document).on('click', '.addpbkcontact, .editpbkcontact', function() {

			// check group
			var group = '<?php echo count($pbkgroup);?>';
			if (group == 0) {
				$('.notification_area').text(<?php echo tr_js('No group detected, add one first.'); ?>);
				$('.notification_area').show();
				setTimeout("	$('.notification_area').fadeOut();", 2000);
			} else {
				if ($("#pbk_add_wizard_dialog").hasClass('ui-dialog-content')) {
					$("#pbk_add_wizard_dialog").dialog('close')
				};
				if ($(this).hasClass('addpbkcontact')) {
					var pbk_title = <?php echo tr_js('Add contact'); ?>;
					var type = 'normal';
					var param1 = '<?php echo (isset($group_id)) ? $group_id : '';?>';
				} else if ($(this).hasClass('editpbkcontact')) {
					var pbk_title = <?php echo tr_js('Edit contact'); ?>;
					var type = 'edit';
					var param1 = $(this).parents("tr:first").attr("id");
				}

				$.get('<?php echo site_url('phonebook/add_contact')?>', {
						'type': type,
						'param1': param1
					})
					.done(function(responseText, textStatus, jqXHR) {
						$("#contact_container").html(responseText);
						$("#contact_container").dialog({
							title: pbk_title,
							closeText: <?php echo tr_js('Close'); ?>,
							modal: true,
							show: 'fade',
							hide: 'fade',
							open: function() {
								$("#name").trigger('focus');
							},
							buttons: {
								<?php echo tr_js('Save'); ?>: function() {
									if ($('#addContact').valid()) {
										$.post("<?php echo site_url('phonebook/add_contact_process') ?>", $("#addContact").serialize())
											.done(function(data) {
												show_notification(data.msg, data.type);
												$("#contact_container").dialog("destroy");
											})
											.fail(function(data) {
												display_error_container(data);
											});
									} else {
										return false;
									}
									$("#pbk_list").load(window.location.href);
								},
								<?php echo tr_js('Cancel'); ?>: function() {
									$(this).dialog('close');
								}
							}
						});
						$("#contact_container").dialog('open');
					})
					.fail(function(data) {
						display_error_container(data);
					});
			}
			return false;
		});

		// select all
		$("a.select_all").on("click", select_all = function() {
			$(".select_contact").prop('checked', true);
			$(".contact_list").addClass("messagelist_hover");
			return false;
		});

		// clear all
		$("a.clear_all").on("click", clear_all = function() {
			$(".select_contact").prop('checked', false);
			$(".contact_list").removeClass("messagelist_hover");
			return false;
		});

		// input checkbox
		$("input.select_contact").on("click", function() {
			if ($(this).prop('checked') == true) $(this).parents('div:eq(2)').addClass("messagelist_hover");
			else $(this).parents('div:eq(2)').removeClass("messagelist_hover");
		});

		// Delete contact
		$("a.delete_contact").on("click", action_delete = function() {
			var count = $("input:checkbox:checked:visible").length;
			var dest_url = '<?php echo site_url('phonebook/delete_contact') ?>';
			if (count == 0) {
				$('.notification_area').text(<?php echo tr_js('No contact selected.'); ?>);
				$('.notification_area').show();
				setTimeout("	$('.notification_area').fadeOut();", 2000);
			} else {
				$("#confirm_delete_contact_dialog").dialog({
					closeText: <?php echo tr_js('Close'); ?>,
					autoOpen: false,
					modal: true,
					buttons: {
						<?php echo tr_js('Delete'); ?>: function() {
							$("input.select_contact:checked:visible").each(function() {
								var row = $(this).parents('tr');
								var id = row.attr('id');
								$.post(dest_url, {
										id: id,
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
							});
							$(this).dialog("close");
						},
						<?php echo tr_js('Cancel'); ?>: function() {
							$(this).dialog("close");
						}
					}
				});
				$("#contact-delete-count").text($("input.select_contact:checked:visible").length);
				$('#confirm_delete_contact_dialog').dialog('open');
			}
		});

		// Add/Remove from Group
		$("select.grp_action").on('change', function() {

			var grp_id = $(this).val();
			if (grp_id == 'null' || grp_id == 'do') return false;

			var count = $("input:checkbox:checked").length;
			var dest_url = '<?php echo site_url('phonebook/update_contact_group') ?>';
			if (count == 0) {
				$('.notification_area').text(<?php echo tr_js('No contact selected.'); ?>);
				$('.notification_area').show();
				setTimeout("$('.notification_area').fadeOut();", 2000);
			} else {
				$("input.select_contact:checked").each(function(i, val) {
					var row = $(this).parents('tr');
					var id = row.attr('id');
					$.post(dest_url, {
							id_pbk: id,
							id_group: grp_id,
							[csrf_name]: csrf_hash,
						})
						.done(function() {
							if (i == ($("input.select_contact:checked").length - 1)) // execute only after the last one.
							{
								$('.notification_area').text(<?php echo tr_js('Updated'); ?>);
								$('.notification_area').show();
								setTimeout("$('.notification_area').fadeOut();", 2000);
							}
						})
						.fail(function(data) {
							display_error_container(data);
						})
						.always(function(data) {
							update_csrf_hash();
						});
				});

			}
			$(this).val('do');
		});

		// Compose SMS
		$('#pbk_list').on('click', '.sendmessage', function() {
			var header = $(this).parents('div:eq(1)');
			var param1 = header.children('.left_column').children('#pbkname').children('#pbknumber').text();
			compose_message('pbk_contact', false, '#message', param1);
			return false;
		});

		// Send to all
		$('#sendallcontact').on('click', null, function() {
			compose_message('all_contacts', false, '#message');
			return false;
		});

		// Contact import
		$('#importpbk').on("click", function() {
			if ($("#pbk_add_wizard_dialog").hasClass('ui-dialog-content')) {
				$("#pbk_add_wizard_dialog").dialog('close')
			};
			$("#pbkimportdialog").dialog({
				closeText: <?php echo tr_js('Close'); ?>,
				bgiframe: true,
				autoOpen: false,
				modal: true,
				buttons: {
					<?php echo tr_js('Import'); ?>: function() {
						$("form.importpbkform").trigger('submit');
					},
					<?php echo tr_js('Cancel'); ?>: function() {
						$(this).dialog('close');
					}
				}
			});
			$('#pbkimportdialog').dialog('open');
		});

		// Add contact wizard
		$('#addpbkcontact_wizard').on("click", function() {
			$("#pbk_add_wizard_dialog").dialog({
				closeText: <?php echo tr_js('Close'); ?>,
				autoOpen: false,
				modal: true,
				buttons: {
					<?php echo tr_js('Cancel'); ?>: function() {
						$(this).dialog('close');
					}
				}
			});
			$('#pbk_add_wizard_dialog').dialog('open');
		});


		// Search onBlur onFocus
		/*$('input.search_name').val(<?php echo tr_js('Search contacts'); ?>);
		
		$('input.search_name').on("blur", function(){
			$(this).val(<?php echo tr_js('Search contacts'); ?>);
		});
		
		$('input.search_name').on("focus", function(){
			$(this).val('');
		});*/

	});

</script>
