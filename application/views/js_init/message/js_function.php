<script type="text/javascript">
	var count = 0;

	$(document).ready(function() {

		var offset = "<?php echo $offset;?>";
		var folder = "<?php echo $folder;?>";
		var base_url = "<?php echo site_url();?>";
		var source = "<?php echo $type;?>";
		var delete_url = base_url + '/messages/delete_messages/';
		var move_url = base_url + '/messages/move_message/';
		var refresh_url = base_url + '/messages/' + folder + '/' + source;
		var delete_folder_url = base_url + '/kalkun/delete_folder/';

		if (folder == 'folder') {
			var current_folder = '';
			var id_folder = '';
		} else {
			var current_folder = "<?php echo $id_folder;?>";
			var id_folder = "<?php echo $id_folder;?>";
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
			var count = $("input.select_conversation:checkbox:checked").length;
			var notif = count + ' conversation deleted';

			if (count == 0) {
				show_notification("<?php echo tr('No item selected')?>");
			} else {
				$("input.select_conversation:checked").each(function() {
					var message_row = $(this).parents('div:eq(2)');
					$.ajaxSetup({
						async: false
					});
					$.post(delete_url + source, {
						type: 'conversation',
						number: $(this).val(),
						current_folder: current_folder
					}, function(data) {
						if (!data) {
							$(message_row).slideUp("slow");
							$(message_row).remove();
						} else {
							notif = data;
						}
					});
				});
				show_notification(notif); // translate
			}
		});


		/**
		 * Recover conversation
		 *
		 * Recover all messages on selected conversation
		 *
		 */
		$(document).on('click', "a.recover_button", action_recover = function() {
			var count = $("input.select_conversation:checkbox:checked").length;
			if (count == 0) {
				show_notification("<?php echo tr('No item selected')?>");
			} else {

				var id_folder = (source == 'inbox') ? 1 : 3;

				$("input.select_conversation:checked").each(function() {
					var message_row = $(this).parents('div:eq(2)');
					$.post(move_url, {
						type: 'conversation',
						current_folder: current_folder,
						folder: source,
						id_folder: id_folder,
						number: $(this).val()
					}, function() {
						$(message_row).slideUp("slow");
					});
				});
				show_notification(count + ' conversation recovered'); // translate
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
			var count = $("input.select_conversation:checkbox:checked").length;
			if (count == 0) {
				$("#movetodialog").dialog('close');
				show_notification("<?php echo tr('No item selected')?>");
			} else {
				var id_folder = $(this).attr('id');
				$("#movetodialog").dialog('close');
				$("input.select_conversation:checked").each(function() {
					var message_row = $(this).parents('div:eq(2)');
					$.post(move_url, {
						type: 'conversation',
						current_folder: current_folder,
						folder: source,
						id_folder: id_folder,
						number: $(this).val()
					}, function() {
						$(message_row).slideUp("slow");
					});
				});
				show_notification(count + ' conversation moved'); // translate
			}
			count = 0;
		});

		$(document).on('click', ".move_to_button", message_move = function() {
			$("#movetodialog").dialog({
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
				$('.loading_area').html('Loading...');
				$('.loading_area').fadeIn("slow");
			}
			$('#message_holder').load(refresh_url, function(response, status, xhr) {
				if (status == "error" || xhr.status != 200) {
					$('.loading_area').html('<nobr>Oops Network Error. <span id="retry-progress-display"> Retrying in <span id="countdown-count">10</span> Seconds.</span></nobr>');
					var cntdwn = setInterval(function() {
						current_val = $('#countdown-count').html();
						if (current_val > 1) $('#countdown-count').html(current_val - 1);
						else {
							clearInterval(cntdwn);
							$('#retry-progress-display').html('Retrying Now...')
						}
					}, 1000);
					setTimeout(function() {
						refresh('retry');
					}, 10000);
					return false;
				}
				new_notification('false');
				$('.loading_area').fadeOut("slow");
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
				bgiframe: true,
				autoOpen: false,
				height: 100,
				modal: true,
				buttons: {
					'<?php echo tr('Save'); ?>': function() {
						$("form.renamefolderform").trigger('submit');
					},
					'<?php echo tr('Cancel'); ?>': function() {
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
				bgiframe: true,
				autoOpen: false,
				height: 165,
				modal: true,
				buttons: {
					'<?php echo tr('Cancel'); ?>': function() {
						$(this).dialog('close');
					},
					'<?php echo tr('Delete this folder'); ?>': function() {
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
			$("#deletealldialog").dialog({
				bgiframe: true,
				autoOpen: false,
				height: 165,
				modal: true,
				buttons: {
					'<?php echo tr('Cancel'); ?>': function() {
						$(this).dialog('close');
					},
					'<?php echo tr('Delete all'); ?>': function() {
						$.get("<?php echo site_url('messages/delete_all').'/'.strtolower($this->Kalkun_model->get_folders('name', $this->uri->segment(4))->row('name'));?>");
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
