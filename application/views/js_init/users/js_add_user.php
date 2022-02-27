<script language="javascript" src="<?php echo $this->config->item('js_path');?>jquery-plugin/jquery.validate.min.js"></script>
<script type="text/javascript">
	$(document).ready(function() {

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
						type: "post",
						data: {
							phone: function() {
								return $("#phone_number").val();
							}
						}
					}
				},
				password: {
					required: true,
					minlength: 6
				},
				confirm_password: {
					equalTo: "#password"
				}
			},
			messages: {
				realname: {
					required: "<?php echo tr('Field required.');?>"
				},
				username: {
					required: "<?php echo tr('Field required.');?>",
					maxlength: "<?php echo tr('Value is too long.');?>"
				},
				phone_number: {
					required: "<?php echo tr('Field required.');?>",
				},
				password: {
					required: "<?php echo tr('Field required.');?>",
					minlength: "<?php echo tr('Value is too short.');?>"
				},
				confirm_password: {
					equalTo: "<?php echo tr('Passwords do not match.');?>"
				}
			}
		});

	});

</script>
