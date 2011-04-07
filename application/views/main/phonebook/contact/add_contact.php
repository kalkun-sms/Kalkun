<!-- Contact dialog -->
<script src="<?php echo $this->config->item('js_path');?>jquery-plugin/jquery.tagsinput.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo $this->config->item('css_path');?>jquery-plugin/jquery.tagsinput.css" />
<script src="<?php echo $this->config->item('js_path');?>jquery-plugin/jquery.autocomplete.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo $this->config->item('css_path');?>jquery-plugin/jquery.autocomplete.css" />
<script type="text/javascript">
$(document).ready(function() {
    <?php
    $group = $this->Phonebook_model->get_phonebook(array('option' => 'group'));
    $grouptext = '';
    foreach($group->result() as $tmp):
    	$grouptext .= $tmp->GroupName.';';
    endforeach; 
    $grouptext = substr($grouptext,0, strlen($grouptext)-1);
    ?>
    var data = "<?=$grouptext?>".split(";");
    $('#groups').tagsInput({
        'autocomplete_url' : data,
        'autocomplete':{matchContains:false},
        'height':'50px',
        'width':'270px',
       'defaultText':'Add Group'
    });
});
</script>

<div id="dialog" class="dialog" style="display: block">
<p id="validateTips"><?php echo lang('tni_form_fields_required'); ?></p>
<?php echo form_open('phonebok/add_contact_process', array('id' => 'addContact'));?>
<fieldset>
<input type="hidden" name="pbk_id_user" id="pbk_id_user" value="<?php echo $this->session->userdata('id_user');?>" />
<label for="name"><?php echo lang('tni_contact_name'); ?></label>
<input type="text" name="name" id="name" value="<?php if(isset($contact)) echo $contact->row('Name');?>" class="text ui-widget-content ui-corner-all" />
<label for="number"><?php echo lang('tni_contact_phonenumber'); ?></label>
<input type="text" name="number" id="number" value="<?php if(isset($contact)) echo $contact->row('Number'); else if(isset($number)) echo $number;?>" class="text ui-widget-content ui-corner-all" />
<label for="group"><?php echo lang('kalkun_group'); ?></label> 
<input name="groups" id="groups" value="<?=$this->Phonebook_model->get_groups($contact->row('id_pbk'),$this->session->userdata('id_user'))->GroupNames?>"  />
<?php
//$group = $this->Phonebook_model->get_phonebook(array('option' => 'group'));
//foreach($group->result() as $tmp):
//	$groups[$tmp->ID]=$tmp->GroupName;
//endforeach; 
//$group_act = (isset($contact)) ? $contact->row('ID') : '';
//$option = 'class="text ui-widget-content ui-corner-all"';
//echo form_dropdown('groupvalue', $groups, $group_act, $option);
?>
<?php if(isset($contact)): ?> 
<input type="hidden" name="editid_pbk" id="editid_pbk" value="<?php echo $contact->row('id_pbk');?>" />
<?php endif;?>
</fieldset>
<?php echo form_close();?>
</div>
