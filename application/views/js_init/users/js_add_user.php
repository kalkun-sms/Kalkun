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
					required: true
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
				password: {
					required: "<?php echo tr('Field required.');?>",
					minlength: "<?php echo tr('Password is too short.');?>"
				},
				confirm_password: {
					equalTo: "<?php echo tr('Passwords do not match.');?>"
				}
			}
		});

	});

</script>
