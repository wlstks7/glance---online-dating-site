var DEF_CLICK = "click";
var postImageCount = 0;
var postPageIndex = 0;
var intID;
var currentBackground;
var getPostsBusy = false;
var likeWorking = false;

$(function(){

	init();

	/*

	TAP EVENTS

	*/

	$("#btn_toTop").on(DEF_CLICK, function(){

		$("html, body").animate({ scrollTop: 0 }, "slow");

	});

	bindVisibility();

	$("#edit_profile_link").on(DEF_CLICK, function(e){
		e.stopPropagtion;
		
		displayMode("edit");

	});

	$("#edit_profile_image").on(DEF_CLICK, function(){

		$("html, body").animate({ scrollTop: 0 }, "slow");

		//show the photo cropping editor
		$("#crop_profile_photo").show();

		//reset the iframe
		var timestamp = new Date().getUTCMilliseconds();
		$("#frame_crop_profile_photo").attr("src", "crop_profile_image.php?=" + timestamp);
	});

	//toggle mode: profile editor 
	$(".btn_editProfile").on(DEF_CLICK, function(){

		var that = this;

		if ( $(that).attr('data-mode') == "run" ) {

			displayMode("edit");
			
		}else{

			displayMode("run");
			updateProfile();
		}
	});

	//cancel profile edit 
	$(".btn_cancelProfile").on(DEF_CLICK, function(){

		displayMode("run");
		
		//return the previous background
		$(".bannerImage").attr("src", currentBackground);

		buildProfileForm();
	});

	//save this post 
	$("#btn_post").on(DEF_CLICK, function(){

		$("#btn_post").hide();
		$("#btn_working").show();

		savePost();
	});
	
	$("#profileDescription").keyup(function(){
	  $("#charCount").text("Characters left: " + (500 - $(this).val().length));
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

	function bindVisibility(){

		$("#glanceProfileVisibilityChange").off(DEF_CLICK).on(DEF_CLICK, function(e){
			e.preventDefault();

			var v = $(this).attr("data-status");

			var postdata = {
				"v" : v,
			}

			json_url = "../func/profile-visibility.php";

			_ajax(postdata, json_url, function(json){

				if (json.status == "success") {

					if (v == "0") {
						//hide
						$("#glanceProfileVisibility").text('Your profile is hidden');
						$("#glanceProfileVisibilityLink").html('<a id="glanceProfileVisibilityChange" data-status="1" href="#">(Show Profile)</a>');

					} else {

						$("#glanceProfileVisibility").text('Your profile is visible');
						$("#glanceProfileVisibilityLink").html('<a id="glanceProfileVisibilityChange" data-status="0" href="#">(Hide Profile)</a>');
					}

					bindVisibility();
				}
			});
		});
	}

	function displayMode(mode){

		if ( mode == "edit" ) {

			clearInterval(intID);
			$(".edit_mode_overlay").show();

			//save the current background
			currentBackground = $(".bannerImage").attr("src");

			//hide all content containers
			$(".contentContainer").hide();

			//show the editor
			$(".editProfile").show();
			$("#postsContainer").hide();

			$(".btn_editProfile")
				.attr('data-mode', 'edit')
				.text('Save Profile');
			
			$("#btn_cancelProfileCenterCol")
				.removeClass("profileHide")
				.addClass("cancelButtonCenter");

			$("#btn_cancelProfileRightCol")
				.removeClass("profileHide")
				.addClass("cancelButtonRight");

			$("#profileContent_leftSection").hide();

			$("#contentLeftCol").addClass("contentLeftEdit");
			$("#contentCenterCol").addClass("contentCenterEdit");
			$("#contentRightCol").addClass("contentRightCol_HIDDEN");

		} else {

			_resetCrop();

			clearInterval(intID);
			$(".edit_mode_overlay").hide();

			//hide the photo cropping editor
			$("#crop_profile_photo").hide();

			//hide all content containers
			$(".contentContainer").show();

			//show the editor
			$(".editProfile").hide();

			$("#postsContainer").show();

			$(".btn_editProfile")
				.attr('data-mode', 'run')
				.text('Edit Profile');

			$("#btn_cancelProfileCenterCol")
				.addClass("profileHide")
				.removeClass("cancelButtonCenter");

			$("#btn_cancelProfileRightCol")
				.addClass("profileHide")
				.removeClass("cancelButtonRight");

			$("#profileContent_leftSection").show();

			$("#contentLeftCol").removeClass("contentLeftEdit");
			$("#contentCenterCol").removeClass("contentCenterEdit");
			$("#contentRightCol").removeClass("contentRightCol_HIDDEN");
		}
	}	

	function getPost(){




	}

	function getPosts(callback){

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

		//reset the container if not appending
		if (postPageIndex == 0) {
			$("#postsContainer").html("");
			 scrollListen();
		}

		postPageIndex++;

		json_url = "../func/posts-get.php";

		_ajax(postdata, json_url, function(json){

			if (json.status == "success") {

				if (json.results == "") {

					if (postPageIndex == 1) {

						//there are no posts
						var the_end = '<div class="ui label fluid ">You have no posts... why not create one?</div>';

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
						pinned = ' · <a class="_unpin_post" data-id="' + v.post_id + '" href="#">Pinned</a>';
					} else {
						pinned = ' · <a class="_pin_post" data-id="' + v.post_id + '" href="#">Pin</a>';
					}

					var editpost = ' · <a class="_edit_post" data-id="' + v.post_id + '" href="#">Edit Post</a>';

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
					
					var howmanylikes = v.howmanylikes;

					if (howmanylikes == "0") {

						howmanylikes = "";

					} else {

						howmanylikes = ' · <span id="postlikes_' + v.post_id + '" data-id="' + v.post_id + '" class="howmanylikes" >Likes: ' + howmanylikes + '</span>';
					}

					photoset = '<div class="postImageContainer"><div class="photoset-grid-custom" data-layout="' + data_layout + '">' + images_ + '</div></div>';

					ele = '';
					ele = ele + '<div id="postMsgContainer_' + v.post_id + '" class="postMsgContainer"></div>';
					ele = ele + '<div id="post_' + v.post_id + '" class="contentContainer __post containerFrame">';
					ele = ele + '<div class="colorTop"></div><div class="postInner"><table><tr>';
					ele = ele + '<td class="postAvatarContainer"><img src="' + my_profile_pic + '" class="ui tiny circular image profile_image_"><br><div style="width:90px;height:1px;"></div></td><td class="postContentContainer">';
					ele = ele + '<h4>' + myProfile.firstName + ' · posted on ' + v.postedDate + editpost + pinned + ' · <a id="deletepost_' + v.post_id + '" class="_delete_post_affirm" data-id="' + v.post_id + '" href="#">Delete</a> <span id="deleteAffirm_' + v.post_id + '" class="deleteAffirm">Really delete? <a class="_delete_post" data-id="' + v.post_id + '" href="#">Yes</a> | <a class="_delete_post_no" data-id="' + v.post_id + '" href="#">No</a></span> ' + howmanylikes + '</h4>';
					ele = ele + '<div id="postContent_' + v.post_id + '" class="postContent">' + v.post;
					ele = ele + '</div><div id="postEditor_' + v.post_id + '"></div></td></tr></table>' + photoset + '</div><div class="colorFooter"></div></div>';
										
					html = html + ele;
				});
				
				$(".loadingPosts").remove();

				if (postPageIndex != -1) {
					if (json.totalPosts > 5) {
						html = html + '<div class="loadingPosts">Loading more posts...</div>';
					};
				}
			
				//render the posts
				$("#postsContainer").append(html);

				//hide the affirm delete options
				$(".deleteAffirm").hide();

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

				//delete this post affirm 
				$("._delete_post_affirm").off(DEF_CLICK).on(DEF_CLICK, function(e){
					e.preventDefault();

					var post_id = $(this).attr("data-id");

					$(this).hide();
					$("#deleteAffirm_" + post_id).show();

				});

				//edit this post event
				$("._edit_post").off(DEF_CLICK).on(DEF_CLICK, function(e){
					e.preventDefault();

					var post_id = $(this).attr("data-id");
					var ele = $("#postContent_" + post_id);
					var editorContainer = $("#postEditor_" + post_id);
					var postContent = ele.html(); 
					
					//revert emoticons
					postContent = emoticonsRev(postContent);

					//remove any tag span html and just leave the original tags
					var re = /<span[^>]*>(.*?)<a[^>]*>(.*?)<\/a><\/span>/gmi;
	    			postContent = postContent.replace(re, '#$2');

	    			postContent = postContent.split("<br>").join("\n");
	    			postContent = postContent.split("<br />").join("\n");

					var editor = '<div id="post-editor-container_' + post_id + '" class="post-editor-container"> <textarea id="postEditorContent_' + post_id + '" placeholder="What a perfect day to edit a post :-)" class="post_editor">' + postContent + '</textarea> <div style="height:3px;"></div> <div data-id="' + post_id + '" class="ui button mini btn_cancelEditor">Cancel</div><div data-id="' + post_id + '" class="ui button mini blue btn_saveEditor">Save Post</div> </div>';

					editorContainer.html(editor).show();
					ele.hide();

					//bind events
					//save
					$(".btn_saveEditor").off(DEF_CLICK).on(DEF_CLICK, function(e){
						e.preventDefault();

						var that = this;
						var post_id = $(that).attr("data-id");
						var ele = $("#postContent_" + post_id);
						var editorContainer = $("#postEditor_" + post_id);
						var editor_text = $("#postEditorContent_" + post_id).val();

						//validate text
						if ( $.trim(editor_text) == "") {

							var _error = '<div id="post-editor-error_' + post_id + '" class="ui message small red post-edit-msg-error">Uh oh... this post is empty. Add some awesome text and try again.</div>';

							//remove any version of this error (if exists)
							$("#post-editor-error_" + post_id).remove();

							$("#post-editor-container_" + post_id).append(_error);

							$(".post_editor").off(DEF_CLICK).on(DEF_CLICK, function(e){

								$(".post-edit-msg-error").remove()
							});
							
							return false;
						};

						$("#postContent_" + post_id).html("Working...").show();
						
						editorContainer.hide();

						//update this post
						postUpdate(editor_text, post_id, function(e){

							if (e.status == 'success') {

								var profilePoints = e.profilePoints;
								var points = e.points;
								var post = e.post;

								//update the profile points display
								$("#profilePoints").text(profilePoints);

								post = emoticons(post);

								var this_site = $("#this_site").attr("data-id");

								var _post = post;
									//_post = _post.replace(/(^|\W)(#[a-z\d][\w-]*)/ig, '$1<span class="site-hashtag"><a title="Search for other posts about [REMOVEHASHTAG]$2" href="' + this_site + 'search/?t=$2">[REMOVEHASHTAG]$2</a></span>');
									_post = _post.replace(/(^|[^&\w])(#[a-z\d][\w-]*)/ig, '$1<span class="site-hashtag"><a title="Search for other posts about [REMOVEHASHTAG]$2" href="' + this_site + 'graffiti/?t=[REMOVEHASHTAG]$2">[REMOVEHASHTAG]$2</a></span>');
									_post = _post.split("[REMOVEHASHTAG]#").join("");
									post = _post.split("search/?t=#").join("search/?t=");

								//place the updated post into html
								$("#postContent_" + post_id).html(post);
								editorContainer.html("");
								ele.show();

							} else {

								ele.hide();
								editorContainer.show();

								var _error = '<div id="post-editor-error_' + post_id + '" class="ui message small red post-edit-msg-error">Uh oh... there was an error. It is: ' + e + '</div>';

								//remove any version of this error (if exists)
								$("#post-editor-error_" + post_id).remove();

								$("#post-editor-container_" + post_id).append(_error);

								$(".post_editor").off(DEF_CLICK).on(DEF_CLICK, function(e){

									$(".post-edit-msg-error").remove()
								});
							}
						});
					});

					//cancel
					$(".btn_cancelEditor").off(DEF_CLICK).on(DEF_CLICK, function(e){
						e.preventDefault();

						var that = this;
						var post_id = $(that).attr("data-id");
						var ele = $("#postContent_" + post_id);
						var editorContainer = $("#postEditor_" + post_id);

						editorContainer.html("");
						ele.show();
					});

				});

				//hide delete affirm 
				$("._delete_post_no").off(DEF_CLICK).on(DEF_CLICK, function(e){
					e.preventDefault();

					var that = this;
					var post_id = $(that).attr("data-id");

					$("#deleteAffirm_" + post_id).hide();
					$("#deletepost_" + post_id).show();
				});

				//show list of people that viewed me 
				$("#glanceCount").off(DEF_CLICK).on(DEF_CLICK, function(){

					if (likeWorking == true) {
						return false;
					};

					var that = this;
					var thisContent = $(that).text();

					$(".stats_dialog").remove();

					likeWorking = true;

					$(that).text("Working....");

					json_url = "../func/profile-who-glanced.php";

					_ajax("", json_url, function(json){

						if (json.status != "success") {
							
							//alert(json.msg);
							likeWorking = false;
							$(that).text(thisContent);

						}else{

							$(that).text(thisContent);

							var bluebox,
								this_site = $("#this_site").attr("data-id"),
								cards = "",
								people,
								count = 0,
								card;

	            			var likecount_msg,
	            				glanceCount_msg,
	            				glanceCountLast_msg,
	            				glancePeoplecount_msg,
	            				glancePeopleCount = $("#glanceCount").attr("data-glancePeopleCount"),
	            				glanceCount = $("#glanceCount").attr("data-glanceCount");


	            			if (glancePeopleCount == "1") {
	            				glancePeoplecount_msg = "1 person";
	            			} else {
	            				glancePeoplecount_msg = glancePeopleCount + " people";
	            			}	

	            			if (glanceCount == "1") {
	            				glanceCount_msg = "1 time";
	            			} else {
	            				glanceCount_msg = glanceCount + " times";
	            			}

	            			if (!isNumber(glancePeopleCount)) {
	            				glancePeopleCount = '0';
	            			};

	            			if (parseInt(glancePeopleCount) > 19) {
	            				glanceCountLast_msg = "Here are the last 20 people.";
	            			} else {
	            				glanceCountLast_msg = "";
	            			}

							bluebox = '<div id="glance_post_dialog" class="ui message blue stats_dialog"> <i class="glance-close close icon"></i> <div class="header"> Awesome! </div> <p style="font-size:12px;">You have been viewed at least ' + glanceCount_msg + ' by ' + glancePeoplecount_msg + '. ' + glanceCountLast_msg + '</p>[CARDS]<div style="clear:both"></div> </div>';

							var my_profile_pic;

							$.each(json.results, function(k,v){

								my_profile_pic = v.profileImage;
					
								if ($.trim(my_profile_pic) == "") {

									if (v.gender == "guy") {
										my_profile_pic = myProfile.profileNoPicGuy;
									} else {
										my_profile_pic = myProfile.profileNoPicGal;
									}
								};

								card = '<div class="card-tiny-card"> <div class="card-tiny-image"> <a title="Click to view in a new window" target="_blank" href="' + this_site + 'profile/?' + v.userName + '"> <img class="ui tiny circular image" src="' + my_profile_pic + '"> </a> </div> <div> <a title="Click to view in a new window" target="_blank" href="' + this_site + 'profile/?' + v.userName + '">' + v.firstName + '</a> </div> </div>';
								cards = cards + card;

								count++;
							});

							bluebox = bluebox.split("[CARDS]").join(cards);

							//append to the container
							$("#contentCenterCol").prepend(bluebox);
						}

						likeWorking = false;

						$('.message .glance-close').off('click').on('click', function() {
							$("#glance_post_dialog").remove();
						});
					});
				});

				//show list of people that liked me 
				$("#likePeopleCount").off(DEF_CLICK).on(DEF_CLICK, function(){

					if (likeWorking == true) {
						return false;
					};

					var that = this;
					var thisContent = $(that).text();

					$(".stats_dialog").remove();

					likeWorking = true;

					$(that).text("Working....");

					json_url = "../func/profile-who-liked.php";

					_ajax("", json_url, function(json){

						if (json.status != "success") {
							
							//alert(json.msg);
							likeWorking = false;
							$(that).text(thisContent);

						}else{

							$(that).text(thisContent);

							var bluebox,
								this_site = $("#this_site").attr("data-id"),
								cards = "",
								people,
								count = 0,
								card;

	            			var likecount_msg,
	            				likepeoplecount_msg,
	            				likecountLast_msg,
	            				likecount = $("#likePeopleCount").attr("data-likecount"),
	            				likepeoplecount = $("#likePeopleCount").attr("data-likepeoplecount");

	            			if (likecount == "1") {
	            				likecount_msg = "1 like";
	            			} else {
	            				likecount_msg = likecount + " likes";
	            			}

	            			fansTitle = "No Fans Yet";
	            			
	            			if (likecount == "0") {
	            				fansTitle = "No Fans Yet";
	            			} else {
	            				fansTitle = "You have fans!";
	            			}

	            			if (likepeoplecount == "1") {
	            				likepeoplecount_msg = "1 person";
	            			} else {
	            				likepeoplecount_msg = likepeoplecount + " people";
	            			}	

	            			if (!isNumber(likepeoplecount)) {
	            				likepeoplecount = '0';
	            			};

	            			if (parseInt(likepeoplecount) > 19) {
	            				likecountLast_msg = "Here are the last 20.";
	            			} else {
	            				likecountLast_msg = "";
	            			}

							bluebox = '<div id="liked_post_dialog" class="ui message blue stats_dialog"> <i class="like-close close icon"></i> <div class="header"> ' + fansTitle + ' </div> <p style="font-size:12px;">You have ' + likecount_msg + ' by ' + likepeoplecount_msg + '. ' + likecountLast_msg + '</p>[CARDS]<div style="clear:both"></div> </div>';

							var my_profile_pic;

							$.each(json.results, function(k,v){

								my_profile_pic = v.profileImage;
					
								if ($.trim(my_profile_pic) == "") {

									if (v.gender == "guy") {
										my_profile_pic = myProfile.profileNoPicGuy;
									} else {
										my_profile_pic = myProfile.profileNoPicGal;
									}
								};

								card = '<div class="card-tiny-card"> <div class="card-tiny-image"> <a title="Click to view in a new window" target="_blank" href="' + this_site + 'profile/?' + v.userName + '"> <img class="ui tiny circular image" src="' + my_profile_pic + '"> </a> </div> <div> <a title="Click to view in a new window" target="_blank" href="' + this_site + 'profile/?' + v.userName + '">' + v.firstName + '</a> </div> </div>';
								cards = cards + card;

								count++;
							});

							bluebox = bluebox.split("[CARDS]").join(cards);

							//append to the container
							$("#contentCenterCol").prepend(bluebox);
						}

						likeWorking = false;

						$('.message .like-close').off('click').on('click', function() {
							$("#liked_post_dialog").remove();
						});
					});
				});

				//get likes 
				$(".howmanylikes").off(DEF_CLICK).on(DEF_CLICK, function(){

					if (likeWorking == true) {
						return false;
					};

					var that = this;
					var post_id = $(that).attr("data-id");
					var thisContent = $(that).text();

					likeWorking = true;

					$(that).text("Working....");

					//try to remove any visible versions of this
					$("#likeid_" + post_id).remove();

					postdata = {
						"post_id" : post_id
					}

					json_url = "../func/post-who-liked.php";

					_ajax(postdata, json_url, function(json){

						if (json.status != "success") {
							
							//alert(json.msg);
							likeWorking = false;
							$(that).text(thisContent);

						}else{

							$(that).text(thisContent);

							var bluebox,
								this_site = $("#this_site").attr("data-id"),
								cards = "",
								people,
								count = 0,
								card;

							bluebox = '<div id="likeid_' + post_id + '" class="like-container"> <div class="ui message blue"> <i class="like-card close icon"></i> <div class="header"> Awesome! </div> <p style="font-size:12px;">[PEOPLE LIKE] this post</p>[CARDS]<div style="clear:both"></div> </div> </div>';

							var my_profile_pic;

							$.each(json.results, function(k,v){

								my_profile_pic = v.profileImage;
					
								if ($.trim(my_profile_pic) == "") {

									if (v.gender == "guy") {
										my_profile_pic = myProfile.profileNoPicGuy;
									} else {
										my_profile_pic = myProfile.profileNoPicGal;
									}
								};

								card = '<div class="card-tiny-card"> <div class="card-tiny-image"> <a title="Click to view in a new window" target="_blank" href="' + this_site + 'profile/?' + v.userName + '"> <img class="ui tiny circular image" src="' + my_profile_pic + '"> </a> </div> <div> <a title="Click to view in a new window" target="_blank" href="' + this_site + 'profile/?' + v.userName + '">' + v.firstName + '</a> </div> </div>';
								cards = cards + card;

								count++;
							});

							if (count == 1) {
								people = "1 person likes ";
							} else {
								people = count + " people like ";
							}

							bluebox = bluebox.split("[PEOPLE LIKE]").join(people);
							bluebox = bluebox.split("[CARDS]").join(cards);

							//append to the container
							$("#postContent_" + post_id).append(bluebox);
						}

						likeWorking = false;

						$('.message .like-card').off('click').on('click', function() {
							$(this).closest('.like-container').remove();
						});
					});
				});

				//delete this post 
				$("._delete_post").off(DEF_CLICK).on(DEF_CLICK, function(e){

					e.preventDefault();

					var post_id = $(this).attr("data-id");

					$("#post_" + post_id).fadeOut("slow");
					
					postdata = {
						"post_id" : post_id
					}

					json_url = "../func/post-delete.php";

					_ajax(postdata, json_url, function(json){

						if (json.status != "success") {

							alert(json.msg);

						}else{

							var profilePoints = json.profilePoints;
							
							//update the profile points display
							$("#profilePoints").text(profilePoints);

							postPageIndex = 0;

							/*$("#post_" + post_id).remove();
							$('#counter_posts').text( $(".__post").length );*/
							setTimeout(function(){

								getPosts(function(){});
							},500);
						}
					});
				});

				//pin this post 
				$("._pin_post").off(DEF_CLICK).on(DEF_CLICK, function(e){

					e.preventDefault();

					var post_id = $(this).attr("data-id");

					postdata = {
						"post_id" : post_id
					}

					json_url = "../func/post-pin.php";

					_ajax(postdata, json_url, function(json){

						if (json.status != "success") {
							
							alert(json.msg);

						}else{

							postPageIndex = 0;

							setTimeout(function(){

								getPosts(function(){});
							},500);

							$('html, body').animate({
						        scrollTop: $("#btn_post").offset().top
						    }, 1000);
						}
					});
				});

				//unpin this post 
				$("._unpin_post").off(DEF_CLICK).on(DEF_CLICK, function(e){

					e.preventDefault();

					var post_id = $(this).attr("data-id");

					postdata = {
						"post_id" : post_id
					}

					json_url = "../func/post-unpin.php";

					_ajax(postdata, json_url, function(json){

						if (json.status != "success") {
							
							alert(json.msg);

						}else{

							postPageIndex = 0;
							
							setTimeout(function(){

								getPosts(function(){});
							},500);
						}
					});
				});

				getPostsBusy = false;

				callback();

			} else {

				getPostsBusy = false;

				_alert("Important Message", json.msg);

				callback();

				return false;
			};
		});
	}

	function init(){

		$("#crop_profile_photo").hide();
		$(".edit_mode_overlay").hide();
		$('.dropdown').dropdown();
		$('.ui.checkbox').checkbox();
		$('.popup').popup();
		$('html, body').animate({ scrollTop: 0 }, 0);
		buildProfileForm();
		getPosts(function(){});
		buildUploader();
		scrollListen();
		$("#pageBody").show();
		$("#btn_working").hide();
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

	function emoticonsRev(str){

		var m = str;
		m = m.split('<i class="icon-emo-happy"></i>').join(':-)');
		m = m.split('<i class="icon-emo-wink"></i>').join(';-)');
		m = m.split('<i class="icon-emo-wink2"></i>').join(';)');
		m = m.split('<i class="icon-emo-unhappy"></i>').join(':-(');
		m = m.split('<i class="icon-emo-thumbsup"></i>').join('(Y)');
		
		return m;
	}
	
	//lazy load posts
	function scrollListen(){

		//$(window).scroll(function() {
		$(window).one("scroll", function () {

			//console.log("scrollTop: " + $(window).scrollTop());

			if($(window).scrollTop() + $(window).height() > $(document).height() - 100) {
				
				if (getPostsBusy==false) {
					
					getPosts(function(){});
				};
			}

			if (postPageIndex != -1) {
				setTimeout(scrollListen(), 200); //rebinds itself after 200ms
			}
		
		});
	}

	function savePost(){

		var postSeed = $("#post_seed").attr("data-id");
		var post = $("#postEditor").val();

		//image count
		postImageCount = 0;
		var images = "";

		//post_imageThumb

		$(".post_imageThumb").each(function(){

			postImageCount++;

			images = images + $(this).attr("data-url") + "(..)";
		});

		if (postImageCount == 0) {
			images = "0";
		} else {
			images = images + "" + postImageCount;
		}

		if ( $.trim(post) == "") {

			$("#btn_post").show();
			$("#btn_working").hide();

			//_alert("Important Message", "Why an empty post? Type something awesome and try again.");
			alert("Why an empty post? Type something awesome and try again.");
			return false;
		}

		var postdata = {
			"i" : $("#form_token").attr("data-id"),
			"postSeed" : postSeed,
			"images" : images,
			"imageCount" : postImageCount,
			"post" : post
		}

		json_url = "../func/post-add.php";

		_ajax(postdata, json_url, function(json){

			if (json.status == "success") {

				$("#btn_post").show();
				$("#btn_working").hide();

				postPageIndex = 0;

				//reset the editor
				$("#postEditor").val("");

				var profilePoints = json.profilePoints;
				var points = json.points;

				//update the profile points display
				$("#profilePoints").text(profilePoints);

				post_seed = json.post_seed;
				post_id = json.post_id;

				//new post seed
				$("#post_seed").attr("data-id", post_seed);

				$("#post_imagesContainer").html("");
		
				setTimeout(function(){

					getPosts(function(){

						var msg = '<div class="ui icon green message"> <i class="trophy icon"></i> <div class="content"> <div class="header"> Awesome! </div> <p>You just scored ' + points + ' points with that post!</p> </div> </div>';
						$("#postMsgContainer_" + post_id).html(msg);

						setTimeout(function(){

							$("#postMsgContainer_" + post_id).html("");

						}, 5000);
					});
				},500);

				$('html, body').animate({
			        scrollTop: $("#btn_post").offset().top
			    }, 1000);
				
			} else {

				$("#btn_post").show();
				$("#btn_working").hide();

				_alert("Important Message", json.msg);
				return false;
			};
		});
	}

	function postUpdate(post, post_id, callback){

		var postdata = {
			"id" : post_id,
			"post" : post
		}

		json_url = "../func/post-update.php";

		_ajax(postdata, json_url, function(json){

			callback(json);
		});
	}

	function updateProfile(){

		var zipcode 				=		$.trim($("#zipcode").val());
		var firstName 				= 		$.trim($("#firstName").val());
		var birthMonth 				= 		$.trim($("#birthMonth").val());
		var birthDay 				=		$.trim($("#birthDay").val());
		var birthYear 				= 		$.trim($("#birthYear").val());
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
		var zodiacPref 				= 		$.trim($("#zodiacPref").val());
		var profileRating 			= 		$.trim($("#profileRating").val());
		var adultPreference			= 		$.trim($("#adultPreference").val());
		var profileDesc 			= 		$.trim($("#profileDescription").val());
		var bannerImage 			= 		$.trim($("#__bannerImage").attr("src"));

		if ( firstName == "" ) {
			_alert("This is important", "Please enter your first name.");
			$("#firstName").focus();
			return false;
		};

		if ( zipcode == "" ) {
			_alert("This is important", "Please enter your zipcode.");
			$("#zipcode").focus();
			return false;
		};

		var postdata = {
			"i" : $("#form_token").attr("data-id"),
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
			"children" : children,
			"zodiacPref" : zodiacPref,
			"profileRating" : profileRating,
			"adultPreference" : adultPreference,
			"bannerImage" : bannerImage,
			"profileDesc" : profileDesc
		}

		//show the loading page
		$("#pageLoading").show();

		//hide this page
		$("#pageAccountInfo").hide();

		json_url = "../func/account-update.php";

		_ajax(postdata, json_url, function(json){

			if (json.status == "success") {

				alert("Your profile was updated. I need to refresh the page now.");
				//location.reload();
				
				var this_site = $("#this_site").attr("data-id");
				window.location = this_site + "home/";
				
			} else {

				$("#pageLoading").hide();
				$("#pageAccountInfo").show();
				_alert("Important Message", json.msg);
				return false;
			};
		});
	}

});

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

	$("#charCount").text("Characters left: " + (500 - $("#profileDescription").val().length));
}

function buildUploader(){

	var btn = document.getElementById('__btn_addPhoto'),
	  progressBar = document.getElementById('__progressBar'),
	  progressOuter = document.getElementById('__progressOuter'),
	  msgBox = document.getElementById('__msgBox');

	var uploader = new ss.SimpleUpload({
	    button: btn,
	    url: '../func/post-file-uploader.php',
	    name: 'uploadfile',
	    hoverClass: 'hover',
	    focusClass: 'focus',
	    responseType: 'json',
	    allowedExtensions: ['jpg', 'jpeg', 'png'],
	    customHeaders: {'image_sizes' : '700'},

	    startXHR: function() {
	        progressOuter.style.display = 'block'; // make progress bar visible
	        this.setProgressBar( progressBar );
	    },
	    onSubmit: function() {
	        msgBox.innerHTML = ''; // empty the message box
	        btn.innerHTML = '<i class="icon loading spinner"></i>Uploading...'; // change button text to "Uploading..."
	        $("#btn_post").hide();
	      },
	    onComplete: function( filename, response ) {
	        btn.innerHTML = '<i class="icon photo"></i>Another Photo';
	        progressOuter.style.display = 'none'; // hide progress bar when upload is completed

	        $("#btn_post").show();

	        if ( !response ) {
	            msgBox.innerHTML = 'Unable to upload file';
	            return;
	        }

	        if ( response.success === true ) {

	            var timestamp = new Date().getUTCMilliseconds();
	        	var height = parseInt(response.file_size_height);
	        	var width = 700;

	        	//check for taller
	        	if (height > width) {

	        		var reducedHeight = 0.11 * height; //11% of file original height
	        		var h = reducedHeight - 80;
	        		var marginOffset = h / 2;
	        		var style = 'width:75px;position:absolute;top:-' + marginOffset + 'px;';

	        		console.log("style" + style);

	        	}else if (height < width) {

	        		var percentageOfHeight = 70 / height * 100;
	        		var h = percentageOfHeight / 100;
	        		var j = h * 700;
	        		var k = j - 70 / 2;
	        		var style = 'height:75px;position:absolute;left:0px;';

	        	} else {

	        		//this is a square
	        		var style = 'width:75px;position:absolute;left:0px;';
	        	};

				var image = '<div class="post_imageThumb disablePanZoom" data-url="' + response.filename + '"><div class="remove_postImage"><i class="_remove_image_post remove icon"></i></div><img style="' + style +'" alt="" src="' + response.filename + '" class=""></div>';

				$("#post_imagesContainer").append(image);

				//bind events
				$(".remove_postImage").off(DEF_CLICK).on(DEF_CLICK, function(){

					$(this).parent().fadeOut("slow").remove();
				});

				$( "#post_imagesContainer" ).sortable();

	        } else {

	            if ( response.msg )  {
	                //msgBox.innerHTML = escapeTags( response.msg );
	                alert(response.msg);
	            } else {
	                //msgBox.innerHTML = 'An error occurred and the upload failed.';
	                alert('An error occurred and the upload failed.');
	            }
	        }
	      },
	    onError: function() {
	        progressOuter.style.display = 'none';
	        msgBox.innerHTML = 'Unable to upload file';
	      }
	});
}

function changeBanner(url){

	$(".bannerImage").attr("src", url);
	
	$(".edit_banner_overlay_view").hide();

	intID = setInterval(function(){

		$(".edit_banner_overlay_view").fadeIn("fast");

		clearInterval(intID);

	},2000);
}

function closeCrop(){

	$("#crop_profile_photo").hide();
	_resetCrop();

}

function saveProfileImage_croppic(image){

	var timestamp = new Date().getUTCMilliseconds();

	image = image + "?=" + timestamp;

	//update profile images with new image
	$("#__mainProfileImage").attr("src", image);
	$(".profile_image_").attr("src", image);
	closeCrop();
}

function _resetCrop(){

	//reset the iframe
	$("#frame_crop_profile_photo").attr("src", "crop_working.php");

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
    string = string.replace(/&apos;/g, "'"); // PHP doesn't currently escape if more than one 0, but it should
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


