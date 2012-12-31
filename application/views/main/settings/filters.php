<?php $this->load->view('js_init/js_filters');?>
<div align="center">
    <a href="#" id="addnewfilter"><?php echo lang('kalkun_filter_add');?></a>
</div>

<?php foreach($filters->result_array() as $filter):?>
<div class="two_column_container contact_list" style="display: inline-block;">
    <div class="left_column">
        <div id="<?php echo $filter['id_filter'];?>" class="id_filter">
            <span>
                <?php if(!empty($filter['from'])):?>
                <?php echo lang('tni_from');?>: <b class="from"><?php echo $filter['from'];?></b>
                <?php endif;?>

                <?php if(!empty($filter['has_the_words'])):?>
                <?php echo lang('kalkun_filter_has_the_words');?>: <b class="has_the_words"><?php echo $filter['has_the_words'];?></b>
                <?php endif;?>
            </span>
            <div style="padding: 2px 0 5px 24px;" class="<?php echo $filter['id_folder'];?>"><?php echo lang('kalkun_move_to');?>: <b class="id_folder"><?php echo $filter['name'];?></b></div>
        </div>
    </div>

    <div class="right_column">
        <span>
            <a href="#" class="editfilter simplelink"><?php echo lang('tni_edit');?></a>
            <img src="<?php echo $this->config->item('img_path');?>circle.gif" />
            <a href="#" class="deletefilter simplelink"><?php echo lang('kalkun_delete');?></a>
        </span>
    </div>
</div>
<?php endforeach;?>

<!-- Filter Dialog -->
<div id="filterdialog" title="<?php echo lang('kalkun_filters');?>" class="dialog">
    <form class="addfilterform" method="post" action="<?php echo site_url('settings/save');?>">
        <input type="hidden" name="option" value="filters" />
        <input type="hidden" name="id_filter" id="id_filter" value="" />
        <input type="hidden" name="id_user" value="<?php echo $this->session->userdata('id_user');?>" />

        <label for="from"><?php echo lang('tni_from');?></label>
        <input type="text" name="from" id="from" class="text ui-widget-content ui-corner-all" />

        <label for="has_the_words"><?php echo lang('kalkun_filter_has_the_words');?></label>
        <input type="text" name="has_the_words" id="has_the_words" class="text ui-widget-content ui-corner-all" />

        <label for="move_to"><?php echo lang('kalkun_move_to');?></label>
        <select name="id_folder" id="id_folder" style="width: 98%">
        <?php 
        foreach ($my_folders->result() as $my_folder):
        echo "<option value=\"$my_folder->id_folder\">$my_folder->name</option>";
        endforeach;
        ?>
        </select>
    </form>
</div>

<!-- Delete Filter Dialog -->
<div class="dialog" id="confirm_delete_filter_dialog" title="<?php echo lang('kalkun_filters');?>">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
	<?php echo lang('kalkun_canned_confirm');?> </p>
</div>
