<?php
$this->load->helper('html');
echo doctype('html5');?>
<html>

<head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<title>Repost message after login</title>
	<script>
		function goBackToForm() {
			window.history.go(<?php echo $this->session->flashdata('bef_login_history_count')?>);
		}

		function submitForm() {
			document.forms["redirectpost"].trigger('submit');
		}

	</script>
	<link type="text/css" rel="stylesheet" href="<?php echo $this->config->item('css_path');?>base.css" />
	<style type="text/css">
		@import url("<?php echo $this->config->item('css_path');?>blue.css");

	</style>
</head>

<?php if ( ! $this->session->flashdata('bef_login_post_data'))
{
	// Here the user logged in, but we lost the content of the POST.
	// So we redirect him to the form he Posted by using the javascript
	// "history.go" function. That way the content of
	// the web-form he initially submitted is not lost.

	// Some browsers like firefox don't honor the history.go() well in case of an onload event
	// Hence this message with a link pointing to the history on which the user can click.?>

<body onload="goBackToForm()">
	<p><?php
			printf(lang('kalkun_msg_login_success_data_lost'), strtoupper($this->session->flashdata('bef_login_method')));
	echo ' <br> ';
	printf(lang('kalkun_msg_go_back_and_submit'), $this->session->flashdata('bef_login_HTTP_REFERER')); ?>
</body>
<?php
}
	else
	{
		// Here the user logged in and we could keep the content of the POST.
		// So resubmit the POSTed data directly to this page?>

<body onload="submitForm()">
	<p><?php echo lang('kalkun_msg_login_success_resubmit'); ?></p>
	<form name="redirectpost" method="post" action="<?php echo current_url(); ?>">
		<?php
			if ( ! is_null($this->session->flashdata('bef_login_post_data')))
			{
				foreach ($this->session->flashdata('bef_login_post_data') as $k => $v)
				{
					echo '<input type="hidden" name="' . $k . '" value="' . $v . '"> ';
				}
			} ?>
	</form>
</body>
<?php
	} ?>

</html>
