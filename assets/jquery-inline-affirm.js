/*

useage:

html : create a div element with the class "affirm" and style with "light", "dark" or "button" classes

example:
<div class="affirm button" data-id="1111" data-title="Save" data-question="Save this document?"></div>

data-id : a unique id for the created element
data-title : the title of the affirm link
data-question : the question to affirm
data-result : optional : text or html to display once affirmed

jQuery :

$(".affirm").inlineAffirm({
	callback : function(ele){
		...
		console.log( $(ele).attr("data-id" ));
	}
});

*/

;(function($) {
  (function(inlineAffirm) {
    var defaults = {
      baseClass : "inlineAffirm",
      callback : function(){},
      testFor: function(div) {
        return true;
      },
      init: function(id, baseClass, callback){

      	var element_id = baseClass + "_main_" + id;
		var element_class = baseClass + "_main_";
		var span_id = baseClass + "_span_" + id;
		var span_class = baseClass + "_span";
		var inlineAffirmYes = baseClass + "_yes_" + id;
		var inlineAffirmNo = baseClass + "_no_" + id;

		var affirm = '';
			affirm = affirm + '<a id="' + element_id + '" class="' + element_class;
			affirm = affirm + '" data-id="' + id + '" href="#">[TITLE]</a> <span id="';
			affirm = affirm + span_id + '" class="' + span_class +'">[QUESTION] <a id="'
			affirm = affirm + inlineAffirmYes + '" class="affirm_yes" data-id="' + id + '" href="#">Yes</a> [DIVIDER] <a id="'
			affirm = affirm + inlineAffirmNo + '" class="affirm_no" data-id="' + id + '" href="#">No</a>';

		callback(affirm);
      },
      inlineAffirmBind: function (id, result, baseClass, ele, callback){

      	var element_id = baseClass + "_main_" + id;
		var element_class = baseClass + "_main_";
		var span_id = baseClass + "_span_" + id;
		var span_class = baseClass + "_span";
		var inlineAffirmYes = baseClass + "_yes_" + id;
		var inlineAffirmNo = baseClass + "_no_" + id;
		var DEF_CLICK = "click";

		$("." + span_class).hide();

		//affirm 
		$("#" + element_id).off(DEF_CLICK).on(DEF_CLICK, function(e){
			e.preventDefault();

			var id = $(this).attr("data-id");
			
			$("#" + element_id).hide();
			$("#" + span_id).show();
		});

		//no 
		$("#" + inlineAffirmNo).off(DEF_CLICK).on(DEF_CLICK, function(e){
			e.preventDefault();

			var id = $(this).attr("data-id");
			
			$("#" + span_id).hide();
			$("#" + element_id).show();
		});

		//yes 
		$("#" + inlineAffirmYes).off(DEF_CLICK).on(DEF_CLICK, function(e){
			e.preventDefault();

			$("#" + span_id).hide();
			$("#" + element_id).show();

			//add the html to the element after the click
			if ( $.trim(result)!= "" ) {
				ele.html(result);
			};

			callback(ele);
		});
      }
    };
    $.fn[inlineAffirm] = function(options) {

    	options = $.extend(true, {}, defaults, options);
            
     	return this.each(function() {
      		
      		var divider = "";
    		var elem = this,
     			$elem = $(elem);

      		var id = $elem.attr("data-id"),
      			title = $elem.attr("data-title"),
      			result = $elem.attr("data-result"),
      			question = $elem.attr("data-question");

      		options.init(id, options.baseClass, function(affirm){

      		if (!$elem.hasClass("button")) {
      			divider = '|';
      		}

      		if (title == undefined || title == "") {
      			title = "The Title";
      		}

      		if (question == undefined || question == "") {
      			question = "Do you really want to do this?";
      		}

      		//create the divider style
      		affirm = affirm.split("[DIVIDER]").join(divider);
      		affirm = affirm.split("[TITLE]").join(title);
      		affirm = affirm.split("[QUESTION]").join(question);

      		$elem.html(affirm);

      		options.inlineAffirmBind(id, result, options.baseClass, $elem, options.callback);
      	});
      });
    };
    $.fn[inlineAffirm].defaults = defaults;
  })('inlineAffirm');
})(jQuery);