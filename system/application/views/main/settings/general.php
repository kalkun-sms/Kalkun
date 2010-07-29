<table width="100%" cellpadding="5">
<tr valign="top">
<td width="175px">Language</td>
<td>
<?php 
$lang = array('bahasa'	=> 'Bahasa Indonesia',
              'english'	=> 'English'); 
$lang_act = $this->Kalkun_model->getSetting()->row('language'); 
echo form_dropdown('language', $lang, $lang_act);       
?>
</td>
</tr>     

<tr valign="top">
<td>Conversation sort</td>
<td>
<?php 
$conv = array('asc' => 'Oldest First', 'desc' => 'Newest First'); 
$conv_act = $this->Kalkun_model->getSetting()->row('conversation_sort'); 
echo form_dropdown('conversation_sort', $conv, $conv_act);  
?>
</td>  
</tr>

<tr valign="top">
<td>Data per page</td>
<td>
<?php 
$paging = array('10' => '10', '15' => '15', '20' => '20', '25' => '25'); 
$paging_act = $this->Kalkun_model->getSetting()->row('paging'); 
echo form_dropdown('paging', $paging, $paging_act);  
?>
<small> - Will be used for paging in message and phonebook</small>
</td>  
</tr>
	
<tr valign="top">
<td>Permanent delete</td>
<td>
<?php $permanent_act = $this->Kalkun_model->getSetting()->row('permanent_delete');?>
<input type="radio" id="permanent_delete_false" name="permanent_delete" value="false" 
<?php if($permanent_act=='false') echo "checked=\"checked\""; ?> /> 
<label for="permanent_delete_false">Permanent delete Off</label> <small> - Always move to Trash first</small><br />
<input type="radio" id="permanent_delete_true" name="permanent_delete" value="true"
<?php if($permanent_act=='true') echo "checked=\"checked\""; ?>/> 
<label for="permanent_delete_true">Permanent delete On</label>
</td>
</tr>    
			
<tr valign="top">
<td>Delivery Report</td>
<td>
<?php 
$report = array('default' => 'Default', 'yes' => 'Yes', 'no' => 'No'); 
$report_act = $this->Kalkun_model->getSetting()->row('delivery_report'); 
echo form_dropdown('delivery_report', $report, $report_act);  
?>			
</td>  
</tr>      
</table>
<input type="hidden" name="option" value="general" /> 