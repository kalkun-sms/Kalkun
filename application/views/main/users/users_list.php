<?php $this->load->view('js_init/users/js_users');
if ($users->num_rows() === 0):
	if ($_POST)
	{
		echo '<p><i>'.tr('User not found').'</i></p>';
	}
	else
	{
		echo '<p><i>'.tr('Users is empty').'</i></p>';
	}
else: ?>
<table>
	<?php foreach ($users->result() as $tmp): ?>
	<tr id="<?php echo $tmp->id_user;?>">
		<td>
			<div class="two_column_container contact_list hover_show">
				<div class="left_column">
					<div id="pbkname">
						<input type="checkbox" class="select_user" />&nbsp;<span style="font-weight: bold;"><?php echo $tmp->realname;?></span>
						<?php if (in_array($tmp->id_user, $this->config->item('inbox_owner_id')))
{
	echo '<sup>( Inbox Master )</sup>';
} ?>
					</div>
				</div>
				<div class="right_column">
					<span class="pbk_menu no-touch-hidden">
						<a class="edit_user simplelink" href="#"><?php echo tr('Edit'); ?></a>
					</span>
		</td>
	</tr>
	<?php endforeach;?>
</table>
<?php endif; ?>
