<script type="text/javascript">
$(document).ready(function() {

    // Add/ Edit packages	
    $('#addpackagesbutton, .editpackagesbutton').click(function() {
        var title = 'Add Packages';

        // Edit mode
        if($(this).hasClass('editpackagesbutton')) {
            title = 'Edit Packages';
            var id_package = $(this).parents('div:eq(1)').find('span.id_package').text();
            var package_name = $(this).parents('div:eq(1)').find('span.package_name').text();
            var sms_amount = $(this).parents('div:eq(1)').find('span.sms_amount').text();
            $('#id_package').val(id_package);
            $('#package_name').val(package_name);
            $('#sms_amount').val(sms_amount);
        }
        
        $("#packages-dialog").dialog({
            bgiframe: true,
            autoOpen: false,
            height: 250,
            modal: true,
            title: title,
            buttons: {
                'Save': function() {
                    $("form#addpackagesform").submit();
                },
                Cancel: function() {
                    $(this).dialog('destroy');
                }
            }
        });

        $('#packages-dialog').dialog('open');
    });

    // Search onBlur onFocus
    if($('input.search_packages').val() == '') {
        $('input.search_packages').val('Search Packages');
    }

    $('input.search_packages').blur(function(){
       $(this).val('Search Packages');
    });

    $('input.search_packages').focus(function(){
        $(this).val('');
    });

    // Delete package
    $("a.deletepackagesbutton").click(function(e){

        e.preventDefault();
        var url = $(this).attr('href');

        // confirm first
        $("#confirm_delete_package_dialog").dialog({
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
        $('#confirm_delete_package_dialog').dialog('open');
    });
}); 
</script>
