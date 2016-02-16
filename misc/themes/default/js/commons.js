/* Common JS Commands And Functions v1.0.0 */
/*Extending Some Basic DataTypes*/
Array.prototype.inArray = function (value) {
	var i;
	for (i=0; i < this.length; i++) {
		if (this[i] === value) {
			return true;
		}
	}
	return false;
};

String.prototype.startsWith = function(str) {
	return (this.match("^"+str)==str)
}
String.prototype.endsWith = function(str) {
	return (this.match(str+"$")==str);
}
String.prototype.capitalize = function() {
	return this.replace(/\w+/g, function(a) {
			return a.charAt(0).toUpperCase() + a.substr(1).toLowerCase();
		});
}
String.prototype.toTitle = function(){ 
	a=this.replace("_"," ");
	a=a.capitalize();
	return a;
}
function getWindowSize() {
	winDimension=null;
	if(typeof $=="function") {
		winDimension={
			"w":$(window).width(),
			"h":$(window).height(),
		};
	} else {
		winDimension={
			"w":(typeof window.innerWidth != 'undefined' ? window.innerWidth : document.body.offsetWidth),
			"h":(typeof window.innerHeight != 'undefined' ? window.innerHeight : document.body.offsetHeight),
		};
	}
	return winDimension;
}
function throttle(fn, delay) {
	var timer = null;
	return function () {
			var context = this, args = arguments;
			clearTimeout(timer);
			timer = setTimeout(function () {
					fn.apply(context, args);
				}, delay);
		};
}
function addEvent(elm, evType, func, useCapture) {
	
	if (elm.addEventListener) {
		elm.addEventListener(evType, func, useCapture);
		return true;
	}
	else if (elm.attachEvent) {
		var r = elm.attachEvent('on' + evType, func);
		return r;
	} else {
		elm['on' + evType] = func;
	}
}

function addLoadEvent(func) {
	var oldonload = window.onload;
	if (typeof window.onload != 'function') {
		window.onload = func;
	} else {
		window.onload = function() {
			oldonload();
			func();
		}
	}
}

//addEvent(window,'load',func1,false);
//addEvent(window,'load',func2,false);
//addEvent(window,'load',func3,false);

function getElementsByClass(searchClass,node,tag) {
	var classElements = new Array();
	if ( node == null )
		node = document;
	if ( tag == null )
		tag = '*';
	var els = node.getElementsByTagName(tag);
	var elsLen = els.length;
	var pattern = new RegExp('(^|\\\\s)'+searchClass+'(\\\\s|$)');
	for (i = 0, j = 0; i < elsLen; i++) {
		if ( pattern.test(els[i].className) ) {
			classElements[j] = els[i];
			j++;
		}
	}
	return classElements;
}

function ucwords(str) {
    return (str + '').replace(/^([a-z])|\s+([a-z])/g, function ($1) {
        return $1.toUpperCase();
    });
}
function trim(str) {// We don't want to trip JUST spaces, but also tabs,line feeds, etc.  Add anything else you want to "trim" here in Whitespace
  return RTrim(LTrim(str));
}
function RTrim(str) {
  var whitespace = new String(" \t\n\r");
  var s = new String(str);
  if (whitespace.indexOf(s.charAt(s.length-1)) != -1) // We have a string with trailing blank(s)...
  {
        var i = s.length - 1; // Get length of string
        while (i >= 0 && whitespace.indexOf(s.charAt(i)) != -1)// Iterate from the far right of string until we don't have any more whitespace...
          i--;
        s = s.substring(0, i+1);// Get the substring from the front of the string to where the last non-whitespace character is...
   }
  return s;
}
function LTrim(str) {
  var whitespace = new String(" \t\n\r");
  var s = new String(str);
  if (whitespace.indexOf(s.charAt(0)) != -1) // We have a string with leading blank(s)...
  {
        var j=0, i = s.length;
        while (j < i && whitespace.indexOf(s.charAt(j)) != -1)// Iterate from the far left of string until we don't have any more whitespace...
            j++;
        s = s.substring(j, i);// Get the substring from the first non-whitespace character to the end of the string...
  }
  return s;
}
function cleanText(txt) {
	txt=txt.replace('&','and');
	return txt;
}

//HTML Element Based Functions
function toggle(obj) {
	var el = document.getElementById(obj);
	if ( el.style.display != 'none' ) {
		el.style.display = 'none';
	} else {
		el.style.display = '';
	}
}

function toggleElement(obj, show) {
	var el = document.getElementById(obj);
	if(show) {
		el.style.display = '';
	} else {
		el.style.display = 'none';
	}
}

function toggleElementsByName(name, show) {	
	var divs = document.getElementsByName(name);
	//alert("NAME " + name + " " + divs.length);
	for(i=0;i<divs.length;i++) {
		//alert(divs[i].name);
		if(show) {
			divs[i].style.display = '';
		} else {
			divs[i].style.display = 'none';
		}
	}
}

function showdiv(pass) {
	var divs = document.getElementsByTagName('div');
	for(i=0;i<divs.length;i++){
		if(divs[i].id.match(pass)){//if they are 'see' divs
			if (document.getElementById) // DOM3 = IE5, NS6
				divs[i].style.display="block";// show/hide
			else if (document.layers) // Netscape 4
				document.divs[i].display = 'block';
			else // IE 4
				document.all.divs[i].display = 'block';
			} else {
				if (document.getElementById)
					divs[i].style.display="none";
				else if (document.layers) // Netscape 4
					document.divs[i].display = 'none';
				else // IE 4
					document.all.divs[i].display = 'none';
			}
	}
}

function disable(tag,disp){
	var fld=document.getElementsByName(tag);
	for(var i=0;i<fld.length;i++){
		var fields=fld[i].getElementsByTagName('input');
		for(var j=0;j<fields.length;j++){
			//fields[j].style.display=disp;
			fields[j].disabled=disp;
		}
		var fields=fld[i].getElementsByTagName('textarea');
		for(var j=0;j<fields.length;j++){
			//fields[j].style.display=disp;
			fields[j].disabled=disp;
		}
		var fields=fld[i].getElementsByTagName('select');
		for(var j=0;j<fields.length;j++){
			//fields[j].style.display=disp;
			fields[j].disabled=disp;
		}
	}
}

function showField(formID,input) {
	if(input.value=='yes' || input.value=='true' || input.value=='1')
		document.getElementById(formID).style.display="block";
	else
		document.getElementById(formID).style.display="none";
}

function insertAfter(parent, node, referenceNode) {
	parent.insertBefore(node, referenceNode.nextSibling);
}

function calcTime(time) {
  var units = {
      "yr": 24*60*365,
      "mnth": 24*60*30,
      "week": 24*60*7,
      "day": 24*60,
      "hr": 60,
      "min": 1,
  }

  var result = []

  for(var name in units) {
    var p =  Math.floor(time/units[name]);
    if(p == 1) result.push(p + " " + name);
    if(p >= 2) result.push(p + " " + name + "s");
    time %= units[name]
  }
  return result.join(" ");
}