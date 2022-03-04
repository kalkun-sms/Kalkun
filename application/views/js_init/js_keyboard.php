<script type="text/javascript">
	var current_number = '';

	$(document).ready(function() {

		$(document).on('keydown', null, 'g;i', function() {
			window.location = "<?php echo site_url('messages/folder/inbox'); ?>";
		});

		$(document).on('keydown', null, 'g;o', function() {
			window.location = "<?php echo site_url('messages/folder/outbox'); ?>";
		});

		$(document).on('keydown', null, 'g;s', function() {
			window.location = "<?php echo site_url('messages/folder/sentitems'); ?>";
			return false;
		});

		$(document).on('keydown', null, 's', function() {
			$("#search").trigger('focus');
			return false;
		});


		$(document).on('keydown', null, 'g;p', function() {
			window.location = "<?php echo site_url('phonebook'); ?>";
		});

		$(document).on('keyup', null, 'c', function() {
			compose_message('normal', true, '#personvalue_tags_tag');
		});

		$(document).on('keydown', null, 'shift+/', function() {
			$("#kbd").dialog({
				closeText: "<?php echo tr_addcslashes('"', 'Close'); ?>",
				bgiframe: true,
				autoOpen: false,
				width: 600,
				modal: true
			});
			$('#kbd').dialog('open');
		});


		<?php if ($this->uri->segment(1) != ''): ?>
		$(document).on('keydown', null, '#', function() {
			action_delete();
		});

		<?php if ($this->uri->segment(1) != 'phonebook'
			&& $this->uri->segment(1) != 'users'
			&& $this->uri->segment(1) != 'plugin'
			&& $this->uri->segment(1) != 'settings'
			&& $this->uri->segment(1) != 'pluginss'): ?>
		$(document).on('keydown', null, 'm', function() {
			message_move();
		});
		<?php endif; ?>


		<?php if ($this->uri->segment(2) == 'conversation' || $this->uri->segment(2) == 'search'): ?>

		<?php if ($this->uri->segment(2) != 'search'): ?>
		$(document).on('keydown', null, 'r', function() {
			message_reply();
		});
		<?php endif; ?>

		// for convesation
		var totalmsg = $("#message_holder > div.messagelist").length;
		var current_select = 0;

		//move next
		$(document).on('keydown', null, 'j', go_next = function() {
			$("#message_holder").children(":eq(" + current_select + ")").children('.message_container').children('.message_header').removeClass('infocus'); //selecting child
			current_select++;
			if (current_select > totalmsg) current_select = 1;
			$("#message_holder").children(":eq(" + current_select + ")").children('.message_container').children('.message_header').addClass('infocus'); //selecting child
		});

		//move prev
		$(document).on('keydown', null, 'k', go_prev = function() {
			$("#message_holder").children(":eq(" + current_select + ")").children('.message_container').children('.message_header').removeClass('infocus'); //selecting child
			current_select--;
			if (current_select < 1) current_select = totalmsg;
			$("#message_holder").children(":eq(" + current_select + ")").children('.message_container').children('.message_header').addClass('infocus'); //selecting child   
		});


		//p - Read previous message within a conversation.
		$(document).on('keydown', null, 'p', function() {
			$("#message_holder").children(":eq(" + current_select + ")").children('.message_container').find('div.message_content').hide();
			$("#message_holder").children(":eq(" + current_select + ")").children('.message_container').find('span.message_preview').show();
			$("#message_holder").children(":eq(" + current_select + ")").children('.message_container').find('div.optionmenu').hide();
			if ($("#message_holder").children(":eq(" + current_select + ")").children('.message_container').find('div.optionmenu').is(":hidden"))
				$("#message_holder").children(":eq(" + current_select + ")").children('.message_container').find('div.detail_area').toggle(false);
			go_prev();
			$("#message_holder").children(":eq(" + current_select + ")").children('.message_container').find('div.message_content').show();
			$("#message_holder").children(":eq(" + current_select + ")").children('.message_container').find('span.message_preview').hide();
			$("#message_holder").children(":eq(" + current_select + ")").children('.message_container').find('div.optionmenu').show();
			return false;
		});

		//n - Read next message within a conversation.
		$(document).on('keydown', null, 'n', function() {
			$("#message_holder").children(":eq(" + current_select + ")").children('.message_container').find('div.message_content').hide();
			$("#message_holder").children(":eq(" + current_select + ")").children('.message_container').find('span.message_preview').show();
			$("#message_holder").children(":eq(" + current_select + ")").children('.message_container').find('div.optionmenu').hide();
			if ($("#message_holder").children(":eq(" + current_select + ")").children('.message_container').find('div.optionmenu').is(":hidden"))
				$("#message_holder").children(":eq(" + current_select + ")").children('.message_container').find('div.detail_area').toggle(false);
			go_next();
			$("#message_holder").children(":eq(" + current_select + ")").children('.message_container').find('div.message_content').show();
			$("#message_holder").children(":eq(" + current_select + ")").children('.message_container').find('span.message_preview').hide();
			$("#message_holder").children(":eq(" + current_select + ")").children('.message_container').find('div.optionmenu').show();
			return false;
		});

		//select
		$(document).on('keydown', null, 'o', read_message = function() {
			$("#message_holder").children(":eq(" + current_select + ")").children('.message_container').find('div.message_content').toggle();
			$("#message_holder").children(":eq(" + current_select + ")").children('.message_container').find('span.message_preview').toggle();
			$("#message_holder").children(":eq(" + current_select + ")").children('.message_container').find('div.optionmenu').toggle();
			if ($("#message_holder").children(":eq(" + current_select + ")").children('.message_container').find('div.optionmenu').is(":hidden"))
				$("#message_holder").children(":eq(" + current_select + ")").children('.message_container').find('div.detail_area').toggle(false);
			return false;
		});

		$(document).on('keydown', null, 'd', function() {
			$("#message_holder").children(":eq(" + current_select + ")").children('.message_container').find('div.detail_area').toggle();
		});

		$(document).on('keydown', null, 'u', function() {
			var dest = $('#back_threadlist').attr('href');
			document.location = dest;
		});

		$(document).on('keydown', null, 'x', function() {
			if ($("#message_holder").children(":eq(" + current_select + ")").children('.message_container').find('.message_header').children('input.select_message').prop('checked') == true) {
				$("#message_holder").children(":eq(" + current_select + ")").children('.message_container').find('.message_header').children('input.select_message').prop('checked', false);
				$("#message_holder").children(":eq(" + current_select + ")").removeClass("messagelist_hover");
			} else {
				$("#message_holder").children(":eq(" + current_select + ")").children('.message_container').find('.message_header').children('input.select_message').prop('checked', true)
				$("#message_holder").children(":eq(" + current_select + ")").addClass("messagelist_hover");
			}

		});

		$(document).on('keydown', null, 'f', function() {
			if (current_select < 1) return false;
			var param2 = $("#message_holder").children(":eq(" + current_select + ")").children('.message_container').find('.message_header').children('input.select_message').attr('id');
			var param1 = $('#item_source' + param2).val();
			compose_message('forward', false, '#personvalue_tags_tag', param1, param2);
			return false;
		});
		<?php endif; ?>

		<?php if ($this->uri->segment(1) == 'messages' && $this->uri->segment(2) != 'conversation' && $this->uri->segment(2) != 'search'): ?>
		// for message_list page
		var totalmsg = $("#message_holder > div.messagelist").length;
		var current_select = -1;

		//move next
		$(document).on('keydown', null, 'j', function() {
			$("#message_holder").children(":eq(" + Math.max(current_select, 0) + ")").removeClass('infocus'); //selecting child
			current_select++;
			if (current_select == totalmsg) current_select = 0;
			$("#message_holder").children(":eq(" + current_select + ")").addClass('infocus'); //selecting child
			current_number = $("#message_holder").children(":eq(" + current_select + ")").children().children().children('input.select_conversation').val();
		});

		//move prev
		$(document).on('keydown', null, 'k', function() {
			$("#message_holder").children(":eq(" + Math.max(current_select, 0) + ")").removeClass('infocus'); //selecting child
			current_select--;
			if (current_select < 0) current_select = totalmsg - 1;
			$("#message_holder").children(":eq(" + current_select + ")").addClass('infocus'); //selecting child
			current_number = $("#message_holder").children(":eq(" + current_select + ")").children().children().children('input.select_conversation').val();
		});

		//select
		$(document).on('keydown', null, 'o return', function() {
			$("#message_holder").children(":eq(" + current_select + ")").children().children().children('span.message_toggle').click();
			return false;
		});

		//quick reply
		$(document).on('keydown', null, 'r', function() {
			if (current_select < 0) return false;
			compose_message('reply', false, '#message', current_number);
			return false;
		});

		$(document).on('keydown', null, 'x', function() {
			if ($("#message_holder").children(":eq(" + current_select + ")").children('.message_container').find('.message_header').children('input.select_conversation').prop('checked') == true) {
				$("#message_holder").children(":eq(" + current_select + ")").children('.message_container').find('.message_header').children('input.select_conversation').prop('checked', false);
				$("#message_holder").children(":eq(" + current_select + ")").removeClass("messagelist_hover");
			} else {
				$("#message_holder").children(":eq(" + current_select + ")").children('.message_container').find('.message_header').children('input.select_conversation').prop('checked', true)
				$("#message_holder").children(":eq(" + current_select + ")").addClass("messagelist_hover");
			}

		});
		<?php endif; ?>

		<?php if ($this->uri->segment(1) != 'phonebook'
			&& $this->uri->segment(1) != 'users'
			&& $this->uri->segment(1) != 'pluginss'
			&& $this->uri->segment(1) != 'plugin'
			&& $this->uri->segment(1) != 'settings'
			&& $this->uri->segment(2) != 'search'): ?>
		$(document).on('keydown', null, 'f5', function() {
			refresh();
			current_select = -1;
			return false;
		});
		<?php endif; ?>

		$(document).on('keydown', null, '*;a', function() {
			select_all();
		});
		$(document).on('keydown', null, '*;n', function() {
			clear_all();
		});

		<?php endif; ?>
	});

</script>
