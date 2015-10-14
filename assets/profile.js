var DEF_CLICK = "click";
var postImageCount = 0;
var postPageIndex = 0;
var intID;
var currentBackground;
var getPostsBusy = false;

$(function(){

	init();

	/*

	TAP EVENTS

	*/

	$("#btn_toTop").on(DEF_CLICK, function(){

		$("html, body").animate({ scrollTop: 0 }, "slow");

	});

	//insert private link into message 
	$("#btn_attachLink").on(DEF_CLICK, function(){

		$("#postEditor").focus();

		var msg = $("#postEditor").val();
		var link_msg = "[Your private link will automatically go here]";

		$("#postEditor").val(msg + '\n\n' + link_msg);
	
	});
	
	//save this post 
	$("#btn_post").on(DEF_CLICK, function(){

		$("#btn_working").show();
		$("#btn_post").hide();
		
		sendMessage();
	});

	$(".btn_likeProfile").on(DEF_CLICK, function(){

		likeProfile();

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

	function getPosts(){

		if (getPostsBusy == true) {
			return false;
		}

		if (postPageIndex == -1) {
			return false;
		}
		
		getPostsBusy = true;

		var postSeed = $("#post_seed").attr("data-id");

		var postdata = {
			"profile_id" : myProfile.profile_id,
			"postPageIndex" : postPageIndex
		}

		postPageIndex++;

		json_url = "../func/profile-posts-get.php";

		_ajax(postdata, json_url, function(json){

			if (json.status == "success") {

				if (json.results == "") {

					if (postPageIndex == 1) {

						//there are no posts
						var the_end = '<div class="ui label fluid ">I have no posts but I promise to make some soon.</div>';

					} else {

						var the_end = '<div class="endOfPosts ui blue button"><i class="icon arrow cirle up"></i>Back to top</div>';
					}

					postPageIndex = -1;
					
					//render the message
					$("#postsContainer").append(the_end);
					$(".loadingPosts").remove();

					$(".endOfPosts").off(DEF_CLICK).on(DEF_CLICK, function(){

						$("html, body").animate({ scrollTop: 0 }, "slow");
					});
				};

				$("#counter_posts").text(json.totalPosts);

				if (json.imageCount == "" || json.imageCount == null) {
					json.imageCount = "0";
				}

				$("#counter_images").text(json.imageCount);

				var ele, 
					html = "";
				var photoset;
				var my_profile_pic = $("#__mainProfileImage").attr("src");
				
				$.each(json.results, function(k,v){

					if (v.pinned == "1") {
						pinned = ' - Pinned';
					} else {
						pinned = '';
					}

					var images = v.images;

					var images_arr = images.split("(..)");

					//get count
					var img_count = images_arr.length;
					
					var images_ = "";
					var img = "";

					$.each(images_arr, function(i,v){

						img = v;
						if (v.length > 5) {
							//image
							images_ = images_ + '<img src="' + v +'">';
						};
					});

					var data_layout = "0";

					if (img_count == 2) {
						data_layout = "1";

					} else if (img_count == 3) {
						data_layout = "2";

					} else if (img_count == 4) {
						data_layout = "21";

					} else if (img_count == 5) {
						data_layout = "31";

					} else if (img_count == 6) {
						data_layout = "32";

					} else if (img_count == 7) {
						data_layout = "321";

					} else if (img_count == 8) {
						data_layout = "133";

					} else if (img_count == 9) {
						data_layout = "2321";
					}

					//add emoticons
					v.post = emoticons(v.post);
					
					var this_site = $("#this_site").attr("data-id");

					var _post = v.post;
						//_post = _post.replace(/(^|\W)(#[a-z\d][\w-]*)/ig, '$1<span class="site-hashtag"><a title="Search for other posts about [REMOVEHASHTAG]$2" href="' + this_site + 'search/?t=$2">[REMOVEHASHTAG]$2</a></span>');
						_post = _post.replace(/(^|[^&\w])(#[a-z\d][\w-]*)/ig, '$1<span class="site-hashtag"><a title="Search for other posts about [REMOVEHASHTAG]$2" href="' + this_site + 'graffiti/?t=[REMOVEHASHTAG]$2">[REMOVEHASHTAG]$2</a></span>');
						_post = _post.split("[REMOVEHASHTAG]#").join("");
						v.post = _post.split("search/?t=#").join("search/?t=");

					var tolike = '<span><span class="likeaffirm" data-id="likethis_' + v.post_id + '" data-profile="' + v.profile_id + '">Like this post</span></span>';

					if ( v.likepost != "0" ) {
						tolike = "Liked";
					}

					photoset = '<div class="postImageContainer"><div class="photoset-grid-custom" data-layout="' + data_layout + '">' + images_ + '</div></div>';

					ele = '';
					ele = ele + '<div id="post_' + v.post_id + '" class="contentContainer __post containerFrame">';
					ele = ele + '<div class="colorTop"></div><div class="postInner"><table><tr>';
					ele = ele + '<td class="postAvatarContainer"><img src="' + my_profile_pic + '" class="ui tiny circular image profile_image_"><br><div style="width:90px;height:1px;"></div></td><td class="postContentContainer">';
					ele = ele + '<h4>' + myProfile.firstName + ' · posted on ' + v.postedDate + pinned + ' - [LIKED]</h4>';
					ele = ele + '<div class="postContent">' + v.post;
					ele = ele + '</div></td></tr></table>' + photoset + '</div><div class="colorFooter"></div></div>';
					
					ele = ele.split("[LIKED]").join(tolike);

					html = html + ele;
				});
				
				$(".loadingPosts").remove();

				/*if (postPageIndex != -1) {
					html = html + '<div class="loadingPosts">Loading more posts...</div>';
				}*/

				if (postPageIndex != -1) {
					if (json.totalPosts > 5) {
						html = html + '<div class="loadingPosts">Loading more posts...</div>';
					};
				}
			
				//render the posts
				$("#postsContainer").append(html);

				$(".likeaffirm").off(DEF_CLICK).on(DEF_CLICK, function(){
						
						var that = this;
						var id = $(that).attr("data-id" );
						var profile_id = $(that).attr("data-profile" );
						var p = $(that).parent();

						id = id.split("likethis_").join("");

						//send a not interested message
						var postdata = {
							"profile_id" : profile_id,
							"post_id" : id
						}
						
						json_url = "../func/graffiti-like-post.php";

						_ajax(postdata, json_url, function(json){});

						$(p).html("Liked");
				});

				//build photoset
				$('.photoset-grid-custom').photosetGrid({
					highresLinks: true,
					rel: 'withhearts-gallery',
					gutter: '5px',

					onComplete: function(){
						$('.photoset-grid-custom').attr('style', '');
						$('.photoset-grid-custom a').colorbox({
							photo: true,
							scalePhotos: true,
							maxHeight:'90%',
							maxWidth:'90%'
						});
					}
				});

				getPostsBusy = false;

			} else {

				getPostsBusy = false;

				_alert("Important Message", json.msg);
				return false;
			};
		});
	}

	function init(){

		$('.dropdown').dropdown();
		$('.ui.checkbox').checkbox();
		$('.popup').popup();
		$('html, body').animate({ scrollTop: 0 }, 0);
		buildProfileForm();
		getPosts();
		scrollListen();

		$("#btn_working").hide();
		$("#status_area").html("").hide();
	}

	function likeProfile(btn){

		$(".btn_likeProfile")
			.addClass("btn_likedProfile")
			.html('<i class="icon heart"></i>Liked!')

		//unbind the like button event for this session
		$(".btn_likeProfile").off(DEF_CLICK);

		var postdata = {
			"page_token" : $("#page_token").attr("data-id"),
			"screenName" : $(".profileScreenName").text()
		}

		json_url = "../func/like-profile.php";

		_ajax(postdata, json_url, function(json){


		});
	}

	//lazy load posts
	function scrollListen(){

		//$(window).scroll(function() {
		$(window).one("scroll", function () {

			//console.log("scrollTop: " + $(window).scrollTop());

			if($(window).scrollTop() + $(window).height() > $(document).height() - 100) {
				
				if (getPostsBusy==false) {
					
					getPosts();
				};
			}

			if (postPageIndex != -1) {
				setTimeout(scrollListen(), 200); //rebinds itself after 200ms
			}
		
		});
	}

	function sendMessage(){

		var message = $("#postEditor").val();

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

				var msg = '<div class="ui blue message">Message Sent</div>';
				$("#status_area").html(msg).show();

				setTimeout(function(){
					$("#status_area").html("").hide();

				},2000);

				$("#postEditor").val("");
				
			} else {

				_alert("Important Message", json.msg);
				return false;
			};
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

function buildProfileForm() {

	$("#profileDescription").val(htmlspecialchars_decode(myProfile.profileDesc, 3));

	$("#zipcode").val(myProfile.zipcode);
	$("#firstName").val(htmlspecialchars_decode(myProfile.firstName));

	$('#_birthMonth').dropdown('set selected', myProfile.birthMonth);
	$('#_birthMonth').dropdown('set value', myProfile.birthMonth);

	$('#_birthDay').dropdown('set selected', myProfile.birthDay);
	$('#_birthDay').dropdown('set value', myProfile.birthDay);
	
	$('#_birthYear').dropdown('set selected', myProfile.birthYear);
	$('#_birthYear').dropdown('set value', myProfile.birthYear);

	$('#_zodiacPref').dropdown('set selected', myProfile.zodiacShow);
	$('#_zodiacPref').dropdown('set value', myProfile.zodiacShow);

	$('#_relationshipStatus').dropdown('set selected', myProfile.relationshipStatus);
	$('#_relationshipStatus').dropdown('set value', myProfile.relationshipStatus);

	$('#_gender').dropdown('set selected', myProfile.gender);
	$('#_gender').dropdown('set value', myProfile.gender);

	$('#_seekingGender').dropdown('set selected', myProfile.seekingGender);
	$('#_seekingGender').dropdown('set value', myProfile.seekingGender);

	$('#_height').dropdown('set selected', myProfile.height);
	$('#_height').dropdown('set value', myProfile.height);

	$('#_eyeDesc').dropdown('set selected', myProfile.eyeDesc);
	$('#_eyeDesc').dropdown('set value', myProfile.eyeDesc);

	$('#_bodyType').dropdown('set selected', myProfile.bodyType);
	$('#_bodyType').dropdown('set value', myProfile.bodyType);

	$('#_hairDesc').dropdown('set selected', myProfile.hairDesc);
	$('#_hairDesc').dropdown('set value', myProfile.hairDesc);

	$('#_religious').dropdown('set selected', myProfile.religious);
	$('#_religious').dropdown('set value', myProfile.religious);

	$('#_ethnicity').dropdown('set selected', myProfile.ethnicity);
	$('#_ethnicity').dropdown('set value', myProfile.ethnicity);

	$('#_income').dropdown('set selected', myProfile.income);
	$('#_income').dropdown('set value', myProfile.income);

	$('#_smokerPref').dropdown('set selected', myProfile.smokerPref);
	$('#_smokerPref').dropdown('set value', myProfile.smokerPref);

	$('#_drinkingPref').dropdown('set selected', myProfile.drinkingPref);
	$('#_drinkingPref').dropdown('set value', myProfile.drinkingPref);

	$('#_children').dropdown('set selected', myProfile.children);
	$('#_children').dropdown('set value', myProfile.children);

	$('#_adultPreference').dropdown('set selected', myProfile.adultViewPref);
	$('#_adultPreference').dropdown('set value', myProfile.adultViewPref);

	$('#_profileRating').dropdown('set selected', myProfile.adultProfileRating);
	$('#_profileRating').dropdown('set value', myProfile.adultProfileRating);

	//$("#charCount").text("Characters left: " + (500 - $("#profileDescription").val().length));
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


