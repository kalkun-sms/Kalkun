<script type="text/javascript">
	$(document).ready(function() {

		// Compose SMS
		$('#send_member').on('click', null, function() {
			var member = '<?php echo $total_member;?>';
			if (member == 0) {
				show_notification("No member registered");
			} else {
				compose_message('member', false, '#message');
				return false;
			}
		});
	});

</script>
