<script type="text/javascript">
$(document).ready(function(){

	var img_path = '<?php echo  $this->config->item('img_path');?>';
	$(".datepicker").datepicker({
		minDate: 0, maxDate: '+1Y',
		dateFormat: 'yy-mm-dd', showOn: 'button', buttonImage: img_path + 'calendar.gif', buttonImageOnly: true
	});

	//$(".word_count").text("");
	//$("input#sms_loop").attr("disabled", true);
	
	$("#personvalue").tokenInput("<?php echo site_url('messages/getphonebook');?>", {
		hintText:"Type name from your contacts",
		noResultsText:"No results",
		searchingText: "Searching...",	
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
			personvalue: "Please enter your destination",
			manualvalue: "Please enter your destination",
			groupvalue: "Please enter your destination",
			message: "Please enter your message",
			datevalue: "Please enter sending date"
		}
	});
	
	/*
	var url = "<?php echo  site_url();?>/messages/getphonebook";
	$("#input_box").tokenInput(url, {
		hintText: "Type in the names of your phonebook",
		noResultsText: "No results",
		searchingText: "Searching..."
	});
	*/
	
	// Phonebook autocompleter
	/*var url = "<?php echo  site_url();?>/messages/getphonebook";
	$("#input_box").autocomplete(url, {
		multiple: true,
		multipleSeparator: ", \n",
		matchContains: true,
		});
	*/
		
		
	// Character counter	
	$('.word_count').each(function(){   
	var length = $(this).val().length;  
	var message = Math.ceil(length/160);
	$(this).parent().find('.counter').html( length + ' characters / ' + message + ' message(s)');  
		$(this).keyup(function(){  
			var new_length = $(this).val().length;  
			var message = Math.ceil(new_length/160);
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
