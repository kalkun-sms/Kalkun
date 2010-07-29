<script type="text/javascript">
var count = 0;

$(document).ready(function() {
    
    var base = "<?php echo  site_url();?>/messages/delete_messages/";
    var source = "<?php echo $this->uri->segment(3);?>";
    
    var folder = "<?php echo $this->uri->segment(2);?>";
    if(folder=='folder') var current_folder = '';
    else var current_folder = "<?php echo $this->uri->segment(4);?>";
    var dest_url = base + source;
       	        
    // delete
	$("a.global_delete").click(function(){
		var count = $("input.select_conversation:checkbox:checked").length;
		if(count==0) { 
			$('.notification_area').text("No conversation selected");
			$('.notification_area').show();
		}
		else 
		{
			var param = '<?php echo $this->uri->segment(4);?>';	
					
			$('.loading_area').fadeIn("slow");
			$("input.select_conversation:checked").each( function () {
				var message_row = $(this).parents('div:eq(2)');
				$.post(dest_url, {type: 'conversation', number: $(this).val(), current_folder: current_folder}, function() {
					$(message_row).slideUp("slow");
					$(message_row).remove();
				});
			});
			$('.loading_area').fadeOut("slow");
		}
	});
	       
    // Move folder        
    $(".move_to").click(function() {
		var count = $("input.select_conversation:checkbox:checked").length;
		if(count==0) { 
			$("#movetodialog").dialog('close');
			$('.notification_area').text("No conversation selected");
			$('.notification_area').show();
		}
		else 
		{    	
			var id_folder = $(this).attr('id');	
			$("#movetodialog").dialog('close');
			$('.loading_area').fadeIn("slow");
			$("input.select_conversation:checked").each(function () {
				var message_row = $(this).parents('div:eq(2)');
				$.post("<?php echo  site_url('messages/move_message') ?>", {type: 'conversation', current_folder: current_folder, folder: source, 
					id_folder: id_folder, number: $(this).val()}, function() {
					$(message_row).slideUp("slow");
				});
			});		
			$('.loading_area').fadeOut("slow");
		}
		count=0;
    });    
    
    $(".move_to_button").click(function() {
    	$('#movetodialog').dialog('open');
    	return false;
    });
    
    
    // select all
    $("a.select_all_button").click(function(){
    	$(".select_conversation").attr('checked', true);
    	$(".messagelist").addClass("messagelist_hover");
    	return false;
    });
    
    // clear all
    $("a.clear_all_button").click(function(){
    	$(".select_conversation").attr('checked', false);
    	$(".messagelist").removeClass("messagelist_hover");
    	return false;
    });        
    
    // input checkbox
    $("input.select_conversation").click(function(){
    	if($(this).attr('checked')==true) 
    	{
    		$(this).parents('div:eq(2)').addClass("messagelist_hover");
    	}
    	else 
    	{
    		//$(this).attr('checked', true)
    		$(this).parents('div:eq(2)').removeClass("messagelist_hover");
    	}
    });
    
    // refresh
    $("a.refresh_button").click(function(){  	
    	$('.loading_area').fadeIn("slow");
    	if(folder=='folder') $('#message_holder').load("<?php echo site_url('messages/folder/'.$this->uri->segment(3).'/ajax/'.$this->uri->segment(4,0).'') ?>");
  		else $('#message_holder').load("<?php echo site_url('messages/my_folder/'.$this->uri->segment(3).'/'.$this->uri->segment(4).'/ajax/'.$this->uri->segment(5,0).'') ?>");
  		
    	$('.loading_area').fadeOut("slow");
    	return false;
    });
    

	// Move To dialog
	$("#movetodialog").dialog({
		bgiframe: true,
		autoOpen: false,
		modal: true,
	});    
     
    	
	// Rename folder button
	$('#renamefolder').click(function() {
		// Rename folder dialog
		$("#renamefolderdialog").dialog({
			bgiframe: true,
			autoOpen: false,
			height: 100,
			modal: true,	
			buttons: {
				'Save': function() {
					$("form.renamefolderform").submit();
				},
				Cancel: function() {
					$(this).dialog('close');
				}
			}
		});			
		var editname = $(this).parents('div').children("span.folder_name").text();
		$("#edit_folder_name").val(editname);
		$('#renamefolderdialog').dialog('open');
	});	    
			
	// Delete Folder link
	$('#deletefolder').click(function(){
		// Delete folder dialog
		var id_folder = '<?php echo $this->uri->segment(4);?>';
		var url = '<?php echo  site_url();?>/kalkun/delete_folder/' + id_folder + '';
		$("#deletefolderdialog").dialog({
			bgiframe: true,
			autoOpen: false,
			height: 150,
			modal: true,	
			buttons: {
				Cancel: function() {
					$(this).dialog('close');
				},			
				'Delete this folder': function() {
					location.href=url;
				}
			}
		});			
		$('#deletefolderdialog').dialog('open');
		return false;
	});

    
});    
</script>
