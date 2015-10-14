var json_url;
var postdata;
var http_base;
var DEF_CLICK = "click";

$(function(){

	$("#textLink").on(DEF_CLICK, function(){

		$(this).select().focus();
	});

	$('.form_input').on("focus", function(){

		$("#error_msg").hide();
	});

	$('#form_login').on('submit', function(e) {
		e.preventDefault();
		
		if ( $.trim( $("#emailAddress").val() ) == "" ) {
			$("#emailAddress").focus();
			_alert("Important Message", "Please enter your email address");
			return false;
		}

		if ( $.trim( $("#password").val() ) == "" ) {
			$("#password").focus();
			_alert("Important Message", "Please enter your password");
			return false;
		}

		postdata = {
			"i" : $("#form_token").attr("data-id"),
			"e" : $("#emailAddress").val(),
			"u" : $("#userName").val(),
			"p" : $("#password").val()
		}

		//show the loading page
		$("#pageLoading").show();

		//hide this page
		$("#pageLogin").hide();

		json_url = "../func/login.php";

		_ajax(postdata, json_url, function(json){

			console.log("9");
			if (json.status == "success") {

				var this_site = $("#this_site").attr("data-id");

				window.location.href = this_site + "home/";

			} else {

				$("#pageLoading").hide();
				$("#pageLogin").show();
				_alert("Important Message", json.msg);
				return false;
			};
		});
	});

	init();

	function init(){

		$("#password").val("");
		$("#emailAddress").val("").focus();
		$('html, body').animate({ scrollTop: 0 }, 0);
	}

	function _alert(title, description){

		$("#error_msg").html(description).show();
	}

	function _ajax(data, url, success) {

		$.ajax({
		    type: "POST",
		    url: url,
		    data: data,
		    timeout: function(){
		        alert("Couldn't reach the server");
		    },
		    success: function(json){
		    	
		       success(json);

		    }
		})
	}
});