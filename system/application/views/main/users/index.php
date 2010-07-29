<?php $this->load->view('js_init/users/js_users');?>
<!-- Delete User Confirmation -->
<div class="dialog" id="confirm_delete_user_dialog" title="Delete users confirmation">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
	Are you sure want to delete selected users? <br />
	All data (message and phonebook) owned by user will be also deleted.</p>
</div>		

<div id="users_container" class="hidden"></div>
	
<div id="window_container">
<div id="window_title">
	<div id="window_title_left">Users</div>
	<div id="window_title_right">
	<?php echo form_open('users', array('class' => 'search_form')); ?>
	<input type="text" name="search_name" size="20" class="search_name" value="" />
	<?php echo form_close(); ?>	
	&nbsp;
	<a href="#" id="addpbkcontact" class="addpbkcontact nicebutton">&#43; Add Users</a>	
	</div>
</div>

<div id="window_content">
	<?php $this->load->view("main/users/navigation");?>
	<div id="users_list"><?php $this->load->view('main/users/users_list');?></div>
	<?php $this->load->view("main/users/navigation");?>
</div>
</div>
