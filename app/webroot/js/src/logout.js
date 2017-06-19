$().ready(function(){
	$('.buttonLogout').click(function() {
		iframe = '<iframe src="https://instagram.com/accounts/logout/" width="0" height="0"></iframe>'
		$(iframe).load(function() {
			$.ajax({
				url: '/register/logout/',
				dataType: 'json',
				success: function(data){
					console.clear();
					if (data == 1) {
						window.location = "/";
					}
				},
				error: function(){
					alert('Sorry, Ajax has some problem!');
				}
			})
		}).appendTo("body");
	});
});