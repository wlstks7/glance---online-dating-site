var json_url;
var postdata;
var http_base;
var DEF_CLICK = "click";
var imageData;

$(function(){

	$("#btn_browseFile").on(DEF_CLICK, function(){

		$('input').trigger("click");
	});

	$("#btn_cropImage").on(DEF_CLICK, function(){

		/*imageData = $('.image-editor').cropit('export', {
		  type: 'image/jpeg',
		  quality: 1
		});*/

		imageData = $('.image-editor').cropit('export');
		$('.hidden-image-data').val(imageData);

		parent.saveProfileImage( $('.hidden-image-data').val() );
	});

	$("#btn_cancel").on(DEF_CLICK, function(){

		parent.closeCrop();
	});

	init();

	function init(){

		$('.image-editor').cropit();

		$('form').submit(function() {
			// Move cropped image data to hidden input
			imageData = $('.image-editor').cropit('export');
			$('.hidden-image-data').val(imageData);

			// Prevent the form from actually submitting
			return false;
		});
	}
});