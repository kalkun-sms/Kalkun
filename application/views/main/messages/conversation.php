<div id="contact_container" class="hidden"></div>
<?php
if(count($messages)==0) 
{
	if($this->uri->segment(2)=='my_folder') echo "<p style=\"padding-left: 10px\"><span class=\"ui-icon ui-icon-alert\" style=\"float:left;\"></span><i>".lang('kalkun_no_message_in_folder').".</i></p>";
    else if($this->uri->segment(2)=='search') echo "<p style=\"padding-left: 10px\"><span class=\"ui-icon ui-icon-alert\" style=\"float:left;\"></span><i>".lang('kalkun_no_message_search').".</i></p>";
	else echo "<p style=\"padding-left: 10px\"><span class=\"ui-icon ui-icon-alert\" style=\"float:left;\"></span><i>".lang('kalkun_no_message')." ".lang('kalkun_'.$this->uri->segment(3)).".</i></p>";
}
else 
{	
	// loop - begin
	foreach($messages as $tmp):

	// initialization
	$type = $this->uri->segment(4);
	if($tmp['source'] == 'inbox') 
	{
		$qry = $this->Phonebook_model->get_phonebook(array('option'=>'bynumber','number'=>$tmp['SenderNumber']));
		if($qry->num_rows()!=0) { $senderName = $qry->row('Name'); $on_pbk=TRUE;}
		else { $senderName = $tmp['SenderNumber']; $on_pbk=FALSE;}
		
		$message_date = $tmp['ReceivingDateTime'];
		$number = $tmp['SenderNumber'];
		$arrow = 'arrow_left';
	}
	else 
	{
		$qry = $this->Phonebook_model->get_phonebook(array('option'=>'bynumber','number'=>$tmp['DestinationNumber']));
		if($qry->num_rows()!=0) { $senderName = $qry->row('Name'); $on_pbk=TRUE;}
		else { $senderName = $tmp['DestinationNumber']; $on_pbk=FALSE;}
		
		$message_date = $tmp['SendingDateTime'];
		$number = $tmp['DestinationNumber'];
		if($type == 'outbox') $arrow = 'circle';
		else $arrow = 'arrow_right';
	}
		
	// count string for message preview
	$char_per_line = 100-strlen(nice_date($message_date))-strlen($senderName);
?>
		
<div class="messagelist conversation messagelist_conversation" >
<div class="message_container <?php echo $tmp['source'];?>">
	<div class="message_header" style="color: #444; height: 20px; overflow: hidden">
    <input  type="hidden" name="item_source<?php echo $tmp['ID'];?>"  id="item_source<?php echo $tmp['ID'];?>"  value="<?php echo $tmp['source']; ?>" />
    <input  type="hidden" class="item_number" name="item_number<?php echo $tmp['ID'];?>"  id="item_number<?php echo $tmp['ID'];?>"  value="<?php echo $number; ?>" />
	<input type="checkbox" id="<?php echo $tmp['ID'];?>" class="select_message nicecheckbox" value="<?php echo $tmp['ID'];?>" style="border: none;" />
	<span class="message_toggle" style="cursor: pointer">
	<span <?php  if($tmp['source'] == 'inbox' && $tmp['readed']=='false') echo "style=\"font-weight: bold\"";?>><?php echo nice_date($message_date);?>&nbsp;&nbsp;<img src="<?php echo $this->config->item('img_path').$arrow;?>.gif" />
	&nbsp;&nbsp;<?php echo $senderName;?></span>
	<span class="message_preview">-&nbsp;<?php echo message_preview($tmp['TextDecoded'], $char_per_line);?></span>
	</span>
	</div>

			
<?php
if($tmp['source'] == 'sentitems'):
	// check delivery status
	$status = check_delivery_report($tmp['Status']);
	$part_no = 1;
	//check multipart
	$multipart['type'] = 'sentitems';
	$multipart['option'] = 'check';
	$multipart['id_message'] = $tmp['ID'];
	if($this->Message_model->get_multipart($multipart)!=0):
		$multipart['option'] = 'all';
		foreach($this->Message_model->get_multipart($multipart)->result() as $part):
		$tmp['TextDecoded'].=$part->TextDecoded;
		$part_no++;
		endforeach;			
	endif;
elseif($tmp['source'] == 'outbox'): 
	//check multipart
	$multipart['type'] = 'outbox';
	$multipart['option'] = 'check';
	$multipart['id_message'] = $tmp['ID'];	
	if($this->Message_model->get_multipart($multipart)=='true'):
		$part_no = 1;
		$multipart['option'] = 'all';
		foreach($this->Message_model->get_multipart($multipart)->result_array() as $part):
		$tmp['TextDecoded'].=$part['TextDecoded'];
		$part_no++;
		endforeach;
	endif;
elseif($tmp['source'] == 'inbox'):
	$part_no = 1;
	// check multipart
	if(!empty($tmp['UDH'])):	
		$multipart['type'] = 'inbox';
		$multipart['option'] = 'all';
		$multipart['udh'] = substr($tmp['UDH'],0,8);
		$multipart['phone_number'] = $tmp['SenderNumber'];
		foreach($this->Message_model->get_multipart($multipart)->result_array() as $part):
		$tmp['TextDecoded'].=$part['TextDecoded'];
		$part_no++;
		endforeach;
	endif;			
endif;				
?>	
		
	<div class="detail_area hidden <?php echo $number;?>" >
	<table cellspacing="0" cellpadding="0" border="0">
    <tr>
    <td width="50px"><?php  if($tmp['source']=='inbox') echo lang('tni_from'); else echo lang('tni_to'); ?></td>
    <td width="10px"> : </td><td><?php echo $number;?></td>
    </tr>
   
    <?php  if($tmp['source'] == 'outbox'): ?>
    <tr><td><?php echo lang('tni_inserted');?></td><td> : </td><td><?php echo simple_date($tmp['InsertIntoDB']);?></td></tr>
    <?php  endif; ?>			    

    <tr><td><?php echo lang('tni_date');?></td><td> : </td><td><?php echo simple_date($message_date);?></td></tr>			    
    
    <?php if($tmp['source'] != 'outbox'): ?>
    <tr><td><?php echo lang('kalkun_smsc'); ?></td><td> : </td><td><?php echo $tmp['SMSCNumber'];?></td></tr>
    <?php endif; ?>
    
    <?php if($tmp['source'] == 'sentitems' OR $tmp['source'] == 'inbox'): ?>
    <?php if($part_no > 1): ?>
    <tr><td><?php echo lang('kalkun_sms_part'); ?></td><td> : </td><td><?php echo $part_no;?> <?php echo lang('kalkun_sms_part_suffix'); ?></td></tr>
    <?php endif;?>
    <?php endif;?>
    <?php if($tmp['source'] == 'sentitems'): ?>		    
    <tr><td><?php echo lang('tni_status');?></td><td> : </td><td><?php echo $status;?></td></tr>
    <?php endif;?>
	</table>
	</div>			
		
	<?php echo "<div class=\"message_content hidden\" style=\"padding: 5px 10px 5px 20px\">".showmsg($tmp['TextDecoded'])."</div>";?>		
		
	<div class="optionmenu hidden" style="padding-left: 20px">
	<ul>
	<li><a class="detail_button" href="#"><?php echo lang('tni_show_details'); ?></a></li>
					
	<?php if($tmp['source'] == 'inbox'): ?>
	<li><img src="<?php echo $this->config->item('img_path');?>circle.gif" /></li>
	<li><a href="#" class="reply_button"><?php echo lang('kalkun_reply');?></a></li>				
	<?php endif; ?>
		
	<?php if($type!='outbox'): ?>
	<li><img src="<?php echo $this->config->item('img_path');?>circle.gif" /></li>
	<li><a href="#" class="forward_button"><?php echo lang('kalkun_forward');?></a></li>
	<?php endif; ?>
	
	<?php if(!$on_pbk): ?>
	<li><img src="<?php echo $this->config->item('img_path');?>circle.gif" /></li>
	<li><a href="#" class="add_to_pbk"><?php echo lang('tni_contact_add'); ?></a></li>	
	<?php endif; ?>
	
	<?php if($tmp['source'] == 'sentitems'): ?>
	<li><img src="<?php echo $this->config->item('img_path');?>circle.gif" /></li>
	<li><a href="#" class="resend"><?php echo lang('kalkun_resend');?></a></li>		
	<?php endif; ?>
	</ul>
	</div>
	
	<div class="message_metadata hidden">
		<span class="coding"><?php echo $tmp['Coding'];?></span>
		<span class="class"><?php echo $tmp['Class'];?></span> 
	</div>
	
	</div></div>
		
<?php 
	if($tmp['source']=='inbox') if($tmp['readed'] == 'false') $this->Message_model->update_read($tmp['ID']);
	endforeach;
} 
?>