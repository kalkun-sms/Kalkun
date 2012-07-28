<?php $this->load->view('js_sms_to_xmpp');?>

<!-- Add Account dialog -->	
<div id="xmpp-dialog" title="Add XMPP Account" class="dialog">
	<p id="validateTips">All form fields are required.</p>
	<?php echo form_open('plugin/sms_to_xmpp/add', array('class' => 'addxmppform')); ?>
	<fieldset>
		<label for="xmpp_host">XMPP Host</label>
		<input type="text" name="xmpp_host" id="xmpp_host" class="text ui-widget-content ui-corner-all" />
		<label for="xmpp_port">XMPP Port</label>
		<input type="text" name="xmpp_port" id="xmpp_port" value="" class="text ui-widget-content ui-corner-all" />
		<label for="xmpp_username">XMPP Username</label>
		<input type="text" name="xmpp_username" id="xmpp_username" value="" class="text ui-widget-content ui-corner-all" />
		<label for="xmpp_password">XMPP Password</label>
		<input type="password" name="xmpp_password" id="xmpp_password" value="" class="text ui-widget-content ui-corner-all" />
		<label for="xmpp_server">XMPP Server</label>
		<input type="text" name="xmpp_server" id="xmpp_server" value="" class="text ui-widget-content ui-corner-all" />
	</fieldset>
	<?php echo form_close(); ?>
</div>

<div id="window_container">
<div id="window_title"><?php echo $title; ?></div>
<div id="window_content">
<?php if (!$status):?>
<a href="#" class="nicebutton" id="addxmppbutton">&#43; Add XMPP account</a>
<?php else:?>
<a href="<?php echo site_url('plugin/sms_to_xmpp/delete')?>" class="nicebutton">&#43; Delete XMPP account</a>
<?php endif;?>

<?php if($xmpp):?>
<h4>XMPP Account:</h4>
<p><?php echo $xmpp['xmpp_username'];?></p>
<?php endif;?>
</div>