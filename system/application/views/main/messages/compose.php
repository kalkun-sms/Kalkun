<link type="text/css" rel="stylesheet" href="<?php echo $this->config->item('css_path');?>jquery-plugin/token-input-facebook.css" />
<script language="javascript" src="<?php echo $this->config->item('js_path');?>jquery-ui/ui.datepicker.min.js"></script>
<script language="javascript" src="<?php echo $this->config->item('js_path');?>jquery-plugin/jquery.validate.min.js"></script>
<script language="javascript" src="<?php echo $this->config->item('js_path');?>jquery-plugin/jquery.tokeninput.min.js"></script>
<style type="text/css">
.ui-datepicker {z-index:10100;}
.left_aligned { margin-left:0; padding-left:0;}
.form_option { width: 100px;}
</style>
<?php $this->load->view('js_init/message/js_compose'); ?>

<?php echo form_open('messages/compose', array('id' => 'composeForm', 'class' => 'composeForm'));?>
<table width="100%">
<?php  
$type = array('inbox', 'sentitems');

// Reply to option
if($val_type=='reply'): ?>
<tr>
<td width="100px" align="right" class="form_label label">To:</td>
<td>
<?php
$phone = base64_decode(HexToAscii($dest));
$qry = $this->Phonebook_model->getPhonebook(array('option' => 'bynumber', 'number' => $phone));
if($qry->num_rows()!=0): 
echo $qry->row('Name')." <".$phone.">";
else:
echo $phone;
endif;
?>
<input type="hidden" name="sendoption" value="reply" />
<input type="hidden" name="reply_value" value="<?php echo $phone;?>" />
</td>
</tr>

<?php /* Member */ elseif($val_type=='member'):?>
<tr>
<td width="100px" align="right" class="form_label label">To:</td>
<td>Member<input type="hidden" name="sendoption" value="member" /></td>    
</tr>

<?php /* Phonebook contact */ elseif($val_type=='pbk_contact'):?>
<tr>
<td width="100px" align="right" class="form_label label">To:</td>
<td>
<?php
$qry = $this->Phonebook_model->getPhonebook(array('option' => 'bynumber', 'number' => $dest));
if($qry->num_rows()!=0): 
echo $qry->row('Name')." <".$dest.">";
else:
echo $dest;
endif;
?>
<input type="hidden" name="sendoption" value="reply" />
<input type="hidden" name="reply_value" value="<?php echo $dest;?>" />
</td>    
</tr>

<?php /* Phonebook group */ elseif($val_type=='pbk_groups'):?>
<tr>
<td width="100px" align="right" class="form_label label">To:</td>
<td>
<?php echo $this->Phonebook_model->getPhonebook(array('option' => 'groupname', 'id' => $dest))->row('GroupName');?>
<input type="hidden" name="sendoption" value="pbk_groups" />
<input type="hidden" name="id_pbk" value="<?php echo $dest;?>" />
</td>    
</tr>
    	
<?php /* Forward to option */ else: ?>
<tr>
<td width="100px" align="right" class="label">
<?php
if($val_type=='forward') echo lang('kalkun_forward_to').":";
else echo lang('kalkun_send_to').":";
?>
</td>
<td>
<span class="form_option">
<input type="radio" id="sendoption1" name="sendoption" value="sendoption1" checked="checked" class="left_aligned"  />
<label for="sendoption1"><?php echo lang('kalkun_contact');?></label>
</span>
<input type="radio" id="sendoption2" name="sendoption" value="sendoption2" />
<label for="sendoption2"><?php echo lang('kalkun_group');?> </label>
<input type="radio" id="sendoption3" name="sendoption" value="sendoption3" />
<label for="sendoption3"><?php echo lang('kalkun_input_manually');?> </label>
</td>    
</tr>
    
<tr>
<td>&nbsp;</td>
<td>
<div id="person">
<textarea id="personvalue" style="width: 95%;" name="personvalue" /></textarea>
</div>

<div id="manually" class="hidden">
<input style="width: 95%;" type="text" name="manualvalue" />
</div>

<div id="group" class="hidden">
<select name="groupvalue">
<option value="" selected="selected">-- Select Group --</option>
<?php
foreach($this->Phonebook_model->getPhonebook(array('option' => 'group'))->result() as $tmp):
echo "<option value=\"".$tmp->ID."\">".$tmp->GroupName."</option>";
endforeach; 
?>
</select>
</div>
</td>
</tr>
<?php endif; ?>

<tr>
<td align="right" class="label"><?php echo lang('kalkun_send_date').":";?></td>
<td>
<input class="left_aligned" type="radio" id="option1" name="senddateoption" value="option1" checked="checked" />
<label for="option1"><?php  echo lang('kalkun_now');?></label>
<input type="radio" id="option2" name="senddateoption" value="option2" />
<label for="option2"><?php  echo lang('kalkun_at_date_time');?></label>
<input type="radio" id="option3" name="senddateoption" value="option3" />
<label for="option3"><?php  echo lang('kalkun_after_a_delay');?></label>
</td>    
</tr>
    
<tr>
<td>&nbsp;</td>
<td>
<div id="nowoption">
</div>
<div id="dateoption" class="hidden">
<input type="text" name="datevalue" id="datevalue" class="datepicker" readonly="readonly" />
<?php echo nbs(2);?>
<select name="hour"><?php echo get_hour();?></select> :
<select name="minute"><?php echo get_minute();?></select> 			
</div>
<div id="delayoption" class="hidden">
<select name="delayhour"><?php echo get_hour();?></select> Hour&nbsp;
<select name="delayminute"><?php echo get_minute();?></select> Minutes 
</div>
</td>
</tr>    

<tr>
<td align="right" class="label"><?php echo lang('kalkun_sms_mode').":";?></td>
<td><input type="checkbox" id="sms_mode" name="sms_mode" value="1" class="left_aligned" />
<label for="sms_mode">Send as Flash SMS</label>
</td>    
</tr>    
   
<?php if($this->config->item('sms_bomber')): ?>    
<tr valign="top">
<td align="right" class="label">Amount:</td>
<td><input type="text" style="width: 25px" name="sms_loop" id="sms_loop" value="1" />&nbsp; times
</td>
</tr>
<?php else: ?>
<input type="hidden" name="sms_loop" id="sms_loop" value="1" />
<?php endif;?>

<tr valign="top">
<td align="right" class="label"><?php echo lang('kalkun_message').":";?></td>
<td>
<textarea class="word_count" style="width: 400px; height: 100px" id="message" name="message">
<?php 
if($val_type=='forward') echo $message;
list($sig_option, $sig)=explode(';',$this->Kalkun_model->getSetting()->row('signature'));
if($sig_option=='true') echo "\n\n".$sig; ?>
</textarea><br /><span class="counter"></span>
</td>
</tr>      
</table>
<br />
<?php  echo form_close();?>
