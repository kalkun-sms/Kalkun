<?php $this->load->view('js_init/js_filters');?>
<div align="center">
	<a href="javascript:void(0);" id="addnewfilter"><?php echo tr('Create a new filter');?></a>
</div>

<?php foreach ($filters->result_array() as $filter):?>
<div class="two_column_container contact_list" style="display: inline-block;">
	<div class="left_column">
		<div id="<?php echo $filter['id_filter'];?>" class="id_filter">
			<span>
				<?php if ( ! empty($filter['from'])):?>
				<?php echo tr('From');?>: <b class="from"><?php echo htmlentities($filter['from'], ENT_QUOTES);?></b>
				<?php endif;?>

				<?php if ( ! empty($filter['has_the_words'])):?>
				<?php echo tr('Has the words');?>: <b class="has_the_words"><?php echo htmlentities($filter['has_the_words'], ENT_QUOTES);?></b>
				<?php endif;?>
			</span>
			<div style="padding: 2px 0 5px 24px;" class="<?php echo $filter['id_folder'];?>"><?php echo tr('Move to');?>: <b class="id_folder"><?php echo htmlentities($filter['name'], ENT_QUOTES);?></b></div>
		</div>
	</div>

	<div class="right_column">
		<span>
			<a href="javascript:void(0);" class="editfilter simplelink"><?php echo tr('Edit');?></a>
			<img src="<?php echo $this->config->item('img_path');?>circle.gif" />
			<a href="javascript:void(0);" class="deletefilter simplelink"><?php echo tr('Delete');?></a>
		</span>
	</div>
</div>
<?php endforeach;?>

<!-- Filter Dialog -->
<div id="filterdialog" title="<?php echo tr('Filters');?>" class="dialog">
	<?php
	$this->load->helper('form');
	echo form_open('settings/save', array('class' => 'addfilterform'));
?>
	<input type="hidden" name="option" value="filters" />
	<input type="hidden" name="id_filter" id="id_filter" value="" />
	<input type="hidden" name="id_user" value="<?php echo $this->session->userdata('id_user');?>" />

	<label for="from"><?php echo tr('From');?></label>
	<input type="text" name="from" id="from" class="text ui-widget-content ui-corner-all" />

	<label for="has_the_words"><?php echo tr('Has the words');?></label>
	<input type="text" name="has_the_words" id="has_the_words" class="text ui-widget-content ui-corner-all" />

	<label for="move_to"><?php echo tr('Move to');?></label>
	<select name="id_folder" id="id_folder" style="width: 98%">
		<?php foreach ($my_folders->result() as $my_folder): ?>
		<option value="<?php echo $my_folder->id_folder; ?>"><?php echo htmlentities($my_folder->name, ENT_QUOTES); ?></option>
		<?php endforeach; ?>
	</select>
	<?php echo form_close(); ?>
</div>

<!-- Delete Filter Dialog -->
<div class="dialog" id="confirm_delete_filter_dialog" title="<?php echo tr('Filters');?>">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
		<?php echo tr('Are you sure?');?> </p>
</div>
