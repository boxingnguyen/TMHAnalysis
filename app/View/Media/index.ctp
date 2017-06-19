<!doctype html>
<html lang="en" class="no-js">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<link href='http://fonts.googleapis.com/css?family=Droid+Serif|Open+Sans:400,700' rel='stylesheet' type='text/css'>
	<?php 
	echo $this->Html->css('src/reset');
	echo $this->Html->css('src/style');
	echo $this->Html->script('https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js');
	echo $this->Html->script('src/media');
	
	echo $this->fetch('meta');
	echo $this->fetch('css');
	echo $this->fetch('script');
	?>

	<title>Timeline</title>
</head>
<body>
	<?php 
		if(!empty($inforAcc)){
			echo '<header>';
				echo '<h1>'. $inforAcc['full_name'].'</h1>';
				echo '<p class="infor">';
					echo '<span>'.number_format($inforAcc['media']['count']).' posts</span><span>'.number_format($inforAcc['followed_by']['count']).' followers</span><span>'.number_format($inforAcc['follows']['count']).' following</span>';
				echo '</p>';
			echo '</header>';
			echo '<img class="pre-media avatar" src="'. $inforAcc['profile_pic_url'] .'" >';
		}
	?>
	<div id='notification'></div>
	<section id="cd-timeline" class="cd-container">
	</section> <!-- cd-timeline -->
	<div style="text-align: center;" >
		<a href="javascript:void(0)" class="cd-read-more loadMore">Load more</a>
	</div>
	<a href="#" class="back-to-top">Back to Top</a>
</body>
</html>