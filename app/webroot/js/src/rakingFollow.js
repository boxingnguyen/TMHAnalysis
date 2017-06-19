$(document).ready(function() {
		var page = -1;
		var pageCurrent = 18; //total record /page
		var baseUrl = (window.location).href; // You can also use document.URL
		var koopId = baseUrl.substring(baseUrl.lastIndexOf('=') + 1);
		$('#loadMore').click(function(){
			page++;
			var start = page*pageCurrent + 1;
			$('#loadMore').html('Loading ...');
			$.ajax({
				method: "POST",
				url: '/FollowRanking/ajax',
				dataType: 'json',
				data: {page:page,id:koopId,currentPage:pageCurrent},
				success: function (result) {
					console.log(result);
					var html = '';var i = start;
					if (result === 404) {
						$('.followList').remove();
						var tpl = '';

						tpl += '<div class="container">';
						tpl +='<div class="col-md-4 col-md-offset-4" id="frame_content_error">';
						tpl +='<div class="col-md-2 col-md-offset-2" >';
						tpl +='<img src="/img/point.png" height="60" width="60">';
						tpl +='</div>';	
						tpl +='<div class="col-md-8" id="authen"><h3>Not found access_token</h3></div>'
						tpl +='</div>'
						tpl +='<div>';					
						$("#content").html(tpl);
					} else {
						result[1].forEach(function(item) {
							html += "<tr class='center'>";
							html += "	<td>"+i+"</td>";
							html += "	<td>"+item.full_name+"</td>";
							html += "	<td><a href='https://www.instagram.com/" +item.username +"'"+ ">"+item.username+" </a></td>";
							html += "	<td>"+item.totalFollow+"</td>";
							html += "</tr>";
							i++;
						});
						$('#appendFollow').append(html);
					}
					if((result[1].length) < pageCurrent) {
						$('#loadMore').fadeOut();
					}
					if((result[0] / pageCurrent) == 1) {
						$('#loadMore').fadeOut();
					}
				},
				error: function(a,b,c) {
					console.log(a); console.log(b); console.log(c);
				},
				complete: function() {
					$('#loadMore').html('Load more');
					
				}			
			});
			
			$('html,body').animate({
	            scrollTop: $(this).offset().top
	        }, 500);
			
		});
		
		$('#loadMore').click();
		var amountScrolled = 50;
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
			}, 80);
			return false;
		});

});

