<script type="text/javascript">
$(document).ready(function() {

    // background
    $("tr:odd").addClass('hover_color');

    // Add STOP dialog
    $("#stop-dialog").dialog({
        bgiframe: true,
        autoOpen: false,
        height: 350,
        modal: true,
        buttons: {
            'Save': function() {
                $("form.addstopform").submit();
            },
            Cancel: function() {
                $(this).dialog('close');
            }
        }
    });

    // Add STOP button
    $('#addstopbutton').click(function() {
        $('#stop-dialog').dialog('open');
    });

});
</script>
