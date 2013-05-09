/*
* jQuery UI Ghost Text!
*
* @version v1.0 (11/2011)
*
* Copyright 2011, LGPL License
*
* Homepage:
*   http://openlogiks.org/
*
* Authors:
*   Bismay Kumar Mohapatra
*
* Maintainer:
*   Bismay Kumar Mohapatra
*
* Dependencies:
*   jQuery v1.4+
*   jQuery UI v1.8+
*/
if(jQuery) (function($){
	$.extend($.fn, {
		ghosttext : function(shadowColor) {
			var txt=$(this).attr("title");
			var clr=$(this).css("color");
			if(shadowColor==null) shadowColor="#ccc";
			
			if(txt==null) return;
			
			$(this).val(txt);
			$(this).css("color",shadowColor);
			
			$(this).focus(function() {
				if($(this).val()==txt) {
					$(this).val("");
				}
				$(this).css("color",clr);
			});
			$(this).blur(function() {
				if($(this).val().length<=0) {
					$(this).val(txt);
					$(this).css("color",shadowColor);
				} else {
					$(this).css("color",clr);
				}
			});
		}
	});
})(jQuery);
