<script type="text/javascript">
	var refreshId = setInterval(function() {
		$('.modem_status').load('<?php echo site_url('kalkun/notification')?>');
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
					var msg = '<?php echo tr('Network Error. <span id="retry-progress-display">Retrying in <span id="countdown-count">10</span> seconds.</span>'); ?>';
					show_loading('<span style="white-space: nowrap">' + msg + '</span>');
					var cntdwn = setInterval(function() {
						current_val = $('#countdown-count').html();
						if (current_val > 1) $('#countdown-count').html(current_val - 1);
						else {
							clearInterval(cntdwn);
							$('#retry-progress-display').html('<?php echo tr('Retrying now'); ?>')
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

	function show_notification(text) {
		$('.notification_area').text(text).fadeIn().delay(1500).fadeOut('slow');
	}

	function compose_message(type, repeatable = false, focus_element = '#personvalue', param1, param2) {
		//console.debug('DEBUG: compose_message');
		//console.debug(type);
		//console.debug(repeatable);
		//console.debug(focus_element);
		//console.debug(param1);
		//console.debug(param2);

		$("#compose_sms_container").html('<div align="center"><?php echo tr('Loading'); ?></div>');
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
			buttons['<?php echo tr('Send message'); ?>'] = function() {
				if ($("#composeForm").valid()) {
					$('.ui-dialog-buttonpane :button').each(function() {
						if ($(this).text() == '<?php echo tr('Send message'); ?>') $(this).html('<?php echo tr('Sending'); ?> <img src="<?php echo $this->config->item('img_path').'processing.gif' ?>" height="12" style="margin:0px; padding:0px;">');
					});
					$.post("<?php echo site_url('messages/compose_process') ?>", $("#composeForm").serialize())
						.done(function(data) {
							$("#compose_sms_container").html(data);
							$("#compose_sms_container").dialog("option", "buttons", {
								"<?php echo tr('Close'); ?>": function() {
									$(this).dialog("destroy");
								}
							});
							setTimeout(function() {
								if ($("#compose_sms_container").hasClass('ui-dialog-content')) {
									$("#compose_sms_container").dialog('destroy')
								}
							}, 1500);
						})
						.fail(function(data) {
							$('.ui-dialog-buttonpane :button').each(function() {
								if ($(this).text() == '<?php echo tr('Sending'); ?> ') $(this).html('<?php echo tr('Send message'); ?>');
							});
							$("#compose_sms_container_error").html($(data.responseText).filter('div'));
							$("#compose_sms_container_error").dialog({
								modal: true,
								width: 550,
								show: 'fade',
								hide: 'fade',
								buttons: {
									"<?php echo tr('Close'); ?>": function() {
										$(this).dialog("destroy");
									}
								}
							});
						});
				}
			};
			if (repeatable) {
				buttons['<?php echo tr('Send and repeat'); ?>'] = function() {
					if ($("#composeForm").valid()) {
						$.post("<?php echo site_url('messages/compose_process') ?>", $("#composeForm").serialize(), function(data) {
							$("#compose_sms_container").append(data);
						});
					}
				};
			}
			buttons["<?php echo tr('Cancel'); ?>"] = function() {
				$(this).dialog('destroy');
			};
			$(this).dialog({
				modal: true,
				width: 550,
				show: 'fade',
				hide: 'fade',
				buttons: buttons,
				open: function() {
					$(focus_element).trigger('focus');
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
			compose_message('normal', true, '#personvalue');
			return false;
		});

		// About
		$('#about_button').on("click", function() {
			$("#about").dialog({
				bgiframe: true,
				autoOpen: false,
				height: 400,
				width: 550,
				modal: true
			});
			$('#about').dialog('open');
			return false;
		});

		// Add folder
		$('#addfolder').on("click", function() {
			$("#addfolderdialog").dialog({
				bgiframe: true,
				autoOpen: false,
				height: 100,
				modal: true,
				buttons: {
					'Save': function() {
						$("form.addfolderform").trigger('submit');
					},
					Cancel: function() {
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

		// languange support
		var save = '<?php echo tr('Save')?>';
		var cancel = '<?php echo tr('Cancel')?>';

		$('div.ui-dialog-buttonpane:eq(0) button:eq(1)').text(cancel);
		$('div.ui-dialog-buttonpane:eq(0) button:eq(0)').text(save);

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
				bgiframe: true,
				autoOpen: false,
				height: 275,
				width: 500,
				modal: true,
				buttons: {
					'<?php echo tr('Search');?>': function() {
						$('#a_search_form').trigger('submit');
					},
					"<?php echo tr('Cancel');?>": function() {
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
