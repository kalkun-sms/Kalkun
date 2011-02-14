<?php $this->load->view('js_init/phonebook/js_phonebook');?>

<div id="contact_container" class="hidden"></div>
	
<div id="window_container">
<div id="window_title">
	<div id="window_title_left">Contacts</div>
	<div id="window_title_right">
	<?php echo form_open('phonebook', array('class' => 'search_form')); ?>
	<input type="text" name="search_name" size="20" class="search_name" value="" />
	<?php echo form_close(); ?>	
	&nbsp;
	<a href="#" id="addpbkcontact" class="addpbkcontact nicebutton">&#43; Add Contact</a>	
	<a href="<?php echo site_url('phonebook/group');?>" id="addpbkgroup" class="nicebutton">&#43; Manage Group</a>
	</div>
</div>

<div id="window_content">
	<?php $this->load->view("main/phonebook/contact/navigation");?>
	<div id="pbk_list"><?php $this->load->view('main/phonebook/contact/pbk_list');?></div>
	<?php $this->load->view("main/phonebook/contact/navigation");?>
</div>
</div>
