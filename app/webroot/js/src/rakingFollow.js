$(document).ready(function() {
		var l = window.location;
		var base_url = l.protocol + "//" + l.host + "/" + l.pathname.split('/')[1];
		var baseUrl = document.location.origin;
		console.log(baseUrl);
		var page = -1;
		var pageCurrent = 10; //total record /page
		var baseUrl = (window.location).href; // You can also use document.URL
		var koopId = baseUrl.substring(baseUrl.lastIndexOf('=') + 1);
		$('#loadMore').click(function(){
			page++;
			var start = page*pageCurrent + 1;
			$('#loadMore').html('Loading ...');
			$.ajax({
				method: "POST",
				url: base_url+'/Ranking/ajax',
				dataType: 'json',
				data: {page:page,id:koopId,currentPage:pageCurrent},
				success: function (result) {
					console.log(result);
					var html = '';var i = start;
					if (result === 404) {
						$('.followList').remove();
						var tpl = '';
						tpl += '<div class="container">';
						tpl += '	<div class="col-md-8 col-md-offset-2" id="frame_content_error">';
						tpl += '		<div class="col-md-10 col-md-offset-1" id="frame_error_404">';
						tpl += '			<div class="col-md-6" id="error_404">';
						tpl += '				<h1 >404</h1>';
						tpl += '			</div>';
						tpl += '			<div class="col-md-6" id="authen">';
						tpl += '				<h3>Not found access_token.</h3>';
						tpl += '			</div>';
						tpl += '		</div>';
						tpl += '	</div>';
						tpl += '</div>';
					
						$("#content").html(tpl);
					} else {
						result.forEach(function(item) {
							html += "<tr class='center'>";
							html += "	<td>"+i+"</td>";
							html += "	<td>"+item.full_name+"</td>";
							html += "	<td><a href='https://www.instagram.com/" +item.username +"'"+ ">"+item.username+" </a></td>";
//							html += "	<td>"+item.username+"</td>";
							html += "	<td>"+item.totalFollow+"</td>";
							html += "</tr>";
							i++;
						});
						$('#appendFollow').append(html);
					}
					
					
					if((result.length) < pageCurrent) {
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

