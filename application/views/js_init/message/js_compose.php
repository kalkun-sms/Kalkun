<script type="text/javascript">
$(document).ready(function(){
	var sms_char;
	var img_path = '<?php echo  $this->config->item('img_path');?>';
	$(".datepicker").datepicker({
		minDate: 0, maxDate: '+1Y',
		dateFormat: 'yy-mm-dd', showOn: 'button', buttonImage: img_path + 'calendar.gif', buttonImageOnly: true
	});
	
	$("#message").autogrow();

	//$(".word_count").text("");
	//$("input#sms_loop").attr("disabled", true);
	
	$("#personvalue").tokenInput("<?php echo site_url('phonebook/get_phonebook/').'/'.(isset($source)?$source:'');?>", {
		hintText:"<?php echo lang('tni_name_search')?>",
		noResultsText:"No results",
		searchingText: "<?php echo lang('tni_compose_searching'); ?>...",
		preventDuplicates: true,	
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

	// Import CSV
	$('#composeForm').ajaxForm({
		dataType:  'json',
	    success: function(data) {
            var limit = data.Field.length;
            for (var i=0; i < limit; i++) {
            	var element = $('#import_value_count').clone().attr('id', 'import_value_' + i).attr('name', data.Field[i]);
            	$('#import_value_count').after(element);
            	$('#import_value_' + i).val(data[data.Field[i]]);
            	
            	var button = $('#field_button').clone().attr('id', 'field_button_' + i).attr('value', data.Field[i]).removeClass('hidden');
            	$('#field_button').after(button).after(' ');
            }
            $('#import_value_count').val(limit);
            $('#field_option').show();          
            
			// Field button
			$('.field_button').click(function() {
				var field = $(this).val();
				var text = $('#message').val();
				$('#message').val(text + '[[' + field + ']]');
			});
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
			import_file: {
				required: "#sendoption4:checked,#import_value:filled"
			},
			message: {
				required: true
			},
			datevalue: {
				required: "#option2:checked"	
			},
            url :  {
                required: "#stype3:checked",
                url: true
            }
		},
		messages: {
			personvalue: "<?php echo lang('tni_compose_enter_dest'); ?>",
			manualvalue: "<?php echo lang('tni_compose_enter_dest'); ?>",
			import_file: "<?php echo lang('tni_compose_enter_dest'); ?>",
			message: "<?php echo lang('tni_compose_enter_msg'); ?>",
			datevalue: "<?php echo lang('tni_compose_enter_sendate'); ?>",
            url : "<?php echo lang('kalkun_compose_valid_url');?>"
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
    if($("input#unicode:checked").length == 1)
    {
	    sms_char = 70;
	}
    else
    {
        sms_char = 160;
    }

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
		$append_username_message = "\n".str_replace("@username", "@".$this->session->userdata('username'), $append_username_message);
		
		$append_username_count = strlen($append_username_message);
		echo "sms_char = sms_char - ".$append_username_count.";";		
	}
	?>
		
	// Character counter
	$('.word_count').each(function(){   
	text_character = "<?php echo lang('kalkun_compose_counter_character');?>";
	text_message = "<?php echo lang('kalkun_compose_counter_message');?>";
	var length = $(this).val().length;  
	var message = Math.ceil(length/sms_char);
	$(this).parent().find('.counter').html( length + ' ' + text_character + ' / ' + message + ' ' + text_message);  
		$(this).keyup(function(){ 
			var new_length = $(this).val().length;
            var str=$(this).val();
            var n=str.match(/\^|\{|\}|\\|\[|\]|\~|\||\€/g);
            n = (n) ? n.length : 0;
            new_length = new_length + n;
			var message = Math.ceil(new_length/sms_char);
			 $(this).parent().find('.counter').html( new_length + ' ' + text_character + ' / ' + message + ' ' + text_message);  
		});  
	});

	$("#nowoption").show();
	$("#delayoption").hide();
	$("#dateoption").hide();
	$("#import").hide();
	$("#manually").hide();

	$("input[name='senddateoption']").click(function() {
		if($(this).val()=='option1')  { $("#nowoption").show(); $("#dateoption").hide(); $("#delayoption").hide(); }
		if($(this).val()=='option2')  { $("#nowoption").hide(); $("#dateoption").show(); $("#delayoption").hide(); }
		if($(this).val()=='option3')  { $("#nowoption").hide(); $("#dateoption").hide(); $("#delayoption").show(); }	
	});

	$("input[name='sendoption']").click(function() {
		if($(this).val()=='sendoption1')  { $("#person").show(); $("#import").hide(); $("#manually").hide();}
		if($(this).val()=='sendoption3')  { $("#person").hide(); $("#import").hide(); $("#manually").show();}
		if($(this).val()=='sendoption4')  { $("#person").hide(); $("#import").show(); $("#manually").hide();}		
	});
	
	$('#import_file').bind('change', function() {
		$('#composeForm').submit();
        return false;
	});    

});

$('#canned_response').bind('click', function() {

var url = '<?php echo site_url('messages/canned_response/list')?>';

$("#canned_response_container").load(url,  function() {
   $(this).dialog({
    modal: true,
    draggable : true,     
	width: 400,
	show: 'fade',
	hide: 'fade',
    title: '<?php echo lang('kalkun_canned_title')?>',
  	buttons: {
	'<?php echo lang('kalkun_canned_save_new');?>': function() {
		save_canned_response(null);
	},
	"<?php echo lang('kalkun_cancel');?>": function() { $(this).dialog('close');}
    }
  });
});
$("#canned_response_container").dialog('open');
return false;
});    
	
 
function save_canned_response(name)
{
    
    if(name == null)     var name = prompt("Please enter a Name for Your Message. This should be unique.",'',"Message Name");
    else{  
        var c = confirm("Are you Sure?  This will overwrite previous message"); 
        if (!c ) return;
    }
    
    var dest_url = "<?php echo  site_url();?>/messages/canned_response/save";
    
    if(name != null){
        $('.loading_area').html("<?php echo lang('kalkun_canned_saving');?>");
       	$('.loading_area').fadeIn("slow");
        $.post(dest_url, {'name': name, message: $('#message').val()}, function() {
                $('.loading_area').fadeOut("slow");
                $("#canned_response_container").dialog('close');
    	});
    }
}

function insert_canned_response(name)
{

    var dest_url = "<?php echo  site_url();?>/messages/canned_response/get";
    $.post(dest_url, {'name': name}, function(data) {
			$('#message').val(data);
            $("#canned_response_container").dialog('close');
	});
}

function delete_canned_response(name)
{

    var c = confirm("Are you Sure?"); 
    if (!c ) return;
    var dest_url = "<?php echo  site_url();?>/messages/canned_response/delete";
    $.post(dest_url, {'name': name}, function() {
			update_canned_responses();
	});
}

function update_canned_responses()
{
    var dest_url = "<?php echo  site_url();?>/messages/canned_response/list";
    $.get(dest_url,  function(data) {    $("#canned_response_container").html(data)	});
}

//tab send message
var is_tab = false;
$(document).ready(function() {
    $('#message').bind('keydown', 'tab', function(){
        //$('.ui-dialog-buttonpane button:eq(0)').focus(); 
        is_tab = true; 
        setTimeout(function(){is_tab = false;}, "5000");
    });
    $("#composeForm").bind('keydown', 'return', function(){
      if(is_tab == true)   
      { 
        if($("#composeForm").valid()) {
            $('.ui-dialog-buttonpane :button').each(function(){ if($(this).text() == '<?php echo lang('tni_send_message'); ?>') $(this).html('<?php echo lang('tni_sending_message'); ?> <img src="<?php echo $this->config->item('img_path').'processing.gif' ?>" height="12" style="margin:0px; padding:0px;">');   });
    		$.post("<?php echo site_url('messages/compose_process') ?>", $("#composeForm").serialize(), function(data) {
    		$("#compose_sms_container").html(data);
    		$("#compose_sms_container" ).dialog( "option", "buttons", { "Okay": function() { $(this).dialog("destroy"); } } );
    		setTimeout(function() {$("#compose_sms_container").dialog('destroy')} , 1500);
    	   }); 
        }
      }
      return false;
    });
});

$("input[name='smstype']").click(function() {
		if($(this).val()=='normal')  { $("#url-display").hide(); }
		if($(this).val()=='flash')  { $("#url-display").hide(); }
		if($(this).val()=='waplink')  { $("#url-display").show(); }	
});
</script>
