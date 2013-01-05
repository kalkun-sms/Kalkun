<div id="window_container">
    <div id="window_title">
        <div id="window_title_left"><?php echo $title;?></div>
        <div id="window_title_right">
            <?php echo form_open('users', array('class' => 'search_form')); ?>
            <input type="text" name="search_name" size="20" class="search_name" value="" />
            <?php echo form_close(); ?>
            &nbsp;
            <a href="<?php echo site_url('plugin/sms_credit/packages');?>" class="nicebutton">Packages</a>	
            <a href="#" id="send_member" class="nicebutton">&#43; <?php echo lang('tni_user_addp');?></a>	
        </div>
    </div>

    <div id="window_content">

        <table>
        <?php foreach($users->result() as $tmp): ?>
        <tr id="<?php echo $tmp->id_user;?>">
        <td>
        <div class="two_column_container contact_list">
            <div class="left_column">
            <div id="pbkname">
                <span style="font-weight: bold;"><?php echo $tmp->realname;?></span>
                <?php if(!is_null($tmp->template_name)): echo "<sup>( $tmp->template_name )</sup>"; ?>
                <?php else: echo "<sup>( No package )</sup>"; ?>
                <?php endif;?>
            </div>	
        </div>
        <div class="right_column">
        <span class="pbk_menu">
        <a class="edit_user simplelink" href="#"><?php echo lang('tni_edit'); ?></a>
        </span>
        </td></tr>
        <?php endforeach;?>
        </table>

    </div>
</div>

