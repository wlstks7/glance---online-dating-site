var json_url;
var postdata;
var _baseline = "quiz";
var http_base;
var cart_uniq_id = 1;
var cart = [];

$(function(){

	$("#btn_create_account").on(click, function(){

		add_customer();
	});

	//create the login
	$("#btn_create_login").on(click, function(){

		var password = $.trim( $("#password").val() );

		if ( password.length < 6 ) {
			_alert("Important Message", "Please enter a password that has 6 characters or more.");
			$("#password").focus();
			return false;
		}
		$("#page_login").hide();

		update_password();

	});

	init();

	$("#togglePassword").checkbox({
		
		onChecked : function(){
			
			$("#password").attr("type", "text");

		},
		onUnchecked : function(){
			
			$("#password").attr("type", "password");

		}
	});

	function update_password(){

		var password = $.trim($("#password").val());

		if ( $.trim( $("#password").val() ) == "" ) {
			_alert("Important Message", "Please enter a password");
			$("#password").focus();
			return false;
		}

		postdata = {
			"password" : password,
			"i" : $("#form_token").attr("data-id")
		}

		//show the loading page
		$("#pageLoading").show();
		$("#pageAccountInfo").hide();

		json_url = "func/account_update.php";

		_ajax(postdata, json_url, function(json){

			if (json.status == "success") {

				window.location = "password_reset.php";

			} else {

				$("#pageLoading").hide();
				$("#pageAccountInfo").show();
				_alert("Important Message", json.msg);
				return false;
			};
		});
	}

	function init(){

		$('.dropdown').dropdown();
		$("#password").val("");
		$('.ui.checkbox').checkbox();
		$('html, body').animate({ scrollTop: 0 }, 0);
		$('#password')
			.popup({
				on: 'focus'
		});
		$("#userName").val("").focus();
	}

	function _alert(title, description){

		$("#alertTitle").text(title);
		$("#alertDescription").text(description);
		$('#alert').modal('show');

	}

	function _ajax(data, url, success) {

		$.ajax({
		    type: "POST",
		    url: url,
		    data: data,
		    dataType: "json",
		    async: false, 
		    timeout: function(){
		        alert("Couldn't reach the server");
		    },
		    success: function(json){
		    	
		       success(json);
		    }
		})
	}
});