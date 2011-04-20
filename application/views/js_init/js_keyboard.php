<script type="text/javascript">
var go_to = false;

//set g
$(document).bind('keyup', 'g', function(){
   go_to = true;
   setTimeout(function(){go_to = false;}, "3000");
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
$(document).bind('keydown', 'shift+#', function(){  action_delete(); });

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
    current_select ++;
    $("#message_holder").children(":eq("+current_select+")").children('.message_container').children('.message_header').addClass('infocus'); //selecting child
});

//move prev
$(document).bind('keydown', 'k', function(){  
    $("#message_holder").children(":eq("+current_select+")").children('.message_container').children('.message_header').removeClass('infocus'); //selecting child
    current_select --;
    $("#message_holder").children(":eq("+current_select+")").children('.message_container').children('.message_header').addClass('infocus'); //selecting child   
});

//select
$(document).bind('keydown', 'o return', function(){  
   $("#message_holder").children(":eq("+current_select+")").children('.message_container').find('div.message_content').toggle();
 $("#message_holder").children(":eq("+current_select+")").children('.message_container').find('span.message_preview').toggle();
  $("#message_holder").children(":eq("+current_select+")").children('.message_container').find('div.optionmenu').toggle();
});

$(document).bind('keydown', 'd', function(){  
   $("#message_holder").children(":eq("+current_select+")").children('.message_container').find('div.detail_area').toggle();
});

$(document).bind('keydown', 'u', function(){  
    var dest = $('#back_threadlist').attr('href');
    document.location = dest;  
});
<?php endif; ?>

<?php if($this->uri->segment(1) == 'messages' && $this->uri->segment(2)!='conversation'  ):   ?>
// for message_list page
var totalmsg = $("#message_holder > div.messagelist").length;
var current_select = 0;
var current_number = '';
//move next
$(document).bind('keydown', 'j', function(){  
    $("#message_holder").children(":eq("+current_select+")").removeClass('infocus'); //selecting child
    current_select ++;
    $("#message_holder").children(":eq("+current_select+")").addClass('infocus'); //selecting child
    current_number = $("#message_holder").children(":eq("+current_select+")").children().children().children('input.select_conversation').val();
   
});

//move prev
$(document).bind('keydown', 'k', function(){  
    $("#message_holder").children(":eq("+current_select+")").removeClass('infocus'); //selecting child
    current_select --;
    $("#message_holder").children(":eq("+current_select+")").addClass('infocus'); //selecting child
    current_number = $("#message_holder").children(":eq("+current_select+")").children().children().children('input.select_conversation').val();
   
   
});

//select
$(document).bind('keydown', 'o return', function(){  
    var group = "<?php echo $this->uri->segment(2) ; ?>";
    var folder = "<?php echo $this->uri->segment(3) ; ?>";
    document.location = "<?php echo site_url('messages/conversation'); ?>/" + group + "/"+ folder+"/" + current_number ;
});
<?php endif; ?>


<?php endif; ?>
</script>