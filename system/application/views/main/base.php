
<!-- About dialog -->
<div id="about" title="About Kalkun" class="dialog">
	<center>
	<div class="base_bg rounded"><img src="<?php echo $this->config->item('img_path');?>logo.png" /></div>
	<h1>PHP Frontend for gammu-smsd</h1>
	</center>
	<table>
		<tr valign="top"><td><b>Author:</b></td><td>&nbsp;</td><td>Azhari Harahap &lt;azhari.harahap@yahoo.com&gt;</td></tr>
		<tr><td><b>Version:</b></td><td>&nbsp;</td><td>0.2</td></tr>		
		<tr><td><b>Released:</b></td><td>&nbsp;</td><td>22 July 2010</td>
		<tr><td><b>License:</b></td><td>&nbsp;</td><td>GNU/GPL</td>		
		<tr><td><b>Homepage:</b></td><td>&nbsp;</td>
		<td><a class="base_color underline_link" href="http://kalkun.sourceforge.net" target="_blank">http://kalkun.sourceforge.net</a></td>	
		</tr>				
	</table>
	<br />
	<center><a class="underline_link" href="http://sourceforge.net/project/project_donations.php?group_id=268699"><b>Donate to this project</b></a></center>
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
