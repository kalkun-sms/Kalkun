
<!-- About dialog -->
<div id="about" title="About Kalkun" class="dialog">
	<div class="mascot" style="float: left;">
	<img src="<?php echo $this->config->item('img_path');?>mascot.png" />
	</div>

	<div class="detail" style="float: left">
	<center>
	<div class="base_bg rounded"><img src="<?php echo $this->config->item('img_path');?>logo.png" /></div>
	<h1>PHP Frontend for gammu-smsd</h1>
	</center>
	<table>
		<tr valign="top"><td><b>Author:</b></td><td>&nbsp;</td><td>See CREDITS page</td></tr>
		<tr><td><b>Version:</b></td><td>&nbsp;</td><td><?php echo $this->config->item('kalkun_version')." (".$this->config->item('kalkun_codename').")";?></td></tr>		
		<tr><td><b>Released:</b></td><td>&nbsp;</td><td><?php echo $this->config->item('kalkun_release_date');?></td>
		<tr><td><b>License:</b></td><td>&nbsp;</td><td>GNU/GPL</td>		
		<tr><td><b>Homepage:</b></td><td>&nbsp;</td>
		<td><a class="base_color underline_link" href="http://kalkun.sourceforge.net" target="_blank">http://kalkun.sourceforge.net</a></td>	
		</tr>				
	</table>
	<br />
	<center>
	<a class="underline_link" href="http://kalkun.sourceforge.net/contribute.php"><b>~ DONATE THIS PROJECT ~</b></a>
	</center>
	</div>
</div>
		
<!-- Add Folder Dialog -->
<div id="addfolderdialog" title="<?php echo lang('kalkun_add_folder');?>" class="dialog">
	<form class="addfolderform" method="post" action="<?php echo  site_url();?>/kalkun/add_folder">
		<label for="name"><?php echo lang('kalkun_folder_name');?></label>
		<input type="hidden" name="source_url" value="<?php echo $this->uri->uri_string();?>" />
		<input type="hidden" name="id_user" value="<?php echo $this->session->userdata('id_user');?>" />
		<input type="text" name="folder_name" id="folder_name" class="text ui-widget-content ui-corner-all" />
	</form>
</div>

<!-- Shortcuts dialog -->
<div id="kbd" title="Kalkun Keyboard Shortcuts" class="dialog">
	 

	<div class="detail" style="float: left">
	<center>
 	<h1>Keyboard Shortcuts</h1>
	</center>
	
    <table>
    <tr valign="top">
    	<td>
        
        <table>
        <tr>
        	<td colspan="2" align="center"><strong> Jumping</strong> </td>
        </tr>
        <tr>
        	<td class="align_right">g then i :</td>
        	<td>Goto Inbox</td>
        </tr>
        <tr>
        	<td class="align_right">g then o :</td>
        	<td>Goto Outbox</td>
        </tr>
        <tr>
        	<td class="align_right">g then s :</td>
        	<td>Goto Sent Items</td>
        </tr>
        <tr>
        	<td class="align_right">g then p :</td>
        	<td>Goto Phonebook</td>
        </tr>
        
        <tr>
        <td colspan="2" align="center"><br /><strong> Navigation</strong></td>
        </tr>
        <tr>
        	<td class="align_right">u :</td>
        	<td>Back to threadlist</td>
        </tr>
        <tr>
        	<td class="align_right">k / j :</td>
        	<td>Newer/older conversation</td>
        </tr>
         <tr>
        	<td class="align_right">p / n :</td>
        	<td>Read Prev/Next conversation</td>
        </tr>
        <tr>
        	<td class="align_right">o or &lt;Enter&gt; :</td>
        	<td>Open conversation; <br/>collapse/expand conversation</td>
        </tr>
	  <tr>
        <td colspan="2" align="center"><br /><strong>	Threadlist selection</strong></td>
        </tr>
