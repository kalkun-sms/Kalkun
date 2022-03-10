<?php $this->load->view('js_packages');?>

<div id="window_container">
	<div id="window_title">
		<div id="window_title_left"><?php echo $title;?></div>
		<div id="window_title_right">
			<?php echo form_open('plugin/sms_credit/packages', array('class' => 'search_form')); ?>
			<input type="text" name="query" class="search_packages" size="20" value="<?php if (isset($query))
{
	echo htmlentities($query, ENT_QUOTES);
}?>" />
			<?php echo form_close(); ?>
			&nbsp;
			<a href="<?php echo site_url('plugin/sms_credit');?>" class="nicebutton"><?php echo tr('Users'); ?></a>
			<a href="javascript:void(0);" id="addpackagesbutton" class="nicebutton">&#43; <?php echo tr('Add Packages'); ?></a>
		</div>
	</div>

	<div id="window_content">
		<?php $this->load->view('navigation');?>
		<table>
			<?php foreach ($packages->result() as $tmp): ?>
			<tr id="<?php echo htmlentities($tmp->id_credit_template, ENT_QUOTES);?>">
				<td>
					<div class="two_column_container contact_list">
						<div class="left_column">
							<div id="pbkname">
								<span class="hidden id_package"><?php echo htmlentities($tmp->id_credit_template, ENT_QUOTES);?></span>
								<span class="package_name"><strong><?php echo htmlentities($tmp->template_name, ENT_QUOTES);?></strong></span>
								<span class="hidden sms_amount"><?php echo htmlentities($tmp->sms_numbers, ENT_QUOTES);?></span>
								<?php echo '<sup>( '.htmlentities($tmp->sms_numbers, ENT_QUOTES).' SMS )</sup>'; ?>
							</div>
						</div>

						<div class="right_column">
							<span class="pbk_menu">
								<a class="deletepackagesbutton simplelink" href="<?php echo site_url('plugin/sms_credit/delete_packages/'.$tmp->id_credit_template);?>"><?php echo tr('Delete'); ?></a>
								<img src="<?php echo $this->config->item('img_path')?>circle.gif" />
								<a class="editpackagesbutton simplelink" href="javascript:void(0);"><?php echo tr('Edit'); ?></a>
							</span>
						</div>
					</div>
				</td>
			</tr>
			<?php endforeach;?>
		</table>
		<?php $this->load->view('navigation');?>
	</div>
</div>

<!-- Add packages dialog -->
<div id="packages-dialog" title="Add Packages" class="dialog">
	<p id="validateTips"><?php echo tr('All form fields are required.'); ?></p>
	<?php echo form_open('plugin/sms_credit/add_packages', array('id' => 'addpackagesform')); ?>
	<fieldset>
		<input type="hidden" name="id_package" id="id_package" class="text ui-widget-content ui-corner-all" />
		<label for="package_name"><?php echo tr('Package name'); ?></label>
		<input type="text" name="package_name" id="package_name" class="text ui-widget-content ui-corner-all" />
		<label for="sms_amount"><?php echo tr('SMS Amount'); ?></label>
		<input type="text" name="sms_amount" id="sms_amount" value="" class="text ui-widget-content ui-corner-all" />
	</fieldset>
	<?php echo form_close(); ?>
</div>

<!-- Delete Package Confirmation Dialog -->
<div class="dialog" id="confirm_delete_package_dialog" title="<?php echo tr('Delete Packages Confirmation'); ?>">
	<p>
		<span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
		<?php echo tr('Are you sure to delete this package? All users belonging to this package will no longer be limited.'); ?>
	</p>
</div>
