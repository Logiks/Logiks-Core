var resizeTimer;
$(function() {
	$(window).bind('resize', function() {
		if (resizeTimer) {
			clearTimeout(resizeTimer);
		}		
		resizeTimer=setTimeout(resizeLayoutUI, 100);
	});
	resizeTimer=setTimeout(resizeLayoutUI, 100);
	loadBrowserCompatiblity();
});
function resizeLayoutUI() {
	w=$(window).width();
	h=$(window).height();
	
	var h1=h;
	var w1=w;
	var insetContent=0;
	var insetPreFooter=0;
	if($("#header").length>0) {
		h1=h-$("#header").height();
		insetContent=$("#header").height();
	}
	if($("#sidebar").length>0) {
		w1=w-$("#sidebar").width();
		$("#sidebar").css("height",h1+"px");
		$("#sidebar-inner").css("height",(h1-3)+"px");
	}
	if($("#banner").length>0) {
		h1=h1-$("#banner").height();
		insetContent=insetContent+$("#banner").height();
		$("#banner").css("width",(w1-0)+"px");
	}
	if($("#footer").length>0) {
		h1=h1-$("#footer").height();
		insetPreFooter=insetPreFooter+$("#footer").height();
		$("#footer").css("width",w+"px");
		if($("#sidebar").length>0) {
			$("#sidebar").css("height",(h-$("#header").height()-$("#footer").height())+"px");
			$("#sidebar-inner").css("height",($("#sidebar").height()-3)+"px");
		}
	}
	if($("#prefooter").length>0) {
		h1=h1-$("#prefooter").height();
		$("#prefooter").css("width",(w1-0)+"px");
		$("#prefooter").css("bottom",insetPreFooter+"px");
	}
	
	if($("#content").length>0) {
		$("#content").css("height",h1+"px");	
		$("#content").css("width",(w1-0)+"px");
		$("#content").css("top",insetContent+"px");
	}
}
function loadBrowserCompatiblity() {
	if(navigator.appName.toLowerCase().indexOf("explorer")>-1) {
		var s=navigator.appVersion;
		var n1=s.indexOf("MSIE") + 5;
		var n2=s.indexOf(";",n1);
		var n3=s.substring(n1,n2);
		var n=parseFloat(n3);
		if(n<8) {
			notYetSupported("Sorry :-), You Are Using An Old Internet Explorer (ver "+n3+"), <br/>Which Is Not Yet Supported. We Are Working On That Though !</p></div>");
		} else {
			setTimeout(function() {
					$("#pageLoader").detach();
					$("#header").fadeIn();
					$("#sidebar").fadeIn();
					$("#banner").fadeIn();
					$("#content").fadeIn();
					$("#prefooter").fadeIn();
					$("#footer").fadeIn();
					$("#copyright").fadeIn();
				}, 100);
		}
	}
	//else if(navigator.appName.toLowerCase()=="opera") {
		//$("#header").css("width","102%");
		//$("#mainbody").css("width","102%");		
	//} 
	else {
		//Firefox, Chrome, Opera, IE8
		setTimeout(function() {
				$("#pageLoader").detach();
				$("#header").fadeIn();
				$("#sidebar").fadeIn();
				$("#banner").fadeIn();
				$("#content").fadeIn();
				$("#prefooter").fadeIn();
				$("#footer").fadeIn();
				$("#copyright").fadeIn();
			}, 100);
	}
}
function notYetSupported(msg) {
	$("#pageLoader").detach();
	$("body").html("<div id='loading-container' style='position:absolute; top:40%; left:37%;'><div class=ajaxerror></div>"+
							"<p id='loading-content1' align=center style='font: bold 18px Verdana, Arial, Helvetica, sans-serif; color: #777; text-shadow: 1px 1px 0px #ccc;'>" +
							msg);
}
