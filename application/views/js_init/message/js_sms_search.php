<script type="text/javascript">
//currently not used
$(document).ready(function() {

// Search onBlur onFocus
	$('input.search_sms').val('<?php echo lang('tni_search_sms'); ?>');
	
	$('input.search_sms').blur(function(){
		$(this).val('<?php echo lang('tni_search_sms'); ?>');
	});
	
	$('input.search_sms').focus(function(){
		$(this).val('');
	});
    
});    
</script>