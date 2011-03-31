<h2>Database setup</h2>
<p>Welcome to database setup, this step will help you dumping data to your database. </p>
<?php echo form_open('install/run_install', array('class' => 'formtable'));?>
<h4 align="center" style="padding-bottom: 5px; border-bottom: 1px solid #999">Database backend engine and gammu database version.</h4>
<table width="90%">
<tr valign="top">
	<td>Database engine</td>
	<td>
		<?php $db_property = get_database_property($database_driver); ?>
		<strong><?php echo $db_property['human']; ?></strong>
		<input type="hidden" name="db_engine" value="<?php echo $db_property['file'];?>" />
		<br /><small>It's readed from your database configuration.</small>
	</td>
</tr>
<tr valign="top">
	<td>Gammu DB Version</small></td>
	<td><strong><?php echo $this->Kalkun_model->get_gammu_info('db_version')->row('Version'); ?></strong>
		<br /><small>It's readed from your gammu database schema.
	</td>
</tr>
</table>
<p>&nbsp;</p>
<p align="center"><input type="submit" class="button" value="Run Database Setup" /></p>
<?php echo form_close();?>

<p>&nbsp;</p>
<div>
<a href="<?php echo site_url();?>/install/requirement_check" class="button">&lsaquo; Back</a>
</div>
