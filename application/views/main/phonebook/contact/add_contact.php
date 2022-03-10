<!-- Contact dialog -->
<link rel="stylesheet" type="text/css" href="<?php echo $this->config->item('css_path');?>jquery-plugin/jquery.tagsinput-revisited.min.css" />
<script type="text/javascript">
	$.when(
	$.cachedScript("<?php echo $this->config->item('js_path');?>jquery-plugin/jquery.validate.min.js"),
	$.cachedScript("<?php echo $this->config->item('js_path');?>jquery-plugin/jquery.tagsinput-revisited.min.js"),
	$.Deferred(function(deferred) {
		$(deferred.resolve);
	})
	).done(function() {

		<?php
	$group = $this->Phonebook_model->get_phonebook(array('option' => 'group'));
	$grouptext_array = [];
	foreach ($group->result() as $tmp):
		array_push($grouptext_array, $tmp->GroupName);
	endforeach;
	?>
		var all_grp = <?php echo json_encode($grouptext_array); ?>;

		function removeFromArray(orig_data, selection) {
			var source = orig_data.slice(0);
			selection.forEach(function(value) {
				var index = source.indexOf(value);
				if (index !== -1) {
					source.splice(index, 1);
				}
			});
			return source;
		}

		$('#groups').tagsInput({
			'autocomplete': {
				source: removeFromArray(all_grp, $('#groups').val().split(",")),
				minLength: 0,
				delay: 0,
				autoFocus: true,
				position: {
					my: "right bottom",
					at: "left top"
				},
			},
			'minChars': 0,
			'interactive': true,
			'delimiter': ',',
			'placeholder': '<?php echo tr_addcslashes('"', 'Type group name');?>',
			"onAddTag": function(element, tag) {
				source = removeFromArray(all_grp, element.value.split(","));
				$('#groups_tag').autocomplete("option", "source", source);
				setTimeout(function() {
					// Workaround for autocomplete("search") to work without throwing
					// an exception is to delay it slightly
					$('#groups_tag').autocomplete("search");
				}, 1);

			},
			"onRemoveTag": function(element, tag) {
				source = removeFromArray(all_grp, element.value.split(","));
				$('#groups_tag').autocomplete("option", "source", source);
				$('#groups_tag').autocomplete("search");
			},
		});
		$('#groups_tag').on("focus", function() {
			$(this).autocomplete("search");
		});
		$('#groups_tag').on("click", function() {
			$(this).autocomplete("search");
		});

		$('#addContact').validate();

		jQuery.validator.classRuleSettings.phone = {
			remote: {
				url: "<?php echo site_url('kalkun/phone_number_validation'); ?>",
				type: "get",
				data: {
					phone: function() {
						return $("#number").val();
					}
				}
			};
		});
	});

</script>

<div id="dialog" class="dialog" style="display: block">
	<p id="validateTips"><?php echo tr('All form fields are required.'); ?></p>
	<?php echo form_open('phonebook/add_contact_process', array('id' => 'addContact'));?>
	<fieldset>
		<input type="hidden" name="pbk_id_user" id="pbk_id_user" value="<?php echo $this->session->userdata('id_user');?>" />
		<label for="name"><?php echo tr('Name'); ?></label>
		<input type="text" name="name" id="name" value="<?php if (isset($contact))
	{
		echo htmlentities($contact->row('Name'), ENT_QUOTES);
	}?>" class="text ui-widget-content ui-corner-all required" />
		<label for="number"><?php echo tr('Telephone number'); ?></label>
		<input type="text" name="number" id="number" value="<?php if (isset($contact))
	{
		echo htmlentities($contact->row('Number'), ENT_QUOTES);
	}
	else
	{
		if (isset($number))
		{
			echo htmlentities($number, ENT_QUOTES);
		}
	}?>" class="text ui-widget-content ui-corner-all required phone" />

		<div style="margin-bottom:12px">
			<input type="checkbox" name="is_public" id="is_public_contact" style="display: inline" <?php if (isset($contact) && $contact->row('is_public') == 'true')
	{
		echo 'checked="checked"';
	}?> />
			<label for="is_public_contact" style="display: inline"><?php echo tr('Set as public contact');?></label>
		</div>

		<label for="groups"><?php echo tr('Groups'); ?></label>
		<?php if (isset($contact)): ?>
		<input name="groups" id="groups" value="<?php echo htmlentities($this->Phonebook_model->get_groups($contact->row('id_pbk'), $this->session->userdata('id_user'))->GroupNames, ENT_QUOTES); ?>" type="text" />
		<?php elseif ( ! empty($group_id)):?>
		<input name="groups" id="groups" value="<?php echo htmlentities($this->Phonebook_model->group_name($group_id, $this->session->userdata('id_user')), ENT_QUOTES); ?>" type="text" />
		<?php else : ?>
		<input name="groups" id="groups" value="" type="text" />
		<?php endif;?>

		<?php if (isset($contact)): ?>
		<input type="hidden" name="editid_pbk" id="editid_pbk" value="<?php echo $contact->row('id_pbk');?>" />
		<?php endif;?>
	</fieldset>
	<?php echo form_close();?>
</div>
