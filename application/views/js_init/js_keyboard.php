<script type="text/javascript">
var current_number = '';

$(document).ready(function() {

$(document).bind('keydown', 'g;i', function(){
      window.location = "<?php echo site_url('messages/folder/inbox'); ?>";
});

$(document).bind('keydown', 'g;o', function(){
   window.location = "<?php echo site_url('messages/folder/outbox'); ?>";
});
 
$(document).bind('keydown', 'g;s', function(){
    window.location = "<?php echo site_url('messages/folder/sentitems'); ?>";
    return false;
});

$(document).bind('keydown', 's', function(){
   $("#search").focus();
    return false;
});


$(document).bind('keydown', 'g;p', function(){
    window.location = "<?php echo site_url('phonebook'); ?>";
});
 
$(document).bind('keyup', 'c', function(){  compose_message();});

$(document).bind('keydown', 'shift+/', function(){
    $("#kbd").dialog({
			bgiframe: true,
			autoOpen: false,
			height: 400,
			width: 600,
			modal: true		
		});	
    $('#kbd').dialog('open');
}); 


<?php if ($this->uri->segment(1) != ''): ?>
$(document).bind('keydown', '#', function(){  action_delete(); });

<?php if ($this->uri->segment(1) != 'phonebook'): ?>
$(document).bind('keydown', 'm', function(){   message_move(); });
<?php endif; ?>

 
<?php if ($this->uri->segment(2) == 'conversation' || $this->uri->segment(2) == 'search'): ?>  

<?php if ($this->uri->segment(2) != 'search'): ?>
$(document).bind('keydown', 'r', function(){   message_reply(); });
<?php endif; ?>

// for convesation
var totalmsg = $("#message_holder > div.messagelist").length;
var current_select =0;

//move next
$(document).bind('keydown', 'j',go_next =  function(){
    $("#message_holder").children(":eq("+current_select+")").children('.message_container').children('.message_header').removeClass('infocus'); //selecting child
    current_select ++; if(current_select > totalmsg  ) current_select = 1;
    $("#message_holder").children(":eq("+current_select+")").children('.message_container').children('.message_header').addClass('infocus'); //selecting child
});

//move prev
$(document).bind('keydown', 'k', go_prev =  function(){
    $("#message_holder").children(":eq("+current_select+")").children('.message_container').children('.message_header').removeClass('infocus'); //selecting child
    current_select --;  if(current_select < 1 ) current_select = totalmsg ;
    $("#message_holder").children(":eq("+current_select+")").children('.message_container').children('.message_header').addClass('infocus'); //selecting child   
});


//p - Read previous message within a conversation.
$(document).bind('keydown', 'p', function(){
    $("#message_holder").children(":eq("+current_select+")").children('.message_container').find('div.message_content').hide();
    $("#message_holder").children(":eq("+current_select+")").children('.message_container').find('span.message_preview').hide();
    $("#message_holder").children(":eq("+current_select+")").children('.message_container').find('div.optionmenu').hide();
    go_prev();
    $("#message_holder").children(":eq("+current_select+")").children('.message_container').find('div.message_content').show();
    $("#message_holder").children(":eq("+current_select+")").children('.message_container').find('span.message_preview').show();
    $("#message_holder").children(":eq("+current_select+")").children('.message_container').find('div.optionmenu').show();
    return false;
});

//n - Read next message within a conversation.
$(document).bind('keydown', 'n', function(){  
    $("#message_holder").children(":eq("+current_select+")").children('.message_container').find('div.message_content').hide();
    $("#message_holder").children(":eq("+current_select+")").children('.message_container').find('span.message_preview').hide();
    $("#message_holder").children(":eq("+current_select+")").children('.message_container').find('div.optionmenu').hide();
    go_next();
    $("#message_holder").children(":eq("+current_select+")").children('.message_container').find('div.message_content').show();
    $("#message_holder").children(":eq("+current_select+")").children('.message_container').find('span.message_preview').show();
    $("#message_holder").children(":eq("+current_select+")").children('.message_container').find('div.optionmenu').show();
    return false;
});

//select
$(document).bind('keydown', 'o', read_message =  function(){  
    $("#message_holder").children(":eq("+current_select+")").children('.message_container').find('div.message_content').toggle();
    $("#message_holder").children(":eq("+current_select+")").children('.message_container').find('span.message_preview').toggle();
    $("#message_holder").children(":eq("+current_select+")").children('.message_container').find('div.optionmenu').toggle();
    return false;
});

$(document).bind('keydown', 'd', function(){  
    $("#message_holder").children(":eq("+current_select+")").children('.message_container').find('div.detail_area').toggle();
});

$(document).bind('keydown', 'u', function(){  
    var dest = $('#back_threadlist').attr('href');
    document.location = dest;  
});

$(document).bind('keydown', 'x', function(){  
    if($("#message_holder").children(":eq("+current_select+")").children('.message_container').find('.message_header').children('input.select_message').attr('checked')==true)
    {
        $("#message_holder").children(":eq("+current_select+")").children('.message_container').find('.message_header').children('input.select_message').removeAttr('checked');
        $("#message_holder").children(":eq("+current_select+")").removeClass("messagelist_hover");    
    }
    else
    {
        $("#message_holder").children(":eq("+current_select+")").children('.message_container').find('.message_header').children('input.select_message').attr('checked', true)
        $("#message_holder").children(":eq("+current_select+")").addClass("messagelist_hover");    
    }  
    
});

$(document).bind('keydown', 'f', function(){
    if(current_select < 1) return false;
    var param2 = $("#message_holder").children(":eq("+current_select+")").children('.message_container').find('.message_header').children('input.select_message').attr('id');
    var param1 = $('#item_source'+param2).val();
    $("#compose_sms_container").html("<div align=\"center\"> Loading...</div>");
    $("#compose_sms_container").load('<?php echo site_url('messages/compose') ?>', { 'type': 'forward', 'param1': param1, 'param2': param2}, function() {
      $(this).dialog({
        modal: true,    
        open: function(event, ui) {$("#message").focus();}, 
    	width: 550,
    	show: 'fade',
    	hide: 'fade',
        buttons: {
    	'<?php echo lang('tni_send_message'); ?>': function() {
    		if($("#composeForm").valid()) {
    		  $('.ui-dialog-buttonpane :button').each(function(){ if($(this).text() == '<?php echo lang('tni_send_message'); ?>') $(this).html('<?php echo lang('tni_sending_message'); ?> <img src="<?php echo $this->config->item('img_path').'processing.gif' ?>" height="12" style="margin:0px; padding:0px;">');   });
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
<?php endif; ?>

<?php if ($this->uri->segment(1) == 'messages' && $this->uri->segment(2) != 'conversation' && $this->uri->segment(2) != 'search'): ?>
// for message_list page
var totalmsg = $("#message_holder > div.messagelist").length;
var current_select = 0;

//move next
$(document).bind('keydown', 'j', function(){  
    $("#message_holder").children(":eq("+current_select+")").removeClass('infocus'); //selecting child
    current_select ++; if(current_select > totalmsg   ) current_select = 1;
    $("#message_holder").children(":eq("+current_select+")").addClass('infocus'); //selecting child
    current_number = $("#message_holder").children(":eq("+current_select+")").children().children().children('input.select_conversation').val();
});

//move prev
$(document).bind('keydown', 'k', function(){  
    $("#message_holder").children(":eq("+current_select+")").removeClass('infocus'); //selecting child
    current_select --; if(current_select < 0 ) current_select = totalmsg ;
    $("#message_holder").children(":eq("+current_select+")").addClass('infocus'); //selecting child
    current_number = $("#message_holder").children(":eq("+current_select+")").children().children().children('input.select_conversation').val();
});

//select
$(document).bind('keydown', 'o return', function(e){
    //var code = (e.keyCode ? e.keyCode : e.which);
    if(current_select < 1) return false;
    var group = "<?php echo $this->uri->segment(2); ?>";
    var folder = "<?php echo $this->uri->segment(3); ?>";
    var fid = "<?php echo $this->uri->segment(4, ''); ?>";
    document.location = "<?php echo site_url('messages/conversation'); ?>/" + group + "/"+ folder+"/" + current_number+"/" +fid ;
    return false;
});

//quick reply
$(document).bind('keydown', 'r', function(){ 
    if(current_select < 1) return false;
    $("#compose_sms_container").html("<div align=\"center\"> Loading...</div>");
    $("#compose_sms_container").load('<?php echo site_url('messages/compose')?>', { 'type':'reply', 'param1': current_number, 'param2': ''}, function() {
      $(this).dialog({
        modal: true, 
        open: function(event, ui) {$("#message").focus();}, 
    	width: 550,
    	show: 'fade',
    	hide: 'fade',
        buttons: {
    	'<?php echo lang('tni_send_message'); ?>': function() {
    		if($("#composeForm").valid()) {
    		  $('.ui-dialog-buttonpane :button').each(function(){ if($(this).text() == '<?php echo lang('tni_send_message'); ?>') $(this).html('<?php echo lang('tni_sending_message'); ?> <img src="<?php echo $this->config->item('img_path').'processing.gif' ?>" height="12" style="margin:0px; padding:0px;">');   });
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

$(document).bind('keydown', 'x', function(){  
    if($("#message_holder").children(":eq("+current_select+")").children('.message_container').find('.message_header').children('input.select_conversation').attr('checked')==true)
    {
        $("#message_holder").children(":eq("+current_select+")").children('.message_container').find('.message_header').children('input.select_conversation').removeAttr('checked');
        $("#message_holder").children(":eq("+current_select+")").removeClass("messagelist_hover");    
    }
    else
    {
        $("#message_holder").children(":eq("+current_select+")").children('.message_container').find('.message_header').children('input.select_conversation').attr('checked', true)
        $("#message_holder").children(":eq("+current_select+")").addClass("messagelist_hover");    
    }  
    
});
<?php endif; ?>

<?php if ($this->uri->segment(1) != 'phonebook' && $this->uri->segment(2) != 'search'): ?>
$(document).bind('keydown', 'f5', function(){   refresh(); current_select =0; return false; });
<?php endif; ?>

$(document).bind('keydown', '*;a', function(){ select_all(); });
$(document).bind('keydown', '*;n', function(){ clear_all(); });

<?php endif; ?>
});
</script>