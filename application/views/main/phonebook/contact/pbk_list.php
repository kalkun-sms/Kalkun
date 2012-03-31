<?php //$this->load->view('js_init/phonebook/js_phonebook');
if($phonebook->num_rows()==0):
	if($_POST) echo "<p><i>".lang('tni_contact_not_found')."</i></p>";
	else echo "<p><i>".lang('tni_contact_search_empty')."</i></p>";
else: ?>
<table>
<?php foreach($phonebook->result() as $tmp): ?>
<tr id="<?php echo $tmp->id_pbk;?>">
<td>
<div class="two_column_container contact_list" style="display: inline-block;">
	<div class="left_column">
		<div id="pbkname">
		<input type="checkbox" class="select_contact" />&nbsp;<span style="font-weight: bold;"><?php echo $tmp->Name  ;?></span>
		<div id="pbknumber" style="padding: 2px 0 5px 24px;"><?php echo $tmp->Number;?></div>
		</div>	
	</div>
	<div class="right_column">
		<span class="pbk_menu hidden">
		<?php
		// hook for contact menu
		$menu = do_action("phonebook.contact.menu", $tmp);
		if($menu != $tmp)
		{
			echo "<a class=\"simplelink\" href=\"{$menu['url']}\">{$menu['title']}</a>&nbsp;";
			echo "<img src=\"{$this->config->item('img_path')}circle.gif\" />";
		}
		?>
		<?php if(isset($public_contact) && !$public_contact):?>
		<a class="editpbkcontact simplelink" href="#"><?php echo lang('tni_edit');?></a>
		<img src="<?php echo $this->config->item('img_path')?>circle.gif" />
		<?php endif;?>
		<a class="sendmessage simplelink" href="#"><?php echo lang('tni_send_message');?></a>
		<img src="<?php echo $this->config->item('img_path')?>circle.gif" />
		<?php echo anchor('messages/conversation/folder/phonebook/'.$tmp->Number,lang('tni_see_conversation'), 'title="'.lang('tni_see_conversation').'" class="simplelink"') ;?>
		</span>
	</div>
</div>	
</td></tr>
<?php endforeach;?>
</table>
<?php endif; ?>
