<!-- Contact dialog -->
<script src="<?php echo $this->config->item('js_path');?>jquery-plugin/jquery.validate.min.js"></script>
<script src="<?php echo $this->config->item('js_path');?>jquery-plugin/jquery.validate.phone.js"></script>
<script src="<?php echo $this->config->item('js_path');?>jquery-plugin/jquery.tagsinput-revisited.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo $this->config->item('css_path');?>jquery-plugin/jquery.tagsinput-revisited.min.css" />
<script type="text/javascript">
	$(document).ready(function() {
		<?php
	$group = $this->Phonebook_model->get_phonebook(array('option' => 'group'));
	$grouptext_array = [];
	foreach ($group->result() as $tmp):
		array_push($grouptext_array, $tmp->GroupName);
	endforeach;
	?>
		var grp_data = <?php echo json_encode($grouptext_array); ?>;

		$('#groups').tagsInput({
			'autocomplete': {
				source: grp_data,
				minLength: 0,
				delay: 0,
				autoFocus: true
			},
			'minChars': 0,
			'interactive': true,
			'delimiter': ',',
			'placeholder': '<?php echo tr('Type group name');?>'
		});

	});

</script>

<div id="dialog" class="dialog" style="display: block">
	<p id="validateTips"><?php echo tr('All form fields are required'); ?></p>
	<?php echo form_open('phonebook/add_contact_process', array('id' => 'addContact'));?>
	<fieldset>
		<input type="hidden" name="pbk_id_user" id="pbk_id_user" value="<?php echo $this->session->userdata('id_user');?>" />
		<label for="name"><?php echo tr('Name'); ?></label>
		<input type="text" name="name" id="name" value="<?php if (isset($contact))
	{
		echo $contact->row('Name');
	}?>" class="text ui-widget-content ui-corner-all required" />
		<label for="number"><?php echo tr('Telephone number'); ?></label>
		<input type="text" name="number" id="number" value="<?php if (isset($contact))
	{
		echo $contact->row('Number');
	}
	else
	{
		if (isset($number))
		{
			echo $number;
		}
	}?>" class="text ui-widget-content ui-corner-all required phone" />

		<div style="margin-bottom:12px">
			<input type="checkbox" name="is_public" id="is_public" style="display: inline" <?php if (isset($contact) && $contact->row('is_public') == 'true')
	{
		echo 'checked="checked"';
	}?> />
			<label for="is_public" style="display: inline"><?php echo tr('Set as public contact');?></label>
		</div>

		<label for="groups"><?php echo tr('Groups'); ?></label>
		<?php if (isset($contact)): ?>
		<input name="groups" id="groups" value="<?php echo $this->Phonebook_model->get_groups($contact->row('id_pbk'), $this->session->userdata('id_user'))->GroupNames?>" type="text" />
		<?php elseif ( ! empty($group_id)):?>
		<input name="groups" id="groups" value="<?php echo $this->Phonebook_model->group_name($group_id, $this->session->userdata('id_user'))?>" type="text" />
		<?php else : ?>
		<input name="groups" id="groups" value="" type="text" />
		<?php endif;?>

		<?php if (isset($contact)): ?>
		<input type="hidden" name="editid_pbk" id="editid_pbk" value="<?php echo $contact->row('id_pbk');?>" />
		<?php endif;?>
	</fieldset>
	<?php echo form_close();?>
</div>
