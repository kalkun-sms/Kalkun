<script type="text/javascript">
$(document).ready(function(){
	var sms_char;
	var img_path = '<?php echo  $this->config->item('img_path');?>';
	$(".datepicker").datepicker({
		minDate: 0, maxDate: '+1Y',
		dateFormat: 'yy-mm-dd', showOn: 'button', buttonImage: img_path + 'calendar.gif', buttonImageOnly: true
	});

	//$(".word_count").text("");
	//$("input#sms_loop").attr("disabled", true);
	
	$("#personvalue").tokenInput("<?php echo site_url('phonebook/get_phonebook');?>", {
		hintText:"<?php echo lang('tni_name_search')?>",
		noResultsText:"No results",
		searchingText: "<?php echo lang('tni_compose_searching'); ?>...",	
		method: "POST",
            classes: {
                tokenList: "token-input-list-facebook",
                token: "token-input-token-facebook",
                tokenDelete: "token-input-delete-token-facebook",
                selectedToken: "token-input-selected-token-facebook",
                highlightedToken: "token-input-highlighted-token-facebook",
                dropdown: "token-input-dropdown-facebook",
                dropdownItem: "token-input-dropdown-item-facebook",
                dropdownItem2: "token-input-dropdown-item2-facebook",
                selectedDropdownItem: "token-input-selected-dropdown-item-facebook",
                inputToken: "token-input-input-token-facebook"
            }

        });
		
	// validation
	$("#composeForm").validate({
		rules: {
			personvalue: {
				required: "#sendoption1:checked",
				minlength: 1
			},
			manualvalue: {
				required: "#sendoption3:checked"
			},
			groupvalue: {
				required: "#sendoption2:checked"
			},
			message: {
				required: true
			},
			datevalue: {
				required: "#option2:checked"	
			}
		},
		messages: {
			personvalue: "<?php echo lang('tni_compose_enter_dest'); ?>",
			manualvalue: "<?php echo lang('tni_compose_enter_dest'); ?>",
			groupvalue: "<?php echo lang('tni_compose_enter_dest'); ?>",
			message: "<?php echo lang('tni_compose_enter_msg'); ?>",
			datevalue: "<?php echo lang('tni_compose_enter_sendate'); ?>"
		}
	});
	
	/*
	var url = "<?php echo  site_url();?>/messages/get_phonebook";
	$("#input_box").tokenInput(url, {
		hintText: "Type in the names of your phonebook",
		noResultsText: "No results",
		searchingText: "Searching..."
	});
	*/
	
	// Phonebook autocompleter
	/*var url = "<?php echo  site_url();?>/messages/get_phonebook";
	$("#input_box").autocomplete(url, {
		multiple: true,
		multipleSeparator: ", \n",
		matchContains: true,
		});
	*/

	// Default value
	sms_char = 160;
		
	// Unicode 
	$("input#unicode").click(function(){
		var n = $("input#unicode:checked").length;
		if(n == 0) { // not checked
			sms_char = 160;
		}
		else { // checked	
			sms_char = 70;
		}
	});

	// if ads is active
	<?php 
	if($this->config->item('sms_advertise'))
	{
		$ads_count = strlen($this->config->item('sms_advertise_message'));
		echo "sms_char = sms_char - ".$ads_count.";";
	}
	
	// if append @username is active
	if($this->config->item('append_username'))
	{
		$append_username_message = $this->config->item('append_username_message');
		$append_username_message = str_replace("@username", "@".$this->session->userdata('username'), $append_username_message);
		
		$append_username_count = strlen($append_username_message);
		echo "sms_char = sms_char - ".$append_username_count.";";		
	}
	?>
		
	// Character counter
	$('.word_count').each(function(){   
	var length = $(this).val().length;  
	var message = Math.ceil(length/sms_char);
	$(this).parent().find('.counter').html( length + ' characters / ' + message + ' message(s)');  
		$(this).keyup(function(){  
			var new_length = $(this).val().length;  
			var message = Math.ceil(new_length/sms_char);
			 $(this).parent().find('.counter').html( new_length + ' characters / ' + message + ' message(s)');  
		});  
	});

	
	$("#nowoption").show();
	$("#delayoption").hide();
	$("#dateoption").hide();
	$("#group").hide();
	$("#manually").hide();

	$("input[name='senddateoption']").click(function() {
		if($(this).val()=='option1')  { $("#nowoption").show(); $("#dateoption").hide(); $("#delayoption").hide(); }
		if($(this).val()=='option2')  { $("#nowoption").hide(); $("#dateoption").show(); $("#delayoption").hide(); }
		if($(this).val()=='option3')  { $("#nowoption").hide(); $("#dateoption").hide(); $("#delayoption").show(); }	
	});

	$("input[name='sendoption']").click(function() {
		if($(this).val()=='sendoption1')  { $("#person").show(); $("#group").hide(); $("#manually").hide();}
		if($(this).val()=='sendoption2')  { $("#group").show(); $("#person").hide(); $("#manually").hide();}
		if($(this).val()=='sendoption3')  { $("#group").hide(); $("#person").hide(); $("#manually").show();}		
	});	

});
</script>
