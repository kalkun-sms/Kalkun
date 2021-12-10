<?php $this->load->view('js_member');?>
<div id="window_container">
	<div id="window_title">
		<div id="window_title_left"><?php echo lang('kalkun_sms_member');?></div>
		<div id="window_title_right">
			<a href="#" id="send_member" class="nicebutton">&#43; <?php echo lang('tni_send_message');?></a>
		</div>
	</div>
	<div id="member information" style="background: #eee; padding: 5px 10px; border-bottom: 1px solid #ccc;">
		<?php echo lang('kalkun_sms_total_member');?>: <?php echo $total_member;?>
	</div>

	<div id="window_content">
		<?php
if ($total_member == 0):
echo '<p class="no_content"><span class="ui-icon ui-icon-alert" style="float:left;"></span><i>'.lang('kalkun_sms_no_member').'.</i></p>';
else:
foreach ($member as $tmp_member):
	echo $tmp_member['phone_number'].' - ';
endforeach;
endif;
?>
	</div>
</div>
