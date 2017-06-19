<?php echo $this->element('switch_top_hashtag'); ?>
<div style = "float:right;display: inline-flex;">
	<button type="button" class="buttonHead buttonReg" data-toggle="modal" data-target="#myModal">Register</button>
	
	<!-- Modal -->
	<div class="modal fade " id="myModal"  tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">
	      <div class="modal-body">
	       <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	        <div class="form">
			      <input type="text"  type="text" id="inputHashtag" class="form-control" placeholder="#instagram"/>
			      <p class="message">Example: #instagram</p>
			      <button class="modalRegTag" data-toggle="modal" data-target="#regisForm"><b>REGIST</b></button>
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
	<div class='col-xs-2'></div>	
	<div class='col-xs-8'>
		<table class="table responstable table-hashtag">
			<tr>
				<th class='center'>No.</th>
				<th class='center'>Hashtag</th>
				<th class='center rank-by-media'>Total media <span class="caret"></span></th>
			</tr>
			<?php
			$i = 0;
			foreach ($data as $value):
				$i ++;
			?>
			<tr class='center'>
				<td><?php echo $i; ?></td>
				<td><a href="<?php echo $this->webroot . 'hashtag/detail?hashtag=' . $value['tag']; ?>" target="_blank">#<?php echo $value['tag']; ?></a></td>
				<td><a href="<?php echo $this->webroot . "hashtag/media?hashtag=" . $value['tag']; ?>" target="_blank"><?php echo number_format($value['total_media']);?></a></td>
			</tr>
			<?php endforeach;?>
		</table>
	</div>
	<div class='col-xs-2'></div>
</div>