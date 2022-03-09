<script type="text/javascript">
	// Initial value for inbox unread cound
	unread_in_count = <?php echo $this->Message_model->get_messages([
		'readed' => FALSE,
		'uid' => $this->session->userdata('id_user'),
	])->num_rows(); ?>;

	csrf_name = "<?php echo $this->security->get_csrf_token_name(); ?>";
	csrf_hash = "<?php echo $this->security->get_csrf_hash() ?>";

	var refreshId = setInterval(function() {
		$('.modem_status').load('<?php echo site_url('kalkun/notification')?>', function(responseText, textStatus, jqXHR) {
			jqXHR
				.done(function(data) {})
				.fail(function(data) {
					display_error_container(data);
				});
		});
		new_notification('true');
	}, 60000);

	function update_csrf_hash() {
		$.get('<?php echo site_url('kalkun/get_csrf_hash')?>', function(data) {
			csrf_hash = data;
			$('input[name="' + csrf_name + '"]').each(function() {
				$(this).val(csrf_hash);
			});
		});
	}

	function play_notification_sound() {
		// Use HTMLAudioElement: https://developer.mozilla.org/en-US/docs/Web/API/HTMLAudioElement
		var audioElement = new Audio('<?php echo $this->config->item('sound_path').$this->config->item('new_incoming_message_sound')?>');
		audioElement.play();
	}

	function new_notification(refreshmode) {
		$.get("<?php echo site_url('kalkun/unread_count')?>")
			.done(function(data) {
				// Get new unread count for inbox
				unread_in_count_new = data['in'];

				// Update UI with new values
				if (data['in'] > 0) {
					$('span.unread_inbox_notif').text("(" + data['in'] + ")");
					// example of title: "(23) Kalkun"
					var title = $(document).attr('title');
					var re_title = title.match('(\\((\\d*)\\) )?(.*)');
					var title_cleaned = re_title[3];
					var title_new = "(" + data['in'] + ") " + title_cleaned;
					$(document).attr('title', title_new);
				} else
					$('span.unread_inbox_notif').text("");
				if (data['spam'] > 0)
					$('span.unread_spam_notif').text("(" + data['spam'] + ")");
				else
					$('span.unread_spam_notif').text("");

				// play the sound
				if (unread_in_count_new > unread_in_count) {
					play_notification_sound();
				}

				// Set new value for unread_in_count
				unread_in_count = unread_in_count_new;
			})
			.fail(function(data) {
				display_error_container(data);
			});

		<?php if ($this->uri->segment(2) == 'folder' || $this->uri->segment(2) == 'my_folder'): ?>

		function auto_refresh() {
			$('#message_holder').load("<?php echo site_url('messages').'/'.$folder.'/'.$type.'/'.$id_folder ?>", function(response, status, xhr) {
				if (status == "error" || xhr.status != 200) {
					var msg = "<?php echo tr_addcslashes('"', 'Network Error. <span id="retry-progress-display">Retrying in <span id="countdown-count">10</span> seconds.</span>'); ?>";
					show_loading('<span style="white-space: nowrap">' + msg + '</span>');
					var cntdwn = setInterval(function() {
						current_val = $('#countdown-count').text();
						if (current_val > 1) $('#countdown-count').text(current_val - 1);
						else {
							clearInterval(cntdwn);
							$('#retry-progress-display').text("<?php echo tr_addcslashes('"', 'Retrying now'); ?>")
						}
					}, 1000);
					setTimeout(function() {
						auto_refresh();
					}, 10000);
					return false;
				}
			});
		}
		if (refreshmode == 'true') //refresh automatically if in threastlist 
			auto_refresh();
		<?php endif; ?>
	}

	function show_loading(text) {
		$('.loading_area').text(text);
		var content_width = ($('.loading_area').width()) / 2;
		$('.loading_container').css('margin-left', -content_width);
		$('.loading_area').fadeIn("slow");
	}

	function show_notification(text, type) {
		if (type == "error") {
			$('.notification_area').addClass("error_notif");
		} else {
			$('.notification_area').removeClass("error_notif");
		}
		$('.notification_area').text(text).fadeIn().delay(1500).fadeOut('slow');
	}

	// Error container
	function display_error_container(data) {
		if (data.responseText !== undefined) {
			attr = $(data.responseText).filter('div').attr("id");
			if (attr === "container") {
				$("#error_container").html($(data.responseText).filter('div').removeAttr("id"));
			} else {
				$("#error_container").html($(data.responseText));
			}
		} else {
			$("#error_container").text(data.statusText);
		}
		$("#error_container").dialog({
			closeText: "<?php echo tr_addcslashes('"', 'Close'); ?>",
			modal: true,
			width: 550,
			maxHeight: 450,
			show: 'fade',
			hide: 'fade',
			buttons: {
				"<?php echo tr_addcslashes('"', 'Close'); ?>": function() {
					$(this).dialog("destroy");
				}
			}
		});
	}

	function compose_message(type, repeatable = false, focus_element = '#personvalue_tags_tag', param1, param2) {
		//console.debug('DEBUG: compose_message');
		//console.debug(type);
		//console.debug(repeatable);
		//console.debug(focus_element);
		//console.debug(param1);
		//console.debug(param2);

		switch (type) {
			case 'forward':
			case 'resend':
				data = {
					type: type,
					source: param1,
					id: param2,
				};
				break;
			case 'pbk_groups':
				data = {
					type: type,
					grp_id: param1,
				};
				break;
			case 'prefill':
				data = {
					type: type,
					phone: param1,
					message: param2,
				};
				break;
			default:
				data = {
					type: type,
				};
				break;
		}
		$.get('<?php echo site_url('messages/compose')?>', data)
			.done(function(responseText, textStatus, jqXHR) {
				$('#compose_sms_container').html(responseText);
				var buttons = {};
				buttons["<?php echo tr_addcslashes('"', 'Send message'); ?>"] = function() {
					if ($("#composeForm").valid()) {
						$('.ui-dialog-buttonpane :button').each(function() {
							if ($(this).text() == "<?php echo tr_addcslashes('"', 'Send message'); ?>") $(this).html("<?php echo tr_addcslashes('"', 'Sending'); ?> <img src=\"<?php echo $this->config->item('img_path').'processing.gif' ?>\" height=\"12\" style=\"margin:0px; padding:0px;\">");
						});
						$.post("<?php echo site_url('messages/compose_process') ?>", $("#composeForm").serialize())
							.done(function(data) {
								show_notification(data.msg, data.type);
								$("#compose_sms_container").dialog("destroy");
							})
							.fail(function(data) {
								$('.ui-dialog-buttonpane :button').each(function() {
									if ($(this).text() == "<?php echo tr_addcslashes('"', 'Sending'); ?> ") $(this).text("<?php echo tr_addcslashes('"', 'Send message'); ?>");
								});
								display_error_container(data);
							});
					}
				};
				if (repeatable) {
					buttons["<?php echo tr_addcslashes('"', 'Send and repeat'); ?>"] = function() {
						if ($("#composeForm").valid()) {
							$.post("<?php echo site_url('messages/compose_process') ?>", $("#composeForm").serialize())
								.done(function(data) {
									$("#compose_sms_container_notif_area").text()
									if (data.type == "error") {
										$("#compose_sms_container_notif_area").addClass("error_notif");
									} else {
										$("#compose_sms_container_notif_area").removeClass("error_notif");
									}
									$("#compose_sms_container_notif_area").text(data.msg);
									$("#compose_sms_container_notif_area").show();
									setTimeout(function() {
										$("#compose_sms_container_notif_area").hide();
									}, 1500);
								})
								.fail(function(data) {
									$("#compose_sms_container_notif_area").hide();
									display_error_container(data);
								}).always(function(data) {
									update_csrf_hash();
								});
						}
					};
				}
				buttons["<?php echo tr_addcslashes('"', 'Cancel'); ?>"] = function() {
					$(this).dialog('destroy');
				};
				$("#compose_sms_container").dialog({
					closeText: "<?php echo tr_addcslashes('"', 'Close'); ?>",
					modal: true,
					width: 550,
					show: 'fade',
					hide: 'fade',
					buttons: buttons,
					open: function() {
						setTimeout(function() {
							// Need to use setTimeout to be able to access focus_element in the case the focus on a tagsInput element.
							$(focus_element).trigger('focus');
							return;
						}, 1);
					}
				});
				$("#compose_sms_container").dialog('open');
			})
			.fail(function(data) {
				display_error_container(data);
			});
	}

	$(document).ready(function() {

		<?php switch ($this->input->get('action')):
		case NULL:
			break;
		case 'compose': ?>
		compose_message(
			'<?php echo $this->input->get('type'); ?>',
			true,
			'#personvalue_tags_tag',
			"<?php echo $this->input->get('phone'); ?>",
			"<?php echo $this->input->get('msg'); ?>"
		);
		<?php break;
		default:
			// TODO for other actions that show a dialog (add/edit user, add/edit contact...).
?>
		<?php break; ?>
		<?php endswitch; ?>

		// Get current page for styling/css
		$("#menu").find("a[href='" + window.location.href + "']").each(function() {
			$(this).addClass("current");
		});

		// Compose SMS
		$('#compose_sms_normal').on('click', null, function() {
			compose_message('normal', true, '#personvalue_tags_tag');
			return false;
		});

		// About
		$('#about_button').on("click", function() {
			$("#about").dialog({
				closeText: "<?php echo tr_addcslashes('"', 'Close'); ?>",
				bgiframe: true,
				autoOpen: false,
				width: 550,
				modal: true
			});
			$('#about').dialog('open');
			return false;
		});

		// Add folder
		$('#addfolder').on("click", function() {
			$("#addfolderdialog").dialog({
				closeText: "<?php echo tr_addcslashes('"', 'Close'); ?>",
				bgiframe: true,
				autoOpen: false,
				modal: true,
				buttons: {
					"<?php echo tr_addcslashes('"', 'Save'); ?>": function() {
						$("form.addfolderform").trigger('submit');
					},
					"<?php echo tr_addcslashes('"', 'Cancel'); ?>": function() {
						$(this).dialog('close');
					}
				},
				open: function() {
					$("#folder_name").trigger('focus');
				}
			});
			$('#addfolderdialog').dialog('open');
			return false;
		});

		$('div.ui-dialog-buttonpane:eq(0) button:eq(1)').text("<?php echo tr_addcslashes('"', 'Cancel')?>");
		$('div.ui-dialog-buttonpane:eq(0) button:eq(0)').text("<?php echo tr_addcslashes('"', 'Save')?>");

		//shift select
		$("input:checkbox").createCheckboxRange(function() {
			if ($(this).prop('checked') == true) {
				$(this).parents('div:eq(2)').addClass("messagelist_hover");
			} else {
				//$(this).prop('checked', true)
				$(this).parents('div:eq(2)').removeClass("messagelist_hover");
			}
		});

		//search
		$('.sms_search_form').on('submit', function() {
			if ($.trim($('#search').val()) == '') return false;
		});

		// advanced search    
		$("#a_search_date_from, #a_search_date_to").datepicker({
			maxDate: 0,
			dateFormat: 'yy-mm-dd'
		});
		$('#a_search').on("click", function() {
			$("#a_search_dialog").dialog({
				closeText: "<?php echo tr_addcslashes('"', 'Close'); ?>",
				bgiframe: true,
				autoOpen: false,
				width: 500,
				modal: true,
				buttons: {
					"<?php echo tr_addcslashes('"', 'Search');?>": function() {
						$('#a_search_form').trigger('submit');
					},
					"<?php echo tr_addcslashes('"', 'Cancel');?>": function() {
						$(this).dialog('close');
					}
				},
				open: function() {
					$("#a_search_from_to").trigger('focus');
				}
			});
			$('#a_search_dialog').dialog('open');
			return false;
		});

		<?php if ($this->uri->segment(2) != 'folder' AND $this->uri->segment(2) != 'my_folder'): ?>
		// logo click 
		$('div#logo a').on("click", function() {
			new_notification('false');
			return false;
		});
		<?php endif;?>
	});

</script>
