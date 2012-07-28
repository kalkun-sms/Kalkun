<div id="window_container">
<div id="window_title"><?php echo $title; ?></div>
<div id="window_content">
<?php if (!$status):?>
<a href="<?php echo site_url('sms_to_twitter/connect')?>" class="nicebutton">&#43; Connect to Twitter</a>
<?php else:?>
<a href="<?php echo site_url('sms_to_twitter/disconnect')?>" class="nicebutton">&#43; Disconnect from Twitter</a>
<?php endif;?>
</div>