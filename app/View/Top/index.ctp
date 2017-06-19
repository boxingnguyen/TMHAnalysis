
<div style = "float:right;display: inline-flex;">
	<button type="button" class="buttonHead buttonReg" data-toggle="modal" data-target="#myModal">Register</button>
	
	<!-- Modal -->
	<div class="modal fade " id="myModal"  tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">
	      <div class="modal-body">
	       <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	        <div class="form">
			      <input type="text"  type="text" id="inputUserName" class="form-control" placeholder="https://www.instagram.com/instagram/"/>
			      <p class="message">Example: https://www.instagram.com/instagram/</p>
			      <button class="modalReg" data-toggle="modal" data-target="#regisForm"><b>REGIST</b></button>
			  </div>
	      </div>
	    </div>
	  </div>
	  
	</div>
	<div class="modal fade " id="regisForm"  tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">
	      <div class="modal-body">
	       		 <p class="messRegis">This is mess</p>
	       		 <div style="text-align: center;margin-top: 12%;">
				      <button type="button" class="btn btn-primary cancel" >Ok</button>
			      </div>
	      </div>
	    </div>
	  </div>
	  <div class="loader"></div>
	</div>
	<a class="buttonLogout buttonHead" href="javascript:void(0)">Logout</a>
</div>
	
<div class='col-xs-12'>
	<table class="table responstable table-top">
			<tr>
			<th class='center'>No.</th>
			<th class='center'>Username</th>
			<th class='center'>Followers</th>
			<th class='center'>Total media</th>
			<th class='center'>Media get</th>
			<th class='center'>Status</th>
			<th class='center'>Total likes</th>
			<th class='center'>Total comments</th>
		</tr>
		<?php
		$count = 1;
		foreach ($data as $value) :
			$percentage = ($value['media_count'] != 0) ? round($value['media_get'] / $value['media_count'] * 100, 2) : 'N/A';
			$miss_count = abs($value['media_count'] - $value['media_get']);
		?>
		<tr class='center <?php if ($miss_count > 10) echo "hard_missing"; elseif ($miss_count > 0) echo "light_missing"; ?>'>
			<td><a class="badge inst_order" href="javascript:void(0)"><?php echo $count; ?></a></td>
			<td><a href="https://www.instagram.com/<?php echo $value['username']; ?>" target="_blank"><?php echo ($value['fullname'] != '') ? $value['fullname'] : $value['username']; ?></a></td>
			<td>
				<?php 
					echo $this->Html->link(
							number_format($value['followers']),
							array('controller' => 'Chart', 'action' => 'follower','?' => array('id' => $value['id'])),
							array('target' => '_blank')
						)
				?>
				<a href="<?php echo Router::url(array('controller'=>'FollowRanking', 'action'=>'index', '?' => array('id' => $value['id'])))?>" target="_blank" class="rankingFollow"><span style="float: right;" class="glyphicon glyphicon-list" aria-hidden="true"></span></a>
			</td>
			
			<td>
				<?php 
					echo $this->Html->link(
							number_format($value['media_count']),
							array('controller' => '', 'action' => 'media','?' => array('id' => $value['id'])),
							array('target' => '_blank', 'escape' => false)
						)
				?>
			</td>
			<td><?php echo number_format($value['media_get']) . " (" . $percentage . "%)"?></td>
			<td>
				<?php 
				if ($percentage == 100) {
					$image = 'right.png';				
				} else if ($percentage >= 90) {
					$image = 'warning.png';
				} else {
					$image = 'wrong.png';
				}
				echo $this->Html->image($image, array('height' => 15, 'width' => 15));
				?>
			</td>
			<td>
				<?php 
					echo $this->Html->link(
							number_format($value['likesTop']),
							array('controller' => 'Chart', 'action' => 'like','?' => array('id' => $value['id'])),
							array('target' => '_blank')
						);
				?>
			</td>
			<td>
				<?php 
					echo $this->Html->link(
							number_format($value['commentsTop']), 
							array('controller' => 'Chart', 'action' => 'comment','?' => array('id' => $value['id'])), 
							array('target' => '_blank')
						)
				?>
			</td>
		</tr>
		<?php
		$count ++;
		endforeach;
		?>
	</table>
</div>