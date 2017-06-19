<?php
  //take data from Mongo
  $m = new MongoClient();
  $db = $m->selectDB('Instagram');
  $collection = new MongoCollection($db, 'media');
  // get userId from URL
  // $GET_['userID'] = $userID
  $userID = '26669533'; // example get number likes & comments of neymar

  $result = $collection->find(array('user.id' => $userID));

  $count = 0;
  foreach ($result as $value) {
      $likes = $value['likes']['count'];
      // $text = $value['caption']['text'];
      $count = $count + $likes;

  }
echo "number of likes" . " : " .  $count;
// sum of likes of neymar is 1204284395
//
  // $m = new MongoClient();
  //   $db = $m->Instagram;
  //   $collection = $db->media;
  //   $demo = $collection->find(array('user.id' => '26669533'));
  //   $date = array();
  //   foreach ($demo as $key => $value) {
  //
  //           $date[]  = date("d m Y", $value['created_time']) ;
  //   }
  //   $unique = array_unique($date);
  //   $like = array();
  //   foreach ($unique as $dates) {
  //     $count = 0;
  //     foreach ($demo as $b) {
  //       if(date("d m Y", $b['created_time'])==$dates){
  //                 $count = $count + $b['likes']['count'];
  //       }
  //
  //     }
  //         $like[$dates]= $count;
  //       }

?>
// use the data to draw charts
<!--
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<div id="chart_div"></div>
<script>
google.charts.load('current', {packages: ['corechart', 'line']});
google.charts.setOnLoadCallback(drawBasic);

function drawBasic() {

    var data = new google.visualization.DataTable();
    data.addColumn('number', 'X');
    data.addColumn('number', 'Dogs');

    data.addRows([
      [0, 0],   [1, 10],  [2, 23],  [3, 17],  [4, 18],  [5, 9],
      [6, 11],  [7, 27],  [8, 33],  [9, 40],  [10, 32], [11, 35],
      [12, 30], [13, 40], [14, 42], [15, 47], [16, 44], [17, 48],
      [18, 52], [19, 54], [20, 42], [21, 55], [22, 56], [23, 57],
      [24, 60], [25, 50], [26, 52], [27, 51], [28, 49], [29, 53],
      [30, 55], [31, 60], [32, 61], [33, 59], [34, 62], [35, 65],
      [36, 62], [37, 58], [38, 55], [39, 61], [40, 64], [41, 65],
      [42, 63], [43, 66], [44, 67], [45, 69], [46, 69], [47, 70],
      [48, 72], [49, 68], [50, 66], [51, 65], [52, 67], [53, 70],
      [54, 71], [55, 72], [56, 73], [57, 75], [58, 70], [59, 68],
      [60, 64], [61, 60], [62, 65], [63, 67], [64, 68], [65, 69],
      [66, 70], [67, 72], [68, 75], [69, 80]
    ]);

    var options = {
      hAxis: {
        title: 'Time'
      },
      vAxis: {
        title: 'Popularity'
      }
    };

    var chart = new google.visualization.LineChart(document.getElementById('chart_div'));

    chart.draw(data, options);
  }
</script> -->
