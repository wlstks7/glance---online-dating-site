var json_url;
var postdata;
var _baseline = "quiz";
var http_base;
var cart_uniq_id = 1;
var cart = [];

$(function(){

	init();

	http_base = $("#http_base").attr("data-id");

	/*

	TAP EVENTS

	*/

	setInterval(function(){

	    //check for a valid session every 2 minutes
	    $.ajax({
	        type: "POST",
	        url: "func/tap.php",
	        timeout: function(){
	                  
	          },
	        success: function(msg){

	          if ( msg != "ok" ) {
	            
	            var ts = $("#this_site").attr("data-id");
	          	window.location = ts;
	            return false;
	          }
	        }
	    });

	},120000);  //300000 

	$("#btnBack_pageItemList").on(click, function(){

		$("#pageItemList").show();
		$("#pageProductItems").hide();
	});


	var handler = StripeCheckout.configure({
	    key: 'pk_test_6pRNASCoBOKtIshFeQd4XMUh',
	    image: '/square-image.png',
	    token: function(token) {
	      // Use the token to create the charge with a server-side script.
	      // You can access the token ID with `token.id`

	      console.log("msg " + token.id);
	    }
	  });

	  $('#btn_placeOrder').on('click', function(e) {
	    // Open Checkout with further options

	    //get email
	    var email = $("#customer_info").attr("data-email");
	    var total = $("#checkout_total").attr("data-totalcents")
	    	//total = total.split(".");

	    handler.open({
	      /*name: 'Demo Site',
	      description: '2 widgets ($20.00)',
	      amount: 2000*/
	      email: email,
	      amount: total

	    });
	    e.preventDefault();
	  });

	  // Close Checkout on page navigation
	  $(window).on('popstate', function() {
	    handler.close();
	  });



	$("#textLink").on(click, function(){

		$(this).select().focus();
	});

	$("#btn_refillItem").on(click, function(){

		refillOrder("#pageProductItems", function(){

			//navigate to the calling page
			$("#btnBack_pageShop").off(click).on(click, function(){

				$("#pageProductItems").show();
				$("#pageShop").hide();
			});

			$("#pageLoading").hide();
			$("#shopItems").hide();
			$("#refillItems").show();
			$("#pageShop").show();


		});
	});

	$("#btn_startNewClient").on(click, function(){

		resetForm();
		
		$("#pageProductItems").hide();
		$("#pageStaging").show();

	});

	$("#btn_viewHistory").on(click, function(){

		$("#itemsFeed").html("");
		$("#historySearch").val("");
		$("#text_filter").text("Click to search items");
		$("#pageProductItems").hide();
		$("#pageHistory").show();

	});

	$("#btnBack_pageStaging").on(click, function(){

		$("#pageProductItems").show();
		$("#pageStaging").hide();
	});

	$("#btnBack_pageHistory").on(click, function(){

		$("#pageProductItems").show();
		$("#pageHistory").hide();
	});

	$("#btn_checkout").on(click, function(){

		$("#pageCheckOut").show();
		$("#pageShop").hide();
	});

	$("#btnBack_pageCheckout").on(click, function(){

		$("#pageCheckOut").hide();
		$("#pageShop").show();
	});

	$("#btn_buy").on(click, function(){

		getShopItems("#pageItemList",function(){});
	});

	/*$("#btn_placeOrder").on(click, function(){

		prepareOrder();
	});*/

	$("#btn_startBaseline").on(click, function(){

		var firstName =	$.trim($("#first-name").val());
		var lastName = $.trim($("#last-name").val());
		var company = $.trim($("#company").val());
		var gender = $("#gender").val();
		var birthdate_month = $("#bdate_month").val();
		var birthdate_day = $("#bdate_day").val();
		var birthdate_year = $("#bdate_year").val();
		//var client_notes = $("#client_notes").val();
		var pack_id = $("#staging_id").attr("data-id");
		
		if ( firstName == "" ) {

			_alert("This is important", "Please enter the client's first name.");
			$("#first-name").focus();
			return false;
		};

		if ( lastName == "" ) {

			_alert("This is important", "Please enter the client's last name.");
			$("#last-name").focus();
			return false;
		};

		if ( gender == "" ) {

			_alert("This is important", "Please select the client's gender.");
			return false;
		};

		if ( birthdate_month == "" ) {

			_alert("This is important", "Please select the client's birth month.");
			return false;
		};

		if ( birthdate_day == "" ) {

			_alert("This is important", "Please select the client's birth day.");
			return false;
		};

		if ( birthdate_year == "" ) {

			_alert("This is important", "Please select the client's birth year.");
			return false;
		};
		
		postdata = {
			"pack_id" : pack_id,
			"firstName" : firstName,
			"lastName" : lastName,
			"company" : company,
			"gender" : gender,
			"birthdate_month" : birthdate_month,
			"birthdate_day" : birthdate_day,
			"birthdate_year" : birthdate_year
		}

		//show the loading page
		$("#pageLoading").show();

		//hide this page
		$("#pageStaging").hide();

		json_url = "func/stage_new_client.php";

		_ajax(postdata, json_url, function(json){

			if (json.status == "success") {

				$("#textLink").val(json.msg);
				$("#pageLoading").hide();
				$("#pageLinkShare").show();

			} else {

				$("#pageLoading").hide();
				$("#pageStaging").show();
				_alert("Important Message", json.msg);
				return false;
			};
		});
	});

	$("#btnBack_linkShareToTop").on(click, function(){

		$("#pageItemList").show();
		$("#pageLinkShare").hide();
	});

	/*

	END TAP EVENTS

	*/

	$("#btn_historySearch").on(click, function(){

		var pack_id = $("#staging_id").attr("data-id");		
		var that = this;
		var q = $.trim($("#historySearch").val());

		if ( $.trim(q) == "" ) {
			//console.log("msg");
			return false;
		}

		$("#btn_filterHistory").trigger("click");
		$("#text_filter").text("Search: " + q);
		$("#historySearch").val("");

		postdata = {
			"pack_id" : pack_id,
			"q" : q
		}

		json_url = "func/clientHistory.php";

		$("#pageLoading").show();
		$("#pageHistory").hide();

		_ajax(postdata, json_url, function(json){

			if (json.status == "success") {

				var id;
				var activated_date;
				var test_status;
				var client_fullName;
				var msg;
				var html;
				var btn;
				var items_completed;
				var items_total;

				$("#itemsFeed").html("");

				$.each(json.results, function(k,v){

					test_status = v.test_status;

					client_fullName = v.client_firstname + " " + v.client_lastname;

					if (test_status == "1") {

						msg = client_fullName + ' has not started yet. Activated on ' + v.activated_date;
						
						var url = http_base + v.url_id;

						btn = '<div class="ui right labeled left small input" style="width:100%;"><input class="share_url" value="' + url + '" data-content="Ready to copy" placeholder="Share this" type="text"> <div class="ui tag label"> Share URL </div> </div>';
						
						btn = btn + '<p></p><div data-id="' + v.id + '" class="ui button mini ion_btn_delete_test"><i class="icon remove"></i>Delete</div>';
					
						items_completed = "0";
						items_total = "0";

					} else if (test_status == "2") {

						msg = client_fullName + ' is working on completing the ' + _baseline;

						var url = http_base + v.url_id;

						btn = '<div class="ui right labeled left small input" style="width:100%;"><input value="' + url + '" class="share_url" data-content="Ready to copy" placeholder="Share this" type="text"> <div class="ui tag label"> Share URL </div> </div>';
						
						btn = btn + '<p></p><div data-id="' + v.id + '" class="ui button mini ion_btn_delete_test"><i class="icon remove"></i>Delete</div>';
						
						items_completed = v.items_completed;
						items_total = v.items_total;

					} else if (test_status == "3") {

						msg = client_fullName + ' has completed the ' + _baseline + '. We are preparing the report.';
						btn = '<span class="ui label">Preparing Report</span>';

						items_completed = "50";
						items_total = "50";

					} else if (test_status == "4") {

						msg = client_fullName + ' has completed the ' + _baseline + '. The report is ready.';
						btn = '<div data-id="' + v.id + '" class="ui button mini"><i class="icon search"></i>View Report</div>';
						
						items_completed = "50";
						items_total = "50";
					}

					html = '<div id="ele_item_' + v.id + '" class="event"> <div class="label"> <div class="ui indicating progress historyProgress" data-value="' + items_completed + '" data-total="' + items_total + '"> <div class="bar"> <div class="progress"></div> </div> <div class="label"></div> </div> </div> <div class="content"> <div class="summary"> <p>' + msg + '</p> <p> ' + btn + ' </p> </div> </div> </div> <div id="div_' + v.id + '" class="ui divider"></div>';
					
					$("#itemsFeed").append(html);

				});
				
				$(".share_url").off(click).on(click, function(){

					$(this).select().focus();
				});

				$('.share_url')
					.off('popup')
					.popup({
				    	on: 'focus'
				});


				$('.historyProgress').progress({
				    label: 'ratio',
				    showActivity : 'true',
				    text: {
				      active  : '{percent}%',
				      success : 'Ready',
				      ratio   : ''
				    }
				});

				$(".bar").each(function(k,v){

					var that = this;
					var d = $(that).parent().attr("data-percent");

					$(that).attr("style", "width: " + d + "%");
				});

				$(".ion_btn_delete_test").off(click).on(click, function(){

					var id = $(this).attr("data-id");
					var that = this;

					affirm("Are You Sure?", "Delete this Ion quiz?", function(e){

						if (e == "yes") {

							postdata = {
								"id" : id
							}

							json_url = "func/delete_in_progress_test.php";

							_ajax(postdata, json_url, function(json){

								if (json.status != "success") {
									
									alert(json.msg);

								}else{

									$(that).parent().parent().parent().remove();

									$("#div_" + id).remove();

									updateItemCount();
								}
							});
						};
					});			
				});

				//itemsFeed

				$("#pageLoading").hide();
				$("#pageHistory").show();

			} else {

				$("#pageLoading").hide();

				_alert("Important Message", json.msg);
				return false;
			};
		}); 
	});

	$("._tagItem").on(click, function(){

		var pack_id = $("#staging_id").attr("data-id");		
		var that = this;
		var q = $(that).attr("data-id");

		$("#btn_filterHistory").trigger("click");
		$("#text_filter").text("Search: " + q);
		$("#historySearch").val("");

		postdata = {
			"pack_id" : pack_id,
			"q" : q
		}

		json_url = "func/clientHistory.php";

		$("#pageLoading").show();
		$("#pageHistory").hide();

		_ajax(postdata, json_url, function(json){

			if (json.status == "success") {

				var id;
				var activated_date;
				var test_status;
				var client_fullName;
				var msg;
				var html;
				var btn;
				var items_completed;
				var items_total;

				$("#itemsFeed").html("");

				$.each(json.results, function(k,v){

					test_status = v.test_status;

					client_fullName = v.client_firstname + " " + v.client_lastname;

					if (test_status == "1") {

						msg = client_fullName + ' has not started yet. Activated on ' + v.activated_date;
						
						var url = http_base + v.url_id;
						btn = '<div class="ui right labeled left small input" style="width:100%;"><input value="' + url + '" data-content="Ready to copy" class="share_url" placeholder="Share this" type="text"> <div class="ui tag label"> Share URL </div> </div>';
						btn = btn + '<p></p><div data-id="' + v.id + '" class="ui button mini ion_btn_delete_test"><i class="icon remove"></i>Delete</div>';
					
						items_completed = "0";
						items_total = "0";

					} else if (test_status == "2") {

						msg = client_fullName + ' is working on completing the ' + _baseline;
						
						var url = http_base + v.url_id;
						btn = '<div class="ui right labeled left small input" style="width:100%;"><input value="' + url + '" data-content="Ready to copy" class="share_url" placeholder="Share this" type="text"> <div class="ui tag label"> Share URL </div> </div>';
						btn = btn + '<p></p><div data-id="' + v.id + '" class="ui button mini ion_btn_delete_test"><i class="icon remove"></i>Delete</div>';
					
						items_completed = v.items_completed;
						items_total = v.items_total;

					} else if (test_status == "3") {

						msg = client_fullName + ' has completed the ' + _baseline + '. We are preparing the report.';
						btn = '<span class="ui label">Preparing Report</span>';

						items_completed = "50";
						items_total = "50";

					} else if (test_status == "4") {

						msg = client_fullName + ' has completed the ' + _baseline + '. The report is ready.';
						btn = '<div data-id="' + v.id + '" class="ui button mini"><i class="icon search"></i>View Report</div>';
						
						items_completed = "50";
						items_total = "50";
					}

					//html = '<div class="event"> <div class="label"> <div class="ui indicating progress historyProgress" data-value="' + items_completed + '" data-total="' + items_total + '"> <div class="bar"> <div class="progress"></div> </div> <div class="label"></div> </div> </div> <div class="content"> <div class="summary"> <p>' + msg + '</p> <p> ' + btn + ' </p> </div> </div> </div> <div class="ui divider"></div>'
					html = '<div id="ele_item_' + v.id + '" class="event"> <div class="label"> <div class="ui indicating progress historyProgress" data-value="' + items_completed + '" data-total="' + items_total + '"> <div class="bar"> <div class="progress"></div> </div> <div class="label"></div> </div> </div> <div class="content"> <div class="summary"> <p>' + msg + '</p> <p> ' + btn + ' </p> </div> </div> </div> <div id="div_' + v.id + '" class="ui divider"></div>';
					
					$("#itemsFeed").append(html);

				});
				
				$(".share_url").off(click).on(click, function(){

					$(this).select().focus();
				});

				$('.share_url')
				  .popup({
				    on: 'focus'
				});

				$('.historyProgress').progress({
				    label: 'ratio',
				    showActivity : 'false',
				    text: {
				      active  : '{percent}%',
				      success : 'Ready',
				      ratio   : ''
				    }
				  });

				$(".bar").each(function(k,v){

					var that = this;
					var d = $(that).parent().attr("data-percent");

					$(that).attr("style", "width: " + d + "%");
				});

				$(".ion_btn_delete_test").off(click).on(click, function(){

					var id = $(this).attr("data-id");
					var that = this;
					
					affirm("Are You Sure?", "Delete this Ion quiz?", function(e){

						if (e == "yes") {

							postdata = {
								"id" : id
							}

							json_url = "func/delete_in_progress_test.php";

							_ajax(postdata, json_url, function(json){

								if (json.status != "success") {
									
									alert(json.msg);

								}else{

									$(that).parent().parent().parent().remove();
									$("#div_" + id).remove();

									updateItemCount();
								}
							});
						};
					});			
				});

				//itemsFeed

				$("#pageLoading").hide();
				$("#pageHistory").show();

			} else {

				$("#pageLoading").hide();

				_alert("Important Message", json.msg);
				return false;
			};
		});
	});

	//get the customer items list
	getMyItems(function(){

		$("#pageItemList").show();
		$("#pageLoading").hide();

		$(".view_baseline").off(click).on(click, function(){
			
			$("#pageItemList").hide();
			$("#pageLoading").show();

			//get id
			var that = this;
			var id = $(that).attr("data-id");
			var title = $(that).attr("data-title");

			//remember the id for this pack for later
			$("#staging_id").attr("data-id", id);

			postdata = {
				"id" : id
			}

			json_url = "func/my_purchased_item.php";

			_ajax(postdata, json_url, function(json){

				$.each(json.results, function(k,v){

					if (v.itemsAvailable == 1) {

						$("#baselineCount").text(v.itemsAvailable + ' ' + _baseline + ' item ');

					} else {

						$("#baselineCount").text(v.itemsAvailable + ' ' + _baseline + ' items ');
					}
				});

				$("#titleProductName").text(title);
				
				$("#pageProductItems").show();
				$("#pageLoading").hide();
			});
		});
	});

	//FUNCTIONS

	function init(){

		$('.dropdown').dropdown();
		$('.historyProgress').progress({
		    label: 'ratio',
		    showActivity : 'false',
		    text: {
		      active  : '{percent}%',
		      success : 'Ready',
		      ratio   : ''
		    }
		  });
		$("#cartlist").html("");
		$("#checkoutList").html("");
		$("#_cart_list_empty").show();
		$("#_cart_list_full").hide();
		//var r = '<div class="event"> <div class="label"> <div class="ui indicating progress historyProgress" data-value="20" data-total="50"> <div class="bar"> <div class="progress"></div> </div> <div class="label"></div> </div> </div> <div class="content"> <div class="summary"> <p>Ryan Miller is working on completing this.</p> <p> <div class="ui button mini"><i class="icon remove"></i>Delete</div> </p> </div> </div> </div> <div class="ui divider"></div>';
		
		
	}

	function copyToClipboard(text) {
	  window.prompt("Copy to clipboard: Ctrl+C, Enter", text);
	}

	function _alert(title, description){

		$("#alertTitle").text(title);
		$("#alertDescription").text(description);
		$('#alert').modal('show');

	}

	function resetForm(){

		$("#first-name").val("");
		$("#last-name").val("");
		$("#company").val("");
		$("#gender").val("");
		$("#bdate_month").val("");
		$("#bdate_day").val("");
		$("#bdate_year").val("");
		//$("#client_notes").val("");
		$("#company").val("");
		
		$("#bdate_month_default")
			.text("Month")
			.addClass("default");
		
		$("#bdate_day_default")
			.text("Day")
			.addClass("default");

		$("#bdate_year_default")
			.text("Year")
			.addClass("default");
		
		$("#gender_default")
			.text("Gender")
			.addClass("default");
	}

	function getMyItems(callback){

		$("#pageItemList").hide();
		$("#pageLoading").show();

		$("#myProducts").html("");

		var item;

		json_url = "func/my_purchased_items.php";

		_ajax("", json_url, function(json){

			$.each(json.results, function(k,v){

				var title = v.product_title;
				var desc = v.product_description;

				title = title.split('"').join("'");
				desc = desc.split('"').join("'");

				item = '<div class="card"><div class="content"><div class="header">' + title + '</div><div class="description">' + desc + '</div></div><div data-id="' + v.id + '" data-title="' + title + '" class="ui bottom attached button view_baseline"><i class="search icon"></i> View Your Items</div> </div>';

				$("#myProducts").append(item);

			});

			callback();
		});
	}

	function refillOrder(pageOut, callback){

		//not sure if this is needed
		clearCart();

		$(pageOut).hide();
		$("#pageLoading").show();

		$("#refillItemList").html("");
		$("#refillItemName").html($("#titleProductName").text());

		var item;
		var id;
		var price;
		var increments;
		var bundleName;

		postdata = {
			"id" : $("#staging_id").attr("data-id")
		}

		json_url = "func/refill_item.php";

		_ajax(postdata, json_url, function(json){

			$.each(json.results, function(k,v){

				id = v.id;
				price = v.price;
				increments = v.increments;
				bundleName = $("#refillItemName").text();

				item = '<div class="item"> <div class="right floated compact ui button refill_menu_item" data-name="' + bundleName + '" data-id="' + id + '" data-increments="' + increments + '" data-price="' + price + '"><i class="icon shop"></i>Add</div> <div class="content"> <div class="header header-list">' + increments + ' for $' + price + '</div> </div> </div>';

				$("#refillItemList").append(item);

			});

			item = '<div style="height:30px;display:block;"></div><div id="btn_toCheckout" class="ui button positive"><i class="icon check circle"></i>Check Out</div>';

			$("#refillItemList").append(item);

			$("#btn_toCheckout").off(click).on(click, function(){
			
				$("#btn_checkout").trigger("click");
			});
			
			//add to cart listener
			$(".refill_menu_item").off(click).on(click, function(){

				cart_uniq_id = cart_uniq_id + 1;

				var id = $(this).attr("data-id");
				var increments = $(this).attr("data-increments");
				var price = $(this).attr("data-price");
				var productName = $(this).attr("data-name");
				
				var item = '<div id="cart_listItem_' + cart_uniq_id + '" class="cartItem" data-id="' + id + '" data-increments="' + increments + '" data-price="' + price + '"> <div class="cart_item_header header">' + productName + '</div> <span class="cart_listItem">' + increments + ' for $' + price + ' - <a id="cart_listItemRemove_' + cart_uniq_id + '" data-id="' + cart_uniq_id + '" class="cart_listItemRemove" href="#">remove</a></span> </div>'; 

				var checkoutItem = '<div id="checkout_listItem_' + cart_uniq_id + '" class="item checkoutItem"> <i data-cartid="' + cart_uniq_id + '" data-id="' + id + '" data-price="' + price + '" class="checkout_listItemRemove removable remove circle icon"></i> <div class="content"> <div class="header">' + productName + '</div> ' + increments + ' for $' + price + '</div> </div>';

				//add to check out list
				$("#checkoutList").append(checkoutItem);

				//add to cart
				$("#cartlist").append(item);

				$("#btn_cart")
					.removeClass("blue")
					.addClass("pink")
					.addClass("loading");

				setTimeout(function(){

					$("#btn_cart")
						.addClass("blue")
						.removeClass("pink")
						.removeClass("loading");
				},500);

				$(".cartItem").off(click).on(click, function(e){

					e.preventDefault();

					setTimeout(function(){

					$(".cartItem")	
							.removeClass("active")
							.removeClass("selected");
						
					},500);
				});

				$(".cartItem").off(click).on(click, function(e){

					e.preventDefault();

					setTimeout(function(){

					$(".cartItem")	
							.removeClass("active")
							.removeClass("selected");
						
					},500);
				});

				updateCartCount();

				setTimeout(function(){

					$(".shop_menu_item")	
						.removeClass("active")
						.removeClass("selected");
					
				},500);

				$(".cart_listItemRemove").off(click).on(click, function(e){

					e.preventDefault();
					
					setTimeout(function(){

					$(".cartItem")	
							.removeClass("active")
							.removeClass("selected");
						
					},500);

					//get cart id
					id = $(this).attr("data-id");
					
					//remove from cart
					$("#cart_listItem_" + id).remove();

					//remove from check out
					$("#checkout_listItem_" + id).remove();

					updateCartCount();
				});

				$(".checkout_listItemRemove").off(click).on(click, function(){

					//console.log("msg");

					//get cart id
					id = $(this).attr("data-cartid");
					
					//remove from cart
					$("#cart_listItem_" + id).remove();

					//remove from check out
					$("#checkout_listItem_" + id).remove();

					updateCartCount();
				});
			});

			callback();
		});
	}

	function getShopItems(pageOut, callback){

		//navigate from the calling page
		$(pageOut).hide();
		$("#pageLoading").show();

		$("#refillItems").hide();
		$("#shopItems").show();

		var item;

		json_url = "func/showShop.php";

		_ajax("", json_url, function(json){

			if (json.status != "success") {

				_alert("Important", json.msg);
				return false;
			}

			$("#shopItems").html("");

			var bundle_id = "";
			var item_termination = "</div></div>";
			var first_flag = "1";
			var menu_item;
			var menu_items;
			var html = "";

			$.each(json.results, function(k,v){

				if (v.id == bundle_id) {

					//no change
					menu_item = '<div class="divider"></div><div data-id="' + v.bundleitem_id + '" data-increments="' + v.increments + '" data-price="' + v.price + '" data-name= "' + v.bundle_name + '" class="item shop_menu_item"> ' + v.increments + ' for $' + v.price + ' </div>';

					menu_items = menu_items + menu_item;

				} else {

					//changed... 	
					if (first_flag == "1") {

						//console.log("first run");

						first_flag = "0";
						bundle_id = v.id;

 						//create new item
						item = '<div class="card"><div class="content"><div class="header">' + v.bundle_name + '</div><div class="description">' + v.bundle_description + '</div></div>[[MENU]]</div>';

						//create first menu item
						menu_first_item = '<div data-id="' + v.id + '" data-title="' + v.bundle_name + '" class="ui attached floating pointing dropdown button view_baseline_item"><i class="paw icon"></i> I want it! <div class="menu"> <div class="header"> <i class="cart icon"></i> Get It! </div> <div data-id="' + v.bundleitem_id + '" data-increments="' + v.increments + '" data-price="' + v.price + '" data-name= "' + v.bundle_name + '" class="item shop_menu_item"> ' + v.increments + ' for $' + v.price + ' </div>';
						
						//add the first item to the items stack
						menu_items = menu_first_item;

					} else {

						//reset first time run flag
						first_flag = "1";
						
						//terminate old item
						menu_items = menu_items + item_termination;

						item = item.split("[[MENU]]").join(menu_items);

						item = item + item_termination;

						//console.log("pre render:::: " + html);
						html = html + item;
					}
				}
			});
			
			//terminate old item
			menu_items = menu_items + item_termination;

			item = item.split("[[MENU]]").join(menu_items);

			item = item + item_termination;

			//console.log("pre render:::: " + html);

			html = html + item;

			html = html + item_termination;

			$("#shopItems").html(html);
			$("#pageLoading").hide();

			$('.dropdown').dropdown();

			//show the shop
			$("#pageShop").show();

			//navigate to the calling page
			$("#btnBack_pageShop").off(click).on(click, function(){

				$(pageOut).show();
				$("#pageShop").hide();
			});

			//add to cart listener
			$(".shop_menu_item").off(click).on(click, function(){

				cart_uniq_id = cart_uniq_id + 1;

				var id = $(this).attr("data-id");
				var increments = $(this).attr("data-increments");
				var price = $(this).attr("data-price");
				var productName = $(this).attr("data-name");
				
				var item = '<div id="cart_listItem_' + cart_uniq_id + '" class="cartItem" data-id="' + id + '" data-increments="' + increments + '" data-price="' + price + '"> <div class="cart_item_header header">' + productName + '</div> <span class="cart_listItem">' + increments + ' for $' + price + ' - <a id="cart_listItemRemove_' + cart_uniq_id + '" data-id="' + cart_uniq_id + '" class="cart_listItemRemove" href="#">remove</a></span> </div>'; 

				var checkoutItem = '<div id="checkout_listItem_' + cart_uniq_id + '" class="item checkoutItem"> <i data-cartid="' + cart_uniq_id + '" data-id="' + id + '" data-price="' + price + '" class="checkout_listItemRemove removable remove circle icon"></i> <div class="content"> <div class="header">' + productName + '</div> ' + increments + ' for $' + price + '</div> </div>';

				//add to check out list
				$("#checkoutList").append(checkoutItem);

				//add to cart
				$("#cartlist").append(item);

				$("#btn_cart")
					.removeClass("blue")
					.addClass("pink")
					.addClass("loading");

				setTimeout(function(){

					$("#btn_cart")
						.addClass("blue")
						.removeClass("pink")
						.removeClass("loading");
				},500);

				$(".cartItem").off(click).on(click, function(e){

					e.preventDefault();

					setTimeout(function(){

					$(".cartItem")	
							.removeClass("active")
							.removeClass("selected");
						
					},500);
				});

				$(".cartItem").off(click).on(click, function(e){

					e.preventDefault();

					setTimeout(function(){

					$(".cartItem")	
							.removeClass("active")
							.removeClass("selected");
						
					},500);
				});

				updateCartCount();

				setTimeout(function(){

					$(".shop_menu_item")	
						.removeClass("active")
						.removeClass("selected");
					
				},500);

				$(".cart_listItemRemove").off(click).on(click, function(e){

					e.preventDefault();
					
					setTimeout(function(){

					$(".cartItem")	
							.removeClass("active")
							.removeClass("selected");
						
					},500);

					//get cart id
					id = $(this).attr("data-id");
					
					//remove from cart
					$("#cart_listItem_" + id).remove();

					//remove from check out
					$("#checkout_listItem_" + id).remove();

					updateCartCount();
				});

				$(".checkout_listItemRemove").off(click).on(click, function(){

					//console.log("msg");

					//get cart id
					id = $(this).attr("data-cartid");
					
					//remove from cart
					$("#cart_listItem_" + id).remove();

					//remove from check out
					$("#checkout_listItem_" + id).remove();

					updateCartCount();
				});
			});

			callback();
		});
	}

	function prepareOrder(){

		var that;
		var item;
		var items = [];

		$("#btn_placeOrder").hide();

		var total = $("#checkout_total").attr("data-total");

		if ($(".checkout_listItemRemove").length == 0) {

			_alert("Empty Cart", "You have an empty cart.")
			$("#btn_placeOrder").show();
			return false;
		};

		$(".checkout_listItemRemove").each(function(){

			that = this;

			item = {
				"id" : $(that).attr('data-id'),
				"price" : $(that).attr('data-price')	
			}

			items.push(item);
		});

		//show the loading page
		$("#pageLoading").show();

		//hide this page
		$("#pageCheckOut").hide();

		postdata = {
			"items" : items,
			"total" : total
		}

		json_url = "func/shopPrepareOrder.php";

		_ajax(postdata, json_url, function(json){

			$("#btn_placeOrder").show();

			if (json.status == "success") {

				console.log(json.msg);

				//empy the cart and check out 
				//update item totals
				$("#cartlist").html("");
				$("#checkoutList").html("");
				$("#_cart_list_empty").show();
				$("#_cart_list_full").hide();

				updateCartCount();
				
				//get my items
				getMyItems(function(){

					$("#pageItemList").show();
					$("#pageLoading").hide();

					$(".view_baseline").off(click).on(click, function(){
						
						$("#pageItemList").hide();
						$("#pageLoading").show();

						//get id
						var that = this;
						var id = $(that).attr("data-id");
						var title = $(that).attr("data-title");

						//remember the id for this pack for later
						$("#staging_id").attr("data-id", id);

						postdata = {
							"id" : id
						}

						json_url = "func/my_purchased_item.php";

						_ajax(postdata, json_url, function(json){

							$.each(json.results, function(k,v){

								if (v.itemsAvailable == 1) {

									$("#baselineCount").text(v.itemsAvailable + ' ' + _baseline + ' item ');

								} else {

									$("#baselineCount").text(v.itemsAvailable + ' ' + _baseline + ' items ');
								}
							});

							$("#titleProductName").text(title);
							
							$("#pageProductItems").show();
							$("#pageLoading").hide();
						});
					});
				});

				//show msg with completed statement
				var msg = '<div class="ui pink message">Purchase Completed!</div>';
				$("#_msg_frontPage").html(msg);

				setTimeout(function(){
					$("#_msg_frontPage").html("");
				},3000);

			} else {

				//show the loading page
				$("#pageLoading").hide();

				//hide this page
				$("#pageCheckOut").show();

				_alert("Important", json.msg);
			}
		});
	}

	function clearCart(){

		//empy the cart and check out 
		$("#cartlist").html("");
		$("#checkoutList").html("");
		$("#_cart_list_empty").show();
		$("#_cart_list_full").hide();

		//update item totals
		updateCartCount();
	}

	function updateCartCount(){

		var price,
			total;

		//count items
		var item_count = $(".cartItem").length;

		if (item_count == 0) {
			
			$("#_cart_list_empty").show();
			$("#_cart_list_full").hide();

		} else {

			$("#_cart_list_empty").hide();
			$("#_cart_list_full").show();
		}
		
		$("#cartItems").text(item_count);

		total = 0;

		//calculate pricing
		$(".cartItem").each(function(){

			price = $(this).attr("data-price");

			total = total + parseFloat(price);
		});

		var totalcents = total * 100;

		$("#btn_buy").attr("data-amount", "3000");

		$("#checkout_total")
			.attr("data-total", total)
			.attr("data-totalcents", totalcents)
			.text(total);
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
		    },
		    onApprove : function() {
		      callback("yes");
		      //$("#app_page").show();
		    }
		  })
		  .modal('show');
	}

	function updateItemCount(){
		
		var id = $("#staging_id").attr("data-id");

		postdata = {
			"id" : id
		}

		json_url = "func/my_purchased_item.php";

		_ajax(postdata, json_url, function(json){

			$.each(json.results, function(k,v){

				if (v.itemsAvailable == 1) {

					$("#baselineCount").text(v.itemsAvailable + ' ' + _baseline + ' item ');

				} else {

					$("#baselineCount").text(v.itemsAvailable + ' ' + _baseline + ' items ');
				}
			});
		});
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