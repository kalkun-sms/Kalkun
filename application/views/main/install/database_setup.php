<h2>Database setup</h2>
<p>Welcome to database setup, this step will help you dumping data to your database. </p>
<?php echo form_open('install/run_install', array('class' => 'formtable'));?>
<h4 align="center" style="padding-bottom: 5px; border-bottom: 1px solid #999">Please choose database engine and gammu database version.</h4>
<table width="90%">
<tr valign="top">
	<td>Database engine</td>
	<td>
		<select name="db_engine">
			<option value="mysql" selected="selected">MySQL</option>
			<option value="sqlite">SQLite3</option>
		</select>
		<br /><small>MySQL is recommended, SQLite3 is currently experimental.</small>
	</td>
</tr>
<tr valign="top">
	<td>Gammu DB Version</small></td>
	<td><strong><?php echo $this->Kalkun_model->getGammuInfo('db_version')->row('Version'); ?></strong>
		<br /><small>It's readed from your gammu database schema.
	</td>
</tr>
</table>
<p>&nbsp;</p>
<p align="center"><input type="submit" class="button" value="Run Database Setup" /></p>
<?php echo form_close();?>

<!--
<p>You have to choose your installation type, <b>New Installation</b> if you install Kalkun for the first time, 
or <b>Upgrading from 0.1.4</b> if you want to upgrade from previous version.</p>
<p>&nbsp;</p>
<p align="center">
<a href="<?php echo site_url();?>/install/run_install/install" class="button">New Installation</a>
<a href="<?php echo site_url();?>/install/run_install/upgrade" class="button">Upgrading from 0.1.4</a>
</p>
-->

<p>&nbsp;</p>
<div>
<a href="<?php echo site_url();?>/install/requirement_check" class="button">&lsaquo; Back</a>
</div>
