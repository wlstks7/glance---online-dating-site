var DEF_CLICK = "click";
var postPageIndex = 0;
var conversationPageIndex = 0;
var currentBackground;
var getPostsBusy = false;
var current_message_element;
var searchItems = {};
var mobileDevice = false;

$(function(){

	init();

	if (mobilecheck() == true) {
		mobileDevice = true;
		$("#inboxMessages").css("position", "inherit");
	}

	$("#messageButtonArea").hide();

	$(window).resize(function() {

		messageResize();
    });

	/*$('#inboxMessages').sticky({
    	context: '#contentCenterCol',
    	offset: 60
    });*/

	/*

	TAP EVENTS

	*/

	//save this post 
	$("#btn_post").on(DEF_CLICK, function(){

		$("#btn_working").show();
		$("#btn_post").hide();
		
		sendMessage();
	});

	//insert private link into message 
	$("#btn_attachLink").on(DEF_CLICK, function(){

		$("#messageReply").focus();

		var msg = $("#messageReply").val();
		var link_msg = "[Your private link will automatically go here]";

		$("#messageReply").val(msg + '\n\n' + link_msg);
	
	});

	$("#btn_reportUser").on(DEF_CLICK, function(){
	
		//show user message
		var ele = '<div id="statusReportUser" class="ui small blue message" style="margin-bottom: 14px;"> <p>Here are a few reasons to report a user: commercial solicitations, inappropriate behavior, threatening messages, harassment/stalking, fake profile, vulgar language, obscene photos, bullying or pressure to communicate offline. </p><p>Please use your best judgement when reporting a user as we take these reports very seriously.</p> <p><div class="ui form"><div class="field"> <label>Reason for report</label> <div id="_reportReason" class="ui selection dropdown"> <input id="reportReasonValue" name="reportReasonValue" type="hidden"> <div class="default text">Please Choose</div> <i class="dropdown icon"></i> <div class="menu"> <div class="header"> Message Issue </div> <div class="item" data-value="SCAM SOLICITATION">Scam solicitation</div> <div class="item" data-value="MESSAGE WITH ADVERTISEMENTS">I received a message with advertisements</div> <div class="item" data-value="INAPPROPRIATE BEHAVIOR">Inappropriate behavior</div> <div class="item" data-value="THREATENING MESSAGE">Threatening message</div> <div class="item" data-value="HARASSMENT/STALKING">Harassment/Stalking</div> <div class="item" data-value="PRESSURE TO COMMUNICATE OFFLINE">Pressure to communicate offline</div> <div class="item" data-value="I FEEL BULLIED">I feel bullied</div> <div class="header"> Profile Issue </div> <div class="item" data-value="PROFILE CONTAINS ADVERTISEMENTS">Profile contains advertisements</div> <div class="item" data-value="FAKE PROFILE">Fake profile</div> <div class="item" data-value="PROFILE IS HATEFUL">Profile is hateful</div> <div class="item" data-value="PROFILE IS RACIST">Profile is racist</div> <div class="item" data-value="OBSCENE PHOTOS">Obscene photos</div> <div class="item" data-value="VULGAR LANGUAGE">Vulgar language</div> </div> </div> </div></div></p><p> <button id="btn_okReportUser" class="ui button blue">Report Now</button> <button id="btn_cancelReportUser" class="ui button">Cancel</button> </p> </div>';
		
		$("#statusArea").html(ele);

		//bind the drop event
		$('#_reportReason').dropdown();

		//bind the click event
		$("#btn_cancelReportUser").off(DEF_CLICK).on(DEF_CLICK, function(){

			$("#statusReportUser").remove();
		});

		//bind the click event
		$("#btn_okReportUser").off(DEF_CLICK).on(DEF_CLICK, function(){

			//grab the id of the message
			var id = $("#btn_deleteConversation").attr("data-id");
			
			var reportReason = $("#reportReasonValue").val();

			$("#statusArea").html("");

			var postdata = {
				"id" : id,
				"report" : reportReason
			}

			json_url = "../func/messages-report-user.php";

			_ajax(postdata, json_url, function(json){

				if (json.status == "success") {
					
					//show user message
					var ele = '<div class="ui small green message" style="margin-bottom: 14px;">' + json.msg + '</div>';
					$("#statusArea").html(ele);
					
					setTimeout(function(){
						$("#statusArea").html("");
					},2500);
				}
			});
		});
	});
	
	$("#btn_blockUser").on(DEF_CLICK, function(){
	
		//show user message
		var ele = '<div id="statusBlockUser" class="ui small blue message" style="margin-bottom: 14px;"> <p>Quietly block this user from showing up in searches, viewing your profile or sending you future messages.</p> <p> <button id="btn_okBlockUser" class="ui button blue">Block Now</button> <button id="btn_okBlockDoucheUser" class="ui button blue">Block & Delete Conversation</button> <button id="btn_cancelBlockUser" class="ui button">Cancel</button> </p> </div>';
		
		$("#statusArea").html(ele);

		//bind the click event
		$("#btn_cancelBlockUser").off(DEF_CLICK).on(DEF_CLICK, function(){

			$("#statusBlockUser").remove();
		});

		//bind the click event
		$("#btn_okBlockUser").off(DEF_CLICK).on(DEF_CLICK, function(){

			$("#statusArea").html("");

			//grab the id of the message
			var id = $("#btn_deleteConversation").attr("data-id");

			var postdata = {
				"id" : id
			}

			json_url = "../func/messages-block-user.php";

			_ajax(postdata, json_url, function(json){

				if (json.status == "success") {
					
					//show user message
					var ele = '<div class="ui small green message" style="margin-bottom: 14px;">' + json.msg + '</div>';
					$("#statusArea").html(ele);
					
					setTimeout(function(){
						$("#statusArea").html("");
					},1500);
				}
			});
		});

		//bind the douche user click event - lol... douche user
		$("#btn_okBlockDoucheUser").off(DEF_CLICK).on(DEF_CLICK, function(){

			$("#statusArea").html("");

			//grab the id of the message
			var id = $("#btn_deleteConversation").attr("data-id");

			var postdata = {
				"id" : id
			}

			json_url = "../func/messages-block-user.php";

			_ajax(postdata, json_url, function(json){

				if (json.status == "success") {
					
					//show user message
					var ele = '<div class="ui small green message" style="margin-bottom: 14px;">' + json.msg + '</div>';
					$("#statusArea").html(ele);
					
					setTimeout(function(){
						$("#statusArea").html("");
					},1500);
				} 
			});

			//reset the page
			conversationPageIndex=0;
			postPageIndex=0;

			$("#messages_list").html("");

			var postdata = {
				"id" : id
			}

			json_url = "../func/messages-delete.php";

			_ajax(postdata, json_url, function(json){

				if (json.status == "success") {
					
					getMessages();

					$(".__messageViewBlank").show();
					$(".__messageView").hide();
				}
			});
		});
	});

	$("#btn_deleteConversation").on(DEF_CLICK, function(){
	
		//show user message
		var ele = '<div id="statusDeleteConversation" class="ui small blue message" style="margin-bottom: 14px;"> <p>This will remove this conversation from your inbox. It cannot be undone.</p> <p> <button id="btn_removeConversation" class="ui button blue">Remove It</button> <button id="btn_cancelRemoveConversation" class="ui button">Cancel</button> </p> </div>';
		$("#statusArea").html(ele);

		//bind the click event
		$("#btn_cancelRemoveConversation").off(DEF_CLICK).on(DEF_CLICK, function(){

			$("#statusDeleteConversation").remove();
		});

		//bind the click event
		$("#btn_removeConversation").off(DEF_CLICK).on(DEF_CLICK, function(){

			//reset the page
			conversationPageIndex=0;
			postPageIndex=0;

			$("#statusDeleteConversation").remove();

			$("#messages_list").html("");

			var id = $("#btn_deleteConversation").attr("data-id");

			var postdata = {
				"id" : id
			}

			json_url = "../func/messages-delete.php";

			_ajax(postdata, json_url, function(json){

				if (json.status == "success") {
					
					getMessages();

					$(".__messageViewBlank").show();
					$(".__messageView").hide();
				}
			});
		});
	});

	$("#btn_notInterested").on(DEF_CLICK, function(){
	
		//show user message
		var ele = '<div id="statusNotInterested" class="ui small blue message" style="margin-bottom: 14px;"> <p>This will politely tell this user you are not interested and block them from sending any further messages.</p> <p> <button id="btn_sendNotInterested" class="ui button blue">Send It</button> <button id="btn_cancelNotInterested" class="ui button">Cancel</button> </p> </div>';
		$("#statusArea").html(ele);

		//bind the click event
		$("#btn_cancelNotInterested").off(DEF_CLICK).on(DEF_CLICK, function(){

			$("#statusNotInterested").fadeOut("slow").remove();
		});

		//bind the click event
		$("#btn_sendNotInterested").off(DEF_CLICK).on(DEF_CLICK, function(){

			//reset the page
			conversationPageIndex=0;

			$("#statusNotInterested").fadeOut("slow").remove();
			sendNotInterested();
		});
	});

	$("#btn_toTop").on(DEF_CLICK, function(){

		$("html, body").animate({ scrollTop: 0 }, "slow");

	});

	$("#messageReply").on("focus", function(){

		//check to see if there is any text in the box
		if ( $(this).val().length > 0 ) {
			return false;
		};

		$(this).css("cssText", "min-height: 160px !important;");

		$("#messageButtonArea").show();
	});

	$("#messageReply").on("blur", function(){

		//check to see if there is any text in the box
		if ( $(this).val().length > 0 ) {
			return false;
		};

		$(this).css("cssText", "min-height: 50px !important;");

		$("#messageButtonArea").hide();
	});

	setInterval(function(){

	    //check for a valid session every 2 minutes
	    $.ajax({
	        type: "POST",
	        url: "../func/tap.php",
	        timeout: function(){
	                  
	          },
	        success: function(msg){

	          
	        }
	    });

	},120000);  //300000 

	/*
	
		functions

	*/

	function init(){

		$("#btn_working").hide();
		postPageIndex = 0;
		$("#messages_list").html("");
		$('.dropdown').dropdown();
		$('.ui.checkbox').checkbox();
		$('.popup').popup();
		$('html, body').animate({ scrollTop: 0 }, 0);
		//scrollListen();
		getMessages();

		setTimeout(function(){
			messageResize();
		},500);

	}

	function getMessages(){

		if (getPostsBusy == true) {
			return false;
		}

		if (postPageIndex == -1) {
			return false;
		}
		
		getPostsBusy = true;

		var postSeed = $("#post_seed").attr("data-id");

		var postdata = {
			"postPageIndex" : postPageIndex
		}

		postPageIndex++;

		json_url = "../func/messages-get.php";

		_ajax(postdata, json_url, function(json){

			if (json.status == "success") {

				if (json.results == "") {

					if (postPageIndex == 1) {

						//there are no posts
						var the_end = '<div class="noMessagesFound">No Messages</div>';

					} else {

						var the_end = '<div style="margin-top:10px;" class="endOfPosts ui blue mini button"><i class="icon arrow cirle up"></i>Back to top</div>';
					}

					postPageIndex = -1;
					
					//render the message
					$("#messages_list").append(the_end);
					$(".loadingPosts").remove();
				};

				var ele, 
					x = 1,
					newMessage,
					html = "",
					my_profile_pic = $("#__mainProfileImage").attr("src"),
					newMessageElement = '<div data-tag="green" class="ui green circular label newMessageLabel"></div>',
					this_site = $("#this_site").attr("data-id");

				$.each(json.results, function(k,v){

					if (v.msg_excerpt.length > 48) {
						v.msg_excerpt = v.msg_excerpt + "..."
					}

					//add emoticons
					v.msg_excerpt = emoticons(v.msg_excerpt);

					ele = '<div id="__messageEvent_' + v.id + '" data-name="' + v.firstName + '" data-screenName="' + v.userName +'" data-id="' + v.id + '" class="event messageEvent"> [NEWMESSAGE] <div class="label"> <img src="' + v.profileImage + '" class="hidden_image_img"> <div style="width:38px;height:1px;"></div></div> <div class="content"> <div class="summary"> <table> <tbody> <tr> <td class="messageHeaderLeft">' + v.firstName + '</td> <td class="messageHeaderRight"> ' + v.activity_date + '</td> </tr> </tbody> </table> <div class="messagePreview"><span>' + v.msg_excerpt + ' </span></div> </div> </div> </div>';

					if (v.msg_read == "0") {
						newMessage = newMessageElement;
					} else {
						newMessage = "";
					}

					ele = ele.split("[NEWMESSAGE]").join(newMessage);

					html = html + ele;

					x++;

				});
				
				$(".loadingPosts").remove();

				//make sure we got 15 posts... if not... this is the end
				if (x==16) {
					html = html + '<div style="margin-top:20px;font-size:8px;" class="ui button small blue">Load More Messages</div>';
				};

				if (postPageIndex != -1) {
					//html = html + '<div class="loadingPosts">Loading more posts...</div>';
				}
			
				//render the posts
				$("#messages_list").append(html);

				messageResize();

				hoverImageEvent();

				$(".messageEvent").off(DEF_CLICK).on(DEF_CLICK, function(){

					conversationPageIndex=0;
					getMessage(this);
				});

				$(".messageHeaderLeft > a").off(DEF_CLICK).on(DEF_CLICK, function(e){
					e.stopPropagation();

				});

				getPostsBusy = false;

				$("html, body").animate({ scrollTop: 0 }, "slow");

			} else {

				getPostsBusy = false;

				_alert("Important Message", json.msg);
				return false;
			};
		});
	}

	function getMessage(ele){

		var messagesReturnedMax = 20;
		var this_site = $("#this_site").attr("data-id");

		//set this element in case we want to refresh this conversation
		current_message_element = ele;

		//clear the reply
		$("#messageReply").val("").trigger("blur");

		//remove the not interested dialog if visible
		//$("#statusNotInterested").remove();
		$("#statusArea").html("")

		//place the data we have while we fetch the conversation...
		$(".__messageViewBlank").hide();
		$(".__messageView").show();
		$(".messageEvent").removeClass("__active")
		$(ele).addClass("__active")

		var profileImage = $(ele).find(".hidden_image_img").attr("src");
		$("#fromProfileImage").attr("src", profileImage);

		var screenName = $(ele).attr("data-screenName");
		$("#btn_post").attr("data-screenName", screenName);

		var firstName = $(ele).attr("data-name");
		var screenNameLink = '<a href="' + this_site + 'profile/?' + screenName + '">' + firstName + '</a>';
		
		$("#messageWhenWho").html(screenNameLink + " says:");

		var messageContent = $(ele).find(".messagePreview").html();
		$("#__messageContent").html(messageContent + '<br><br><div class="ui label mini green messagePreviewLoading">Loading conversation...</div>');

		//get the conversation
		var id = $(ele).attr("data-id");

		//place the id of the conversation
		$("#btn_deleteConversation").attr("data-id", id);

		var postdata = {
			"message_id" : id,
			"conversationPageIndex" : conversationPageIndex
		}

		json_url = "../func/message-get.php";

		_ajax(postdata, json_url, function(json){

			var thisMessage = json.msg;

			//add emoticons
			thisMessage = emoticons(thisMessage);

			var pagetoken = json.pagetoken;

			$("#page_token").attr("data-id", pagetoken);

			$("#__messageContent").html(thisMessage);

			if (json.results != "") {

				console.log("msg" + $(ele).find(".newMessageLabel").attr("data-tag") );

				if ($(ele).find(".newMessageLabel").attr("data-tag") != undefined) {

					//remove green 'new message' dot
					$(ele).find(".newMessageLabel").remove();

					navMessageCount--;

					if (navMessageCount < 1) {

						//remove the green circle
						$(".__messages-count").remove();

					} else {

						//update it
						$(".__messages-count").text(navMessageCount);
					}
				};

				var message_ele,
					msg_read,
					canDelete,
					message_html="";

				if (conversationPageIndex==0) {
					//reset the conversation list on the first run
					$("#_messageConversation").html("");
				};

				$.each(json.results, function(k,v){
					
					msg_read = v.msg_read;

					if (msg_read != "0") {

						msg_read = '<div class="message_read_sticker"><i class="icon check circle"></i>Read</div>';

					} else {

						msg_read = "";
					}

					canDelete = deleteAffirm(v.id, "_delete_message");

					//add emoticons
					v.msg = emoticons(v.msg);

					message_ele = '<div id="messageContainer_' + v.id + '" class="contentContainer previousMessage"> <div class="messageReplyContainer messageReplyContainerHidden"> </div> <div class="previousMessageContainer"> <table> <tbody> <tr> <td class="postAvatarContainer"> <img style="width:51px;" class="ui tiny circular image hidden_image_img" src="' + v.profileImage + '"> <div style="width:65px;height:1px;"></div> </td> <td class="postContentContainer"> ' + msg_read + '<h4><span style="text-transform: capitalize;">' + v.activity_date + '&nbsp;-&nbsp;' + v.firstName + ' - ' + canDelete + '</span></h4> <div class="previousMessageContent"> <p>' + v.msg + '</p> </div> </td> </tr> </tbody> </table> <div style="height:40px;"></div> </div> </div>';
					message_html = message_html + message_ele;
				});
				
				if (json.count == messagesReturnedMax) {

					//we received the max number of records
					var the_end = '<div class="viewMoreMessages_ ui blue button"><i class="icon refresh"></i>View More Messages</div>';

				} else {

					//this is the end of the list
					var the_end = '<div class="endOfPosts ui blue button"><i class="icon arrow cirle up"></i>Back to top</div>';
				}

				message_html = message_html + the_end;

				//add the conversation
				$("#_messageConversation").append(message_html);

				//bind the affirm delete
				deleteAffirmBind("_delete_message", function(id){

					var postdata = {
						"id" : id
					}

					json_url = "../func/message-delete.php";

					_ajax(postdata, json_url, function(json){

						if (json.status == "success") {
							
							$("#messageContainer_" + id).fadeOut("slow", function(){

								var that = this;

								setTimeout(function(){

									$(that).remove();
								}, 800);
							});
						}
					});
				});

				$(".endOfPosts").off(DEF_CLICK).on(DEF_CLICK, function(){

					$("html, body").animate({ scrollTop: 0 }, "slow");
				});

				$(".viewMoreMessages_").off(DEF_CLICK).on(DEF_CLICK, function(){

					$(this).remove();

					//get more 
					getMessage(current_message_element);
				});

				//bind hover img event
				hoverImageEvent();
			}

			if (conversationPageIndex==0) {
				//scroll to the top list on the first run
				$("html, body").animate({ scrollTop: 0 }, "fast");
			};

			//increment the page counter for this conversation
			conversationPageIndex++;

		});
	}

	function emoticons(str){

		var m = str;
		m = m.split(":-)").join('<i class="icon-emo-happy"></i>');
		m = m.split(":)").join('<i class="icon-emo-happy"></i>');
		m = m.split(";-)").join('<i class="icon-emo-wink"></i>');
		m = m.split(";)").join('<i class="icon-emo-wink2"></i>');
		m = m.split(":-(").join('<i class="icon-emo-unhappy"></i>');
		m = m.split(":(").join('<i class="icon-emo-unhappy"></i>');
		m = m.split("(Y)").join('<i class="icon-emo-thumbsup"></i>');

		return m;
	}

	function deleteAffirm(id, baseClass){

		var element_id = baseClass + "_main_" + id;
		var element_class = baseClass + "_main_";
		var span_id = baseClass + "_span_" + id;
		var span_class = baseClass + "_span";
		var deleteAffirmYes = baseClass + "_yes_";
		var deleteAffirmNo = baseClass + "_no_";

		var affirm = '<a id="' + element_id + '" class="' + element_class + '" data-id="' + id + '" href="#">Delete</a> <span id="' + span_id + '" class="' + span_class +'">Really delete? <a class="' + deleteAffirmYes + '" data-id="' + id + '" href="#">Yes</a> | <a class="' + deleteAffirmNo + '" data-id="' + id + '" href="#">No</a>';

		return affirm;
	}

	function deleteAffirmBind(baseClass, callback){

		var element_id = baseClass + "_main_";
		var element_class = baseClass + "_main_";
		var span_id = baseClass + "_span_";
		var span_class = baseClass + "_span";
		var deleteAffirmYes = baseClass + "_yes_";
		var deleteAffirmNo = baseClass + "_no_";

		$('.' + span_class).hide();

		//delete this post affirm 
		$("." + element_class).off(DEF_CLICK).on(DEF_CLICK, function(e){
			e.preventDefault();

			var id = $(this).attr("data-id");

			$(this).hide();
			$("#" + span_id + id).show();

		});

		//no 
		$("." + deleteAffirmNo).off(DEF_CLICK).on(DEF_CLICK, function(e){
			e.preventDefault();

			var id = $(this).attr("data-id");

			$("#" + span_id + id).hide();
			$("#" + element_id + id).show();
		});

		//yes 
		$("." + deleteAffirmYes).off(DEF_CLICK).on(DEF_CLICK, function(e){
			e.preventDefault();

			var id = $(this).attr("data-id");

			$("#" + span_id + id).hide();
			$("#" + element_id + id).show();

			callback(id);
		});
	}

	function hoverImageEvent(){
		
		//provide hover function for hovering profile image in recent activity
	    $('.hidden_image_img').on({
	        mousemove: function(e) {
	            $(this).next('img').css({
	                top: e.pageY - 260,
	                left: e.pageX + 10
	            });
	        },
	        mouseenter: function() {

	            //var big = $('<img />', {'class': 'big_img', src: this.src});
	            var big = '<div class="hidden_image"><img src="' + this.src + '"></div>';
	            $(this).after(big);
	        },
	        mouseleave: function() {
	            $('.hidden_image').remove();
	        }
	    });
	}

	function messageResize(){

		$(".messagePreview")
			.width( $("#inboxMessagesInner").width() -80);

		if ( mobileDevice == false ) {
			$("#inboxMessages")
				.width( $("#__message_contentInnerLeft").width());
		};	
    }

	//lazy load messages
	function scrollListen(){

		//$(window).scroll(function() {
		$(window).one("scroll", function () {

			if ($(window).scrollTop() + $(window).height() > $(document).height() - 100) {
				
				if (getPostsBusy==false) {
					
					getMessages();
				};
			}

			if (postPageIndex != -1) {
				setTimeout(scrollListen(), 200); //rebinds itself after 200ms
			}
		});
	}

	function sendMessage(){

		var message = $("#messageReply").val();

		if ( $.trim(message) == "") {

			$("#btn_working").hide();
			$("#btn_post").show();

			alert("Why an empty message? Type something awesome and try again.");
			return false;
		}

		var postdata = {
			"i" : $("#form_token").attr("data-id"),
			"page_token" : $("#page_token").attr("data-id"),
			"screenName" : $(".profileScreenName").text(),
			"message" : message
		}

		json_url = "../func/message-add.php";

		_ajax(postdata, json_url, function(json){

			$("#btn_working").hide();
			$("#btn_post").show();

			if (json.status == "success") {

				//show user message
				var ele = '<div class="ui small blue message" style="margin-bottom: 14px;">Message Sent</div>';
				$("#statusArea").html(ele);
				
				setTimeout(function(){
					$("#statusArea").html("");
				},1000);
				
				$("#messageReply").val("");

				conversationPageIndex = 0;

				getMessage(current_message_element);
				
			} else {

				_alert("Important Message", json.msg);
				return false;
			};
		});
	}

	function sendNotInterested(){

		var message = "I don't think we're a match but I wish you the best!";

		var postdata = {
			"i" : $("#form_token").attr("data-id"),
			"page_token" : $("#page_token").attr("data-id"),
			"request_token" : "10589-102-3123A",
			"screenName" : $(".profileScreenName").text(),
			"message" : message
		}

		json_url = "../func/message-add.php";

		_ajax(postdata, json_url, function(json){

			$("#btn_working").hide();
			$("#btn_post").show();

			if (json.status == "success") {

				//show user message
				var ele = '<div class="ui small blue message" style="margin-bottom: 14px;">Message Sent</div>';
				$("#statusArea").html(ele);
				
				setTimeout(function(){
					$("#statusArea").html("");
				},1000);
				
				$("#messageReply").val("");

				getMessage(current_message_element);
				
			} else {

				_alert("Important Message", json.msg);
				return false;
			};
		});
	}
});

