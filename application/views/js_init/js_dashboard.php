<script language="javascript">

function reload(url)
{
  tmp = findSWF("test_chart");

  //
  // to load from a specific URL:
  // you may need to 'escape' (URL escape, i.e. percent escape) your URL if it has & in it
  //
  x = tmp.reload(url);
}

function findSWF(movieName) 
{
  if (navigator.appName.indexOf("Microsoft")!= -1) {
    return window["ie_" + movieName];
  }
  else {
    return document[movieName];
  }
}

$(document).ready(function() {
    $('a.stats-toggle').css("color", "#454545");
    $('a.stats-toggle').click(function(e) {
        url = $(this).attr('href');
        e.preventDefault();
        reload(url);
        $('a.stats-toggle').css("font-weight", "normal");
        $(this).css("font-weight", "bold");
    });
});

</script>	
