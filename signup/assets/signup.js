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
		$("#page_account").show();
		$("#p_info").text("Awesome!! Your password is saved. Now, let's setup your profile.");

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

	function add_customer(){


		/*
			userName = trim(sanitize(_POST['userName']));
			password = trim(_POST['password']);

			zipcode = sanitize(_POST["zipcode"]);

			birthMonth = sanitize(_POST["birthMonth"]);
			birthDay = sanitize(_POST["birthDay"]);
			birthYear = sanitize(_POST["birthYear"]);

			firstName = sanitize(_POST["firstName"]);
			relationshipStatus = sanitize(_POST["relationshipStatus"]);
			gender = sanitize(_POST["gender"]);
			height = sanitize(_POST["height"]);
			eyeDesc = sanitize(_POST["eyeDesc"]);
			bodyType = sanitize(_POST["bodyType"]);
			hairDesc = sanitize(_POST["hairDesc"]);
			religious = sanitize(_POST["religious"]);
			ethnicity = sanitize(_POST["ethnicity"]);
			income = sanitize(_POST["income"]);
			smokerPref = sanitize(_POST["smokerPref"]);
			drinkingPref = sanitize(_POST["drinkingPref"]);
			children = sanitize(_POST["children"]);
			*/

		var userName 				=		$.trim($("#userName").val());
		var password 				=		$.trim($("#password").val());
		var zipcode 				=		$.trim($("#zipcode").val());
		var birthMonth 				= 		$.trim($("#birthMonth").val());
		var birthDay 				=		$.trim($("#birthDay").val());
		var birthYear 				= 		$.trim($("#birthYear").val());
		var firstName 				= 		$.trim($("#firstName").val());
		var relationshipStatus 		= 		$.trim($("#relationshipStatus").val());
		var gender 					= 		$.trim($("#gender").val());
		var seekingGender 			= 		$.trim($("#seekingGender").val());
		var height 					= 		$.trim($("#height").val());
		var eyeDesc 				= 		$.trim($("#eyeDesc").val());
		var bodyType 				= 		$.trim($("#bodyType").val());
		var hairDesc 				= 		$.trim($("#hairDesc").val());
		var religious 				= 		$.trim($("#religious").val());
		var ethnicity 				= 		$.trim($("#ethnicity").val());
		var income 					= 		$.trim($("#income").val());
		var smokerPref 				= 		$.trim($("#smokerPref").val());
		var drinkingPref 			= 		$.trim($("#drinkingPref").val());
		var children 				= 		$.trim($("#children").val());


		if ( password == "" ) {
			_alert("Important Message", "Please enter a password");
			$("#password").focus();
			return false;
		}

		if ( userName == "" ) {
			_alert("This is important", "Please enter a user name.");
			$("#lastName").focus();
			return false;
		};

		if ( firstName == "" ) {
			_alert("This is important", "Please enter your first name.");
			$("#firstName").focus();
			return false;
		};

		postdata = {
			"i" : $("#form_token").attr("data-id"),
			"userName" : userName,
			"password" : password,
			"firstName" : firstName,
			"zipcode" : zipcode,
			"birthMonth" : birthMonth,
			"birthDay" : birthDay,
			"birthYear" : birthYear,
			"relationshipStatus" : relationshipStatus,
			"gender" : gender,
			"seekingGender" : seekingGender,
			"height" : height,
			"eyeDesc" : eyeDesc,
			"bodyType" : bodyType,
			"hairDesc" : hairDesc,
			"religious" : religious,
			"ethnicity" : ethnicity,
			"income" : income,
			"smokerPref" : smokerPref,
			"drinkingPref" : drinkingPref,
			"children" : children
		}

		//show the loading page
		$("#pageLoading").show();

		//hide this page
		$("#pageAccountInfo").hide();

		json_url = "func/account_add.php";

		_ajax(postdata, json_url, function(json){

			if (json.status == "success") {

				window.location = "account-created.php";

			} else {

				$("#pageLoading").hide();
				$("#pageAccountInfo").show();
				alert(json.msg);
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
		    timeout: function(){
		        alert("Couldn't reach the server");
		    },
		    success: function(json){
		    	
		       success(json);
		    }
		})
	}
});