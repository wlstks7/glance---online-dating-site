var DEF_CLICK = "click";
var postPageIndex = 0;
var conversationPageIndex = 0;
var currentBackground;
var getPostsBusy = false;
var previousSearchTitle = "";
var searchItems = {};
var currentSearch = "";
var mobileDevice = false;

$(function(){

	init();

	if (mobilecheck() == true) {
		mobileDevice = true;
		$("#searchVertical").css("position", "inherit");
	}

	$(window).resize(function() {

		searchResize();
    });

	/*

	TAP EVENTS

	*/

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

		searchResize();

		bindsearch();

		//$("#btn_working").hide();
		postPageIndex = 0;

		$('.ui.checkbox').checkbox();
		$('.popup').popup();
		$('html, body').animate({ scrollTop: 0 }, 0);

		setTimeout(function(){

			var tagSearch = myProfile.tag;

			if ( $.trim(tagSearch) != "" ) {

				doSearchForTag(tagSearch);

			} else {

				getGraffiti();
			}

		},500);

		//scrollListen();

		$("#__searchView").hide();
	}

	function bindsearch(){

		console.log("bindsearch");

		$("#txt_search").on("keypress", function(e){

			if(e.which == 13) {
			    $("#btn_search").trigger(DEF_CLICK);
			  }
		});

		$("#btn_search").off(DEF_CLICK).on(DEF_CLICK, function(){
			
			console.log("DEF_CLICK");

			currentSearch = $("#txt_search").val();

			if ($.trim(currentSearch) == "") {

				$("#__searchView").show();
				$("#__searchViewBlank").hide();
				var msg='<div class="ui blue message">No Results. Please try again.</div>';
				$("#searchResults").html(msg);
				$("#txt_search").focus();
				return false;
			}

			$("#__searchView").show();
			$("#__searchViewBlank").hide();

			_runSearch('true');
		});
	}

	function doSearchForTag(tag){

		var tag = myProfile.tag;

		$("#txt_search").val("#" + tag);
		$("#btn_search").trigger(DEF_CLICK);
	}

	function getGraffiti(newSearch){

		$('html, body').animate({ scrollTop: 0 }, 0);

		if (getPostsBusy == true) {
			return false;
		}

		if (newSearch == 'true') {
			postPageIndex = 0;
		};

		if (postPageIndex == -1) {
			return false;
		}
		
		getPostsBusy = true;

		var postSeed = $("#post_seed").attr("data-id");

		var postdata = {
			"postPageIndex" : postPageIndex
		}

		$("#__searchViewBlank").hide();
		$("#__searchView").show();

		if (newSearch == 'true') {			
			var loader = '<div class="ui icon blue message"> <i class="paw loading icon"></i> <div class="content"> <div class="header"> Go get em! </div> <p>Fetching those results... rrrruff!</p> </div> </div>';
			$("#__searchViewBlank").hide();
			$("#__searchView").show();
			$("#postsContainer").show().html(loader);
		};

		postPageIndex++;

		json_url = "../func/graffiti-get.php";

		_ajax(postdata, json_url, function(json){

			if (json.status == "success") {

				if (json.results == "") {

					if (postPageIndex == 1) {

						//there are no posts
						var the_end = '<div class="ui label fluid ">There are no posts.</div>';

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
					
					var my_profile_pic = v.profileImage;
					
					if ($.trim(my_profile_pic) == "") {

						if (v.gender == "guy") {
							my_profile_pic = myProfile.profileNoPicGuy;
						} else {
							my_profile_pic = myProfile.profileNoPicGal;
						}
					};

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
					ele = ele + '<div id="post_' + v.post_id + '" data-profile="' + v.profile_id + '" class="contentContainer __post containerFrame">';
					ele = ele + '<div class="colorTop"></div><div class="postInner"><table><tr>';
					ele = ele + '<td class="postAvatarContainer"><img src="' + my_profile_pic + '" class="ui tiny circular image profile_image_"><br><div style="width:90px;height:1px;"></div></td><td class="postContentContainer">';
					//ele = ele + '<h4>' + v.firstName + ' · posted on ' + v.postedDate + pinned + ' - <div class="affirm notinterested-affirm" data-id="notinterested_' + v.post_id + '" data-profile="' + v.profile_id + '" data-title="Not Interested" data-question="Block this person?"></div> - <div class="affirm likeaffirm" data-id="likethis_' + v.post_id + '" data-profile="' + v.profile_id + '" data-title="Like this post" data-result="<strong>Liked</strong>" data-question="Like this post?"></div> </h4>';
					ele = ele + '<h4><a href="'+ this_site + 'profile/?' + v.userName + '">' + v.firstName + '</a> · posted on ' + v.postedDate + pinned + ' - <div class="affirm notinterested-affirm" data-id="notinterested_' + v.post_id + '" data-profile="' + v.profile_id + '" data-title="Not Interested" data-question="Block this person?"></div> - [LIKED]</div> </h4>';
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
				if (newSearch == 'true') {

					$("#postsContainer").html(html);;

				} else {

					$("#postsContainer").append(html);
				}

				//bind the events
				$(".likeaffirm").on(DEF_CLICK, function(){
						
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

				$(".notinterested-affirm").inlineAffirm({
					callback : function(ele){
						var profile_id = $(ele).attr("data-profile" );
						var id = $(ele).attr("data-id" );

						$(".__post").each(function(){

							if ( $(this).attr("data-profile") ==  profile_id) {
								$(this).fadeOut('slow');
							};
						});

						//send a not interested message
						var postdata = {
							"id" : profile_id,
						}

						json_url = "../func/search-not-interested.php";

						_ajax(postdata, json_url, function(json){});
					}
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

				var loader = '<div class="ui icon blue message"> <i class="paw icon"></i> <div class="content"> <div class="header"> Something went wrong </div> <p>' + json.msg + '</p> </div> </div>';
				$("#__searchViewBlank").hide();
				$("#__searchView").show();
				$("#postsContainer").show().html(loader);

				return false;
			};
		});
	}

	function _runSearch(newSearch){

		$('html, body').animate({ scrollTop: 0 }, 0);

		if (getPostsBusy == true) {
			return false;
		}

		if (newSearch == 'true') {
			postPageIndex = 0;
		};

		if (postPageIndex == -1) {
			return false;
		}
		
		getPostsBusy = true;

		var postSeed = $("#post_seed").attr("data-id");

		var postdata = {
			"search" : currentSearch,
			"postPageIndex" : postPageIndex
		}

		postPageIndex++;

		json_url = "../func/graffiti-search-posts.php";

		_ajax(postdata, json_url, function(json){

			if (json.status == "success") {

				if (json.results == "") {

					if (postPageIndex == 1) {

						//there are no posts
						var the_end = '<div class="ui icon blue message"> <i class="paw icon"></i> <div class="content"> <div class="header"> No Results </div> <p>Hmmm...maybe try another search?</p> </div> </div>';
						$("#postsContainer").html(the_end);
						getPostsBusy = false;
						return false;

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
					
					var my_profile_pic = v.profileImage;
					
					if ($.trim(my_profile_pic) == "") {

						if (v.gender == "guy") {
							my_profile_pic = myProfile.profileNoPicGuy;
						} else {
							my_profile_pic = myProfile.profileNoPicGal;
						}
					};

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
					ele = ele + '<div id="post_' + v.post_id + '" data-profile="' + v.profile_id + '" class="contentContainer __post containerFrame">';
					ele = ele + '<div class="colorTop"></div><div class="postInner"><table><tr>';
					ele = ele + '<td class="postAvatarContainer"><img src="' + my_profile_pic + '" class="ui tiny circular image profile_image_"><br><div style="width:90px;height:1px;"></div></td><td class="postContentContainer">';
					ele = ele + '<h4><a href="'+ this_site + 'profile/?' + v.userName + '">' + v.firstName + '</a> · posted on ' + v.postedDate + pinned + ' - <div class="affirm notinterested-affirm" data-id="notinterested_' + v.post_id + '" data-profile="' + v.profile_id + '" data-title="Not Interested" data-question="Block this person?"></div> - [LIKED]</div> </h4>';
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
				
				if (newSearch == 'true') {
					$("#postsContainer").html("");
				};

				//render the posts
				$("#postsContainer").append(html);

				//bind the events
				$(".likeaffirm").on(DEF_CLICK, function(){
						
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

				$(".notinterested-affirm").inlineAffirm({
					callback : function(ele){
						var profile_id = $(ele).attr("data-profile" );
						var id = $(ele).attr("data-id" );

						$(".__post").each(function(){

							if ( $(this).attr("data-profile") ==  profile_id) {
								$(this).fadeOut('slow');
							};
						});

						//send a not interested message
						var postdata = {
							"id" : profile_id
						}

						json_url = "../func/search-not-interested.php";

						_ajax(postdata, json_url, function(json){});
					}
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

	function hoverImageEvent(){
		
		//provide hover function for hovering profile image in recent activity
	    $('.profile_image_').on({
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

	function runSearch(firstrun){

		$('html, body').animate({ scrollTop: 0 }, 0);
		
		if (getPostsBusy == true) {
			return false;
		}

		console.log("postPageIndex " + postPageIndex);

		if (firstrun == 'true') {
			postPageIndex = 0;
		};

		if (postPageIndex == -1) {
			return false;
		}
		
		getPostsBusy = true;

		var postdata = {
			"postPageIndex" : postPageIndex,
			"filter" : JSON.stringify(currentSearchItem)
		}

		postPageIndex++;

		json_url = "../func/search-run.php";

		var loader = '<div class="ui icon blue message"> <i class="paw loading icon"></i> <div class="content"> <div class="header"> Go get em! </div> <p>Fetching those results... rrrruff!</p> </div> </div>';
		$("#searchResults").html(loader);

		_ajax(postdata, json_url, function(json){

			if (json.status == "success") {

				if (json.results == "") {

					if (postPageIndex == 1) {

						//there are no posts
						//var the_end = '<div class="ui label fluid ">I have no posts but I promise to make some soon.</div>';

					} else {

						//var the_end = '<div class="endOfPosts ui blue button"><i class="icon arrow cirle up"></i>Back to top</div>';
					}

					postPageIndex = -1;

					//render the message
					/*$("#postsContainer").append(the_end);
					$(".loadingPosts").remove();

					$(".endOfPosts").off(DEF_CLICK).on(DEF_CLICK, function(){

						$("html, body").animate({ scrollTop: 0 }, "slow");
					});*/
				};

				//$("#statusArea").html('<div class="_statusLoading">Loading....</div>');

				var template,
					this_site = $("#this_site").attr("data-id"),
					firstName,
					profile_id,
					userName,
					profileImage,
					age,
					cityState,
					secondLine,
					last_online,
					milesAway;

				$("#searchResults").html("");

				var results_html = "";

				$.each(json.results, function(k,v){

					profileImage = v.profileImage;
					userName = v.userName;
					profile_id = v.profile_id;
					firstName = v.firstName;
					cityState = v.city + " " + v.state;
					age = v.age;
					last_online = v.last_online;
					milesAway = v.milesAway;
					
					if (milesAway == "0") {
						milesAway = "1";
					};

					if (milesAway == "1") {
						milesAway = '<i class="icon marker"></i> 1 Mile Away';
					} else {
						milesAway =  '<i class="icon marker"></i> ' + milesAway + " Miles Away";
					}

					online = '<i class="icon circle green"></i> Online Now </div>';
					
					if ( last_online == "" ) {
						online = "&nbsp;";
					};

					template = '<div id = "card_' + profile_id + '" data-id = "' + profile_id + '" data-username="' + userName + '" class="search-column"> <div class="search-column-inner"> <div class="contentContainerSearch"> <div class="postInner"> <table> <tbody> <tr> <td class="postAvatarContainer"> <img class="ui tiny circular image profile_image_" src="' + profileImage + '"> <br> <div style="width:90px;height:1px;"></div> </td> <td class="postContentContainer"> <h4>' + firstName + '</h4> <div class="postContent"> ' + age + ', ' + cityState + ' <br> ' + milesAway + ' <br> ' + online + ' </td> </tr> </tbody> </table> <div class="cardFooter"> <div class="linkNotInterested affirm results_affirm light" data-title="Not Interested" data-question="Remove this profile?" data-id="' + profile_id + '" href="#">Not Interested</div> <span title="Click to view in another window" class="cardFooterExtLink"> <a class="linkNewSite" target="_blank" href="' + this_site + 'profile/?' + userName + '"><i class="icon gray external square newWindowLink"></i></a> </span> </div> </div> </div> </div> </div>';

					results_html = results_html + template;

				});
				
				if (results_html == "") {
					//no results
					var loader = '<div class="ui icon blue message"> <i class="paw icon"></i> <div class="content"> <div class="header"> No Results </div> <p>Hmmm...maybe try another search?</p> </div> </div>';
					$("#searchResults").html(loader);

				}else {

					var loader = '<div class="ui icon blue message"> <i class="paw icon"></i> <div class="content"> <div class="header"> Got em! </div> <p>Hooray! Rrrrruff!</p> </div> </div>';
					$("#searchResults").html(loader);

					setTimeout(function(){

						$("#searchResults").html(results_html);
						
						$("#statusArea").html('');

						//bind the events
						$(".results_affirm").inlineAffirm({
							callback : function(ele){
								var id = $(ele).attr("data-id" );
								notInterested(id);
							}
						});

						$(".search-column").off(DEF_CLICK).on(DEF_CLICK, function(){

							var id = $(this).attr("data-id");
							var u = $(this).attr("data-username");

							$("#pageBody").hide();
							$("#loading").show();

							window.location = this_site + 'profile/?' + u;
						});

						$(".linkNotInterested").off(DEF_CLICK).on(DEF_CLICK, function(e){
							e.stopPropagation();

						});

						//stop propagation on a href new window
						$(".linkNewSite").off(DEF_CLICK).on(DEF_CLICK, function(e){
							e.stopPropagation();
						});
					},300);
				}
			}

			getPostsBusy = false;

			buildSearchNarrative();
		});
	}

	function searchResize(){

		$(".searchPreview")
			.width( $("#searchVerticalInner").width() -80);

		if ( mobileDevice == false ) {
			$("#searchVertical")
				.width( $("#__searchLeftCol").width());
		};
    }

	//lazy load messages
	function scrollListen(){

		//$(window).scroll(function() {
		$(window).one("scroll", function () {

			if ($(window).scrollTop() + $(window).height() > $(document).height() - 100) {
				
				if (getPostsBusy==false) {
					
					//getsearchs();
				};
			}

			if (postPageIndex != -1) {
				setTimeout(scrollListen(), 200); //rebinds itself after 200ms
			}
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
