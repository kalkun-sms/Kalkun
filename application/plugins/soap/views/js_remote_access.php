<?php
/**
 *	@Author: bullshit "oskar@biglan.at"
 *	@Copyright: bullshit, 2010
 *	@License: GNU General Public License
*/
?>
<script type="text/javascript">
	$(document).ready(function() {

		// validation
		$(".addremoteaccessform").validate({
			rules: {
				access_name: {
					required: true
				},
				ip_address: {
					required: true
				},
			},
			messages: {
				access_name: <?php echo tr_js('Field required.'); ?>,
				ip_address: <?php echo tr_js('Field required.'); ?>,
			}
		});

		$(".addnotificationform").validate({
			rules: {
				notifynumber: {
					required: true,
					number: true,
				},
				notifyvalue: {
					required: true,
					number: true,
				},
			},
			messages: {
				notifynumber: {
					required: <?php echo tr_js('Field required.'); ?>,
					number: <?php echo tr_js('Value must be a number.'); ?>,
				},
				notifyvalue: {
					required: <?php echo tr_js('Field required.'); ?>,
					number: <?php echo tr_js('Value must be a number.'); ?>,
				},
			}
		});

		// background
		$("tr:odd").addClass('hover_color');

		// Add remote access dialog
		$("#remoteaccess-dialog").dialog({
			closeText: <?php echo tr_js('Close'); ?>,
			bgiframe: true,
			autoOpen: false,
			modal: true,
			buttons: {
				<?php echo tr_js('Save'); ?>: function() {
					$("form.addremoteaccessform").trigger('submit');
				},
				<?php echo tr_js('Cancel'); ?>: function() {
					$(this).dialog('close');
				}
			}
		});

		$("#notification-dialog").dialog({
			closeText: <?php echo tr_js('Close'); ?>,
			bgiframe: true,
			autoOpen: false,
			modal: true,
			buttons: {
				<?php echo tr_js('Save'); ?>: function() {
					$("form.addnotificationform").trigger('submit');
				},
				<?php echo tr_js('Cancel'); ?>: function() {
					$(this).dialog('close');
				}
			}
		});

		// Add remote acces button	
		$('#addremotebutton').on("click", function() {
			$('#remoteaccess-dialog').dialog('open');
		});

		$('#addnotificationbutton').on("click", function() {
			$('#notification-dialog').dialog('open');
		});

		// Edit remote access dialog
		$("#editremoteaccess-dialog").dialog({
			closeText: <?php echo tr_js('Close'); ?>,
			bgiframe: true,
			autoOpen: false,
			modal: true,
			buttons: {
				<?php echo tr_js('Save'); ?>: function() {
					$("form.editremoteaccessform").trigger('submit');
				},
				<?php echo tr_js('Cancel'); ?>: function() {
					$(this).dialog('close');
				}
			}
		});

		// Edit blacklist - get data
		$('a.edit').on("click", function() {
			var editid_remote_access = $(this).parents("tr:first").attr("id");
			$("#editid_remote_access").val(editid_remote_access);
			var editaccess_name = $(this).parents("tr:first").children("td.access_name").text();
			$("#editaccess_name").val(editaccess_name);
			var editip_address = $(this).parents("tr:first").children("td.ip_address").text();
			$("#editip_address").val(editip_address);
			var edittoken = $(this).parents("tr:first").children("td.token").text();
			$("#edittoken").val(edittoken);

			// FIXME
			//var editstatus = $('#statusBox').prop('checked');
			var editstatus = $(this).parents("tr:first").children("td.status").children("input.statusbox").prop('checked');
			$("#editstatus").prop('checked', editstatus);

			$('#editremoteaccess-dialog').dialog('open');
		});

		// Delete remote access
		$("a.delete").on('click', function() {
			var element = this;
			$.post("<?php echo site_url(); ?>/plugin/soap/delete_remote_access", {
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
		});
	});

</script>
