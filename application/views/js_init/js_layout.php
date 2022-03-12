<script type="text/javascript">
	// Initial value for inbox unread cound
	unread_in_count = <?php echo $this->Message_model->get_messages([
		'readed' => FALSE,
		'uid' => $this->session->userdata('id_user'),
	])->num_rows(); ?>;

	csrf_name = "<?php echo $this->security->get_csrf_token_name(); ?>";
	csrf_hash = "<?php echo $this->security->get_csrf_hash() ?>";

	let cntdwnId, timeoutIdAutoRefr;

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
			$.get("<?php echo site_url('messages').'/'.$folder.'/'.$type.'/'.$id_folder ?>")
				.done(function(data) {
					if ($("#error_container").hasClass("ui-dialog-content")) {
						$("#error_container").dialog("close");
					}
					$('#message_holder').html(data.responseText);
					new_notification('false');
					hide_loading();
				})
				.fail(function(data) {
					var retry_delay = 10;
					display_error_container(data, retry_delay);
					if (!timeoutIdAutoRefr) {
						timeoutIdAutoRefr = setTimeout(function() {
							timeoutIdAutoRefr = null;
							auto_refresh();
						}, retry_delay * 1000);
					}
					return false;
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

	function hide_loading() {
		$('.loading_area').fadeOut("slow");
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
	function display_error_container(data, retry_delay) {
		if (data !== undefined) {
			if (data.responseText !== undefined) {
				var attr = $(data.responseText).filter('div').attr("id");
				if (attr === "container") {
					$("#error_container_main").html($(data.responseText).filter('div').removeAttr("id"));
				} else {
					$("#error_container_main").html($(data.responseText));
				}
			} else if (data.statusText !== undefined) {
				$("#error_container_main").text(data.statusText);
			} else {
				$("#error_container_main").text(data);
			}
		} else {
			$("#error_container_main").text(<?php echo tr_js('Network error.'); ?>);
		}
		if (retry_delay) {
			if (!cntdwnId) {
				$('#countdown-count').text(retry_delay);
				$('#error_container_delay_notif').show();
				$('#retry-progress').show();
				$('#retry-now').hide();
				cntdwnId = setInterval(function() {
					current_val = $('#countdown-count').text();
					if (current_val > 1) {
						$('#countdown-count').text(current_val - 1);
					} else {
						clearInterval(cntdwnId);
						cntdwnId = null;
						$('#retry-progress').hide();
						$('#retry-now').show();
					}
				}, 1000);
			}
		}
		$("#error_container").dialog({
			closeText: <?php echo tr_js('Close'); ?>,
			modal: true,
			width: 550,
			maxHeight: 450,
			show: 'fade',
			hide: 'fade',
			buttons: {
				<?php echo tr_js('Close'); ?>: function() {
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
			case 'reply':
			case 'pbk_contact':
			case 'pbk_groups':
				data = {
					type: type,
					dest: param1,
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
				buttons[<?php echo tr_js('Send message'); ?>] = function() {
					if ($("#composeForm").valid()) {
						$('.ui-dialog-buttonpane :button').each(function() {
							if ($(this).text() == <?php echo tr_js('Send message'); ?>) {
								var sending_html = <?php echo tr_js('Sending'); ?>;
								sending_html += " <img src=\"<?php echo $this->config->item('img_path').'processing.gif' ?>\" height=\"12\" style=\"margin:0px; padding:0px;\">";
								$(this).html(sending_html);
							}

						});
						$.post("<?php echo site_url('messages/compose_process') ?>", $("#composeForm").serialize())
							.done(function(data) {
								show_notification(data.msg, data.type);
								$("#compose_sms_container").dialog("destroy");
							})
							.fail(function(data) {
								$('.ui-dialog-buttonpane :button').each(function() {
									if ($(this).text() == <?php echo tr_js('Sending'); ?> + " ")
										$(this).text(<?php echo tr_js('Send message'); ?>);
								});
								display_error_container(data);
							});
					}
				};
				if (repeatable) {
					buttons[<?php echo tr_js('Send and repeat'); ?>] = function() {
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
				buttons[<?php echo tr_js('Cancel'); ?>] = function() {
					$(this).dialog('destroy');
				};
				$("#compose_sms_container").dialog({
					closeText: <?php echo tr_js('Close'); ?>,
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
						}, 300);
					}
				});
				$("#compose_sms_container").dialog('open');
			})
			.fail(function(data) {
				display_error_container(data);
			});
	}

	$(document).ready(function() {

		// Do the UI action requested by GET or POST
		var post_get_data = JSON.parse($("#post_get_data").text());

		if ("action" in post_get_data) {
			switch (post_get_data.action) {
				case 'compose':
					if ("type" in post_get_data && post_get_data.type == "prefill" && "phone" in post_get_data && "msg" in post_get_data) {
						compose_message(
							post_get_data.type,
							true,
							'#manualvalue',
							post_get_data.phone,
							post_get_data.msg);
					} else if ("type" in post_get_data && post_get_data.type == "normal") {
						compose_message(
							post_get_data.type,
							true,
							'#personvalue_tags_tag');
					}
					break;
					// TODO: Support additional UI actions: add/edit user, contact
				default:
					break;
			}
		}

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
				closeText: <?php echo tr_js('Close'); ?>,
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
				closeText: <?php echo tr_js('Close'); ?>,
				bgiframe: true,
				autoOpen: false,
				modal: true,
				buttons: {
					<?php echo tr_js('Save'); ?>: function() {
						$("form.addfolderform").trigger('submit');
					},
					<?php echo tr_js('Cancel'); ?>: function() {
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

		$('div.ui-dialog-buttonpane:eq(0) button:eq(1)').text(<?php echo tr_js('Cancel'); ?>);
		$('div.ui-dialog-buttonpane:eq(0) button:eq(0)').text(<?php echo tr_js('Save'); ?>);

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
				closeText: <?php echo tr_js('Close'); ?>,
				bgiframe: true,
				autoOpen: false,
				width: 500,
				modal: true,
				buttons: {
					<?php echo tr_js('Search'); ?>: function() {
						$('#a_search_form').trigger('submit');
					},
					<?php echo tr_js('Cancel'); ?>: function() {
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

	jQuery.cachedScript = function(url, options) {
		// Allow user to set any option except for dataType, cache, and url
		options = $.extend(options || {}, {
			dataType: "script",
			cache: true,
			url: url
		});

		// Use $.ajax() since it is more flexible than $.getScript
		// Return the jqXHR object so we can chain callbacks
		return jQuery.ajax(options);
	};

</script>
