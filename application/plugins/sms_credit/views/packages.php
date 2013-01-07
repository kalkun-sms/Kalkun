<?php $this->load->view('js_packages');?>

<div id="window_container">
    <div id="window_title">
        <div id="window_title_left"><?php echo $title;?></div>
        <div id="window_title_right">
            <?php echo form_open('users', array('class' => 'search_form')); ?>
            <input type="text" name="search_name" size="20" class="search_name" value="" />
            <?php echo form_close(); ?>
            &nbsp;
            <a href="<?php echo site_url('plugin/sms_credit');?>" class="nicebutton">Users</a>
            <a href="#" id="addpackagesbutton" class="nicebutton">&#43; Add Packages</a>	
        </div>
    </div>

    <div id="window_content">

        <table>
        <?php foreach($packages->result() as $tmp): ?>
        <tr id="<?php echo $tmp->id_credit_template;?>">
            <td>
            <div class="two_column_container contact_list">
                <div class="left_column">
                    <div id="pbkname">
                        <span class="hidden id_package"><?php echo $tmp->id_credit_template;?></span>
                        <span class="package_name"><strong><?php echo $tmp->template_name;?></strong></span>
                        <span class="hidden sms_amount"><?php echo $tmp->sms_numbers;?></span>
                        <?php echo "<sup>( $tmp->sms_numbers SMS )</sup>"; ?>
                    </div>	
                </div>

                <div class="right_column">
                    <span class="pbk_menu">
                        <a class="editpackagesbutton simplelink" href="#"><?php echo lang('tni_edit'); ?></a>
                    </span>
                </div>
            </div>
            </td>
        </tr>
        <?php endforeach;?>
        </table>

    </div>
</div>

<!-- Add packages dialog -->
<div id="packages-dialog" title="Add Packages" class="dialog">
    <p id="validateTips">All form fields are required.</p>
    <?php echo form_open('plugin/sms_credit/packages', array('id' => 'addpackagesform')); ?>
    <fieldset>
        <input type="hidden" name="id_package" id="id_package" class="text ui-widget-content ui-corner-all" />
        <label for="package_name">Package name</label>
        <input type="text" name="package_name" id="package_name" class="text ui-widget-content ui-corner-all" />
        <label for="sms_amount">SMS Amount</label>
        <input type="text" name="sms_amount" id="sms_amount" value="" class="text ui-widget-content ui-corner-all" />
    </fieldset>
    <?php echo form_close(); ?>
</div>
