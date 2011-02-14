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
				required: true
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
				required: "Enter a name"	
			},
			password: {
				required: "Enter user password",
				minlength: "Too short"
			},
			confirm_password: { 
				equalTo: "Passwords don't match"
			}
		}
	});

});
</script>
