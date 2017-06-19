
<script type="text/javascript">


$(function () {
	var page = -1;
	var limit = 20;
	$('#loadMore').click(function(){
		page++;
		$('#loadMore').html('Loading...');
		$.ajax({
			method: "POST",
			url: '/test/index',
			dataType: 'json',
			data:{
				page: page, ID: "3980281197"
			},
			success: function(data){
				            console.log(page);
			                console.log(data);
					// 		if(data.length>0){
 				// 	for(var i=0;i<data.length;i++){
 				// 		console.log(data[i].username);
					// 	}
					// }
// 					    var myDate = new Date(1000*data[i].created_time);
// //					    console.log(myDate.toLocaleString());
					    
// 						var textmess = '';
// 						if(typeof(data[i].caption) != "undefined" && data[i].caption !== null){
// 						 textmess = data[i].caption.text;
// 						}
						
// 						var typeOfPost = '<div class="cd-timeline-img cd-picture"><img src="/img/cd-icon-picture.svg" alt="Picture"></div>';
// 						var post = '<a href="'+data[i].link+'" target="_blank"><img class="pre-media" src="'+ data[i].images.low_resolution.url +'" ></a>';
// 						if(data[i].type == 'video'){
// 							typeOfPost = '<div class="cd-timeline-img cd-movie"><img src="/img/cd-icon-movie.svg" alt="Movie"></div>';
// 							post = '<video class="pre-media" controls width="320" height="320"><source src="'+data[i].alt_media_url+'" type="video/mp4"></video>';
// 						}
						
// 						var location = '';
// 						if(data[i].location !== null){
// 							typeOfPost = '<div class="cd-timeline-img cd-location"><img src="/img/cd-icon-location.svg" alt="Location"></div>';
// 							var location = data[i].location.name;
// 						}
						
// 						var html = '<div class="cd-timeline-block">'+
// 										typeOfPost+ 
// 										'<div class="cd-timeline-content">'+
// 											'<a href="'+ data[i].link +'" target="_blank"><h2>'+ myDate.toUTCString().substr(5, 11)+'</h2></a>'+
// 											post+
// 											'<p>'+textmess+'</p>'+
// 											'<div class="div-like-insta">'+
// 												'<img class="icon-insta" src="/img/like_insta.png" alt="Picture">'+
// 												'<span class="number-insta">'+ data[i].likes.count+'</span>'+
// 											'</div>'+
// 										'<div>'+
// 											'<img class="icon-insta" src="/img/cmt_insta.png" alt="Picture">'+
// 											'<span class="number-insta">' +data[i].comments.count+ '</span>'+
// 										'</div>'+
// 										'<span class="cd-date">'+location+'</span>'+
// 									'</div>'+
// 									'</div>';
// 						$('#cd-timeline').append(html);
// 						$('.loadMore').html('Load More');
// 					}
// 					if(data.length < limit){
// 						$('.loadMore').remove();
// 					}
// 				}else {
// 					$('#notification').append('<p class= "danger">Not found media</p>');
// 					$('.loadMore').remove();
// 					$('#cd-timeline').remove();
 				}
				
				
		
		});
		
	});
	$('#loadMore').click();


// $('a[href=#top]').click(function () {
//     $('body,html').animate({
//         scrollTop: 0
//     }, 600);
//     return false;
// });

// $(window).scroll(function () {
//     if ($(this).scrollTop() > 50) {
//         $('.totop a').fadeIn();
//     } else {
//         $('.totop a').fadeOut();
//     }
// });
});
</script>

<strong>LOAD MORE</strong>



<a href="#" id="loadMore">Load More</a>

<p class="totop"> 
    <a href="#top">Back to top</a> 
</p>