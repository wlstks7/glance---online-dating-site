var json_url;
var postdata;
var _baseline = "quiz";
var http_base;
var cart_uniq_id = 1;
var cart = [];

$(function(){

	$("#textLink").on(click, function(){

		$(this).select().focus();
	});

	$("#btn_update_account").on(click, function(){

		update_customer();
	});

	init();
	
	function update_customer(){

		var id = $("#c_id").attr("data-id");
		var firstName =	$.trim($("#firstName").val());
		var lastName = $.trim($("#lastName").val());
		var companyName = $.trim($("#companyName").val());
		var address = $.trim($("#address").val());
		var city = $.trim($("#city").val());
		var state = $.trim($("#state").val());
		var zip = $.trim($("#zip").val());
		var phone = $.trim($("#phone").val());
		var email = $.trim($("#email").val());
		var website = $.trim($("#website").val());
		var twitter = $.trim($("#twitter").val());
		var facebook = $.trim($("#facebook").val());

		if ( firstName == "" ) {
			_alert("This is important", "Please enter your first name.");
			$("#firstName").focus();
			return false;
		};

		if ( lastName == "" ) {
			_alert("This is important", "Please enter your last name.");
			$("#lastName").focus();
			return false;
		};

		if ( email == "" ) {
			_alert("This is important", "Please enter your email address.");
			$("#email").focus();
			return false;
		};


		postdata = {
			"id" : id,
			"first_name" : firstName,
			"last_name" : lastName,
			"company_name" : companyName,
			"address" : address,
			"city" : city,
			"state" : state,
			"zip" : zip,
			"phone" : phone,
			"email" : email,
			"website" : website,
			"twitter" : twitter,
			"facebook" : facebook
		}

		//show the loading page
		$("#pageLoading").show();

		//hide this page
		$("#pageAccountInfo").hide();

		json_url = "../func/account_update.php";

		_ajax(postdata, json_url, function(json){

			if (json.status == "success") {

				$("#pageLoading").hide();
				$("#pageAccountInfo").show();

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