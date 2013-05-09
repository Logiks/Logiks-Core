/*
* jQuery UI Rate-it!
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
* 
* @Paramas
* 
* o : Parameter Json
* h : callBack Function
* 
*/
if(jQuery) (function($){
	$.extend($.fn, {
		rateit : function(o) {
			h=null;
			//Defaults FallBacks
			if( !o ) var o = {};
			if( o.count == undefined ) o.count = '5';
			if( o.value == undefined ) o.value = '0';
			if( o.iconOn == undefined || o.iconOn.length<=0) o.iconOn = 'ui-icon ui-icon-star';
			if( o.iconOff == undefined || o.iconOff.length<=0) o.iconOff = 'ui-icon ui-icon-star';
			if( o.iconClose == undefined || o.iconClose.length<=0) o.iconClose = 'ui-icon ui-icon-circlesmall-close';
			if( o.noIconClose == undefined || o.noIconClose.length<=0) o.noIconClose = false;
			if( o.readOnly == undefined || o.readOnly.length<=0) o.readOnly = false;
			
			if( o.callBack != undefined && o.callBack.length>0) h=o.callBack;
						
			updateRate($(this),o.value,o.count);
						
			function updateRate(obj, val, cnt) {
				obj.html("");
				val=parseInt(val);
				el="";
				if(o.noIconClose) {
					if(val>0) el+="<div rate='0' class='"+o.iconClose+" norateicon' style='float:left;cursor:pointer;opacity:0.4;'></div>";
					if(val<=0) el+="<div rate='0' class='"+o.iconClose+" norateicon' style='float:left;cursor:pointer;opacity:1;'></div>";
				}
				for(var i=0;i<val;i++) {
					el+="<div rate='"+(i+1)+"' class='"+o.iconOn+" rateicon' style='float:left;cursor:pointer;' rated=true></div>";
				}
				for(var i=val;i<cnt;i++) {
					if(o.iconOff.length<=0)
						el+="<div rate='"+(i+1)+"' class='"+o.iconOn+" rateicon' style='float:left;cursor:pointer;opacity:0.4;' rated=false></div>";
					else if(o.iconOn==o.iconOff)
						el+="<div rate='"+(i+1)+"' class='"+o.iconOff+" rateicon' style='float:left;cursor:pointer;opacity:0.4;' rated=false></div>";
					else
						el+="<div rate='"+(i+1)+"' class='"+o.iconOff+" rateicon' style='float:left;cursor:pointer;' rated=false></div>";
				}
				obj.append(el);
				obj.find(".rateicon").each(function() {
						if(!o.readOnly) {
							$(this).click(function() {
									updateRate(obj, $(this).attr("rate"),cnt);
									if(h!=null) 
										h(obj,$(this).attr("rate"),cnt);
								});
						}
					});
				obj.find(".norateicon").each(function() {
						if(!o.readOnly) {
							$(this).click(function() {
									updateRate(obj, 0,cnt);
									if(h!=null)
										h(obj,0,cnt);
								});
						}
					});
			}
		},
		getRate: function() {
			if(this.find("div[rated]").length<=0) {
				console.log("RateIt Plugin Not Initialized Properly.");
			}
			return this.find("div[rated=true]").length;
		}
	});
})(jQuery);
