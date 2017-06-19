$().ready(function(){
	var page = 0;
	var limit = 20;
	var tag = getUrlParameter('hashtag');
	var sort = getUrlParameter('sort') === undefined ? '' : getUrlParameter('sort');
	// console.log(sort);
	var count = 0;
	$('.loadMore-hashtag').click(function(){
		page++;
		$('.loadMore-hashtag').html('Loading...');
		$.ajax({
			method: "POST",
			url: '/hashtag/more',
			dataType: 'json',
			data: {page : page, tag : tag, sort : sort},
			success: function(data){
				console.log(data);
				if(data.length>0){
					for(var i=0;i<data.length;i++){
						count++;
						var html = "<tr class='center'><td style='width: 10%;'>"+ count +"</td>"+
										"<td style='text-align: center !important; width: 200px;'><a target = '_blank' href='https://instagram.com/p/"+data[i].code+"'><img style='height:150px' src='"+data[i].thumbnail_src+"'></a></td>"+
										//"<td><a target = '_blank' href='https://instagram.com/p/"+data[i].code+"'>https://instagram.com/p/"+data[i].code +"</td>"+
										"<td>"+data[i].caption+"</td>"+
										"<td style='width: 10%;'>"+data[i].likes.count+"</td>"+
										"<td style='width: 10%;'>"+data[i].comments.count+"</td>"+
									"</tr>";
						$('.hashtag-detail').append(html);
						$('.loadMore-hashtag').html('Load More');
					}
					if(data.length < limit){
						$('.loadMore-hashtag').remove();
					}
				}else {
					$('#notification').append('<p class= "danger">Not found media</p>');
					$('.loadMore-hashtag').remove();
					$('.hashtag-detail').remove();
				}

				$.ajax({
					method: "POST",
					url: '/hashtag/total',
					data: {tag : tag},
					dataType: 'json',
					success: function(data){
						console.log(data);
						if(data%limit == 0){
							if(page == data/limit){
								$('.loadMore-hashtag').remove();
							}
						}
					},
					error: function(){
						alert('Sorry, Ajax has some problem!');
						$('.loadMore-hashtag').remove();
					}
				});
			},
			error: function(){
				alert('Sorry, Ajax has some problem!');
				$('.loadMore-hashtag').remove();
			}
		})

	});
	$('.loadMore-hashtag').click();
	function getUrlParameter(sParam) {
	    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
	        sURLVariables = sPageURL.split('&'),
	        sParameterName,
	        i;

	    for (i = 0; i < sURLVariables.length; i++) {
	        sParameterName = sURLVariables[i].split('=');

	        if (sParameterName[0] === sParam) {
	            return sParameterName[1] === undefined ? true : sParameterName[1];
	        }
	    }
	};
	var amountScrolled = 300;

	$(window).scroll(function() {
		if ( $(window).scrollTop() > amountScrolled ) {
			$('a.scroll-top').fadeIn('slow');
		} else {
			$('a.scroll-top').fadeOut('slow');
		}
	});
	$('a.scroll-top').click(function() {
		$('html, body').animate({
			scrollTop: 0
		}, 700);
		return false;
	});
});
