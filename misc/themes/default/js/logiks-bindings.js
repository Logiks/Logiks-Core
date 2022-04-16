/*
* jQuery Based Element Event Binding System. 
*
* @version v1.0 (04/2022)
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

const logiksBindings={
	init:function() {
		$("body").delegate(".actionCmd[data-cmd],.actionCMD[data-cmd],.actionIcon[data-cmd]", "click", function(e) {
	        e.preventDefault();

	        var cmd = $(this).data("cmd");

	        if (window[cmd] != null && typeof window[cmd] == "function") {
	            window[cmd](this);
	        } else {
	            console.warn("Command Not Found", cmd);
	        }
	    });

	    $("body").delegate(".onChange[data-target]", "change", function(e) {
	    	var target = $(this).data("target");
	    	$(target, "body").html($(this).val());
	    });

	    $("body").delegate(".popupLink[href]", "click", function(e) {
	        var href = $(this).attr("href");
	        var title = $(this).text();
	        if(title==null) title = $(this).attr("title");
	        if(title==null) title = "Viewer";

	        if (href != null && href.length > 1 && href.substr(0, 1) == "#") {
	            lgksOverlayURL(href, title, a=> {});
	        }
	    });

	    $("body").delegate(".frameLink[href]", "click", function(e) {
	        var href = $(this).attr("href");
	        var title = $(this).text();
	        if(title==null) title = $(this).attr("title");
	        if(title==null) title = "Viewer";

	        if (href != null && href.length > 1 && href.substr(0, 1) == "#") {
	            lgksOverlayFrame(href, title);
	        }
	    });

	    $("body").delegate(".windowLink[href]", "click", function(e) {
	        var href = $(this).attr("href");
	        if (href != null && href.length > 1 && href.substr(0, 1) == "#") {
	            window.open(href);
	        }
	    });

	    $("body").delegate(".goBackLink", "click", function(e) {
	        window.history.back();
	    });
	}
}
//logiksBindings.init()
