<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Layouts
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

$cakeDescription = __d('cake_dev', 'CakePHP: the rapid development php framework');
$cakeVersion = __d('cake_dev', 'CakePHP %s', Configure::version())
?>
<!DOCTYPE html>
<html>
<head>
	<?php echo $this->Html->charset(); ?>
	<title>
		<?php $this->assign('title', 'Analysis | TMH Techlab');?>
		<?php echo $this->fetch('title'); ?>
	</title>

	<?php
		echo $this->Html->meta('icon');
		echo $this->Html->css('css/bootstrap.min');
		echo $this->Html->css('css/bootstrap-theme.min');
		echo $this->Html->css('style');
		echo $this->Html->css('css/raking');

		echo $this->Html->script('jquery.min');
		echo $this->Html->script('loader');
		echo $this->Html->script('src/register');
		echo $this->Html->script('src/logout');
		echo $this->Html->script('src/rakingFollow');
		echo $this->Html->script('js/bootstrap.min');
		echo $this->Html->script('src/hashtag');

		echo $this->fetch('meta');
		echo $this->fetch('css');
		echo $this->fetch('script');
	?>
</head>
<body>
	<div class="container">
		<header class="clearfix">
			<?php
				if (isset($acc_infor)) {
					echo '<div class="profile-basic">';
					echo '<img src="' . $acc_infor['profile_pic_url'] . '" ><br>';
					echo '<a target="_blank" href="https://www.instagram.com/'.$acc_infor['username'].'">' . $acc_infor["full_name"] . '</a>';
					echo '</div>';
				}
			?>
			<?php if (strtolower($this->params['controller']) == 'hashtag'){ ?>
				<?php $hashtagName = isset($this->params['url']['hashtag']) ? '#'.$this->params['url']['hashtag'] : 'Ranking'; ?>
        		<h1>Hashtag <?php echo $hashtagName;?></h1>
        	<?php }elseif (strtolower($this->params['controller']) == 'ranking') {?>
        		<h1>Ranking <?php echo $this->Html->image('/img/icon_ranking.png', array('class' => 'icon-ranking')); ?> </h1>
        	<?php } else {?>
        		<h1>Tribal Analysis</h1>
        	<?php } ?>
    	</header>
		<div id="content">

			<?php echo $this->Flash->render(); ?>

			<?php echo $this->fetch('content'); ?>
		</div>
		<footer class="footer col-xs-12">
			<?php if (strtolower($this->params['controller']) == 'top'): ?>
			<div class="col-xs-6">
				<p class="team icon"><span class="glyphicon glyphicon-user"></span> <u>CONTACT US</u></p>
				<p class="team text"> Tribal Media House Technology Lab</p>
			</div>
			<div class="col-xs-6 contact">
				<p><span class="glyphicon glyphicon-home"></span> 7F IPH, 241 Xuan Thuy Str., Cau Giay Dist., Hanoi, Vietnam.</p>
				<p><span class="glyphicon glyphicon-earphone"></span> ＋84-(0)4-3256-5182</p>
				<p><span class="glyphicon glyphicon-globe"></span> <a href='https://www.facebook.com/tmhtechlab' target="_blank">www.tmh-techlab.vn</a></p>
				<p><span class="glyphicon glyphicon-thumbs-up"></span> <a href='https://www.facebook.com/tmhtechlab' target="_blank">www.facebook.com/tmhtechlab</a></p>
			</div>
			<?php endif;?>
        </footer>

	</div>
	<?php echo $this->element('sql_dump'); ?>
	<?php echo $this->Html->script('main');?>
</body>
</html>
