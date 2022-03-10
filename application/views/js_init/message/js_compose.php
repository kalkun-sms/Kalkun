<script type="text/javascript">
	$(document).ready(function() {
		var max_chars_per_sms;
		var message_length_correction;

		var img_path = '<?php echo  $this->config->item('img_path');?>';
		$(".datepicker").datepicker({
			minDate: 0,
			maxDate: '+1Y',
			dateFormat: 'yy-mm-dd',
			showOn: 'button',
			buttonImage: img_path + 'calendar.gif',
			buttonImageOnly: true
		});

		autosize($("#message"));

		//$(".word_count").text("");
		//$("input#sms_loop").attr("disabled", true);

		function removeFromArray(orig_data, key, input_val) {
			newInput = [];
			if (input_val != "")
				newInput = input_val.split(',');

			var source = orig_data.slice(0);
			newInput.forEach(function(value) {
				var index = source.findIndex(function(element) {
					return element[key] == value;
				});
				if (index !== -1) {
					source.splice(index, 1);
				}
			});
			return source;
		}

		$("#personvalue_tags").tagsInput({
			"autocomplete": {
				source: [],
				minLength: 1,
				delay: 0,
				autoFocus: true,
			},
			"minChars": 1,
			"interactive": true,
			"delimiter": ",",
			"placeholder": <?php echo tr_js('Insert name from contact list'); ?>,
			"onAddTag": function(element, tag) {
				source = $("#personvalue_tags_tag").autocomplete("option", "source");

				matchIndex = source.findIndex(function(element) {
					return element["value"] == tag;
				});

				if (matchIndex !== -1) {
					id = source[matchIndex]["id"];

					// Add to JSON field
					var currentJSON = [];
					if ($("#personvalue_json").val() != "")
						currentJSON = JSON.parse($("#personvalue_json").val());
					currentJSON.push(source[matchIndex]);
					$("#personvalue_json").val(JSON.stringify(currentJSON));

					// Add to submitted input
					newInput = [];
					if ($("#personvalue").val() != "")
						newInput = $("#personvalue").val().split(",");

					newInput.push(id);
					$("#personvalue").val(newInput.join(","));
				} else {
					$("#personvalue_tags").removeTag(tag);
				}
			},
			"onRemoveTag": function(element, tag) {
				// Search index of removed tag in personvalue_json
				var currentJSON = [];
				if ($("#personvalue_json").val() != "")
					currentJSON = JSON.parse($("#personvalue_json").val());

				matchIndex = currentJSON.findIndex(function(elt) {
					return elt["value"] == tag;
				});

				// Remove it from personvalue_json & personvalue
				if (matchIndex !== -1) {
					// Remove from personvalue
					newInput = [];
					if ($("#personvalue").val() != "")
						newInput = $("#personvalue").val().split(",");
					newInput.splice(matchIndex, 1);
					$("#personvalue").val(newInput.join(","));

					// Remove from personvalue_json
					currentJSON.splice(matchIndex, 1);
					$("#personvalue_json").val(JSON.stringify(currentJSON));
				}
			},
		});

		$("#personvalue_tags_tag").on("keydown", function(event) {
			onTagInputKeydown();
			$(this).autocomplete("search");
		});

		onTagInputKeydownRunning = false;

		function onTagInputKeydown(e) {
			if (onTagInputKeydownRunning) {
				return;
			}
			onTagInputKeydownRunning = true;

			$("#personvalue_tags_tag").autocomplete("close");
			$("#personvalue_tags_tag").autocomplete("option", "source", []);

			setTimeout(function() {
				var value = $("#personvalue_tags_tag").val();

				if (value == "") {
					onTagInputKeydownRunning = false;
					return;
				}

				// show loading animation
				$("#personvalue_tags_tag").addClass("processing_image");

				$.get("<?php echo site_url('phonebook/get_phonebook/').'/'.(isset($source) ? $source : '');?>", {
						q: value,
						output_format: "tagInput"
					})
					.done(function(data) {
						result = removeFromArray(data, "id", $("#personvalue").val());

						if (result.length === 0) {
							var msg = <?php echo tr_js('No results for {0}', NULL, '%arg%'); ?>;
							msg = msg.replace("%arg%", value);
							$("#personvalue_tags_tag").autocomplete("option", "source", [msg]);
							$("#personvalue_tags_tag").autocomplete("search");
						} else {
							$("#personvalue_tags_tag").autocomplete("option", "source", result);
							$("#personvalue_tags_tag").autocomplete("search");
						}
						$("#personvalue_tags_tag").removeClass("processing_image");
						onTagInputKeydownRunning = false;
						return;
					})
					.fail(function(data) {
						$("#personvalue_tags_tag").removeClass("processing_image");
						onTagInputKeydownRunning = false;
						display_error_container(data);
						return;
					});
				return;
			}, 300);
		}

		// Import CSV
		$('#composeForm').ajaxForm({
			dataType: 'json',
			success: function(data) {
				var limit = data.Field.length;
				for (var i = 0; i < limit; i++) {
					var element = $('#import_value_count').clone().attr('id', 'import_value_' + i).attr('name', data.Field[i]);
					$('#import_value_count').after(element);
					$('#import_value_' + i).val(data[data.Field[i]]);

					var button = $('#field_button').clone().attr('id', 'field_button_' + i).attr('value', data.Field[i]).removeClass('hidden');
					$('#field_button').after(button).after(' ');
				}
				$('#import_value_count').val(limit);
				$('#field_option').show();

				// Field button
				$('.field_button').on("click", function() {
					var field = $(this).val();
					var text = $('#message').val();
					$('#message').val(text + '[[' + field + ']]');
					$('#message').focus();
				});
			}
		});

		// validation
		$("#composeForm").validate({
			ignore: '', // By default, jquery validation ignores hidden fields. Set this to the empty string to not ignore hidden fields (needed for personvalue which is hidden by tagsInput).
			rules: {
				personvalue: {
					required: "#sendoption1:checked",
				},
				manualvalue: {
					required: "#sendoption3:checked",
					remote: {
						url: "<?php echo site_url('kalkun/phone_number_validation'); ?>",
						type: "get",
						data: {
							phone: function() {
								return $("#manualvalue").val();
							},
						}
					}
				},
				import_file: {
					required: "#sendoption4:checked,#import_value:filled"
				},
				message: {
					required: true
				},
				datevalue: {
					required: "#option2:checked"
				},
				url: {
					required: "#stype3:checked",
					url: true
				}
			},
			messages: {
				personvalue: {
					required: <?php echo tr_js('Field required.'); ?>
				},
				manualvalue: {
					required: <?php echo tr_js('Field required.'); ?>,
				},
				import_file: {
					required: <?php echo tr_js('Field required.'); ?>
				},
				message: {
					required: <?php echo tr_js('Field required.'); ?>
				},
				datevalue: {
					required: <?php echo tr_js('Field required.'); ?>
				},
				url: {
					required: <?php echo tr_js('Field required.'); ?>,
					url: <?php echo tr_js('Should be a valid URL'); ?>
				}
			}
		});

		<?php
	$message_length_correction = 0;

	// if ads is active
	if ($this->config->item('sms_advertise'))
	{
		$ads_count = strlen($this->config->item('sms_advertise_message'));
		$message_length_correction += $ads_count;
	}

	// if append @username is active
	if ($this->config->item('append_username'))
	{
		$append_username_message = $this->config->item('append_username_message');
		$append_username_message = "\n".str_replace('@username', '@'.$this->session->userdata('username'), $append_username_message);

		$append_username_count = strlen($append_username_message);
		$message_length_correction += $append_username_count;
	}

	echo 'message_length_correction = '.$message_length_correction.';';
	?>

		// From: https://stackoverflow.com/a/12673229/15401262 (Copyright: Lajos Arpad, License: CC-BY-SA-3.0)
		function isGSMAlphabet(text) {
			var regexp = new RegExp("^[A-Za-z0-9 \\r\\n@£$¥èéùìòÇØøÅå\u0394_\u03A6\u0393\u039B\u03A9\u03A0\u03A8\u03A3\u0398\u039EÆæßÉ!\"#$%&'()*+,\\-./:;<=>?¡ÄÖÑÜ§¿äöñüà^{}\\\\\\[~\\]|\u20AC]*$");
			return regexp.test(text);
		}

		// Character counter
		$('.word_count').each(function() {
			var msg = <?php echo tr_js('{0} character(s) / {1} message(s)'); ?>;
			var length = $(this).val().length;
			max_chars_per_sms = isGSMAlphabet($(this).val()) ? 160 : 70;
			var message_count = Math.ceil((length + message_length_correction) / max_chars_per_sms);
			$(this).parent().find('.counter').text(msg.replace("{0}", length).replace("{1}", message_count));
			$(this).keyup(function() {
				var str = $(this).val();
				var new_length = str.length;
				max_chars_per_sms = isGSMAlphabet(str) ? 160 : 70;
				var n = str.match(/\^|\{|\}|\\|\[|\]|\~|\||\€/g);
				n = (n) ? n.length : 0;
				new_length = new_length + n;
				var message_count = Math.ceil((new_length + message_length_correction) / max_chars_per_sms);
				$(this).parent().find('.counter').text(msg.replace("{0}", new_length).replace("{1}", message_count));
			});
		});

		$("#nowoption").show();
		$("#delayoption").hide();
		$("#dateoption").hide();
		$("#import").hide();
		$("#manually").hide();

		$("input[name='senddateoption']").on("click", function() {
			if ($(this).val() == 'option1') {
				$("#nowoption").show();
				$("#dateoption").hide();
				$("#delayoption").hide();
			}
			if ($(this).val() == 'option2') {
				$("#nowoption").hide();
				$("#dateoption").show();
				$("#delayoption").hide();
			}
			if ($(this).val() == 'option3') {
				$("#nowoption").hide();
				$("#dateoption").hide();
				$("#delayoption").show();
			}
		});

		function refresh_recipient_input(element) {
			if (element.val() == 'sendoption1') {
				$("#person").show();
				$("#import").hide();
				$("#manually").hide();
			}
			if (element.val() == 'sendoption3') {
				$("#person").hide();
				$("#import").hide();
				$("#manually").show();
			}
			if (element.val() == 'sendoption4') {
				$("#person").hide();
				$("#import").show();
				$("#manually").hide();
			}
		}

		refresh_recipient_input($("input[name='sendoption']:checked"));

		$("input[name='sendoption']").on("click", function() {
			refresh_recipient_input($(this));
		});

		$('#import_file').on('change', null, function() {
			$('#composeForm').trigger('submit');
			update_csrf_hash();
			return false;
		});

	});

	$('#canned_response').on('click', null, function() {

		var url = '<?php echo site_url('messages/canned_response/list')?>';

		$("#canned_response_container").load(url, function() {
			$(this).dialog({
				closeText: <?php echo tr_js('Close'); ?>,
				modal: true,
				draggable: true,
				width: 400,
				show: 'fade',
				hide: 'fade',
				title: <?php echo tr_js('Choose response'); ?>,
				buttons: {
					<?php echo tr_js('Save'); ?>: function() {
						save_canned_response(null);
					},
					<?php echo tr_js('Cancel'); ?>: function() {
						$(this).dialog('close');
					}
				}
			});
			$("#canned_response_container").dialog('open');
		});
		return false;
	});


	function save_canned_response(name) {

		if (name == null) var name = prompt(<?php echo tr_js('Please enter a name for your message. It should be unique.'); ?>, '', "Message Name");
		else {
			var c = confirm(<?php echo tr_js('Are you sure? This will overwrite the previous message.'); ?>);
			if (!c) return;
		}

		var dest_url = "<?php echo site_url();?>/messages/canned_response/save";

		if (name != null) {
			$('.loading_area').text(<?php echo tr_js('Saving...'); ?>);
			$('.loading_area').fadeIn("slow");
			$.post(dest_url, {
					'name': name,
					message: $('#message').val(),
					[csrf_name]: csrf_hash,
				})
				.done(function(data) {
					$('.loading_area').fadeOut("slow");
					$("#canned_response_container").dialog('close');
				})
				.fail(function(data) {
					display_error_container(data);
				})
				.always(function(data) {
					update_csrf_hash();
				});
		}
	}

	function insert_canned_response(name) {

		var dest_url = "<?php echo site_url();?>/messages/canned_response/get";
		$.post(dest_url, {
				'name': name,
				[csrf_name]: csrf_hash,
			})
			.done(function(data) {
				$('#message').val(data);
				$("#canned_response_container").dialog('close');
			})
			.fail(function(data) {
				display_error_container(data);
			})
			.always(function(data) {
				update_csrf_hash();
			});
	}

	function delete_canned_response(name) {

		var c = confirm(<?php echo tr_js('Are you sure?'); ?>);
		if (!c) return;
		var dest_url = "<?php echo site_url();?>/messages/canned_response/delete";
		$.post(dest_url, {
				'name': name,
				[csrf_name]: csrf_hash,
			})
			.done(function(data) {
				update_canned_responses();
			})
			.fail(function(data) {
				display_error_container(data);
			})
			.always(function(data) {
				update_csrf_hash();
			});
	}

	function update_canned_responses() {
		var dest_url = "<?php echo site_url();?>/messages/canned_response/list";
		$.get(dest_url, function(data) {
			$("#canned_response_container").html(data)
		});
	}

	$("input[name='smstype']").on("click", function() {
		if ($(this).val() == 'normal') {
			$("#url-display").hide();
		}
		if ($(this).val() == 'flash') {
			$("#url-display").hide();
		}
		if ($(this).val() == 'waplink') {
			$("#url-display").show();
		}
	});

</script>
