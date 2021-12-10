<?php $this->load->view('js_member');?>
<div id="window_container">
	<div id="window_title">
		<div id="window_title_left"><?php echo tr('Member');?></div>
		<div id="window_title_right">
			<a href="#" id="send_member" class="nicebutton">&#43; <?php echo tr('Send message');?></a>
		</div>
	</div>
	<div id="member information" style="background: #eee; padding: 5px 10px; border-bottom: 1px solid #ccc;">
		<?php echo tr('Total member');?>: <?php echo $total_member;?>
	</div>

	<div id="window_content">
		<?php
if ($total_member == 0):
echo '<p class="no_content"><span class="ui-icon ui-icon-alert" style="float:left;"></span><i>'.tr('There is no registered member yet').'.</i></p>';
else:
foreach ($member as $tmp_member):
	echo $tmp_member['phone_number'].' - ';
endforeach;
endif;
?>
	</div>
</div>