<tr>
        	<td class="align_right">* then a :</td>
        	<td>Select all conversations</td>
        </tr><tr>
        	<td class="align_right">* then n :</td>
        	<td>Deselect all conversations</td>
        </tr>
	
	
	
        </table>
         
        </td>
        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
        
    	<td >
        <table>
        <tr>
        	<td colspan="2" align="center"><strong>Actions</strong></td>
        </tr>
        
        <tr>
        	<td class="align_right">x :</td>
        	<td>Select Conversation</td>
        </tr>
        <tr>
        	<td class="align_right">m :</td>
        	<td>Move To</td>
        </tr>
         <tr>
        	<td class="align_right">r :</td>
        	<td>Reply</td>
        </tr>
        <tr>
        	<td class="align_right">f :</td>
        	<td>Forward</td>
        </tr>
        <tr>
        	<td class="align_right">&lt;Tab&gt; then &lt;Enter&gt; :</td>
        	<td><?php echo lang('tni_send_message'); ?></td>
        </tr>
        <tr>
        	<td class="align_right">d :</td>
        	<td>Show message details</td>
        </tr>
          
        
        <tr>
        	<td class="align_right"># :</td>
        	<td>Delete</td>
        </tr>
        <tr><td colspan="2" align="center">	<br /><strong>Application</strong>
 </td>
        </tr>
        <tr>
        	<td class="align_right">c :</td>
        	<td>Compose</td>
        </tr>
        <tr>
        	<td class="align_right">s :</td>
        	<td>Search</td>
        </tr>
        <tr>
        	<td class="align_right">? :</td>
        	<td>Open shortcut help</td>
        </tr>
        <tr>
        	<td></td>
        	<td></td>
        </tr>
        </table>
        
        </td>
    </tr>
    </table>
    
	<br />
 
	</div>
</div>

<!-- Advanced Search Dialog -->
<div id="a_search_dialog" title="<?php echo lang('kalkun_advanced_search');?>" class="dialog">
	<?php
	$this->load->helper('form');
	echo form_open("messages/query", array('id' => 'a_search_form'));
	echo form_hidden('a_search_trigger', TRUE);
	?>
	<table width="100%">
		<tr>
			<td align="right"><label for="a_search_from_to"><b><?php echo lang('kalkun_advanced_search_from_to');?></b></label></td>
			<td colspan="3"><input style="width: 95%" type="text" id="a_search_from_to" name="a_search_from_to" /></td>
		</tr>	
		<tr>
			<td align="right"><label for="a_search_query"><b><?php echo lang('kalkun_advanced_search_query');?></b></label></td>
			<td colspan="3"><input style="width: 95%" type="text" id="a_search_query" name="a_search_query" /></td>
		</tr>		
		<tr>
			<td align="right"><label for="a_search_on"><b><?php echo lang('kalkun_advanced_search_on');?></b></label></td>
			<td colspan="3">
				<select name="a_search_on" style="width: 98%">
					<option value="all"><?php echo lang('kalkun_advanced_search_on_all_folder');?></option>
					<option value="1"><?php echo lang('kalkun_inbox');?></option>
					<option value="3"><?php echo lang('kalkun_sentitems');?></option>
					<option value="6"><?php echo lang('kalkun_spam');?></option>
					<option value="5"><?php echo lang('kalkun_trash');?></option>
					<?php 
					$my_folders=$this->Kalkun_model->get_folders('all');
					foreach ($my_folders->result() as $my_folder):
					echo "<option value=\"$my_folder->id_folder\">$my_folder->name</option>";
					endforeach;
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td align="right"><label for="a_search_date_from"><b><?php echo lang('kalkun_advanced_search_date_from');?></b></label></td>
			<td><input type="text" id="a_search_date_from" name="a_search_date_from" /></td>
			<td><label for="a_search_date_to"><b><?php echo lang('kalkun_advanced_search_date_to');?></b></label></td>
			<td><input type="text" id="a_search_date_to" name="a_search_date_to" /></td>
		</tr>
		<tr>
			<td align="right"><label for="a_search_sentitems_status"><b><?php echo lang('kalkun_advanced_search_msg_status');?></b></label></td>
			<td colspan="3">
				<select name="a_search_sentitems_status" style="width: 98%">
					<option><?php echo lang('kalkun_advanced_search_msg_status_any');?></option>
					<option><?php echo lang('tni_msg_stat_deliv');?></option>
					<option><?php echo lang('tni_msg_stat_fail');?></option>
				</select>
			</td>
		</tr>
		<tr>
			<td align="right"><label for="a_search_paging"><b><?php echo lang('kalkun_advanced_search_paging');?></b></label></td>
			<td colspan="3">
				<select name="a_search_paging" style="width: 98%">
					<option value="10">10 <?php echo lang('kalkun_advanced_search_paging_per_page');?></option>
					<option value="20">20 <?php echo lang('kalkun_advanced_search_paging_per_page');?></option>
					<option value="30">30 <?php echo lang('kalkun_advanced_search_paging_per_page');?></option>
					<option value="40">40 <?php echo lang('kalkun_advanced_search_paging_per_page');?></option>
					<option value="50">50 <?php echo lang('kalkun_advanced_search_paging_per_page');?></option>
					<option value="all"><?php echo lang('kalkun_advanced_search_no_paging');?></option>
				</select>
			</td>
		</tr>				
	</table>
	<?php echo form_close();?>
</div>