/*


global functions


*/
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

function affirm(title, msg, callback){

	//$("#app_page").hide();

	if (title != "") {

		$("#affirm_title").text(title);
	};
	
	$("#affirm_question").text(msg);

	$('#affirm')
	  .modal('setting', {
	    closable  : false,
	    onDeny    : function(){
	    	callback("no");
	    	//$("#app_page").show();
	      	buildUploader();
	    },
	    onApprove : function() {
	      callback("yes");
	      buildUploader();
	      //$("#app_page").show();
	    }
	  })
	  .modal('show');
}

function _alert(title, description){

	alert(description);
	/*$("#alertTitle").text(title);
	$("#alertDescription").text(description);
	$('#alert').modal('show');*/

}

function htmlspecialchars_decode(string, quote_style) {

  var optTemp = 0,
    i = 0,
    noquotes = false;
  if (typeof quote_style === 'undefined') {
    quote_style = 2;
  }
  string = string.toString()
    .replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>');
  var OPTS = {
    'ENT_NOQUOTES': 0,
    'ENT_HTML_QUOTE_SINGLE': 1,
    'ENT_HTML_QUOTE_DOUBLE': 2,
    'ENT_COMPAT': 2,
    'ENT_QUOTES': 3,
    'ENT_IGNORE': 4
  };
  if (quote_style === 0) {
    noquotes = true;
  }
  if (typeof quote_style !== 'number') {
    // Allow for a single string or an array of string flags
    quote_style = [].concat(quote_style);
    for (i = 0; i < quote_style.length; i++) {
      // Resolve string input to bitwise e.g. 'PATHINFO_EXTENSION' becomes 4
      if (OPTS[quote_style[i]] === 0) {
        noquotes = true;
      } else if (OPTS[quote_style[i]]) {
        optTemp = optTemp | OPTS[quote_style[i]];
      }
    }
    quote_style = optTemp;
  }
  if (quote_style & OPTS.ENT_HTML_QUOTE_SINGLE) {
    string = string.replace(/&#0*39;/g, "'"); // PHP doesn't currently escape if more than one 0, but it should
    // string = string.replace(/&apos;|&#x0*27;/g, "'"); // This would also be useful here, but not a part of PHP
  }
  if (!noquotes) {
    string = string.replace(/&quot;/g, '"');
  }
  // Put this in last place to avoid escape being double-decoded
  string = string.replace(/&amp;/g, '&');

  //replace br with NL
  string = string.replace(/<br\s*\/?>/mg,"\n");

  return string;
}


