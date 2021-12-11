<h2 style="float: left"><?php echo tr('Kalkun installation assistant'); ?></h2>
<div style="float: right">
	<?php
	echo form_open('');
	echo form_dropdown('idiom', $language_list, $idiom, 'onchange="this.form.submit()"');
	echo form_close();
?>
</div>

<p style="clear:both">Welcome to the installation assistant of Kalkun, the open source web based SMS manager.
	This assistant will help you check your system meets all requirements and proceed with the database setup.
</p>

<p><?php echo tr('Installation steps'); ?></p>

<p>
<ol>
	<li><?php echo tr('This welcome screen'); ?></li>
	<li><?php echo tr('Requirements check'); ?></li>
	<li><?php echo tr('Database installation or upgrade'); ?></li>
	<li><?php echo tr('Final configuration steps'); ?></li>
</ol>
</p>

<p>&nbsp;</p>

<div>
	<?php
	echo form_open('install/requirement_check');
	echo form_hidden('idiom', $idiom);
	echo form_submit('submit', tr('Continue'), 'class="button"');
	echo form_close();
?>
</div>
