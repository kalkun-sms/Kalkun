<script type="text/javascript">
	var count = 0;

	$(document).ready(function() {
		let timeoutIdMsg;

		var offset = <?php echo json_protect($offset);?>;
		var folder = <?php echo json_protect($folder);?>;
		var base_url = "<?php echo site_url();?>";
		var source = <?php echo json_protect($type);?>;
		var delete_url = base_url + '/messages/delete_messages/';
		var move_url = base_url + '/messages/move_message/';
		var refresh_url = base_url + '/messages/' + encodeURIComponent(folder) + '/' + encodeURIComponent(source);
		var delete_folder_url = base_url + '/kalkun/delete_folder/';

		if (folder == 'folder') {
			var current_folder = '';
			var id_folder = '';
		} else {
			var current_folder = <?php echo json_protect($id_folder);?>;
			var id_folder = <?php echo json_protect($id_folder);?>;
			refresh_url = refresh_url + '/' + id_folder;
		}

		refresh_url = refresh_url + '/' + offset;

		// --------------------------------------------------------------------

		/**
		 * Delete conversation
		 *
		 * Delete all messages on selected conversation
		 *
		 */
		$(document).on('click', "a.global_delete", action_delete = function() {
			var count = $("input.select_conversation:checked:visible").length;
			var notif = {
				msg: <?php echo tr_js('{0} conversation(s) deleted'); ?>,
				type: "info",
			};
			notif.msg = notif.msg.replace('{0}', count);
			if (count == 0) {
				show_notification(<?php echo tr_js('No item selected.'); ?>);
			} else {
				$("input.select_conversation:checked:visible").each(function() {
					var message_row = $(this).parents('div:eq(2)');
					$.ajaxSetup({
						async: false
					});
					$.post(delete_url + source, {
							[csrf_name]: csrf_hash,
							<?php if ($this->config->item('conversation_grouping')): ?>
							type: 'conversation',
							number: $(this).val(),
							current_folder: current_folder,
							<?php else: ?>
							type: 'single',
							id: $(this).val(),
							<?php endif; ?>
						})
						.done(function(data) {
							if (!data) {
								$(message_row).slideUp("slow");
								$(message_row).remove();
							} else {
								notif = data;
							}
						})
						.fail(function(data) {
							display_error_container(data);
						})
						.always(function(data) {
							update_csrf_hash();
						});
				});
				show_notification(notif.msg, notif.type);
			}
		});


		/**
		 * Recover conversation
		 *
		 * Recover all messages on selected conversation
		 *
		 */
		$(document).on('click', "a.recover_button", action_recover = function() {
			var count = $("input.select_conversation:checked:visible").length;
			var notif = <?php echo tr_js('{0} conversation(s) recovered'); ?>;
			notif = notif.replace('{0}', count);
			if (count == 0) {
				show_notification(<?php echo tr_js('No item selected.'); ?>);
			} else {

				var id_folder = (source == 'inbox') ? 1 : 3;

				$("input.select_conversation:checked:visible").each(function() {
					var message_row = $(this).parents('div:eq(2)');
					$.post(move_url, {
							[csrf_name]: csrf_hash,
							<?php if ($this->config->item('conversation_grouping')): ?>
							type: 'conversation',
							current_folder: current_folder,
							folder: source,
							id_folder: id_folder,
							number: $(this).val(),
							<?php else: ?>
							type: 'single',
							current_folder: current_folder,
							folder: source,
							id_folder: id_folder,
							id_message: $(this).val(),
							<?php endif; ?>
						})
						.done(function() {
							$(message_row).slideUp("slow");
						})
						.fail(function(data) {
							display_error_container(data);
							return;
						})
						.always(function(data) {
							update_csrf_hash();
						});
				});
				show_notification(notif);
			}
		});


		// --------------------------------------------------------------------

		/**
		 * Move conversation
		 *
		 * Move all messages on selected conversation from a folder to another folder
		 *
		 */
		$(document).on('click', ".move_to", function() {
			var count = $("input.select_conversation:checked:visible").length;
			var notif = <?php echo tr_js('{0} conversation(s) moved'); ?>;
			notif = notif.replace('{0}', count);
			if (count == 0) {
				$("#movetodialog").dialog('close');
				show_notification(<?php echo tr_js('No item selected.'); ?>);
			} else {
				var id_folder = $(this).attr('id');
				$("#movetodialog").dialog('close');
				$("input.select_conversation:checked:visible").each(function() {
					var message_row = $(this).parents('div:eq(2)');
					$.post(move_url, {
							[csrf_name]: csrf_hash,
							<?php if ($this->config->item('conversation_grouping')): ?>
							type: 'conversation',
							current_folder: current_folder,
							folder: source,
							id_folder: id_folder,
							number: $(this).val(),
							<?php else: ?>
							type: 'single',
							current_folder: current_folder,
							folder: source,
							id_folder: id_folder,
							id_message: $(this).val(),
							<?php endif; ?>
						})
						.done(function() {
							$(message_row).slideUp("slow");
						})
						.fail(function(data) {
							display_error_container(data);
							return;
						})
						.always(function(data) {
							update_csrf_hash();
						});
				});
				show_notification(notif);
			}
			count = 0;
		});

		$(document).on('click', ".move_to_button", message_move = function() {
			$("#movetodialog").dialog({
				closeText: <?php echo tr_js('Close'); ?>,
				bgiframe: true,
				autoOpen: false,
				modal: true,
			});
			$('#movetodialog').dialog('open');
			return false;
		});

		// select all
		$(document).on('click', "a.select_all_button", select_all = function() {
			$(".select_conversation").prop('checked', true);
			$(".messagelist").addClass("messagelist_hover");
			return false;
		});

		// clear all
		$(document).on('click', "a.clear_all_button", clear_all = function() {
			$(".select_conversation").prop('checked', false);
			$(".messagelist").removeClass("messagelist_hover");
			return false;
		});

		// input checkbox
		$(document).on('click', "input.select_conversation", function() {
			if ($(this).prop('checked') == true) {
				$(this).parents('div:eq(2)').addClass("messagelist_hover");
				current_number = $(this).val();
			} else {
				$(this).parents('div:eq(2)').removeClass("messagelist_hover");
				current_number = '';
			}
		});

		// refresh
		$(document).on('click', "a.refresh_button, div#logo a", refresh = function(type) {
			if (type != 'retry') {
				show_loading(<?php echo tr_js('Loading'); ?>);
			}
			$.get(refresh_url)
				.done(function(data) {
					if ($("#error_container").hasClass("ui-dialog-content")) {
						$("#error_container").dialog("close");
					}
					$('#message_holder').html(data);
					new_notification('false');
					hide_loading();
				})
				.fail(function(data) {
					var retry_delay = 10;
					display_error_container(data, retry_delay);
					if (!timeoutIdMsg) {
						timeoutIdMsg = setTimeout(function() {
							timeoutIdMsg = null;
							refresh('retry');
						}, retry_delay * 1000);
					}
					return false;
				});
			return false;
		});

		// --------------------------------------------------------------------

		/**
		 * Rename folder
		 *
		 * Rename custom folder
		 *
		 */
		$(document).on('click', '#renamefolder', function() {
			$("#renamefolderdialog").dialog({
				closeText: <?php echo tr_js('Close'); ?>,
				bgiframe: true,
				autoOpen: false,
				modal: true,
				buttons: {
					<?php echo tr_js('Save'); ?>: function() {
						$("form.renamefolderform").trigger('submit');
					},
					<?php echo tr_js('Cancel'); ?>: function() {
						$(this).dialog('close');
					}
				}
			});
			var editname = $(this).parents('div').children("span.folder_name").text();
			$("#edit_folder_name").val(editname);
			$('#renamefolderdialog').dialog('open');
		});

		// --------------------------------------------------------------------

		/**
		 * Delete folder
		 *
		 * Delete custom folder
		 *
		 */
		$(document).on('click', '#deletefolder', function() {
			$("#deletefolderdialog").dialog({
				closeText: <?php echo tr_js('Close'); ?>,
				bgiframe: true,
				autoOpen: false,
				modal: true,
				buttons: {
					<?php echo tr_js('Cancel'); ?>: function() {
						$(this).dialog('close');
					},
					<?php echo tr_js('Delete this folder'); ?>: function() {
						location.href = delete_folder_url + id_folder;
					}
				}
			});
			$('#deletefolderdialog').dialog('open');
			return false;
		});

		// --------------------------------------------------------------------

		<?php if ($this->uri->segment(4) == '5' || $this->uri->segment(4) == '6'):?>
		/**
		 * Delete all
		 *
		 * Delete all
		 *
		 */
		$(document).on('click', '#delete-all-link', function() {
			var url = "<?php echo site_url('messages/delete_all'); ?>";
			url += <?php echo json_protect(strtolower($this->Kalkun_model->get_folders('name', $this->uri->segment(4))->row('name'))); ?>;
			$("#deletealldialog").dialog({
				closeText: <?php echo tr_js('Close'); ?>,
				bgiframe: true,
				autoOpen: false,
				modal: true,
				buttons: {
					<?php echo tr_js('Cancel'); ?>: function() {
						$(this).dialog('close');
					},
					<?php echo tr_js('Delete all'); ?>: function() {
						$.get(url);
						$(this).dialog('close');
						refresh();
					}
				}
			});
			$('#deletealldialog').dialog('open');
			return false;
		});
		<?php endif; ?>


	});

</script>
