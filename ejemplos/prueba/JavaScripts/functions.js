$(document).ready(function() {
	var a = '.main-menu nav ul li'

		$(a).on('mouseover', function() {
		$(this).addClass("active");
		});
		$(a).on('mouseout', function() {
			$(this).removeClass("active");
		});

});