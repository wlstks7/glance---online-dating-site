var DEF_CLICK = "click";

$(function(){

	$(".clickable").on(DEF_CLICK, function(){

		var url = $(this).attr("data-url");
		parent.changeBanner(url);

	});

});