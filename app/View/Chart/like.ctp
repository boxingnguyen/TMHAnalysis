<html>
  <head>
  	<link href='/css/style.css' rel='stylesheet' type='text/css'>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawVisualization);

      function drawVisualization() {
        // Some raw data (not necessarily accurate)
        var data = google.visualization.arrayToDataTable([
         ['Date', 'Likes'],
         
        <?php foreach ($dataLikes as $key => $val) { ?>
        		[<?php echo "'".$key."'";?> ,  <?php  echo $val; ?>],      
        <?php } ?> 
         
      ]);

    var options = {
      title : 'Daily amount of like rising',
      vAxis: {title: 'Total'},
      hAxis: {title: 'Day'},
      seriesType: 'bars',
      series: {5: {type: 'line'}}
    };

    var chart = new google.visualization.ComboChart(document.getElementById('chart_div'));
    chart.draw(data, options);
  }
    </script>
  </head>
  <body id='body_chart'>
    <div id="chart_div"></div>
  </body>
</html>