<script type="text/javascript">
var refreshId = setInterval(function() {
	$('.modem_status').load('<?php echo site_url('kalkun/notification')?>');
	//$('.unread_inbox').load('<?php echo site_url('kalkun/unread_inbox')?>');\
	//var current_title = $(document).attr('title');
	new_notification('true');
}, 60000);

function new_notification(refreshmode)
{
    $.get("<?php echo site_url('kalkun/unread_count')?>", function(data) {
		unreadcount = data.split('/');
         
        $('span.unread_inbox_notif').text(unreadcount[0]);
        $('span.unread_spam_notif').text(unreadcount[2]);
						
		var current_title = $(document).attr('title');
		var stopNumber = current_title.search('\\)');
		if(stopNumber!='-1') var title = current_title.substr(stopNumber+1);
		else var title = current_title;
			
		var newtitle = unreadcount[0] + ' ' + title;
		$(document).attr('title', newtitle);
				
		// play the sound
		if (unreadcount[0] != '')
		{
			$.fn.soundPlay({
				url: "<?php echo $this->config->item('sound_path').$this->config->item('new_incoming_message_sound')?>",
				playerId: 'embed_player',
				command: 'play'
			});
		}
	});
    
    <?php if ($this->uri->segment(2) == 'folder' || $this->uri->segment(2) == 'my_folder'): ?>  
    function auto_refresh(){
            $('#message_holder').load("<?php echo site_url('messages').'/'.$folder.'/'.$type.'/'.$id_folder ?>", function(response, status, xhr) {
            if (status == "error" || xhr.status != 200 ) 
            {
            		show_loading('<nobr>Oops Network Error. <span id="retry-progress-display"> Retrying in <span id="countdown-count">10</span> Seconds.</span></nobr>');
                    var cntdwn = setInterval(function() {
                        current_val = $('#countdown-count').html();
                        if(current_val > 1)   $('#countdown-count').html(current_val  - 1 )	;
                        else    {  clearInterval(cntdwn); $('#retry-progress-display').html('Retrying Now...') }                    
                    } , 1000);	
                    setTimeout(function() {auto_refresh();	} , 10000);	
                    return false;
            }
        });
    }
    if(refreshmode == 'true') //refresh automatically if in threastlist 
        auto_refresh();
    <?php endif; ?>
}

function show_loading(text)
{
	$('.loading_area').html(text);
	var content_width = ($('.loading_area').width())/2;
	$('.loading_container').css('margin-left',-content_width); 
	$('.loading_area').fadeIn("slow");
}

function show_notification(text)
{
	$('.notification_area').text(text).fadeIn().delay(1500).fadeOut('slow');
}

$(document).ready(function() {

	// Get current page for styling/css	
	$("#menu").find("a[href='"+window.location.href+"']").each(function(){
		$(this).addClass("current");
	});
	
	// Compose SMS
	$('#compose_sms_normal').bind('click', compose_message = function() 
	{
		$("#compose_sms_container").html("<div align=\"center\"> Loading...</div>");
		$("#compose_sms_container").load('<?php echo site_url('messages/compose')?>', { 'type': "normal" }, function() {
		  $(this).dialog({
		    modal:true,
            draggable : true,            
			width: 550,
			show: 'fade',
			hide: 'fade',
		    buttons: {
			'<?php echo lang('tni_send_message'); ?>': function() {
				if($("#composeForm").valid()) {
				    $('.ui-dialog-buttonpane :button').each(function(){ if($(this).text() == '<?php echo lang('tni_send_message'); ?>') $(this).html('<?php echo lang('tni_sending_message'); ?> <img src="<?php echo $this->config->item('img_path').'processing.gif' ?>" height="12" style="margin:0px; padding:0px;">');                 });
				$.post("<?php echo site_url('messages/compose_process') ?>", $("#composeForm").serialize(), function(data) {
					$("#compose_sms_container").html(data);
                    $("#compose_sms_container" ).dialog( "option", "buttons", { "Okay": function() { $(this).dialog("destroy"); } } );
					setTimeout(function() {$("#compose_sms_container").dialog('destroy')} , 1500);
				});
				}
			},
			"Send and Repeat": function() { 
				if($("#composeForm").valid()) {
				$.post("<?php echo site_url('messages/compose_process') ?>", $("#composeForm").serialize(), function(data) {
					$("#compose_sms_container").append(data);
				});
				}
			},
			"<?php echo lang('kalkun_cancel'); ?>": function() { $(this).dialog('destroy');}
		    },
		    open: function() {
            $("#personvalue").focus();
        	}
		  });
		});
		$("#compose_sms_container").dialog('open');
		return false;
	});	
		
	// About
	$('#about_button').click(function() {
		$("#about").dialog({
			bgiframe: true,
			autoOpen: false,
			height: 300,
			width: 525,
			modal: true		
		});	
		$('#about').dialog('open');
		return false;
	});		
		
	// Add folder
	$('#addfolder').click(function() {		
		$("#addfolderdialog").dialog({
		bgiframe: true,
		autoOpen: false,
		height: 100,
		modal: true,
		buttons: {
			'Save': function() {
				$("form.addfolderform").submit();
			},
			Cancel: function() {
				$(this).dialog('close');
			}
		},
		open: function() {
            $("#folder_name").focus();
        }
		});		
		$('#addfolderdialog').dialog('open');
		return false;
	});	
	
	// languange support
	var save = '<?php echo lang('kalkun_save')?>';
	var cancel = '<?php echo lang('kalkun_cancel')?>';
		
	$('div.ui-dialog-buttonpane:eq(0) button:eq(1)').text(cancel);
	$('div.ui-dialog-buttonpane:eq(0) button:eq(0)').text(save);

   	//shift select
 	$("input:checkbox").createCheckboxRange(function(){
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
    
    //search
    $('.sms_search_form').submit(function() {
       if($.trim($('#search').val()) == '')  return false;
    });

	// advanced search    
	$("#a_search_date_from, #a_search_date_to").datepicker({
		maxDate: 0,
		dateFormat: 'yy-mm-dd'
	});
	$('#a_search').click(function() {
		$("#a_search_dialog").dialog({
		bgiframe: true,
		autoOpen: false,
		height: 275,
		width: 500,
		modal: true,
		buttons: {
			'<?php echo lang('kalkun_search');?>': function() {
				$('#a_search_form').submit();
			},
			"<?php echo lang('kalkun_cancel');?>": function() {
				$(this).dialog('close');
			}
		},
		open: function() {
            $("#a_search_from_to").focus();
        }
		});		
		$('#a_search_dialog').dialog('open');
		return false;
	});

	<?php if ($this->uri->segment(2) != 'folder' AND $this->uri->segment(2) != 'my_folder'): ?>	
	// logo click 
	$('div#logo a').click(function() {
		new_notification('false');
		return false;
	});
	<?php endif;?>
});
</script>