<script language="javascript" src="<?php echo $this->config->item('js_path');?>jquery-plugin/jquery.validate.min.js"></script>
<script type="text/javascript">
	$(document).ready(function() {

		var img_path = '<?php echo  $this->config->item('img_path');?>';
		$(".datepicker").datepicker({
			minDate: 0,
			maxDate: '+1Y',
			dateFormat: 'yy-mm-dd',
			showOn: 'button',
			buttonImage: img_path + 'calendar.gif',
			buttonImageOnly: true
		});

		// validation
		$("#addUser").validate({
			rules: {
				realname: {
					required: true
				},
				username: {
					required: true,
					maxlength: 12
				},
				phone_number: {
					required: true,
					remote: {
						url: "<?php echo site_url('kalkun/phone_number_validation'); ?>",
						type: "get",
						data: {
							phone: function() {
								return $("#phone_number").val();
							}
						},
						async: false, // Workaround so that form Submit on unchanged Edit doesn't submit on validation error.
					}
				},
				password: {
					required: true,
					minlength: 6
				},
				confirm_password: {
					equalTo: "#password"
				},
				package_start: {
					required: true
				},
				package_end: {
					required: true
				}
			},
			messages: {
				realname: {
					required: <?php echo tr_js('Field required.'); ?>
				},
				username: {
					required: <?php echo tr_js('Field required.'); ?>,
					maxlength: <?php echo tr_js('Value is too long.'); ?>
				},
				phone_number: {
					required: <?php echo tr_js('Field required.'); ?>,
				},
				password: {
					required: <?php echo tr_js('Field required.'); ?>,
					minlength: <?php echo tr_js('Value is too short.'); ?>
				},
				confirm_password: {
					equalTo: <?php echo tr_js('Passwords do not match.'); ?>
				},
				package_start: {
					required: <?php echo tr_js('Field required.'); ?>
				},
				package_end: {
					required: <?php echo tr_js('Field required.'); ?>
				},
			}
		});

	});

</script>

<script language="javascript">
	$(document).ready(function() {

		// Add User
		$('.addpbkcontact').on('click', null, function() {

			$("#users_container").dialog({
				title: <?php echo tr_js('Add user'); ?>,
				closeText: <?php echo tr_js('Close'); ?>,
				maxHeight: 400,
				modal: true,
				show: 'fade',
				hide: 'fade',
				buttons: {
					<?php echo tr_js('Save'); ?>: function() {
						if ($("#addUser").valid()) {
							$("form#addUser").trigger('submit')
						}
					},
					<?php echo tr_js('Cancel'); ?>: function() {
						$(this).dialog('destroy');
					}
				}
			});

			$("#users_container").dialog('open');
			return false;
		});

		// Edit User
		$('.edit_user').on('click', null, function() {

			var id_user = $(this).parents('tr').attr('id');
			var id_package = $(this).parents('div:eq(1)').find('span.id_package').text();
			var package_start = $(this).parents('div:eq(1)').find('span.package_start').text();
			var package_end = $(this).parents('div:eq(1)').find('span.package_end').text();
			$('#id_user').val(id_user);
			$('#edit_id_package').val(id_package);
			$('#edit_package_start').val(package_start);
			$('#edit_package_end').val(package_end);

			$("#edit_users_container").dialog({
				title: <?php echo tr_js('Edit user package'); ?>,
				closeText: <?php echo tr_js('Close'); ?>,
				modal: true,
				show: 'fade',
				hide: 'fade',
				buttons: {
					<?php echo tr_js('Save'); ?>: function() {
						$("form#editUser").trigger('submit')
					},
					<?php echo tr_js('Cancel'); ?>: function() {
						$(this).dialog('destroy');
					}
				}
			});

			$("#edit_users_container").dialog('open');
			return false;
		});

		// Delete
		$("a.delete_user").on('click', function() {
			var element = this;

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
						$.post("<?php echo site_url(); ?>/plugin/sms_credit/delete_users", {
								id: $(element).parents("tr:first").attr("id"),
								[csrf_name]: csrf_hash,
							})
							.done(function(data) {
								$(element).parents("tr:first").slideUp("slow");
								show_notification(<?php echo tr_js('Item deleted.'); ?>, "info");
							})
							.fail(function(data) {
								display_error_container(data);
							})
							.always(function(data) {
								update_csrf_hash();
							});
						$(this).dialog('close');
					}
				}
			});
			$('#confirm_delete_user_dialog').dialog('open');
		});

		// Search onBlur onFocus
		if ($('input.search_name').val() == '') {
			$('input.search_name').val(<?php echo tr_js('Search'); ?>);
		}

		$('input.search_name').on("blur", function() {
			$(this).val(<?php echo tr_js('Search'); ?>);
		});

		$('input.search_name').on("focus", function() {
			$(this).val('');
		});

	});

</script>
