<script type="text/javascript">
	var refreshId = setInterval(function() {
		$('.modem_status').load('<?php echo site_url('kalkun/notification')?>', function(responseText, textStatus, jqXHR) {
			jqXHR
				.done(function(data) {})
				.fail(function(data) {
					display_error_container(data);
				});
		});
		//$('.unread_inbox').load('<?php echo site_url('kalkun/unread_inbox')?>');\
		//var current_title = $(document).attr('title');
		new_notification('true');
	}, 60000);

	function new_notification(refreshmode) {
		$.get("<?php echo site_url('kalkun/unread_count')?>", function(data) {
			unreadcount = data.split('/');

			$('span.unread_inbox_notif').text(unreadcount[0]);
			$('span.unread_spam_notif').text(unreadcount[2]);

			// example of title: "(23) Kalkun"
			var title = $(document).attr('title');
			var re_title = title.match('(\\((\\d*)\\) )?(.*)');
			var unreadcount_inbox_previous = re_title[2] ? re_title[2] : 0;
			var title_cleaned = re_title[3];

			var re = unreadcount[0].match('\\((.*)\\)');
			var unreadcount_inbox_current = 0;
			if (re != null && re[1]) {
				unreadcount_inbox_current = re[1];
			}

			var title_new = unreadcount[0] + ' ' + title_cleaned;
			$(document).attr('title', title_new);

			/*
			console.debug(
				"title: " + title +
				"\ntitle_cleaned: " + title_cleaned +
				"\nunreadcount_inbox_previous: " + unreadcount_inbox_previous +
				"\nunreadcount_inbox_current: " + unreadcount_inbox_current +
				"\ntitle_new: " + title_new
			);
			console.debug(re_title);
			console.debug(re);
			*/

			// play the sound
			if (unreadcount_inbox_current > 0 && unreadcount_inbox_current !== unreadcount_inbox_previous) {
				// Use HTMLAudioElement: https://developer.mozilla.org/en-US/docs/Web/API/HTMLAudioElement
				var audioElement = new Audio('<?php echo $this->config->item('sound_path').$this->config->item('new_incoming_message_sound')?>');
				audioElement.play();
			}
		});

		<?php if ($this->uri->segment(2) == 'folder' || $this->uri->segment(2) == 'my_folder'): ?>

		function auto_refresh() {
			$('#message_holder').load("<?php echo site_url('messages').'/'.$folder.'/'.$type.'/'.$id_folder ?>", function(response, status, xhr) {
				if (status == "error" || xhr.status != 200) {
					var msg = "<?php echo tr_addcslashes('"', 'Network Error. <span id="retry-progress-display">Retrying in <span id="countdown-count">10</span> seconds.</span>'); ?>";
					show_loading('<span style="white-space: nowrap">' + msg + '</span>');
					var cntdwn = setInterval(function() {
						current_val = $('#countdown-count').html();
						if (current_val > 1) $('#countdown-count').html(current_val - 1);
						else {
							clearInterval(cntdwn);
							$('#retry-progress-display').html("<?php echo tr_addcslashes('"', 'Retrying now'); ?>")
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
		$('.loading_area').html(text);
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
			$("#error_container").html($(data.responseText).filter('div').removeAttr("id"));
		} else {
			$("#error_container").html("<p>" + data.statusText + "</p>");
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

		$("#compose_sms_container").html("<div align=\"center\"><?php echo tr_addcslashes('"', 'Loading'); ?></div>");
		var data = {
			type: type
		};
		if (param1) {
			data.param1 = param1;
		}
		if (param2) {
			data.param2 = param2;
		}
		$("#compose_sms_container").load('<?php echo site_url('messages/compose')?>', data, function() {
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
								if ($(this).text() == "<?php echo tr_addcslashes('"', 'Sending'); ?> ") $(this).html("<?php echo tr_addcslashes('"', 'Send message'); ?>");
							});
							display_error_container(data);
						});
				}
			};
			if (repeatable) {
				buttons["<?php echo tr_addcslashes('"', 'Send and repeat'); ?>"] = function() {
					if ($("#composeForm").valid()) {
						$.post("<?php echo site_url('messages/compose_process') ?>", $("#composeForm").serialize(), function(data) {
							if (data.type == "error") {
								html = '<div class="notif" style="color:red">' + data.msg + '</div>';
							} else {
								html = '<div class="notif">' + data.msg + '</div>';
							}
							$("#compose_sms_container").append(html);
						});
					}
				};
			}
			buttons["<?php echo tr_addcslashes('"', 'Cancel'); ?>"] = function() {
				$(this).dialog('destroy');
			};
			$(this).dialog({
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
		});
	}

	$(document).ready(function() {

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
