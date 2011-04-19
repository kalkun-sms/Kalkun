<script type="text/javascript">
var go_to = false;

//set g
$(document).bind('keydown', 'g', function(){
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

$(document).bind('keydown', 'c', function(){
  compose_message();
});
<?php if($this->uri->segment(1)!=''):   ?>

$(document).bind('keydown', 'shift+#', function(){
 action_delete();
});
<?php if($this->uri->segment(1)!='phonebook' ):   ?>
$(document).bind('keydown', 'm', function(){
  message_move();
});
<?php endif; ?>
<?php if($this->uri->segment(1)!='phonebook' && $this->uri->segment(2)!='search'):   ?>

$(document).bind('keydown', 'f5', function(){
  refresh();return false;
});

<?php endif; ?>
$(document).bind('keydown', 'shift+/', function(){
    $("#kbd").dialog({
			bgiframe: true,
			autoOpen: false,
			height: 300,
			width: 400,
			modal: true		
		});	
		$('#kbd').dialog('open');
  
}); 

<?php if($this->uri->segment(2)=='conversation' ):   ?>
$(document).bind('keydown', 'r', function(){
  message_reply();
});
<?php endif; ?>
<?php endif; ?>
</script>