<script type="text/javascript">
	$(document).ready(function() {
		var base = "<?php echo  site_url();?>/messages/delete_messages/";
		var source = "<?php echo $this->uri->segment(4);?>";
		var current_folder = "<?php echo $this->uri->segment(6);?>";
		//var dest_url = base + source;

		<?php if ($this->config->item('enable_emoticons')) : ?>
		$(".message_preview").emoticons("<?php echo   $this->config->item('img_path').'emoticons/'; ?>");
		$(".message_content").emoticons("<?php echo   $this->config->item('img_path').'emoticons/'; ?>");
		<?php endif; ?>

		// Delete messages
		$(document).on('click', "a.global_delete", action_delete = function() {
			var count = $("input.select_message:checked:visible").length;
			if (count == 0) {
				show_notification("<?php echo tr_addcslashes('"', 'No item selected.'); ?>");
			} else {
				var notif = {
					msg: "<?php echo tr_addcslashes('"', '{0} message(s) deleted'); ?>",
					type: "info",
				};
				notif.msg = notif.msg.replace('{0}', count);
				$("input.select_message:checked:visible").each(function() {
					var message_row = $(this).parents('div:eq(2)');
					id_access = '#item_source' + $(this).val();
					item_folder = $(id_access).val();
					dest_url = base + item_folder;
					$.ajaxSetup({
						async: false
					});
					$.post(dest_url, {
							type: 'single',
							id: $(this).val(),
							current_folder: current_folder,
							[csrf_name]: csrf_hash,
						})
						.done(function(data) {
							if (!data) {
								$(message_row).slideUp("slow");
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
			var count = $("input.select_message:checked:visible").length;
			if (count == 0) {
				show_notification("<?php echo tr_addcslashes('"', 'No item selected.')?>");
			} else {

				var id_folder = (source == 'inbox') ? 1 : 3;

				$("input.select_message:checked:visible").each(function() {
					var message_row = $(this).parents('div:eq(2)');
					$.post("<?php echo  site_url('messages/move_message') ?>", {
							type: 'single',
							current_folder: current_folder,
							folder: source,
							id_folder: id_folder,
							id_message: $(this).val(),
							[csrf_name]: csrf_hash,
						})
						.done(function() {
							$(message_row).slideUp("slow");
						})
						.fail(function(data) {
							display_error_container(data);
						})
						.always(function(data) {
							update_csrf_hash();
						});
				});
				var notif = "<?php echo tr_addcslashes('"', '{0} conversation(s) recovered'); ?>"
				notif = notif.replace('{0}', count);
				show_notification(notif);
			}
		});

		// Move messages
		$(document).on('click', ".move_to", function() {
			var count = $("input.select_message:checked:visible").length;
			if (count == 0) {
				$("#movetodialog").dialog('close');
				show_notification("<?php echo tr_addcslashes('"', 'No item selected.'); ?>");
			} else {
				var id_folder = $(this).attr('id');
				$("#movetodialog").dialog('close');
				$("input.select_message:checked:visible").each(function() {
					var message_row = $(this).parents('div:eq(2)');
					id_access = '#item_source' + $(this).val();
					item_folder = $(id_access).val();
					$.post("<?php echo  site_url('messages/move_message') ?>", {
							type: 'single',
							current_folder: current_folder,
							folder: item_folder,
							id_folder: id_folder,
							id_message: $(this).val(),
							[csrf_name]: csrf_hash,
						})
						.done(function() {
							$(message_row).slideUp("slow");
							show_notification("<?php echo tr_addcslashes('"', 'Messages moved successfully')?>")
						})
						.fail(function(data) {
							display_error_container(data);
						})
						.always(function(data) {
							update_csrf_hash();
						});
				});
			}
		});

		$(document).on('click', ".move_to_button", message_move = function() {
			$('#movetodialog').dialog('open');
			return false;
		});

		// Move To dialog
		$("#movetodialog").dialog({
			closeText: "<?php echo tr_addcslashes('"', 'Close'); ?>",
			bgiframe: true,
			autoOpen: false,
			modal: true,
		});


		// message detail
		$(document).on('click', "span.message_toggle", function() {
			var row = $(this).parents('div:eq(1)');
			$(row).find("div.message_content").toggle();
			$(row).find("span.message_preview").toggle();
			$(row).find("div.optionmenu").toggle();

			if ($(row).find("div.detail_area").is(":visible")) {
				$(row).find("div.detail_area").toggle();
				$(row).find("a.detail_button").text("<?php echo tr_addcslashes('"', 'Show details'); ?>");
			}
			return false;
		});


		// select all
		$(document).on('click', "a.select_all_button", select_all = function() {
			$(".select_message").prop('checked', true);
			$(".messagelist").addClass("messagelist_hover");
			return false;
		});

		// clear all
		$(document).on('click', "a.clear_all_button", clear_all = function() {
			$(".select_message").prop('checked', false);
			$(".messagelist").removeClass("messagelist_hover");
			return false;
		});

		// input checkbox
		$(document).on('click', "input.select_message", function() {
			if ($(this).prop('checked') == true) {
				$(this).parents('div:eq(2)').addClass("messagelist_hover");
				current_number = $(this).val();
			} else {
				$(this).parents('div:eq(2)').removeClass("messagelist_hover");
				current_number = '';
			}
		});

		<?php if ( ! is_ajax()) : ?>
		// refresh
		$(document).on('click', "a.refresh_button", refresh = function(type) {
			if (type != 'retry') {
				$('.loading_area').text("<?php echo tr_addcslashes('"', 'Loading'); ?>");
				$('.loading_area').fadeIn("slow");
			}
			$('#message_holder').load("<?php echo  site_url('messages/conversation/'.$this->uri->segment(3).'/'.$this->uri->segment(4).'/'.preg_replace ('/ /', '%20', $this->uri->segment(5)).'/'.$this->uri->segment(6, 0)) ?>", function(response, status, xhr) {
				if (status == "error" || xhr.status != 200) {
					var msg = "<?php echo tr_addcslashes('"', 'Network Error. <span id="retry-progress-display">Retrying in <span id="countdown-count">10</span> seconds.</span>'); ?>";
					$('.loading_area').html('<span style="white-space: nowrap">' + msg + '</span>');
					var cntdwn = setInterval(function() {
						current_val = $('#countdown-count').text();
						if (current_val > 1) $('#countdown-count').text(current_val - 1);
						else {
							clearInterval(cntdwn);
							$('#retry-progress-display').text("<?php echo tr_addcslashes('"', 'Retrying now'); ?>");
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
		});

		<?php endif; ?>

		// Reply, forward, resend SMS
		$(document).on('click', 'a.reply_button, a.forward_button, a.resend', message_reply = function() {
			var button = $(this).attr('class');
			var url = '<?php echo site_url('messages/compose')?>';
			switch (button) {
				case 'forward_button':
					var header = $(this).parents('div:eq(1)');
					var source = header.attr('class').split(' ').slice(-1)['0'];
					var message_id = header.children().children('input.select_message').attr('id');
					compose_message('forward', false, '#personvalue_tags_tag', source, message_id);
					break;
				case 'resend':
					var header = $(this).parents('div:eq(1)');
					var source = header.attr('class').split(' ').slice(-1)['0'];
					var message_id = header.children().children('input.select_message').attr('id');
					compose_message('resend', false, '#personvalue_tags_tag', source, message_id);
					break;
				default:
					var type = 'reply';
					var phone = '<?php echo rawurldecode($this->uri->segment(5));?>';
					if (phone == null || phone == '')
						var phone = $(this).parents('div:eq(1)').children().children('input.item_number').val();
					compose_message('reply', false, '#message', phone);
			}
			return false;
		});

		// Show/hide detail
		$(document).on('click', 'a.detail_button', function() {
			var row = $(this).parents('div:eq(2)');
			$(row).find("div.detail_area").toggle();

			if ($(this).text() == "<?php echo tr_addcslashes('"', 'Hide details'); ?>")
				$(this).text("<?php echo tr_addcslashes('"', 'Show details'); ?>");
			else
				$(this).text("<?php echo tr_addcslashes('"', 'Hide details'); ?>");
			return false;
		});

		// Add contact
		$(document).on('click', '.add_to_pbk', function() {
			var param1 = $(this).parents('div:eq(1)').children().children('input.item_number').val(); /* phone number */
			$.get('<?php echo site_url('phonebook/add_contact')?>', {
					'type': 'message',
					'param1': param1
				})
				.done(function(responseText, textStatus, jqXHR) {
					$("#contact_container").html(responseText);
					$("#contact_container").dialog({
						title: "<?php echo tr_addcslashes('"', 'Add contact');?>",
						closeText: "<?php echo tr_addcslashes('"', 'Close'); ?>",
						modal: true,
						show: 'fade',
						hide: 'fade',
						buttons: {
							"<?php echo tr_addcslashes('"', 'Save'); ?>": function() {
								//if($("#addContact").valid()) {
								$.post("<?php echo site_url('phonebook/add_contact_process') ?>", $("#addContact").serialize())
									.done(function(data) {
										show_notification(data.msg, data.type);
										$("#contact_container").dialog("destroy");
									})
									.fail(function(data) {
										display_error_container(data);
									});
							},
							"<?php echo tr_addcslashes('"', 'Cancel'); ?>": function() {
								$(this).dialog('close');
							}
						}
					});
					$("#contact_container").dialog('open');
				})
				.fail(function(data) {
					display_error_container(data);
				});
			return false;
		});

		<?php if ($this->uri->segment(4) != '6' && $this->uri->segment(6) != '6' && ! is_ajax()) : ?>
		// report spam
		$(document).on('click', ".spam_button", function() {
			var count = $("input.select_message:checked:visible").length;

			if (count == 0) {
				show_notification("<?php echo tr_addcslashes('"', 'No item selected.'); ?>");
			} else {
				$("input.select_message:checked:visible").each(function() {
					var message_row = $(this).parents('div:eq(2)');
					id_access = '#item_source' + $(this).val();
					item_folder = $(id_access).val();
					if (item_folder != 'inbox') {
						show_notification("<?php echo tr_addcslashes('"', 'Outgoing message cannot be spam'); ?>");
						return;
					}
					$.post("<?php echo  site_url('messages/report_spam/spam') ?>", {
							id_message: $(this).val(),
							[csrf_name]: csrf_hash,
						})
						.done(function() {
							$(message_row).slideUp("slow");
						})
						.fail(function(data) {
							display_error_container(data);
						})
						.always(function(data) {
							update_csrf_hash();
						});

				});
				show_notification("<?php echo tr_addcslashes('"', 'Spam reported'); ?>")
			}
		});
		<?php else: ?>
		//report ham
		$(document).on('click', ".ham_button", function() {
			var count = $("input.select_message:checked:visible").length;
			if (count == 0) {
				show_notification("<?php echo tr_addcslashes('"', 'No item selected.'); ?>");
			} else {
				var id_folder = $(this).attr('id');
				$("input.select_message:checked:visible").each(function() {
					var message_row = $(this).parents('div:eq(2)');
					$.post("<?php echo  site_url('messages/report_spam/ham') ?>", {
							id_message: $(this).val(),
							[csrf_name]: csrf_hash,
						})
						.done(function() {
							$(message_row).slideUp("slow");

						})
						.fail(function(data) {
							display_error_container(data);
						})
						.always(function(data) {
							update_csrf_hash();
						});
				});
				show_notification("<?php echo tr_addcslashes('"', 'Message(s) marked non-spam'); ?>")
			}
		});
		<?php endif; ?>

		//resend_bulk
		$(document).on('click', ".resend_bulk", function() {
			var count = $("input.select_message:checked:visible").length;
			if (count == 0) {
				$('.notification_area').text("<?php echo tr_addcslashes('"', 'No item selected.'); ?>");
				$('.notification_area').show();
			} else {
				resend_conf_label = "<?php echo tr_addcslashes('"', 'You are about to resend {0} message(s).'); ?>";
				resend_conf_label = resend_conf_label.replace('{0}', count);
				resend_conf = `<p>${resend_conf_label}</p>`;
				delete_dup_label = "<?php echo tr_addcslashes('"', 'Delete copy (prevents duplicates).'); ?>";
				delete_dup = `<input type="checkbox" id="delete_dup" /> <label for="delete_dup">${delete_dup_label}</label>`;
				$("#compose_sms_container").html(resend_conf + delete_dup);
				$("#compose_sms_container").dialog({
					//title: 'Resend SMS',
					closeText: "<?php echo tr_addcslashes('"', 'Close'); ?>",
					modal: true,
					draggable: true,
					width: 550,
					show: 'fade',
					hide: 'fade',
					buttons: {
						"<?php echo tr_addcslashes('"', 'Continue'); ?>": function() {
							delete_dup_status = $("#delete_dup").is(":checked");

							$("input.select_message:checked:visible").each(function() {
								DestinationNumber = $(this).parents('div:eq(0)').children('input.item_number').val();
								TextDecoded = $(this).parents('div:eq(1)').children('div.message_content').text();
								ID = $(this).parents('div:eq(1)').children().children('input.select_message').attr('id');
								Class = $(this).parents('div:eq(1)').children('div.message_metadata').children('span.class').text();
								$.post("<?php echo site_url('messages/compose_process') ?>", {
										sendoption: 'sendoption3',
										manualvalue: DestinationNumber,
										senddateoption: 'option1',
										class: Class,
										validity: '-1',
										smstype: 'normal',
										sms_loop: '1',
										message: TextDecoded,
										[csrf_name]: csrf_hash,
									})
									.done(function(data) {
										show_notification(data.msg, data.type);
										$("#compose_sms_container").dialog("close");
										return;
									})
									.fail(function(data) {
										display_error_container(data);
										return;
									})
									.always(function(data) {
										update_csrf_hash();
									});

								// Delete copy
								if (delete_dup_status) {
									dest_url = base + 'sentitems';
									$.post(dest_url, {
											type: 'single',
											id: ID,
											current_folder: current_folder,
											[csrf_name]: csrf_hash,
										})
										.fail(function(data) {
											display_error_container(data);
										})
										.always(function(data) {
											update_csrf_hash();
										});
								}
							});
						},
						"<?php echo tr_addcslashes('"', 'Cancel'); ?>": function() {
							$(this).dialog('close');
						}
					}
				});
				$("#compose_sms_container").dialog('open');
			}
		});
	});

</script>
