<script type="text/javascript">
var go_to = false;
var s_all = false;
var current_number = '';

$(document).ready(function() {
//set g
$(document).bind('keyup', 'g', function(){
   go_to = true;
   setTimeout(function(){go_to = false;}, "3000");
});

$(document).bind('keyup', '*', function(){
   s_all = true;
   setTimeout(function(){s_all = false;}, "3000");
});


$(document).bind('keydown', 'i', function(){
  if(go_to == true)    window.location = "<?php echo site_url('messages/folder/inbox');  ?>";
});

$(document).bind('keydown', 'o', function(){
  if(go_to == true)    window.location = "<?php echo site_url('messages/folder/outbox');  ?>";
});
 
$(document).bind('keydown', 's', function(){
  if(go_to == true)    window.location = "<?php echo site_url('messages/folder/sentitems');  ?>";
});

$(document).bind('keydown', 'p', function(){
  if(go_to == true)    window.location = "<?php echo site_url('phonebook');  ?>";
});

$(document).bind('keyup', 's', function(){   $("#search").focus(); });

$(document).bind('keyup', 'c', function(){  compose_message();});

$(document).bind('keydown', 'shift+/', function(){
    $("#kbd").dialog({
			bgiframe: true,
			autoOpen: false,
			height: 400,
			width: 500,
			modal: true		
		});	
    $('#kbd').dialog('open');
}); 

<?php if($this->uri->segment(1)!=''):   ?>
$(document).bind('keydown', '#', function(){  action_delete(); });

<?php if($this->uri->segment(1)!='phonebook' ):   ?>
$(document).bind('keydown', 'm', function(){   message_move(); });
<?php endif; ?>

<?php if($this->uri->segment(1)!='phonebook' && $this->uri->segment(2)!='search'):   ?>
$(document).bind('keydown', 'f5', function(){   refresh();return false; });
<?php endif; ?>

<?php if($this->uri->segment(2)=='conversation' ):   ?>  
$(document).bind('keydown', 'r', function(){   message_reply(); });
// for convesation
var totalmsg = $("#message_holder > div.messagelist").length;
var current_select =2;

//move next
$(document).bind('keydown', 'j', function(){  
    $("#message_holder").children(":eq("+current_select+")").children('.message_container').children('.message_header').removeClass('infocus'); //selecting child
    current_select ++; if(current_select > totalmsg +2 ) current_select = 2;
    $("#message_holder").children(":eq("+current_select+")").children('.message_container').children('.message_header').addClass('infocus'); //selecting child
});

//move prev
$(document).bind('keydown', 'k', function(){  
    $("#message_holder").children(":eq("+current_select+")").children('.message_container').children('.message_header').removeClass('infocus'); //selecting child
    current_select --;  if(current_select <3 ) current_select = totalmsg +2;
    $("#message_holder").children(":eq("+current_select+")").children('.message_container').children('.message_header').addClass('infocus'); //selecting child   
});

//select
$(document).bind('keydown', 'o', function(){  
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
      
    }else
    {
        $("#message_holder").children(":eq("+current_select+")").children('.message_container').find('.message_header').children('input.select_message').attr('checked', true)
        $("#message_holder").children(":eq("+current_select+")").addClass("messagelist_hover");    
    }  
    
});

$(document).bind('keydown', 'f', function(){
   var param2 = $("#message_holder").children(":eq("+current_select+")").children('.message_container').find('.message_header').children('input.select_message').attr('id');
   var param1 = $('#item_source'+param2).val();
  $("#compose_sms_container").load('<?php echo site_url('messages/compose')?>', { 'type': 'forward', 'param1': param1, 'param2': param2}, function() {
  $(this).dialog({
    modal: true,    
    open: function(event, ui) {$("#message").focus();}, 
	width: 550,
	show: 'fade',
	hide: 'fade',
    buttons: {
	'<?php echo lang('tni_send_message'); ?>': function() {
		if($("#composeForm").valid()) {
		$.post("<?php echo site_url('messages/compose_process') ?>", $("#composeForm").serialize(), function(data) {
			$("#compose_sms_container").html(data);
			$("#compose_sms_container").dialog({ buttons: { "Okay": function() { $(this).dialog("destroy"); } } });
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

<?php if($this->uri->segment(1) == 'messages' && $this->uri->segment(2)!='conversation'  ):   ?>
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
    current_select --; if(current_select < 1 ) current_select = totalmsg ;
    $("#message_holder").children(":eq("+current_select+")").addClass('infocus'); //selecting child
    current_number = $("#message_holder").children(":eq("+current_select+")").children().children().children('input.select_conversation').val();
});

//select
$(document).bind('keydown', 'o return', function(){  
    var group = "<?php echo $this->uri->segment(2) ; ?>";
    var folder = "<?php echo $this->uri->segment(3) ; ?>";
    var fid = "<?php echo $this->uri->segment(4,'') ; ?>";
    document.location = "<?php echo site_url('messages/conversation'); ?>/" + group + "/"+ folder+"/" + current_number+"/" +fid ;
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

 
$(document).bind('keydown', 'a', function(){   if(s_all == true)    select_all(); });
$(document).bind('keydown', 'n', function(){   if(s_all == true)     clear_all(); });
 

<?php endif; ?>
});
</script>