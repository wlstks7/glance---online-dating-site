var DEF_CLICK = "click";
var DEF_POINTS = {
	silver : 50,
	platinum : 500
}
var postPageIndex = 0;
var conversationPageIndex = 0;
var currentBackground;
var getPostsBusy = false;
var previousSearchTitle = "";
var searchItems = {};
var mobileDevice = false;
var currentSearchItem = {
	"searchName" : "Default",
	"searchingGender" : "guygal",
	"searchingAgeFrom" : 38,
	"searchingAgeTo" : 48,
	"searchDistance" : 15,
	"searchZipcode" : "85210",
	"onlyWithProfileImage" : "checked",
	"onlyOnline" : "",
	"showAppearance" : "false",
	"bodyType" : "",
	"hairDesc" : "",
	"ethnicity" : "", 
	"eyeDesc" : "",
	"minHeight" : "",
	"maxHeight" : "",
	"showLifestyle" : "false",
	"relationshipStatus" : "",
	"religious" : "",
	"children" : "",
	"income" : "",
	"smokerPref" : "",
	"drinkingPref" : "",
	"zodiacPref" : "",
	"adultSearch" : ""
}

$(function(){

	init();

	if (mobilecheck() == true) {
		mobileDevice = true;
		$("#searchVertical").css("position", "inherit");
		$("#searchMenuContainer").css("position", "inherit");
	}

	buildDefaultSearchItem(function(){});

	//$("#searchButtonArea").hide();
	$("#btn_whoOnline").on(DEF_CLICK, function(){

		postPageIndex = 0;
		getWhosOnline();
	});

	//search settings saved
	$("#btn_applySearch").on(DEF_CLICK, function(){
			
		if ($.trim($("#input_searchName").val())=="") {
			$("#input_searchName").focus();
			alert("Please name this search before saving it.");
			return false;
		};

		saveSearch(function(){

			//run this search
			runSearch('true');

			//close the dialog
			toggleSearchEditDialog("closed");

			buildSearchNarrative();
		});
	});

	//create a new search
	$("#btn_newSearch").on(DEF_CLICK, function(){
		
		console.log("msg");
		buildDefaultSearchItem(function(){

			currentSearchItem.searchName = "New Search";
			buildSearchForm();
		});
	});

	//toggle open appearance section
	$("#sectionAreaInnerAppearance").on(DEF_CLICK, function(){
		
		$("#sectionAreaInnerAppearance").removeClass("sectionClosed");
		$("#sectionAreaAppearance").removeClass("sectionClosed");
	});

	//toggle open lifestyle section
	$("#sectionAreaInnerLifestyle").on(DEF_CLICK, function(){
		
		$("#sectionAreaInnerLifestyle").removeClass("sectionClosed");
		$("#sectionAreaLifestyle").removeClass("sectionClosed");
	});

	//close search edit
	$("#btn_closeSearchEdit").on(DEF_CLICK, function(){
		
		toggleSearchEditDialog("closed");
	});

	//open search edit
	$("#btn_editSearch").on(DEF_CLICK, function(){
		
		if ( $(this).attr("data-action") == "closed" ) {

			toggleSearchEditDialog("open");

		} else {
			
			toggleSearchEditDialog("closed");
		}
	});

	//close search edit favorites
	$("#btn_closeSearchFavEdit").on(DEF_CLICK, function(){
		
		$("#__searchEditFavList").hide();
		$("#__searchView").show();
		$("#__searchEdit").hide();
		$(".__editSearchContainers").hide();
		$("#topHide").show();	

		/*$('#searchMenu').sticky({
	    	context: '#contentCenterCol',
	    	offset: 60
	    });*/
	});

	//$("#searchButtonArea").hide();
	$("#btn_mySearch").on(DEF_CLICK, function(){

		var defaultSearch = myProfile.defaultSearch;

		if ($.trim(defaultSearch) == "") {

			currentSearchItem = currentSearchItemTemplate();

			//run the deafult search item
			buildDefaultSearchItem(function(){

				runSearch('true');
			});

		} else {

			//try to run this item
			if ( $.trim(searchItems[defaultSearch]) == "" ) {

				//if it doesn't exist then run the deafult item
				buildDefaultSearchItem(function(){

					runSearch('true');
				});
			} else {

				currentSearchItem = currentSearchItemTemplate();

				//build currentSearchItem from this
			 	currentSearchItem = searchItems[defaultSearch];

				runSearch('true');
			}
		}
	});

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

	function buildSearchForm(){
		
		//restore defaults
		$('.searchForm').dropdown('restore defaults');

		//if this search has a name... display it
		$("#input_searchName").val(currentSearchItem.searchName);

		$('#_searchingGender').dropdown('set selected', currentSearchItem.searchingGender);
		$('#_searchingGender').dropdown('set value', currentSearchItem.searchingGender);
		
		$('#_searchingAgeFrom').dropdown('set selected', currentSearchItem.searchingAgeFrom);
		$('#_searchingAgeFrom').dropdown('set value', currentSearchItem.searchingAgeFrom);

		$('#_searchingAgeTo').dropdown('set selected', currentSearchItem.searchingAgeTo);
		$('#_searchingAgeTo').dropdown('set value', currentSearchItem.searchingAgeTo);

		$("#input_distance").val(currentSearchItem.searchDistance);
		$("#input_zipcode").val(currentSearchItem.searchZipcode);
		
		if ( currentSearchItem.onlyWithProfileImage == "checked" ) {
			$('#chk_profileImage').checkbox('check');
		} else {
			$('#chk_profileImage').checkbox('uncheck');
		}
		
		if ( currentSearchItem.onlyOnline == "checked" ) {
			$('#chk_online').checkbox('check');
		} else {
			$('#chk_online').checkbox('uncheck');
		}

		if (currentSearchItem.showAppearance == "true") {

			//show the section
			$("#sectionAreaInnerAppearance").removeClass("sectionClosed")
			$("#sectionAreaAppearance").removeClass("sectionClosed")
		
			//load the values
			$('#_bodyType').dropdown('set exactly', currentSearchItem.bodyType.split(","));
			$('#_hairDesc').dropdown('set exactly', currentSearchItem.hairDesc.split(","));
			$('#_ethnicity').dropdown('set exactly', currentSearchItem.ethnicity.split(","));
			$('#_eyeDesc').dropdown('set exactly', currentSearchItem.eyeDesc.split(","));
			$('#_minHeight').dropdown('set exactly', currentSearchItem.minHeight.split(","));
			$('#_maxHeight').dropdown('set exactly', currentSearchItem.maxHeight.split(","));

		} else {

			//hide the section
			$("#sectionAreaInnerAppearance").addClass("sectionClosed");
			$("#sectionAreaAppearance").addClass("sectionClosed");
		}

		if (currentSearchItem.showLifestyle == "true") {
			
			//show the section
			$("#sectionAreaInnerLifestyle").removeClass("sectionClosed")
			$("#sectionAreaLifestyle").removeClass("sectionClosed")
		
			$('#_relationshipStatus').dropdown('set exactly', currentSearchItem.relationshipStatus.split(","));
			$('#_religious').dropdown('set exactly', currentSearchItem.religious.split(","));
			$('#_children').dropdown('set exactly', currentSearchItem.children.split(","));
			$('#_income').dropdown('set exactly', currentSearchItem.income.split(","));
			$('#_smokerPref').dropdown('set exactly', currentSearchItem.smokerPref.split(","));
			$('#_drinkingPref').dropdown('set exactly', currentSearchItem.drinkingPref.split(","));
			$('#_zodiacPref').dropdown('set exactly', currentSearchItem.zodiacPref.split(","));

			if ( currentSearchItem.adultSearch == "checked" ) {
				$('#chk_adultSearch').checkbox('check');
			} else {
				$('#chk_adultSearch').checkbox('uncheck');
			}
		} else {

			//show the section
			$("#sectionAreaInnerLifestyle").addClass("sectionClosed");
			$("#sectionAreaLifestyle").addClass("sectionClosed");
		}
	}

	function buildSearchItem(callback){

		//set defaults based on profile
		var searchingGender,
			ageMin = 18,
			ageMax = 100,
			searchingAgeFrom = 18,
			searchingAgeTo = 100,
			searchDistance = 15,
			searchZipcode = "",
			onlyWithProfileImage = "",
			onlyOnline = "",
			showAppearance = "false",
			showLifestyle = "false",
			searchName = "",
			adultSearch = "";

		//seeking gender
		if ( $("#searchingGender").val() == "" ) {
			searchingGender = myProfile.gender + myProfile.seekingGender;
		} else {
			searchingGender = $("#searchingGender").val();
		}

		//check the profile age value
		if (!isNumber(myProfile.age)) {
			
			//something went wrong... make this the min number
			myProfile.age = ageMin;

		} else {

			//type int value
			myProfile.age = parseInt(myProfile.age);
		}

		//calculate default age ranges based on user profile age
		if (myProfile.age < 28) {

			searchingAgeFrom = 18;
			searchingAgeTo = myProfile.age + 10;

		} else {

			searchingAgeFrom = myProfile.age - 10;
			searchingAgeTo = myProfile.age + 10;
		}

		//seeking ages
		if (isNumber($("#searchingAgeFrom").val())) {

			//check for valid values
			var n = parseInt($("#searchingAgeFrom").val());
			if (n >= ageMin && n <= ageMax) {
				searchingAgeFrom = n;
			}
		}
		
		if (isNumber($("#searchingAgeTo").val())) {

			//check for valid values
			var n = parseInt($("#searchingAgeTo").val());
			if (n >= ageMin && n <= ageMax) {
				searchingAgeTo = n;
			}
		}

		//set default distance
		searchDistance = 50;

		if (isNumber($("#input_distance").val())) {

			//check for valid value
			var n = parseInt($("#input_distance").val());
			if (n >= 1 && n <= 200) {
				searchDistance = n;
			}
		}

		//check the profile zipcode value
		if (!isNumber(myProfile.zipcode)) {
			
			//something went wrong...do something..anything...
			//here's an idea... show them Mesa Arizona ;-)
			myProfile.zipcode = "85209";
		}

		//set the default zipcode
		searchZipcode = myProfile.zipcode;

		//seeking valid zipcode 
		if (isNumber($("#input_zipcode").val())) {

			//check for valid values
			searchZipcode = $("#input_zipcode").val();	
		}

		if ($("#chk_profileImage").hasClass("checked")) {
			onlyWithProfileImage = "checked";
		}

		if ($("#chk_online").hasClass("checked")) {
			onlyOnline = "checked";
		}

		if ($("#chk_adultSearch").hasClass("checked")) {
			adultSearch = "checked";
		}

		if ( $.trim($("#input_searchName").val()) != "" ) {
			searchName = $.trim($("#input_searchName").val());
		};

		//check values in hidden sections
		if ($("#bodyType").val() != "") {showAppearance = "true"};
		if ($("#hairDesc").val() != "") {showAppearance = "true"};
		if ($("#ethnicity").val() != "") {showAppearance = "true"};
		if ($("#eyeDesc").val() != "") {showAppearance = "true"};
		if ($("#minHeight").val() != "") {showAppearance = "true"};
		if ($("#maxHeight").val() != "") {showAppearance = "true"};

		if ($("#relationshipStatus").val() != "") {showLifestyle = "true"};
		if ($("#religious").val() != "") {showLifestyle = "true"};
		if ($("#children").val() != "") {showLifestyle = "true"};
		if ($("#income").val() != "") {showLifestyle = "true"};
		if ($("#smokerPref").val() != "") {showLifestyle = "true"};
		if ($("#drinkingPref").val() != "") {showLifestyle = "true"};
		if ($("#zodiacPref").val() != "") {showLifestyle = "true"};
		if ($("#adultSearch").hasClass("checked")) {showLifestyle = "true"};

		var item = {
			"searchName" : searchName,
			"searchingGender" : searchingGender,
			"searchingAgeFrom" : searchingAgeFrom,
			"searchingAgeTo" : searchingAgeTo,
			"searchDistance" : searchDistance,
			"searchZipcode" : searchZipcode,
			"onlyWithProfileImage" : onlyWithProfileImage,
			"onlyOnline" : onlyOnline,
			"showAppearance" : showAppearance,
			"bodyType" : $("#bodyType").val(),
			"hairDesc" : $("#hairDesc").val(),
			"ethnicity" : $("#ethnicity").val(),
			"eyeDesc" : $("#eyeDesc").val(),
			"minHeight" : $("#minHeight").val(),
			"maxHeight" : $("#maxHeight").val(),
			"showLifestyle" : showLifestyle,
			"relationshipStatus" : $("#relationshipStatus").val(),
			"religious" : $("#religious").val(),
			"children" : $("#children").val(),
			"income" : $("#income").val(),
			"smokerPref" : $("#smokerPref").val(),
			"drinkingPref" : $("#drinkingPref").val(),
			"zodiacPref" : $("#zodiacPref").val(),
			"adultSearch" : adultSearch
		}

		//set the global search item
		currentSearchItem = item;

		callback();
		//console.log(item);
	}

	function buildDefaultSearchItem(callback){

		//set defaults based on profile
		var searchingGender,
			ageMin = 18,
			ageMax = 100,
			searchingAgeFrom = 18,
			searchingAgeTo = 100,
			searchDistance = 15,
			searchZipcode = "",
			onlyWithProfileImage = "",
			onlyOnline = "",
			showAppearance = "false",
			showLifestyle = "false",
			searchName = "",
			adultSearch = "";

		//reset the current item
		currentSearchItem = currentSearchItemTemplate();

		searchingGender = myProfile.gender + myProfile.seekingGender;
		
		//check the profile age value
		if (!isNumber(myProfile.age)) {
			
			//something went wrong... make this the min number
			myProfile.age = ageMin;

		} else {

			//type int value
			myProfile.age = parseInt(myProfile.age);
		}

		//calculate default age ranges based on user profile age
		if (myProfile.age < 28) {

			searchingAgeFrom = 18;
			searchingAgeTo = myProfile.age + 10;

		} else {

			searchingAgeFrom = myProfile.age - 10;
			searchingAgeTo = myProfile.age + 10;
		}

		//set default distance
		searchDistance = 50;

		//check the profile zipcode value
		if (!isNumber(myProfile.zipcode)) {
			
			//something went wrong...do something..anything...
			//here's an idea... show them Mesa Arizona ;-)
			myProfile.zipcode = "85209";
		}

		//set the default zipcode
		searchZipcode = myProfile.zipcode;

		//update the current search item with defaults
		currentSearchItem.searchingGender = searchingGender;
		currentSearchItem.searchingAgeFrom = searchingAgeFrom;
		currentSearchItem.searchingAgeTo = searchingAgeTo;
		currentSearchItem.searchDistance = searchDistance;
		currentSearchItem.searchZipcode = searchZipcode;

		callback();
	}
 	
 	function buildDefaultOnlineSearchItem(callback){

		//set defaults based on profile
		var searchingGender,
			ageMin = 18,
			ageMax = 100,
			searchingAgeFrom = 18,
			searchingAgeTo = 100,
			searchDistance = 15,
			searchZipcode = "",
			onlyWithProfileImage = "",
			onlyOnline = "",
			showAppearance = "false",
			showLifestyle = "false",
			searchName = "",
			adultSearch = "";

		currentSearchItem = currentSearchItemTemplate();

		searchingGender = myProfile.gender + myProfile.seekingGender;
		
		//check the profile age value
		if (!isNumber(myProfile.age)) {
			
			//something went wrong... make this the min number
			myProfile.age = ageMin;

		} else {

			//type int value
			myProfile.age = parseInt(myProfile.age);
		}

		//calculate default age ranges based on user profile age
		if (myProfile.age < 28) {

			searchingAgeFrom = 18;
			searchingAgeTo = myProfile.age + 10;

		} else {

			searchingAgeFrom = myProfile.age - 10;
			searchingAgeTo = myProfile.age + 10;
		}

		//set default distance
		searchDistance = 50;

		//check the profile zipcode value
		if (!isNumber(myProfile.zipcode)) {
			
			//something went wrong...do something..anything...
			//here's an idea... show them Mesa Arizona ;-)
			myProfile.zipcode = "85209";
		}

		//set the default zipcode
		searchZipcode = myProfile.zipcode;

		//update the current search item with defaults
		currentSearchItem.searchingGender = searchingGender;
		currentSearchItem.searchingAgeFrom = searchingAgeFrom;
		currentSearchItem.searchingAgeTo = searchingAgeTo;
		currentSearchItem.searchDistance = searchDistance;
		currentSearchItem.searchZipcode = searchZipcode;
		currentSearchItem.searchName = "Who's Online";

		callback();
	}

 	function buildFavoriteSearches (){

 		var items = "";
 		var edititems = "";
 		var searchName;
 		var i;
 		var x=0;

 		var keys = Object.keys(searchItems),
		    ii, len = keys.length;

		keys.sort();

		for (ii = 0; ii < len; ii++)
		{
		    k = keys[ii];
		    //console.log(k + ':' + searchItems[k]);
 			
 			searchName = k;

 			//remove quotes
 			i = searchName;
 			i = i.split("'").join("");
 			searchName = i.split('"').join("");

 			//remove the spaces in search name 
 			search_id = searchName.split(" ").join("_____");

 			edititems = edititems + '<p id="edititem_' + search_id + '">' + searchName + '  -  <span class="item_affirm affirm" data-item="' + searchName + '" data-id="' + x + '" data-question="Delete this search?" data-title="Delete"></span></p>';

 			items = items + '<div id="item_' + search_id + '" data-id="' + searchName + '" class="searchItemFav item"><i class="icon yellow small star"></i>' + searchName + '</div>';

 			x++;
		}

		var header = '<div class="header">My Favorite Searches</div><div data-type="editList" class="item"><i class="icon blue edit"></i>Edit List</div>';

 		$("#favoriteSearchesDrop").html(header + items);
 		$("#searchEditcontentContainerInner").html(edititems);

		//bind the events
		$(".item_affirm").inlineAffirm({
			callback : function(ele){
				var id = $(ele).attr("data-item" );
				var search_id = id.split(" ").join("_____");
				
				$("#edititem_" + search_id).remove();
				$("#item_" + search_id).remove();

				delete searchItems[id];

				console.log(id);

				//save the search list
				var postdata = {
					"search" : JSON.stringify(searchItems)
				}

				json_url = "../func/search-save-fav-search.php";

				_ajax(postdata, json_url, function(json){});
			}
		});

 		$('.btn_mySearchDrop').dropdown('refresh');
 	}

 	function buildSearchNarrative(){
		
		var basicNarrative="",
			milesStr = " miles";

		//build current search name
		$("#comboHeaderTitle").text(currentSearchItem.searchName);

		// basic narrative
		if (currentSearchItem.searchingGender == "guygal" || currentSearchItem.searchingGender == "galgal") {
			basicNarrative = "Women: ";
		} else {
			basicNarrative = "Men: ";
		}

		if (currentSearchItem.searchingAgeFrom == 1) {
			milesStr = " mile";
		}

		basicNarrative = basicNarrative + currentSearchItem.searchingAgeFrom + " - " + currentSearchItem.searchingAgeTo + " years old <br>";
		basicNarrative = basicNarrative + currentSearchItem.searchDistance + milesStr + " from " + currentSearchItem.searchZipcode;

		$(".searchDetails").html(basicNarrative);
	}

	function init(){

		searchResize();

		//toggle adult search on preference
		if (myProfile.adultViewPref == "yesNudity") {
			$(".option_adultSearch").show();
		}else{
			$(".option_adultSearch").hide();
		}

		//create the global search list
		searchItems = myProfile.searches;

		if (searchItems == "") {
			searchItems = {};
		};

		//build the fav search list
		buildFavoriteSearches();

		//$("#btn_working").hide();
		postPageIndex = 0;

		//create the multiseach droplists
		$('.searchForm').dropdown({
			'className' : {
				'label' : 'ui label teal __smaller'
			}
		});

		//bind the items in the favs droplist
		$(".btn_mySearchDrop").dropdown({

		    onChange: function (val,text,$choice) {
		        
		        var n = $choice.attr("data-id");

		        if (n == undefined) {
		        	//edit item
		        	$("#__searchEditFavList").show();
					$("#__searchView").hide();
					$("#topHide").hide();

		        } else {

		 			//build currentSearchItem from this
		 			currentSearchItem = searchItems[n];

		 			runSearch('true');
		        }
		    }
		})

		$('.ui.checkbox').checkbox();
		$('.popup').popup();
		$('html, body').animate({ scrollTop: 0 }, 0);
		//scrollListen();

		//run the who's online search
		getWhosOnline('true');

		$("#__searchView").show();
		$("#__searchEditFavList").hide();	
		$("#__searchViewBlank").hide();	
		$("#__searchEdit").hide();
		$(".__editSearchContainers").hide();

		/*$('#searchVertical').sticky({
	    	context: '#contentCenterCol',
	    	offset: 60
	    });
*/
	    /*$('#searchMenu').sticky({
	    	context: '#contentCenterCol',
	    	offset: 60
	    });	*/

	}

	function isNumber(n) {
	  return !isNaN(parseFloat(n)) && isFinite(n);
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

	function getWhosOnline(firstrun){

		$('html, body').animate({ scrollTop: 0 }, 0);

		if (getPostsBusy == true) {
			return false;
		}

		if (firstrun == 'true') {
			postPageIndex = 0;
		};

		if (postPageIndex == -1) {
			return false;
		}
		
		getPostsBusy = true;

		var postdata = {
			"postPageIndex" : postPageIndex
		}

		postPageIndex++;

		json_url = "../func/search-who-is-online.php";

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
					pointsInt,
					milesAway;

				$("#searchResults").html("");

				var results_html = "";

				$.each(json.results, function(k,v){

					profileImage = v.profileImage;
					userName = v.userName;
					profile_id = v.profile_id;
					firstName = v.firstName;
					pointsInt = v.pointsInt;
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

					var memberLevel = '';

					if ( pointsInt < DEF_POINTS.silver ) {

						memberLevel = '<span><i class="icon user"></i>New Member</span><br>';

					} else if ( pointsInt >= DEF_POINTS.platinum ) {

						memberLevel = '<span style="color:#008DF2;"><i class="icon diamond"></i>Platinum</span><br>';

					}

					online = '<i class="icon circle green"></i> Online Now </div>';
					
					if ( last_online == "" ) {
						online = "&nbsp;";
					};

					template = '<div id = "card_' + profile_id + '" data-id = "' + profile_id + '" data-username="' + userName + '" class="search-column"> <div class="search-column-inner"> <div class="contentContainerSearch"> <div class="postInner"> <table> <tbody> <tr> <td class="postAvatarContainer"> <img class="ui tiny circular image profile_image_" src="' + profileImage + '"> <br> <div style="width:90px;height:1px;"></div> </td> <td class="postContentContainer"> <h4>' + firstName + '</h4> <div class="postContent"> ' + age + ', ' + cityState + ' <br> ' + memberLevel  +  online + ' </td> </tr> </tbody> </table> <div class="cardFooter"> <div class="linkNotInterested affirm results_affirm light" data-title="Not Interested" data-question="Remove this profile?" data-id="' + profile_id + '" href="#">Not Interested</div> <span title="Click to view in another window" class="cardFooterExtLink"> <a class="linkNewSite" target="_blank" href="' + this_site + 'profile/?' + userName + '"><i class="icon gray external square newWindowLink"></i></a> </span> </div> </div> </div> </div> </div>';

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
					},200);
				}
			}

			getPostsBusy = false;

			//build the online search item
			buildDefaultOnlineSearchItem(function(){

				//build the narrative for teh online search
				buildSearchNarrative();	
			});
		});
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

	function notInterested(id){

		var postdata = {
			"id" : id,
			"page_token" : $("#form_token").attr("data-id")
		}

		json_url = "../func/search-not-interested.php";

		_ajax(postdata, json_url, function(json){

		});
		
		$("#card_" + id).fadeOut("slow");

		setTimeout(function(){
			$("#card_" + id).remove();
		},500);
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

	function saveSearch(callback){

		var this_searchName = $.trim($("#input_searchName").val());
		var defaultSearchItem = "";

		//remove any quotes
		this_searchName = this_searchName.split('"').join("");
		this_searchName = this_searchName.split("'").join("");

		if ($("#chk_defaultSearch").hasClass("checked")) {
			defaultSearchItem = this_searchName;
			myProfile.defaultSearch = defaultSearchItem;
		};

		//build this search item as currentSearchItem
		buildSearchItem(function(){

			//buildSearchForm();
		});

		//add to search bundle
		searchItems["" + this_searchName] = currentSearchItem;

		//update the favorite searches dropdown list
		buildFavoriteSearches();

		var postdata = {
			"search" : JSON.stringify(searchItems),
			"defaultSearch" : defaultSearchItem
		}

		json_url = "../func/search-save-fav-search.php";

		_ajax(postdata, json_url, function(json){});

		callback();
	}

	function searchResize(){

		$(".searchPreview")
			.width( $("#searchVerticalInner").width() -80);

		if ( mobileDevice == false ) {
			$("#searchVertical")
				.width( $("#__searchLeftCol").width());	

			$("#searchMenuContainer")
				.width( $("#contentCenterCol").width());	
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

	function toggleSearchEditDialog(action){

		if (action == "open") {

			$('html, body').animate({ scrollTop: 0 }, 0);

			$("#btn_editSearch")
				.attr("data-action", "open")
				.html('<i class="icon remove"></i>Cancel Search Edit');

			$("#__searchView").hide();
			$("#__searchEdit").show();
			$(".__editSearchContainers").show();
			$("#topHide").hide();	

			previousSearchTitle = $("#comboHeaderTitle").text();
			$("#comboHeaderTitle").text("Custom");

			buildSearchForm();


		} else {

			$('html, body').animate({ scrollTop: 0 }, 0);

			//close it
			$("#btn_editSearch")
				.attr("data-action", "closed")
				.html('Edit Search <i class="icon arrow right"></i>');
			$("#__searchView").show();
			$("#__searchEdit").hide();
			$(".__editSearchContainers").hide();
			$("#topHide").show();	

			/*$('#searchMenu').sticky({
		    	context: '#contentCenterCol',
		    	offset: 60
		    });*/

		    $("#comboHeaderTitle").text(previousSearchTitle);
		}
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

function currentSearchItemTemplate(){

	var template = {
		"searchName" : "Default",
		"searchingGender" : "guygal",
		"searchingAgeFrom" : 38,
		"searchingAgeTo" : 48,
		"searchDistance" : 15,
		"searchZipcode" : "85210",
		"onlyWithProfileImage" : "checked",
		"onlyOnline" : "",
		"showAppearance" : "false",
		"bodyType" : "",
		"hairDesc" : "",
		"ethnicity" : "", 
		"eyeDesc" : "",
		"minHeight" : "",
		"maxHeight" : "",
		"showLifestyle" : "false",
		"relationshipStatus" : "",
		"religious" : "",
		"children" : "",
		"income" : "",
		"smokerPref" : "",
		"drinkingPref" : "",
		"zodiacPref" : "",
		"adultSearch" : ""
	}

	return template;

}
