$(document).ready(function(){
	$(".nav1").css("height","400px");
	$(".ntabs li h3:first").addClass("current");
	$(".ntabs li h3").click(function() {
		$(".ntabs li h3").removeClass("current");
		$(this).addClass("current");
		$(".ntabs li ul").animate({height:"0"},"fast");
		$(this).next().animate({height:"500"},"slow");
	});
});