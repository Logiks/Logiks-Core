/* Simple AJAX Kit v1.0 */
/* Author : Bismay Kumar Mohapatra */
/* Used for Ajax Operations */

var loadingMsg = "<td class=ajaxloading3>Loading ...</td>";

this.createAJAX = function() {
	var xmlhttp=null;
	var failed=false;
	if (window.XMLHttpRequest) {
		// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp=new XMLHttpRequest();
	} else {
		// code for IE6, IE5
		try {
			xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e1) {
			try {
				xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e2) {
				this.xmlhttp = null;
			}
		}
	}
	if (! this.xmlhttp) {
		if (typeof XMLHttpRequest != "undefined") {
			xmlhttp = new XMLHttpRequest();
			
			xmlhttp.ontimeout = function(){
				alert("Request timed out");
			}
			
		} else {
			failed = true;
		}
	}
	if(failed) return null;
	else return xmlhttp;
};

this.runAJAXPostForm = function(formElement, func) {
	http=createAJAX();

	var params="";
	var ele=formElement.getElementsByTagName('input');
	for(var i=0;i<ele.length;i++) {
		if(ele[i].type=='reset' || ele[i].type=='submit') continue;
		if(ele[i].type=='radio') {
			if(ele[i].checked) {
				params+="&" + ele[i].name + "=" + ele[i].value;
			}
			continue;
		}
		//ele[i].type=='file'
		params+="&" + ele[i].name + "=" + ele[i].value;
	}
	var ele=formElement.getElementsByTagName("textarea");
	for(var i=0;i<ele.length;i++) {
		params+="&" + ele[i].name + "=" + ele[i].value;
	}
	var ele=formElement.getElementsByTagName("select");
	for(var i=0;i<ele.length;i++) {
		params+="&" + ele[i].name + "=" + ele[i].value;
	}

	http.onreadystatechange=function() {
		if (http.readyState==4) {
			if(http.status==200) {
				txt=http.responseText;
				invokeFunction(func,txt);
			} else {
				txt="<div id=\"ajaxerror" + Math.floor(Math.random()*1000) + "\"><table width=100% height=" + (ele.offsetHeight - 50) + "px><tr><td class=ajaxerror>" +
				"<br/> Error : " + getAJAXError(http.status) + "</td></tr></table></div>";
				invokeFunction(func,txt);
			}
		}
	}

	http.open("POST", formElement.getAttribute('action'), true);
	http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	http.setRequestHeader("Content-length", params.length);
	//http.setRequestHeader("Content-Type", "text/xml");
	//http.setRequestHeader("Referer", "http://www.google.com");
	//http.setRequestHeader("User-Agent", "Mozilla/5.0");
	//http.setRequestHeader("Accept","text/plain");
	http.setRequestHeader("Connection", "close");
	http.send(params);
}

this.runAJAXGet = function(divID, url, func) {
	xmlhttp=createAJAX();
	var ele=document.getElementById(divID);
	
	var str="<div id=\"ajaxloading" + Math.floor(Math.random()*1000) + "\"><table width=100% height=" + (ele.offsetHeight - 50) + "px><tr>" + loadingMsg + "</tr></table></div>";
	ele.innerHTML=str;

	xmlhttp.open("GET",url,true);
	xmlhttp.send();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState==4) {
			if(xmlhttp.status==200) {
				txt=xmlhttp.responseText;
				ele.innerHTML=txt;
				invokeFunction(func,txt);
			} else {
				txt="<div id=\"ajaxerror" + Math.floor(Math.random()*1000) + "\"><table width=100% height=" + (ele.offsetHeight - 50) + "px><tr><td class=ajaxerror>" +
				"<br/> Error : " + getAJAXError(xmlhttp.status) + "</td></tr></table></div>";
				ele.innerHTML=txt;
				invokeFunction(func,txt);
			}
		}
		//alert(xmlhttp.readyState + " " + xmlhttp.status);
	}
}

this.runAJAXPost = function(divID, linkURL, qurl, func) {
	xmlhttp=createAJAX();
	var ele=document.getElementById(divID);
	
	var str="<div id=\"ajaxloading" + Math.floor(Math.random()*1000) + "\"><table width=100% height=" + (ele.offsetHeight - 50) + "px><tr>" + loadingMsg + "</tr></table></div>";
	ele.innerHTML=str;

	xmlhttp.open("POST",linkURL,true);
	xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xmlhttp.setRequestHeader("Content-length", qurl.length);
	xmlhttp.setRequestHeader("Connection", "close");
	xmlhttp.send(qurl);
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState==4) {
			if(xmlhttp.status==200) {
				txt=xmlhttp.responseText;
				ele.innerHTML=txt;
				invokeFunction(func,txt);
			} else {
				txt="<div id=\"ajaxerror" + Math.floor(Math.random()*1000) + "\"><table width=100% height=" + (ele.offsetHeight - 50) + "px><tr><td class=ajaxerror>" +
				"<br/> Error : " + getAJAXError(xmlhttp.status) + "</td></tr></table></div>";
				ele.innerHTML=txt;
				invokeFunction(func,txt);
			}
		}
	}
}

