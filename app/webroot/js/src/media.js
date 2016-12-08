$().ready(function(){
	var page = 0;
	var limit = 20;
  var like_img = "/img/like_insta.png";
  var unlike_img = "/img/unlike_insta.png";
	$('.loadMore').click(function(){
		page++;
		$('.loadMore').html('Loading...');
		$.ajax({
			method: "POST",
			url: '/media/more',
			dataType: 'json',
			data: {page:page},
			success: function(data){
				console.log(data);
				if(data.length>0){
          var media_id =[];
					for(var i=0;i<data.length;i++){

            media_id.push({id:data[i].id});

				    var myDate = new Date(1000*data[i].created_time);
				    // console.log(myDate.toLocaleString());

						var textmess = '';
						if(typeof(data[i].caption) != "undefined" && data[i].caption !== null){
						 textmess = data[i].caption.text;
						}

						var typeOfPost = '<div class="cd-timeline-img cd-picture"><img src="/img/cd-icon-picture.svg" alt="Picture"></div>';
						var post = '<a href="'+data[i].link+'" target="_blank"><img class="pre-media" src="'+ data[i].images.low_resolution.url +'" ></a>';
						if(data[i].type == 'video'){
							typeOfPost = '<div class="cd-timeline-img cd-movie"><img src="/img/cd-icon-movie.svg" alt="Movie"></div>';
							post = '<video class="pre-media" controls width="320" height="320"><source src="'+data[i].alt_media_url+'" type="video/mp4"></video>';
						}

						var location = '';
						if(data[i].location !== null){
							typeOfPost = '<div class="cd-timeline-img cd-location"><img src="/img/cd-icon-location.svg" alt="Location"></div>';
							var location = data[i].location.name;
						}
            var numberOfLike = data[i].likes.count;
            var id = data[i].id;
						var html = '<div class="cd-timeline-block">'+
										typeOfPost+
										'<div class="cd-timeline-content">'+
											'<a href="'+ data[i].link +'" target="_blank"><h2>'+ myDate.toUTCString().substr(5, 11)+'</h2></a>'+
											post+
											'<p>'+textmess+'</p>'+
											'<div class="div-like-insta">'+
												'<span id="'+id+'" class = "like"></span>'+
												'<span class="number-insta'+id+'">'+ numberOfLike+'</span>'+
											'</div>'+
										'<div>'+
											'<img class="icon-insta" src="/img/cmt_insta.png" alt="Picture">'+
											'<span class="number-insta">' +data[i].comments.count+ '</span>'+
										'</div>'+
										'<span class="cd-date">'+location+'</span>'+
									'</div>'+
									'</div>';
            $('#cd-timeline').append(html);
            $('.loadMore').html('Load More');
            $img = $('<img class="icon-insta" alt="Picture">');

            if(data[i].user_has_liked == "true"){
              $('#'+id).append(
                $img.attr('src', like_img)
              ).attr('like', 'true');
            }
            else{
              $('#'+id).append(
                $img.attr('src', unlike_img)
              ).attr('like', 'false');
            }
					}
          // like
          // have to check and get from mongodb
          $('.like').click(function(){
            id = this.id;
            liked = $(this).attr('like');
            like_count = parseInt($('.number-insta'+id).text());
            // console.log("media id: "+id + " and like_count: " + like_count);
            $.ajax({

              method: "POST",
              url: '/media/like',
              data: {
                media_id:id,
                like_status: liked,
                num_likes: like_count
              },
              success: function(data){
                // console.log(data);
              },
              error: function(jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
                alert('Sorry, Ajax has a problem!');
                $('.loadMore').remove();
              }
            });
            if(liked == 'true'){
              $(this).attr('like', 'false');
              dislike = like_count-1;
              $('.number-insta'+id).text(dislike);
              $('#'+id+' img').attr('src', unlike_img);
            }
            else{
              $(this).attr('like', 'true');
              add_like = like_count+1;
              $('.number-insta'+id).text(add_like);
              $('#'+id+' img').attr('src', like_img);
            }
            // toggle, like = true -> php call likeMedia() else like = false and call deleteLikeMedia()
          });
					if(data.length < limit){
						$('.loadMore').remove();
					}
				}else {
					$('#notification').append('<p class= "danger">Not found media</p>');
					$('.loadMore').remove();
					$('#cd-timeline').remove();
				}


				//show form comment
				$('.addComment').hide();
				$('.comment').click(function(){
					var id = $(this).attr('data-id');
					$('#'+id).show();
				});

				// ajax send comment
				$('.form-control').keyup(function(e){
					if(e.keyCode ==13 && $(this).val() != ""){
						var id = $(this).attr('data-id');
						var text = $(this).val().trim();
					$.ajax({
							url: '/media/postComment',
							type: 'post',
							data: {id:id,text:text},
							success: function (data) {
								if(data == 400){
								 alert('error');
								 e.preventDefault();
								}
							 	else{
							 		$('.form-control').val('');
							 		var html = '<li>'+
													'<a href="' +'https://www.instagram.com/'+data+'/' +'">'+ data + '</a>'+
													'<label>'+ text + '</label>'+
												'</li>';
									$('.'+id).append(html);
							 	}
			
							}
						});

					}
					else e.preventDefault();
				});


				$.ajax({
					method: "POST",
					url: '/media/total',
					dataType: 'json',
					success: function(data){
						console.log(data);
						if(data%limit == 0){
							if(page == data/limit){
								$('.loadMore').remove();
							}
						}
					},
					error: function(){
						alert('Sorry, Ajax has some problem!');
						$('.loadMore').remove();
					}
				});
			},
			error: function(){
				alert('Sorry, Ajax has some problem!');
				$('.loadMore').remove();
			}
		})
	});
	$('.loadMore').click();

	var amountScrolled = 300;

	$(window).scroll(function() {
		if ( $(window).scrollTop() > amountScrolled ) {
			$('a.back-to-top').fadeIn('slow');
		} else {
			$('a.back-to-top').fadeOut('slow');
		}
	});
	$('a.back-to-top').click(function() {
		$('html, body').animate({
			scrollTop: 0
		}, 700);
		return false;
	});
});
