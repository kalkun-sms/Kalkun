<script language="javascript" src="<?php echo $this->config->item('js_path');?>jquery-plugin/jquery.validate.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){

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
</script>
