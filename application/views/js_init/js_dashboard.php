<script language="javascript">

function reload(url)
{
    $.get(url).done(function(data)
    {
        updateChartData(data);
    });
}

function updateChartData(content)
{
    myChart.data = JSON.parse(content)
    myChart.update();
}

$(document).ready(function() {
    // Load data for the chart
    reload("<?php echo $data_url;?>");

    $('a.stats-toggle').css("color", "#454545");
    $('a.stats-toggle').on("click", function(e) {
        url = $(this).attr('href');
        e.preventDefault();
        reload(url);
        $('a.stats-toggle').css("font-weight", "normal");
        $(this).css("font-weight", "bold");
    });
});

</script>
