$().ready(function(){
	$('.loader').hide();
	$('button.modalReg').prop('disabled', true);
	$('button.modalRegTag').prop('disabled', true);
	
	$('.buttonReg').click(function(){
		$('#myModal').show();
		initModal();
	})
	$('input[type=text]').click(function(){
		initModal();
	});
	$('#inputUserName').change(function(){
		var input = $(this).val();
		//remove spaces in string input
		input = input.replace(/\s/g, '');
		//check input
		if (input.match(/^http([s]?):\/\/.*/)) {
			if (!input.match(/^http([s]?):\/\/www\.instagram\.com\/.*/)) {
				$('p.message').text('Sorry! This link has the wrong format. Please re-type!').css("color",'red');
				$('button.modalReg').prop('disabled', true);
			}
		}
	});
	$('#inputHashtag').change(function(){
		var input = $(this).val();
		//remove spaces in string input
		input = input.replace(/\s/g, '');
		//check input
		if (!input.match(/^#.*/)) {
			$('p.message').text('Sorry! This hashtag has wrong format. Please re-type!').css("color",'red');
			$('button.modalRegTag').prop('disabled', true);
		}
	});
	
	$('button.modalReg').click(function(){
		var input = $('input[type=text]').val();
		
		if(input == ''){
			$('p.message').text('Please input the link of instagram!').css("color",'red');
			$('button.modalReg').prop('disabled', true);
		}else{
			//remove spaces in string input
			input = input.replace(/\s/g, '');
			
			var username = '';
			if (input.match(/^http([s]?):\/\/.*/)) {
				username = getQueryVariable(input);
			}else{
				username = input;
			}
			if(username.slice(-1) == '/'){
				username = username.slice(0, -1);
			}
			
			$('#myModal').modal('hide');
			$('.loader').show();
			$('.modal-body').css('opacity','0.4');
			$('button.cancel').prop('disabled', true);
			
			$.ajax({
				method: "POST",
				url: '/register/register',
				data: {username:username},
				dataType: 'json',
				success: function(data){
					console.log(username);
					console.log(data);
					if(data == 1){
						$('p.messRegis').text('You have successfully registered');
						
						//get information of registered account
						$.ajax({
							method: "POST",
							url: '/register/getDataRegister',
							data: {username:username},
							dataType: 'json',
							success: function(infor){
								console.log(infor);
								
								var no = $('table.table-top tr').length; 
								var tr = '<tr class="center"><td><a class="badge inst_order">'+no+
											'</a></td><td><a href="https://www.instagram.com/'+username+'" target="_blank">'+ infor.fullname +
											'</a></td><td><a href="/Chart/follower?id='+ infor.id +'" target="_blank">'+ infor.follower +'</a><a href="/FollowRanking?id='+ infor.id +
											'" target="_blank" class="rankingFollow"><span style="float: right;" class="glyphicon glyphicon-list" aria-hidden="true"></span></a></td><td><a href="/media?id='+infor.id+'" target="_blank">'+ infor.totalMedia+
											'</a></td><td></td><td></td><td></td><td></td></tr>';
								$('table.table-top').append(tr);
								var trLast = $('table.table-top tr').last();
								trLast.css('opacity', '0.4');
								
								//get media of registered account
								$.ajax({
									method: "POST",
									url: '/register/getMediaRegister',
									data: {infor:infor},
									dataType: 'json',
									success: function(data){
										console.log(data);
										
										var no = $('table.table-top tr').length-1;
										var percentage = (infor.totalMedia != 0) ? Math.round(data.mediaGet / infor.totalMedia * 100, 2) : 'N/A';
										
										var miss_count = Math.abs(infor.totalMedia - data.mediaGet);
										var classColor = '';
										if (miss_count > 10){ 
											classColor = "hard_missing";
										} else if (miss_count > 0) {	
											classColor = "light_missing";
										}
										
										var imageStatus = 'wrong.png';
										if (percentage == 100) {
											imageStatus = 'right.png';				
										} else if (percentage >= 90) {
											imageStatus = 'warning.png';
										}

										var tr = '<tr class="center '+ classColor +'"><td><a class="badge inst_order">'+no+
													'</a></td><td><a href="https://www.instagram.com/'+username+'" target="_blank">'+ infor.fullname +
													'</a></td><td><a href="/Chart/follower?id='+ infor.id +'" target="_blank">'+ infor.follower +'</a><a href="/FollowRanking?id='+ infor.id +
													'" target="_blank" class="rankingFollow"><span style="float: right;" class="glyphicon glyphicon-list" aria-hidden="true"></span></a></td><td><a href="/media?id='+infor.id+'" target="_blank">'+ infor.totalMedia+
													'</a></td><td>'+ data.mediaGet +
													'</td><td><img src="/img/'+ imageStatus +
													'" height="15" width="15"></td><td><a href="/Chart/like?id='+infor.id+'"target="_blank">'+ data.totalLike +
													'</a></td><td><a href="/Chart/comment?id='+infor.id+'"target="_blank">'+ data.totalComment +'</a></td></tr>';
										trLast.replaceWith(tr);
									},
									error: function(){
										alert('Error when getting media');
									}
								});
								
							},
							error: function(){
								alert('Error when getting information');
							}
						});
						
					}else{
						if($.type(data) === "string"){
							$('p.messRegis').text(data);
						}
					}
					$('.loader').hide();
					$('.modal-body').css('opacity','1');
					$('button.cancel').prop('disabled', false);
				},
				error: function(){
					$('.loader').hide();
					$('.modal-body').css('opacity','1');
					$('button.cancel').prop('disabled', false);
					$('p.messRegis').text('Sorry, Ajax has some problem!');
				}
			});
		}
	});
	
	$('button.modalRegTag').click(function(){
		var input = $('input[type=text]').val();
		
		if (input == '') {
			$('p.message').text('Please input hastag!').css("color",'red');
			$('button.modalRegTag').prop('disabled', true);
		} else {
			//remove spaces in string input
			hashtag = input.replace(/\s/g, '');
			console.log(hashtag);
			
			$('#myModal').modal('hide');
			$('.loader').show();
			$('.modal-body').css('opacity','0.4');
			$('button.cancel').prop('disabled', true);
			
			var controller = window.location.pathname.split("/")[1];
			
			$.ajax({
				method: "POST",
				url: controller + '/register',
				data: {hashtag:hashtag},
				dataType: 'json',
				success: function(data) {
					if (data == 1) {
						$('p.messRegis').text('You have successfully registered');
						
						//get data of registered hashtag
						$.ajax({
							method: "POST",
							url: '/hashtag/getDataRegister',
							data: {hashtag:hashtag},
							dataType: 'json',
							success: function(data){
								console.log(data);
								
								var no = $('table.table-hashtag tr').length;

								var tr = '<tr class="center"><td>'+no+
											'</td><td><a href="/hashtag/detail?hashtag='+ data.name +'" class="mediaHashtag" target="_blank">'+ hashtag +
											'</a></td><td><a href="/hashtag/media?hashtag='+ data.name +'" target="_blank">'+ data.totalMedia +
											'</a></td></tr>';
								$('table.table-hashtag').append(tr);
								
								
//								var trLast = $('table.table-hashtag tr').last();
//								trLast.css('opacity', '0.4');
//								trLast.find(">td >a.mediaHashtag").bind('click', function(e){
//							        e.preventDefault();
//								})
								
//								$.ajax({
//									method: "POST",
//									url: '/hashtag/getMediaRegister',
//									data: {hashtag:hashtag},
//									dataType: 'json',
//									success: function(data){
//										console.log(data);
//										if (data == 1 || data == true){
//											trLast.css('opacity', '1');
//											trLast.find(">td >a.mediaHashtag").unbind('click');
//										}else{
//											alert('Error');
//										}
//									},
//									error: function(){
//										alert('Error');
//									}
//								});
								
							},
							error: function(){
								alert('Error');
							}
						});
						
						
					} else {
						if ($.type(data) === "string") {
							$('p.messRegis').text(data);	
						}
					}
					$('.loader').hide();
					$('.modal-body').css('opacity','1');
					$('button.cancel').prop('disabled', false);
				},
				error: function(){
					$('.loader').hide();
					$('.modal-body').css('opacity','1');
					$('button.cancel').prop('disabled', false);
					$('p.messRegis').text('Sorry, Ajax has some problem!');
				}
			});
		}
	});
//	$('input[type=text]').keypress(function (e) {
//		  if (e.which == 13) {
//			  var input = $(this).val();
//				//remove spaces in string input
//				input = input.replace(/\s/g, '');
//				//check input
//				if (input.match(/^http([s]?):\/\/.*/)) {
//					if (!input.match(/^http([s]?):\/\/www\.instagram\.com\/.*/)) {
//						$('p.message').text('Sorry! This link has the wrong format. Please re-type!').css("color",'red');
//						$('button.modalReg').prop('disabled', true);
//					}else{
//						$('button.modalReg').click();
//					  }
//				}else{
//					$('button.modalReg').click();
//				}
//		  }
//		});
	
	$('button.cancel').click(function(){
		$('#regisForm').modal('hide');
		$('.modal-backdrop, .modal-backdrop.fade.in').css('opacity',"0");
		$('p.messRegis').text("This is mess");
	});
	
	function initModal(){
		$('#inputUserName').val('');
		$('#inputHashtag').val('');
		$('p.message').text(' ').css("color",'lightblue');
		//$('p.message').text('Example: https://www.instagram.com/instagram/').css("color",'lightblue');
		$('button.modalReg').prop('disabled', false);
		$('button.modalRegTag').prop('disabled', false);
	}
	function getQueryVariable(query){
	       var vars = query.split("/");
	       return vars[3];
	}
});