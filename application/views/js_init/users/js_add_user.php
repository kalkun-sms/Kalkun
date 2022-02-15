<script language="javascript" src="<?php echo $this->config->item('js_path');?>jquery-plugin/jquery.validate.min.js"></script>
<script language="javascript" src="<?php echo $this->config->item('js_path');?>libphonenumber-1.9.49-max.js"></script>
<script language="javascript" src="<?php echo $this->config->item('js_path');?>jquery-plugin/jquery.validate.phone.js"></script>
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
					phone: '<?php echo $this->Kalkun_model->get_setting()->row('country_code'); ?>',
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
					required: "<?php echo lang('tni_error_enter_name');?>"
				},
				password: {
					required: "<?php echo lang('tni_error_enter_password');?>",
					minlength: "<?php echo lang('tni_error_toshort');?>"
				},
				confirm_password: {
					equalTo: "<?php echo lang('tni_error_password_nomatch');?>"
				}
			}
		});

	});

	function toggle_allow_invalid(element) {
		if ($(element)[0].classList.contains("allow_invalid")) {
			$(element).removeClass("allow_invalid");
		} else {
			$(element).addClass("allow_invalid");
		}
	}
</script>
