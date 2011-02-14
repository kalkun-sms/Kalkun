<base href="<?= $this->config->item('base_url') ?>" />
<script type="text/javascript" src="<?php echo $this->config->item('js_path');?>swfobject.js"></script>

<script type="text/javascript">
swfobject.embedSWF(
"<?php echo $this->config->item('swf_path');?>open-flash-chart.swf", "test_chart", "525", "200",
"9.0.0", "expressInstall.swf",
{"data-file":"<?php echo urlencode($data_url);?>"},{"wmode":"transparent"}
);
</script>

<div align="center" id="test_chart">&nbsp;</div>

<div style="float: left; width: 150px;">
<h4><?php echo lang('kalkun_folder');?>: </h4>
<p><span><?php echo lang('kalkun_inbox');?>:</span> <?php echo  $this->Message_model->getMessages('inbox', 'count');?></p>
<p><span><?php echo lang('kalkun_outbox');?>:</span> <?php echo  $this->Message_model->getMessages('outbox', 'count');?></p>

<p><span><?php echo lang('kalkun_sentitems');?>:</span> <?php echo  $this->Message_model->getMessages('sentitems', 'count');?></p>
<p><span><?php echo lang('kalkun_trash');?>:</span> <?php echo $this->Message_model->getMessages('inbox', 'count', '5') + $this->Message_model->getMessages('sentitems', 'count', '5');?></p>
</div>

<div style="float: left; width: 200px;">
<h4><?php echo lang('kalkun_myfolder');?>: </h4>
<?php  
foreach($this->Kalkun_model->getFolders('all')->result() as $val):
$folder_count = $this->Message_model->getMessages('inbox', 'count', $val->id_folder) + $this->Message_model->getMessages('sentitems', 'count', $val->id_folder);
echo "<p><span>".$val->name.": </span>".$folder_count."</p>";
endforeach;	
?>
</div>

<div style="float: left; width: 175px;">
<h4><?php echo lang('kalkun_phonebook');?>: </h4>
<p><span><?php echo lang('kalkun_contact');?>: </span>
<?php echo  $this->Phonebook_model->getPhonebook(array('option' => 'all'))->num_rows();?></p>
<p><span><?php echo lang('kalkun_group');?>: </span>
<?php echo  $this->Phonebook_model->getPhonebook(array('option' => 'group'))->num_rows();?></p>
</div>

<div style="clear: both;">
&nbsp;
</div>
