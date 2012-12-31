<script type="text/javascript">

$(document).ready(function() {

    $('#addnewfilter, .editfilter').click(function() 
    {
        if($(this).hasClass('editfilter'))
        {
            var id_filter = $(this).parents("div:eq(1)").find("div.id_filter").attr('id');
            var id_folder = $(this).parents("div:eq(1)").find("b.id_folder").parent("div").attr('class');
            var from = $(this).parents("div:eq(1)").find("b.from").text();
            var has_the_words = $(this).parents("div:eq(1)").find("b.has_the_words").text();
            $('input#id_filter').val(id_filter);
            $('input#from').val(from);
            $('input#has_the_words').val(has_the_words);
            $('select#id_folder').val(id_folder);
        }
        else
        {
            $('input#from').val("");
            $('input#has_the_words').val("");
            $('input#id_filter').val("");
        }

        $("#filterdialog").dialog({
        bgiframe: true,
        autoOpen: false,
        height: 250,
        modal: true,
        buttons: {
            'Save': function() {
                $("form.addfilterform").submit();
            },
            Cancel: function() {
                $(this).dialog('close');
            }
        },
        open: function() {
            $("#from").focus();
        }
        });		
        $('#filterdialog').dialog('open');
        return false;
    });	

    $("a.deletefilter").live("click", function(){
        var dest_url = '<?php echo site_url('kalkun/delete_filter') ?>';
        var row = $(this).parents("div:eq(1)");
        var id_filter = row.find("div.id_filter").attr('id');

        // confirm first
        $("#confirm_delete_filter_dialog").dialog({
        bgiframe: true,
        autoOpen: false,
        height: 150,
        modal: true,
        buttons: {
            '<?php echo lang('kalkun_cancel')?>': function() {
                $(this).dialog('destroy');
            },
            '<?php echo lang('tni_yes')?>': function() {
                $.get(dest_url + '/' + id_filter, function() {
                    $(row).slideUp("slow");
                });
                $(this).dialog('destroy');
            } }
        });
        $('#confirm_delete_filter_dialog').dialog('open');
    });

});

</script>
