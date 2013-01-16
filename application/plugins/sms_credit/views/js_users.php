<script language="javascript" src="<?php echo $this->config->item('js_path');?>jquery-plugin/jquery.validate.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){

    var img_path = '<?php echo  $this->config->item('img_path');?>';
    $(".datepicker").datepicker({
        minDate: 0, maxDate: '+1Y',
        dateFormat: 'yy-mm-dd', showOn: 'button', buttonImage: img_path + 'calendar.gif', buttonImageOnly: true
    });

    // validation
    $("#addUser").validate({
        rules: {
            realname: {
                required: true
            },
            username: {
                required: true,
                maxlength: 12
            },
            phone_number: {
                required: true
            },
            password: {
                required: true,
                minlength: 6
            },
            confirm_password: {
                equalTo: "#password"
            },
            package_start: {
                required: true
            },
            package_end: {
                required: true
            }
        },
        messages: {
            realname: {
                required: "<?php echo lang('tni_error_enter_name');?>"	
            },
            password: {
                required: "<?php echo lang('tni_error_enter_password');?>",
                minlength: "<?php echo lang('tni_error_toshort');?>"
            },
            confirm_password: { 
                equalTo: "<?php echo lang('tni_error_password_nomatch');?>"
            }
        }
    });

});
</script>

<script language="javascript">
$(document).ready(function() {

var inbox_master = '<?php echo $this->config->item('inbox_owner_id');?>';
	
// Add User
$('.addpbkcontact').bind('click', function() {

    $("#users_container").dialog({
        title: '<?php echo lang('tni_user_addp');?>',
        modal: true,
        show: 'fade',
        hide: 'fade',
        buttons: {
            '<?php echo lang('kalkun_save');?>': function() {
                if($("#addUser").valid()) {
                    $("form#addUser").submit()
                }
            },
            '<?php echo lang('kalkun_cancel');?>': function() { 
                $(this).dialog('destroy');
            }
        }
    });

    $("#users_container").dialog('open');
    return false;
});

// Edit User
$('.edit_user').bind('click', function() {

    var id_user = $(this).parents('tr').attr('id');
    var id_package = $(this).parents('div:eq(1)').find('span.id_package').text();
    var package_start = $(this).parents('div:eq(1)').find('span.package_start').text();
    var package_end = $(this).parents('div:eq(1)').find('span.package_end').text();
    $('#id_user').val(id_user);
    $('#edit_id_package').val(id_package);
    $('#edit_package_start').val(package_start);
    $('#edit_package_end').val(package_end);

    $("#edit_users_container").dialog({
        title: 'Change Package for ',
        modal: true,
        show: 'fade',
        hide: 'fade',
        buttons: {
        '<?php echo lang('kalkun_save');?>': function() {
            $("form#editUser").submit()
        },
        '<?php echo lang('kalkun_cancel');?>': function() { 
            $(this).dialog('destroy');}
        }
    });

    $("#edit_users_container").dialog('open');
    return false;
});

// Delete user
$("a.delete_user").click(function(e){

    e.preventDefault();
    var url = $(this).attr('href');

    // confirm first
    $("#confirm_delete_user_dialog").dialog({
    bgiframe: true,
    autoOpen: false,
    height: 150,
    modal: true,
    buttons: {
        '<?php echo lang('kalkun_cancel')?>': function() {
            $(this).dialog('close');
        },
        '<?php echo lang('tni_yes')?>': function() {
            window.location.href = url;
            $(this).dialog('close');
        }
    }
    });
    $('#confirm_delete_user_dialog').dialog('open');
});

// Search onBlur onFocus
if($('input.search_name').val() == '') {
    $('input.search_name').val('<?php echo lang('tni_user_search'); ?>');
}

$('input.search_name').blur(function(){
	$(this).val('<?php echo lang('tni_user_search'); ?>');
});

$('input.search_name').focus(function(){
	$(this).val('');
});
	  
});    
</script>
