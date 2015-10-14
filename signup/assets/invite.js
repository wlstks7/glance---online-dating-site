var json_url;
var postdata;
var _baseline = "quiz";
var http_base;
var cart_uniq_id = 1;
var cart = [];

$(function(){

	$("#btn_create_invite").on(click, function(){

		var f = $("#form_token").attr("data-id");
		var email =	$.trim($("#email").val());

		if ( email.indexOf("@") == -1 || email.indexOf(".") == -1 ) {
			_alert("Important", "This does not appear to be a valid email address. Please enter your email address.");
			$("#email").focus();
			return false;
		};

		$('.elements').hide();
		$('.stepTwo').show();

	});

	init();
	
	function inviteMe(){

		var f = $("#form_token").attr("data-id");
		var email =	$.trim($("#email").val());

		if ( email.indexOf("@") == -1 || email.indexOf(".") == -1 ) {
			_alert("Important", "This does not appear to be a valid email address. Please enter your email address.");

			captchaInit();
			$('.elements').hide();
			$('.stepOne').show();

			$("#email").focus();
			return false;
		};

		$("#myEmail").text(email);

		postdata = {
			"f" : f,
			"email" : email
		}

		//show the loading page
		$("#pageLoading").show();

		//hide this page
		$("#pageAccountInfo").hide();

		json_url = "../func/invite_email.php";

		_ajax(postdata, json_url, function(json){

			if (json.status == "success") {

				$('.elements').hide();
				$('.stepThree').show();

				$("#pageLoading").hide();
				$("#pageAccountInfo").show();

			} else {

				$("#pageLoading").hide();
				$("#pageAccountInfo").show();
				alert(json.msg);
				location.reload()
				return false;
			};
		});
	}

	function captchaInit(){

		$("#humanTest").html("");

		var i;
		var arr = [];
		while(arr.length < 3){
		  var randomnumber=Math.ceil(Math.random()*7)
		  var found=false;
		  for(var i=0;i<arr.length;i++){
			if(arr[i]==randomnumber){found=true;break}
		  }
		  if(!found)arr[arr.length]= '<img class="ui image clickable _captchaItem" src="captcha/item' + randomnumber + ".png" + '">';      //"item" + randomnumber + ".png";
		}

		//get non food item
		randomnumber=Math.ceil(Math.random()*3);
		var n = '<img class="ui image clickable _captchaItem _captchaItem_tag" src="captcha/nitem' + randomnumber + ".png" + '">';

		//choose a random spot to place the non food item
		randomnumber=Math.ceil(Math.random()*3);

		//build the items
		for (var i = 1; i < 4; i++) {
			
			console.log(i);

			if (i == randomnumber) {

				$("#humanTest").append(n + arr[i]);

			} else {

				$("#humanTest").append(arr[i]);
			}
		};

		//create a click event
		$("._captchaItem").off("click").on("click", function(){

			var that = this;

			if ( $(that).hasClass("_captchaItem_tag") ) {

				inviteMe();

			} else {

				_alert("Try Again", "Please choose the item that is NOT food :-)");
			}
		});
	}

	function init(){

		$('.dropdown').dropdown();
		$('.elements').hide();
		$('#email').val("");
		$('.stepOne').show();
		captchaInit();

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