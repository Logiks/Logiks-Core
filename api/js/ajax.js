/* Simple AJAX Kit v1.0 */
/* Author : Bismay Kumar Mohapatra */
/* Used for Ajax Operations */

var ajaxLoadingMsg = "<td class='ajaxloading3'>Loading ...</td>";

function LAJAX() {
	var xmlhttp=null;
	var failed=false;
	var fx=this;
	fx.createAJAX = function() {
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
					xmlhttp = null;
				}
			}
		}
		if (! xmlhttp) {
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

	fx.runAJAXPostForm = function(formElement, func) {
		http=fx.createAJAX();

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
					"<br/> Error : " + this.getAJAXError(http.status) + "</td></tr></table></div>";
					invokeFunction(func,txt);
				}
			}
		}

		http.open("POST", formElement.getAttribute('action'), true);
		http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		http.setRequestHeader("Content-length", params.length);
		http.setRequestHeader("Connection", "close");
		http.send(params);
	}

	fx.runAJAXGet = function(divID, url, func) {
		xmlhttp=fx.createAJAX();
		var ele=document.getElementById(divID);

		var str="<div id=\"ajaxloading" + Math.floor(Math.random()*1000) + "\"><table width=100% height=" + (ele.offsetHeight - 50) + "px><tr>" + ajaxLoadingMsg + "</tr></table></div>";
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
					"<br/> Error : " + this.getAJAXError(xmlhttp.status) + "</td></tr></table></div>";
					ele.innerHTML=txt;
					invokeFunction(func,txt);
				}
			}
			//alert(xmlhttp.readyState + " " + xmlhttp.status);
		}
	}

	fx.runAJAXPost = function(divID, linkURL, qurl, func) {
		xmlhttp=fx.createAJAX();
		var ele=document.getElementById(divID);

		var str="<div id=\"ajaxloading" + Math.floor(Math.random()*1000) + "\"><table width=100% height=" + (ele.offsetHeight - 50) + "px><tr>" + ajaxLoadingMsg + "</tr></table></div>";
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
					"<br/> Error : " + this.getAJAXError(xmlhttp.status) + "</td></tr></table></div>";
					ele.innerHTML=txt;
					invokeFunction(func,txt);
				}
			}
		}
	}

	fx.processAJAXQuery = function(url, func, responseFormat) {
		xmlhttp=fx.createAJAX();

		xmlhttp.open("GET",url,true);
		xmlhttp.send();
		xmlhttp.onreadystatechange=function() {
			if (xmlhttp.readyState==4) {
				if(xmlhttp.status==200) {
					txt="";
					if(responseFormat=="xml") {
						txt=xmlhttp.responseXML;
					} else if(responseFormat=="json") {
						txt=JSON.parse(xmlhttp.responseText);
					} else {
						txt=xmlhttp.responseText;
					}
					invokeFunction(func,txt);
				} else {
					txt="<div id=\"ajaxerror" + Math.floor(Math.random()*1000) +
					"\"><table width=100% height=100%><tr><td class=ajaxerror>" +
					"<br/> Error : " + this.getAJAXError(xmlhttp.status) + "</td></tr></table></div>";
					invokeFunction(func,txt);
				}
			}
		}
	}
	fx.processAJAXPostQuery = function(url, qurl, func, responseFormat) {
		xmlhttp=fx.createAJAX();

		xmlhttp.open("POST",url,true);
		xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xmlhttp.setRequestHeader("Content-length", qurl.length);
		xmlhttp.setRequestHeader("Connection", "close");
		xmlhttp.send(qurl);
		xmlhttp.onreadystatechange=function() {
			if (xmlhttp.readyState==4) {
				if(xmlhttp.status==200) {
					txt="";
					if(responseFormat=="xml") {
						txt=xmlhttp.responseXML;
					} else if(responseFormat=="json") {
						txt=JSON.parse(xmlhttp.responseText);
					} else {
						txt=xmlhttp.responseText;
					}
					invokeFunction(func,txt);
				} else {
					txt="<div id=\"ajaxerror" + Math.floor(Math.random()*1000) +
					"\"><table width=100% height=100%><tr><td class=ajaxerror>" +
					"<br/> Error : " + this.getAJAXError(xmlhttp.status) + "</td></tr></table></div>";
					invokeFunction(func,txt);
				}
			}
		}
	}
	fx.getAJAXError = function(code) {
		if(code==400) return "Bad Request";
		else if(code==403) return "Forbidden";
		else if(code==404) return "Page Not Found";
		else if(code=="50x") return "Server Errors";
		else if(code==500) return "Internal Server Error";
		else if(code==999) return "Null Initiallization Error";
		else return "Unknown Error Code";
	}
}
function runAJAXPostForm(formElement, func) {
	new LAJAX().runAJAXPostForm(formElement, func);
}
function runAJAXGet(divID, url, func) {
	new LAJAX().runAJAXGet(divID, url, func);
}
function runAJAXPost(divID, linkURL, qurl, func) {
	new LAJAX().runAJAXPost(divID, linkURL, qurl, func);
}
function processAJAXQuery(url, func, responseFormat) {
	new LAJAX().processAJAXQuery(url, func, responseFormat);
}
function processAJAXPostQuery(url, qurl, func, responseFormat) {
	new LAJAX().processAJAXPostQuery(url, qurl, func, responseFormat);
}

//Utility Functions
function invokeFunction(func,txt) {
	if(func==null || func.length<=0) return;
	if(typeof(func)=='function') func(txt);
	else window[func](txt);
}
function ajaxChainCombo(element, comboTarget,scriptName,func) {
	var s=getServiceCMD(scriptName) + "&value=" + encodeURIComponent(element.value) +"&name="+element.name;
	document.getElementById(comboTarget).innerHTML='<option>Loading...</option>';
	runAJAXGet(comboTarget,s,func);
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
	.find("input[type=text],input[type=email],input[type=url],input[type=phone],input[type=number],input[type=search],input[type=color],input[type=date],input[type=password], input[type=radio]:checked, input[type=checkbox], textarea, select")
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