<script type="text/javascript">
$(document).ready(function() {	    
var base = "<?php echo  site_url();?>/messages/delete_messages/";
var source = "<?php echo $this->uri->segment(4);?>";
var current_folder = "<?php echo $this->uri->segment(6);?>";
//var dest_url = base + source;
     
<?php if($this->config->item('enable_emoticons')) : ?> 
$(".message_preview").emoticons("<?php echo   $this->config->item('img_path').'emoticons/'; ?>");
$(".message_content").emoticons("<?php echo   $this->config->item('img_path').'emoticons/'; ?>");        
<?php endif; ?>

// Delete messages
$("a.global_delete").live('click', action_delete = function(){
var count = $("input:checkbox:checked").length;
if(count==0) { 
	$('.notification_area').text("<?php echo lang('tni_msg_no_conv_selected'); ?>");
	$('.notification_area').show();
}
else {
    var notif = count + ' messages deleted';
	$("input.select_message:checked").each( function () {
	   var message_row = $(this).parents('div:eq(2)');
         id_access = '#item_source'+$(this).val();
         item_folder =  $(id_access).val();
         dest_url = base + item_folder;
        $.ajaxSetup({async: false});
		$.post(dest_url, {type: 'single', id: $(this).val(), current_folder: current_folder}, function(data) {
            if (!data) {
                $(message_row).slideUp("slow");
            }
            else {
                notif = data;
            }
		});
	});
	show_notification(notif); // translate
}
});
/**
	 * Recover conversation
	 *
	 * Recover all messages on selected conversation
	 *
	 */	
	$("a.recover_button").live('click', action_recover = function()
	{
		var count = $("input.select_message:checkbox:checked:visible").length;
		if(count==0) 
		{ 
			show_notification("<?php echo lang('tni_msg_no_conv_selected')?>");
		}
		else 
		{
 
            var id_folder = ( source == 'inbox' ) ?  1 : 3;	
 
			$("input.select_message:checked:visible").each(function () {
				var message_row = $(this).parents('div:eq(2)');
                	$.post("<?php echo  site_url('messages/move_message') ?>", {type: 'single', current_folder: current_folder, folder: source, 
					id_folder: id_folder, id_message: $(this).val()}, function() {
					$(message_row).slideUp("slow");
				});
			});
			show_notification(count + ' conversation recovered'); // translate
		}
});
    
// Move messages
$(".move_to").live('click', function() {
var count = $("input:checkbox:checked").length;
if(count==0) { 
	$("#movetodialog").dialog('close');
	show_notification("<?php echo lang('tni_msg_no_conv_selected'); ?>");
}
else {    	
var id_folder = $(this).attr('id');
$("#movetodialog").dialog('close');
$("input.select_message:checked").each(function () {
	var message_row = $(this).parents('div:eq(2)');
    id_access = '#item_source'+$(this).val();
    item_folder =  $(id_access).val();
	$.post("<?php echo  site_url('messages/move_message') ?>", {type: 'single', current_folder: current_folder, folder: item_folder, 
		id_folder: id_folder, id_message: $(this).val()}, function() {
		$(message_row).slideUp("slow");
        show_notification("Messages Moved")
	});
});		
}
});    
    
$(".move_to_button").live('click', message_move = function() {
	$('#movetodialog').dialog('open');
	return false;
});

// Move To dialog
$("#movetodialog").dialog({
	bgiframe: true,
	autoOpen: false,
	modal: true,
});        
    

// message detail
$("span.message_toggle").live('click', function(){
var row = $(this).parents('div:eq(1)');
$(row).find("div.message_content").toggle();
$(row).find("span.message_preview").toggle();
$(row).find("div.optionmenu").toggle();
	
if($(row).find("div.detail_area").is(":visible"))
{ 
	$(row).find("div.detail_area").toggle(); 
	$(row).find("a.detail_button").html('<?php echo lang('tni_show_details'); ?>'); 
}
return false;
});
    
    
// select all
$("a.select_all_button").live('click', select_all = function(){
	$(".select_message").attr('checked', true);
	$(".messagelist").addClass("messagelist_hover");
	return false;
});

// clear all
$("a.clear_all_button").live('click', clear_all =  function(){
	$(".select_message").attr('checked', false);
	$(".messagelist").removeClass("messagelist_hover");
	return false;
});        

// input checkbox
$("input.select_message").live('click',function()
{
	if($(this).attr('checked')==true) 
	{
		$(this).parents('div:eq(2)').addClass("messagelist_hover");
        current_number = $(this).val();
	}
	else 
	{
		$(this).parents('div:eq(2)').removeClass("messagelist_hover");
        current_number = '';
	}
});
    
<?php if(!is_ajax()) : ?>
// refresh
$("a.refresh_button").live('click', refresh = function(type){
	if(type != 'retry') {
            $('.loading_area').html('Loading...');
            $('.loading_area').fadeIn("slow");
    }
	$('#message_holder').load("<?php echo  site_url('messages/conversation/'.$this->uri->segment(3).'/'.$this->uri->segment(4).'/'.preg_replace ('/ /', '%20' ,$this->uri->segment(5)).'/'.$this->uri->segment(6,0)) ?>", function(response, status, xhr) { 
       if (status == "error" || xhr.status != 200 )  
       {
            $('.loading_area').html('<nobr>Oops Network Error. <span id="retry-progress-display"> Retrying in <span id="countdown-count">10</span> Seconds.</span></nobr>');
            var cntdwn = setInterval(function() {
                  current_val = $('#countdown-count').html();
                  if(current_val > 1)   $('#countdown-count').html(current_val  - 1 )	;
                  else    {  clearInterval(cntdwn); $('#retry-progress-display').html('Retrying Now...') }                    
             } , 1000);	
             setTimeout(function() {refresh('retry');	} , 10000);	
             return false;
        }
        new_notification('false');
        $('.loading_area').fadeOut("slow");
    });
});    

<?php endif; ?> 
    
// Reply SMS
$('a.reply_button, a.forward_button').live('click', message_reply =  function() {
var button = $(this).attr('class');
var url = '<?php echo site_url('messages/compose')?>';

if(button=='forward_button')
{
	var type = 'forward';
	var header = $(this).parents('div:eq(1)');
	var param1 = header.attr('class').split(' ').slice(-1)['0']; /* source */
	var param2 = header.children().children('input.select_message').attr('id'); /* message_id */
}
else
{
	var type = 'reply';
	var param1 = '<?php echo $this->uri->segment(5);?>';
    if(param1 == null || param1 == '')  
        var param1 = $(this).parents('div:eq(1)').children().children('input.item_number').val();  /* phone number */
         
}
$("#compose_sms_container").html("<div align=\"center\"> Loading...</div>");		
$("#compose_sms_container").load(url, { 'type': type, 'param1': param1, 'param2': param2}, function() {
  $(this).dialog({
    modal: true,
    draggable : true,    
    open: function(event, ui) {$("#message").focus();}, 
	width: 550,
	show: 'fade',
	hide: 'fade',
    buttons: {
	'<?php echo lang('tni_send_message'); ?>': function() {
		if($("#composeForm").valid()) {
		  $('.ui-dialog-buttonpane :button').each(function(){ if($(this).text() == '<?php echo lang('tni_send_message'); ?>') $(this).html('<?php echo lang('tni_sending_message'); ?> <img src="<?php echo $this->config->item('img_path').'processing.gif' ?>" height="12" style="margin:0px; padding:0px;">');                    });
		$.post("<?php echo site_url('messages/compose_process') ?>", $("#composeForm").serialize(), function(data) {
			$("#compose_sms_container").html(data);
		    $("#compose_sms_container" ).dialog( "option", "buttons", { "Okay": function() { $(this).dialog("destroy"); } } );
			setTimeout(function() {$("#compose_sms_container").dialog('destroy')} , 1500);
		});
		}
	},
	'<?php echo lang('kalkun_cancel'); ?>': function() { $(this).dialog('destroy');}
    }
  });
});
$("#compose_sms_container").dialog('open');
return false;
});    
	
    
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
   		
// Show/hide detail
$('a.detail_button').live('click', function() {
var row = $(this).parents('div:eq(2)');
$(row).find("div.detail_area").toggle();
	
if($(this).text()=='<?php echo lang('tni_hide_details'); ?>') $(this).html('<?php echo lang('tni_show_details'); ?>');
else $(this).html('<?php echo lang('tni_hide_details'); ?>');
return false;		
});	

// Add contact
$('.add_to_pbk').live('click', function() {
var param1 = $(this).parents('div:eq(1)').children().children('input.item_number').val();  /* phone number */
$("#contact_container").load('<?php echo site_url('phonebook/add_contact')?>', { 'type': 'message', 'param1': param1}, function() {
$(this).dialog({
	title: '<?php echo lang('tni_contact_add');?>',
	modal: true,
	show: 'fade',
	hide: 'fade',
	buttons: {
	'Save': function() {
		//if($("#addContact").valid()) {
		$.post("<?php echo site_url('phonebook/add_contact_process') ?>", $("#addContact").serialize(), function(data) {
		$("#contact_container").html(data);
		$("#contact_container").dialog({ buttons: { "Okay": function() { $(this).dialog("close"); } } });
		setTimeout(function() {$("#contact_container").dialog('close')} , 1500);
	});
	}, "<?php echo lang('kalkun_cancel'); ?>": function() { $(this).dialog('close');} }
	});
});
$("#contact_container").dialog('open');
return false;
});	
    
<?php if($this->uri->segment(4)!='6' &&  $this->uri->segment(6)!='6' && !is_ajax()  ) : ?>
// report spam
$(".spam_button").live('click', function() {
    var count = $("input:checkbox:checked:visible").length;
    
    if(count==0) { 
    	show_notification("<?php echo lang('tni_msg_no_conv_selected'); ?>");
    }
    else {    	
    $("input.select_message:checked:visible").each(function () {
    	var message_row = $(this).parents('div:eq(2)');
        id_access = '#item_source'+$(this).val();
        item_folder =  $(id_access).val();
        if(item_folder != 'inbox') {show_notification("Outgoing Message cannot be spam") ;return; } 
    	$.post("<?php echo  site_url('messages/report_spam/spam') ?>", { id_message: $(this).val()}, function() {
    		$(message_row).slideUp("slow");
    	});
    });
    show_notification("Spam Reported")		
    }
});   
<?php else: ?>
 //report ham
$(".ham_button").live('click', function() {
    var count = $("input:checkbox:checked:visible").length;
    if(count==0) { 
    	show_notification("<?php echo lang('tni_msg_no_conv_selected'); ?>");
    }
    else {    	
    var id_folder = $(this).attr('id');
    $("input.select_message:checked:visible").each(function () {
    	var message_row = $(this).parents('div:eq(2)');
    	$.post("<?php echo  site_url('messages/report_spam/ham') ?>", {  id_message: $(this).val()}, function() {
    		$(message_row).slideUp("slow");
            
    	});
    });
    show_notification("Messages Marked not Spam")		
    }
});   
<?php endif; ?>

// resend
$(".resend").live('click', function() {
DestinationNumber = $(this).parents('div:eq(1)').children().children('input.item_number').val();
TextDecoded = $(this).parents('div:eq(1)').children('div.message_content').text();
ID = $(this).parents('div:eq(1)').children().children('input.select_message').attr('id');
Class = $(this).parents('div:eq(1)').children('div.message_metadata').children('span.class').text();
Coding = $(this).parents('div:eq(1)').children('div.message_metadata').children('span.coding').text();
if (Coding=='Unicode_No_Compression')
{
	Coding = 'unicode';	
}

resend_conf = '<p>You are about to resend message to <strong>' + DestinationNumber + '</strong></p>';
message_content = '<p><strong>Message content:</strong> <br />'+ TextDecoded + '</p>';
delete_dup = '<input type="checkbox" id="delete_dup" /> <label for="delete_dup">Delete copy of this message (Prevent Duplicate)</label>';
$("#compose_sms_container").html(resend_conf + message_content + delete_dup);	
$("#compose_sms_container").dialog({
	//title: 'Resend SMS',
    modal: true,
    draggable : true,
	width: 550,
	show: 'fade',
	hide: 'fade',
	buttons: {
	'Continue': function() {
		delete_dup_status = $("#delete_dup").is(":checked");
		$.post("<?php echo site_url('messages/compose_process') ?>", {sendoption: 'sendoption3', manualvalue: DestinationNumber, senddateoption: 'option1', class: Class, unicode: Coding, validity: '-1', smstype: 'normal', sms_loop: '1', message: TextDecoded}, function(data) {
				$("#compose_sms_container").html(data);
				$("#compose_sms_container").dialog({ buttons: { "Okay": function() { $(this).dialog("close"); } } });
				setTimeout(function() {$("#compose_sms_container").dialog('close')} , 1500);
			});

		// Delete copy
		if (delete_dup_status)
		{
			dest_url = base + 'sentitems';
			$.post(dest_url, {type: 'single', id: ID, current_folder: current_folder});
		}			
	}, "<?php echo lang('kalkun_cancel'); ?>": function() { $(this).dialog('close');} }
	});
$("#compose_sms_container").dialog('open');	
});

//resend_bulk
$(".resend_bulk").live('click', function() {
var count = $("input:checkbox:checked").length;
if(count==0) { 
	$('.notification_area').text("<?php echo lang('tni_msg_no_conv_selected'); ?>");
	$('.notification_area').show();
}
else {
	resend_conf = '<p>You are about to resend ' + count + ' message(s)</p>';
	delete_dup = '<input type="checkbox" id="delete_dup" /> <label for="delete_dup">Delete copy of this message (Prevent Duplicate)</label>';
	$("#compose_sms_container").html(resend_conf + delete_dup);
	$("#compose_sms_container").dialog({
		//title: 'Resend SMS',
	    modal: true,
	    draggable : true,
		width: 550,
		show: 'fade',
		hide: 'fade',
		buttons: {
		'Continue': function() {
			delete_dup_status = $("#delete_dup").is(":checked");

			$("input.select_message:checked").each(function() {
				DestinationNumber = $(this).parents('div:eq(0)').children('input.item_number').val();
				TextDecoded = $(this).parents('div:eq(1)').children('div.message_content').text();
				ID = $(this).parents('div:eq(1)').children().children('input.select_message').attr('id');
				Class = $(this).parents('div:eq(1)').children('div.message_metadata').children('span.class').text();
				Coding = $(this).parents('div:eq(1)').children('div.message_metadata').children('span.coding').text();
				if (Coding=='Unicode_No_Compression')
				{
					Coding = 'unicode';	
				}				
				$.post("<?php echo site_url('messages/compose_process') ?>", {sendoption: 'sendoption3', manualvalue: DestinationNumber, senddateoption: 'option1', class: Class, unicode: Coding, validity: '-1', smstype: 'normal', sms_loop: '1', message: TextDecoded}, function(data) {
						$("#compose_sms_container").html(data);
						$("#compose_sms_container").dialog({ buttons: { "Okay": function() { $(this).dialog("close"); } } });
						setTimeout(function() {$("#compose_sms_container").dialog('close')} , 1500);
					});
					
				// Delete copy
				if (delete_dup_status)
				{
					dest_url = base + 'sentitems';
					$.post(dest_url, {type: 'single', id: ID, current_folder: current_folder});
				}		
			});		
		}, "<?php echo lang('kalkun_cancel'); ?>": function() { $(this).dialog('close');} }
		});
	$("#compose_sms_container").dialog('open');
}	
});

});    
</script>
