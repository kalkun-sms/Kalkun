<table width="100%" cellpadding="5">
<tr valign="top">
<td width="175px"><?php echo lang('tni_language'); ?></td>
<td>
<?php 
$lang = array('czech'	=> 'Česky',
       		'danish'	=> 'Danish',
       		'dutch'		=> 'Dutch',
       		'english'	=> 'English',
       		'finnish'	=> 'Finnish',
       		'french'	=> 'French',
       		'german'	=> 'German',
       		'indonesian'	=> 'Indonesian',
       		'italian'	=> 'Italian',
       		'polish'        => 'Polski',
       		'portuguese'	=> 'Portuguese',
       		'russian'	=> 'Russian',
            'spanish'       => 'Español',
            'slovak'       => 'Slovak',
       		'turkish'	=> 'Turkish',
      		); 
$lang_act = $this->Kalkun_model->get_setting()->row('language'); 
echo form_dropdown('language', $lang, $lang_act);       
?>
</td>
</tr>  

<tr valign="top">
<td>Country dial code</td>
<td>
<?php 
$dial_code = getCountryDialCode(); 
$dial_code_act = $this->Kalkun_model->get_setting()->row('country_code'); 
echo form_dropdown('dial_code', $dial_code, $dial_code_act);
?>
</td>
</tr>    

<tr valign="top">
<td><?php echo lang('tni_set_conv_sort'); ?></td>
<td>
<?php 
$conv = array('asc' => lang('tni_set_conv_order_old'), 'desc' => lang('tni_set_conv_order_new')); 
$conv_act = $this->Kalkun_model->get_setting()->row('conversation_sort'); 
echo form_dropdown('conversation_sort', $conv, $conv_act);  
?>
</td>  
</tr>

<tr valign="top">
<td><?php echo lang('tni_set_data_pp'); ?></td>
<td>
<?php 
$paging = array('10' => '10', '15' => '15', '20' => '20', '25' => '25'); 
$paging_act = $this->Kalkun_model->get_setting()->row('paging'); 
echo form_dropdown('paging', $paging, $paging_act);  
?>
<small>&nbsp;&nbsp;<?php echo lang('tni_set_data_pp_hint'); ?></small>
</td>  
</tr>
	
<tr valign="top">
<td><?php echo lang('tni_set_perm_del'); ?></td>
<td>
<?php $permanent_act = $this->Kalkun_model->get_setting()->row('permanent_delete');?>
<input type="radio" id="permanent_delete_false" name="permanent_delete" value="false" 
<?php if($permanent_act=='false') echo "checked=\"checked\""; ?> /> 
<label for="permanent_delete_false"><?php echo lang('tni_set_perm_deloff'); ?></label> <small><?php echo lang('tni_set_perm_deloff_hint'); ?></small><br />
<input type="radio" id="permanent_delete_true" name="permanent_delete" value="true"
<?php if($permanent_act=='true') echo "checked=\"checked\""; ?>/> 
<label for="permanent_delete_true"><?php echo lang('tni_set_perm_delon'); ?></label>
</td>
</tr>    
			
<tr valign="top">
<td><?php echo lang('tni_set_deliv_report'); ?></td>
<td>
<?php 
$report = array('default' => lang('tni_default'), 'yes' => lang('tni_yes'), 'no' => lang('tni_no'));
$report_act = $this->Kalkun_model->get_setting()->row('delivery_report'); 
echo form_dropdown('delivery_report', $report, $report_act);  
?>			
</td>  
</tr>
   
</table>
<input type="hidden" name="option" value="general" /> 