this.processAJAXQuery = function(url, func) {
	xmlhttp=createAJAX();
	
	xmlhttp.open("GET",url,true);
	xmlhttp.send();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState==4) {
			if(xmlhttp.status==200) {				
				txt=xmlhttp.responseText;
				invokeFunction(func,txt);
			} else {
				txt="<div id=\"ajaxerror" + Math.floor(Math.random()*1000) +
				"\"><table width=100% height=100%><tr><td class=ajaxerror>" +
				"<br/> Error : " + getAJAXError(xmlhttp.status) + "</td></tr></table></div>";
				invokeFunction(func,txt);
			}
		}
	}
}
this.processAJAXPostQuery = function(url, qurl, func) {
	xmlhttp=createAJAX();
	
	xmlhttp.open("POST",url,true);
	xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xmlhttp.setRequestHeader("Content-length", qurl.length);
	xmlhttp.setRequestHeader("Connection", "close");
	xmlhttp.send(qurl);
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState==4) {
			if(xmlhttp.status==200) {				
				txt=xmlhttp.responseText;
				invokeFunction(func,txt);
			} else {
				txt="<div id=\"ajaxerror" + Math.floor(Math.random()*1000) +
				"\"><table width=100% height=100%><tr><td class=ajaxerror>" +
				"<br/> Error : " + getAJAXError(xmlhttp.status) + "</td></tr></table></div>";
				invokeFunction(func,txt);
			}
		}
	}
}
this.processAJAXCatalog = function(url, func) {
	xmlhttp=createAJAX();
	xmlhttp.open("GET",url,true);
	xmlhttp.send();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState==4) {
			if(xmlhttp.status==200) {
				xml=xmlhttp.responseXML;
				invokeFunction(func,xml);
			} else {
				txt="<div id=\"ajaxerror" + Math.floor(Math.random()*1000) +
				"\"><table width=100% height=100%><tr><td class=ajaxerror>" +
				"<br/> Error : " + getAJAXError(xmlhttp.status) + "</td></tr></table></div>";
				invokeFunction(func,txt);
			}
		}
	}
}
this.processAJAXPostCatalog = function(url, qurl,func) {
	xmlhttp=createAJAX();
	
	xmlhttp.open("POST",url,true);
	xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xmlhttp.setRequestHeader("Content-length", qurl.length);
	xmlhttp.setRequestHeader("Connection", "close");
	xmlhttp.send(qurl);
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState==4) {
			if(xmlhttp.status==200) {
				xml=xmlhttp.responseXML;
				invokeFunction(func,xml);
			} else {
				txt="<div id=\"ajaxerror" + Math.floor(Math.random()*1000) +
				"\"><table width=100% height=100%><tr><td class=ajaxerror>" +
				"<br/> Error : " + getAJAXError(xmlhttp.status) + "</td></tr></table></div>";
				invokeFunction(func,txt);
			}
		}
	}
}
//Extra Functions
this.getAJAXError = function(code) {
	if(code==400) return "Bad Request";
	else if(code==403) return "Forbidden";
	else if(code==404) return "Page Not Found";
	else if(code=="50x") return "Server Errors";
	else if(code==500) return "Internal Server Error";
	else if(code==999) return "Null Initiallization Error";
	else return "Unknown Error Code";
}
function invokeFunction(func,txt) {
	if(func==null || func.length<=0) return;
	if(typeof(func)=='function') func(txt);
	else window[func](txt);
}
//Utility Functions
function ajaxChainCombo(element, comboTarget,scriptName) {	
	var s="../services/?scmd=" + scriptName + "&value=" + element.value+"&name="+element.name;
	document.getElementById(comboTarget).innerHTML='<option>Loading...</option>';
	runAJAX(comboTarget,s);
}
function AJAXSubmit(id,href,func, hidden) {
	if(hidden==null) hidden=true;
	$("*").addClass("PageLoadWait");
	
	var params = {};
	if(hidden) {
		l=$(id)
		.find("input[type=hidden]")
		.each(function() {
			params[ this.name || this.id || this.parentNode.name || this.parentNode.id ] = encodeURIComponent(this.value);
		});
	}
	l=$(id)
	//.find("input[type=text], input[type=password], input[type=radio]:checked, input[type=checkbox], textarea, select")
	.find("input[type=text],input[type=email],input[type=url],input[type=phone],input[type=number],input[type=search],input[type=date],input[type=password], input[type=radio]:checked, input[type=checkbox], textarea, select")
	.filter(":enabled")
	.each(function() {
		if($(this).attr("type")=='checkbox') {
			if($(this).is(":checked")) params[ this.name || this.id || this.parentNode.name || this.parentNode.id ] = "true";
			else params[ this.name || this.id || this.parentNode.name || this.parentNode.id ] = "false";
		} else {
			params[ this.name || this.id || this.parentNode.name || this.parentNode.id ] = encodeURIComponent($(this).val());
		}
	});
	
	$.post(
		href,
		params, 
		function(txt){
			$("*").removeClass("PageLoadWait");
			invokeFunction(func,txt);
	});
	/*$.ajax({
		  type: 'POST',
		  url: href,
		  data: params,
		  success: function(txt) {
				console.log(txt);
			},
		  error: function(txt) {
				console.log(txt);
			},
		});*/
}
