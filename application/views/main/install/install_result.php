<h2>Installation result</h2>
<p>This is the last step of the installation step.</p>
<p>Installation status: 
<?php if($error==0) echo "<span class=\"green\">SUCCESS</span>"; else { echo "<span class=\"red\">FAILED</span>";}?></p>

<?php if($error==0):?>
<p>&nbsp;</p>
<h4>Remove Installation folder</h4>
<p>Before run Kalkun, you <b>MUST</b> remove the <b>install</b> folder located on the root of Kalkun directory.</p>
<p>Removal status: 
<?php
	$rm = rmdir('./install');
	if($rm) echo "<span class=\"green\">SUCCESS</span>"; 
	else echo "<span class=\"red\">FAILED</span> (You have to remove it manually)";
?>
</p>
<p>&nbsp;</p>
<h4>Configure daemon</h4>
<p>Please note that you also <b>MUST</b> configure "daemon" otherwise you can't get your inbox, see instruction on README file.</p>
<p>&nbsp;</p>
<p align="center"><a href="<?php echo site_url();?>" class="button" >Go To Application</a></p>
<?php else: ?>
<p>Consider manual installation, read the README instruction file.</p>
<?php endif; ?>
<p>&nbsp;</p>