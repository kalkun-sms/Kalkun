<?php if($this->uri->segment(1) == 'messages'): ?>
<div  id="message_search_box">
<?php echo form_open("messages/search/results/all", array('class' => 'search_form')); ?>
<input type="text" name="search_sms" size="20" class="search_sms ui-corner-all" value="" />
<?php echo form_close(); ?>	
</div>
<?php endif; ?>