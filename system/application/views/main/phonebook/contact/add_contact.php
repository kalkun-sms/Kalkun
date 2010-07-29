<!-- Contact dialog -->	
<div id="dialog" class="dialog" style="display: block">
<p id="validateTips">All form fields are required.</p>
<?php echo form_open('phonebok/add_contact_process', array('id' => 'addContact'));?>
<fieldset>
<input type="hidden" name="pbk_id_user" id="pbk_id_user" value="<?php echo $this->session->userdata('id_user');?>" />
<label for="name">Name</label>
<input type="text" name="name" id="name" value="<?php if(isset($contact)) echo $contact->row('Name');?>" class="text ui-widget-content ui-corner-all" />
<label for="number">Phone Number</label>
<input type="text" name="number" id="number" value="<?php if(isset($contact)) echo $contact->row('Number'); else if(isset($number)) echo $number;?>" class="text ui-widget-content ui-corner-all" />
<label for="group">Group</label>
<?php
$group = $this->Phonebook_model->getPhonebook(array('option' => 'group'));
foreach($group->result() as $tmp):
	$groups[$tmp->ID]=$tmp->GroupName;
endforeach; 
$group_act = (isset($contact)) ? $contact->row('ID') : '';
$option = 'class="text ui-widget-content ui-corner-all"';
echo form_dropdown('groupvalue', $groups, $group_act, $option);
if(isset($contact)): ?> 
<input type="hidden" name="editid_pbk" id="editid_pbk" value="<?php echo $contact->row('id_pbk');?>" />
<?php endif;?>
</fieldset>
<?php echo form_close();?>
</div>
