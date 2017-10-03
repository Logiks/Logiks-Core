//Abstract Functionality
function lgksOverlay(msg, title, func) {
}
function lgksOverlayURL(url, title, func, w, h) {
}
function lgksOverlayDiv(divID, func, w, h) {
}
function lgksOverlayFrame(url, title, func, w, h) {
}
function lgksPrompt(msg, title, fields, func) {
}
function lgksConfirm(msg, title, okFunc, cancelFunc) {
}
function lgksAlert(msg, title) {
}
function lgksPopup(divID,buttons, paramConfigs,type,title) {
}


function openInNewWindow(mypage, myname) {
	win = window.open(mypage, myname);
}
function openInNewPopupWindow(mypage, myname, w, h, scroll,resize,menubar,status,titlebar,toolbar) {
	if(myname==null) myname="Message";
	if(w==null) w=800;
	if(h==null) h=500;
	if(scroll==null) scroll="yes";
	if(resize==null) resize="yes";

	if(menubar==null) menubar="no";
	if(status==null) status="no";
	if(titlebar==null) titlebar="no";
	if(toolbar==null) toolbar="no";

	var winl = (screen.width - w) / 2;
	var wint = (screen.height - h) / 2;
	winprops = 'location=no,directories=no,copyhistory=no,height='+h+',width='+w+',top='+wint+',left='+winl;
	winprops+= ',scrollbars='+scroll+',resizable='+resize+',menubar='+menubar+',status='+status+',titlebar='+titlebar+',toolbar='+toolbar;
	return window.open(mypage, myname, winprops);
}

function lgksToast(msg,opts) {
	var defOpts = {
            displayTime: 2000,
            bodyclass: "",
            inTime: 300,
            outTime: 200,
            effects: true,
            inEffect:"fade",
            outEffect:"fade",
            maxWidth: 500,
            position: "top-right",
        };
    opts = $.extend(defOpts, opts);
	opts.position=opts.position.toLowerCase().split("-");
	var y,x;
	switch (opts.position[0]) {
        case "top":
            y = 32;
            break;
        case "bottom":
            y = 1.0325;
            break;
        default:
            y = 2;
    }
    switch (opts.position[1]) {
        case "left":
            x = 72;
            break;
        case "right":
            x = 72;
            break;
        default:
            x = 2;
    }
    $("body .lgksToast.toast").detach();
	toast = $("<div class='toast lgksToast "+opts.bodyclass+"'>" + msg + "</div>");
    $("body").append(toast);
    var l = window.innerHeight;
    var j = window.innerWidth;
    toast.css({
            "max-width": opts.maxWidth + "px",
            top: ((l - toast.outerHeight()) / y) + $(window).scrollTop() + "px",
			position:"absolute",
			padding:"10px",
			"z-index":99999999,
			display:"none",
        });
    switch (opts.position[1]) {
		case "left":
			toast.css({
				left: ((j - toast.outerWidth()) / x) + $(window).scrollLeft() + "px",
			});
			break;
		case "right":
			toast.css({
				right: ((j - toast.outerWidth()) / x) + $(window).scrollLeft() + "px",
			});
			break;
		default:
			toast.css({
				right: ((j - toast.outerWidth()) / x) + $(window).scrollLeft() + "px",
			});
	}
    if(opts.bodyclass=="" || opts.bodyclass==null) {
		toast.css({
            color:"#ffffff",
			"background-color":"rgba(0,0,0, 0.7)",
			"border-radius":"4px",
			"-moz-border-radius":"4px",
			"-webkit-border-radius":"4px",
			"border":"2px solid #CCCCCC"
        });
	}
	if(opts.effects===true) {
		toast.show(opts.inEffect,opts.inTime).delay(opts.displayTime).hide(opts.outEffect,opts.outTime, function() {
					//toast.remove();
				});
	} else {
		toast.show(opts.inTime).delay(opts.displayTime).hide(opts.outTime, function() {
					//toast.remove();
				});
	}
}
