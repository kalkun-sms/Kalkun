<?php $this->load->view('js_init/js_dashboard');?>

<base href="<?= $this->config->item('base_url') ?>" />
<script type="text/javascript" src="<?php echo $this->config->item('js_path');?>Chart.bundle.min.js"></script>

<div align="right">
	<a href="<?php echo site_url('kalkun/get_statistic/days');?>" class="stats-toggle"><?php echo ucwords(tr('day'));?></a>&nbsp; &nbsp;
	<a href="<?php echo site_url('kalkun/get_statistic/weeks');?>" class="stats-toggle"><?php echo ucwords(tr('week'));?></a>&nbsp; &nbsp;
	<a href="<?php echo site_url('kalkun/get_statistic/months');?>" class="stats-toggle"><?php echo ucwords(tr('month'));?></a>&nbsp; &nbsp;
</div>

<div align="center" class="chart-container" style="position: relative; height:200px; width:650px">
	<canvas id="myChart" width="650" height="200" aria-label="Statistics chart" role="img" style="background:#fff; border:1px solid #ccc;">
		<p>Stats Fallback</p>
	</canvas>
</div>

<script>
	var ctx = document.getElementById('myChart');
	var myChart = new Chart(ctx, {
		type: 'bar',
		data: {
			labels: ["No data"],
			datasets: [{
				label: "No data",
				data: [0]
			}]
		},
		options: {
			scales: {
				yAxes: [{
					ticks: {
						beginAtZero: true
					}
				}]
			}
		}
	});

</script>

<?php
$uid = $this->session->userdata('id_user');
$inbox = $this->Message_model->get_messages(array('type' => 'inbox', 'uid' => $uid))->num_rows();
$outbox = $this->Message_model->get_messages(array('type' => 'outbox', 'uid' => $uid))->num_rows();
$sentitems = $this->Message_model->get_messages(array('type' => 'sentitems', 'uid' => $uid))->num_rows();
$trash_inbox = $this->Message_model->get_messages(array('type' => 'inbox', 'id_folder' => '5', 'uid' => $uid))->num_rows();
$trash_sentitems = $this->Message_model->get_messages(array('type' => 'sentitems', 'id_folder' => '5', 'uid' => $uid))->num_rows();
$trash = $trash_inbox + $trash_sentitems;
?>

<div style="float: left; width: 200px;">
	<h4><?php echo tr('Folders');?>: </h4>
	<p><span><?php echo tr('Inbox');?>:</span> <?php echo $inbox;?></p>
	<p><span><?php echo tr('Outbox');?>:</span> <?php echo $outbox;?></p>
	<p><span><?php echo tr('Sent items');?>:</span> <?php echo $sentitems;?></p>
	<p><span><?php echo tr('Trash');?>:</span> <?php echo $trash;?></p>
</div>

<div style="float: left; width: 250px;">
	<h4><?php echo tr('My folders');?>: </h4>
	<?php
foreach ($this->Kalkun_model->get_folders('all')->result() as $val):
$folder_count_inbox = $this->Message_model->get_messages(array('type' => 'inbox', 'id_folder' => $val->id_folder))->num_rows();
$folder_count_sentitems = $this->Message_model->get_messages(array('type' => 'sentitems', 'id_folder' => $val->id_folder))->num_rows();
$folder_count = $folder_count_inbox + $folder_count_sentitems;
echo '<p><span>'.$val->name.': </span>'.$folder_count.'</p>';
endforeach;
?>
</div>

<div style="float: left; width: 200px;">
	<h4><?php echo tr('Phonebook');?>: </h4>
	<p><span><?php echo tr('Contact');?>: </span>
		<?php echo  $this->Phonebook_model->get_phonebook(array('option' => 'all'))->num_rows();?></p>
	<p><span><?php echo tr('Group');?>: </span>
		<?php echo  $this->Phonebook_model->get_phonebook(array('option' => 'group'))->num_rows();?></p>
</div>

<div style="clear: both;">&nbsp;</div>
