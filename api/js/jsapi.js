function jsloader() {
	var scripts=new Array()
	var callback;
	var over=false;
	var me=this;

	this.loadFromLib=function(name,vers) {
		var jsfile=_service("jsapi")+"&jslib=" + name + "&vers=" + vers;
		scripts.push(jsfile);
	}
	this.loadFromLink=function(jslink) {
		scripts.push(jslink);
	}

	this.startFetch=function(func) {
		this.callback=func;
		scripts.reverse();
		over=false;
		this.fetchScript();
	}

	this.fetchScript=function() {
		//alert(scripts.length);
		if(scripts.length==0) {
			me.scriptLoaded();
			return;
		}
		
		jslink=scripts.pop();
		var head= document.getElementsByTagName('head')[0];
		var script= document.createElement('script');
		script.type= 'text/javascript';
		
		//For IE Only
		script.onreadystatechange= me.fetchScript;
		//For Gecko/Opera
		script.onload= me.fetchScript;
				
		script.src= jslink;
		head.appendChild(script);
	}
	
	this.scriptLoaded=function() {		
		if(over==true) return;
		else over=true;
		
		if(this.callback==null) return;
		if(typeof(this.callback)=='function') this.callback();
		else window[this.callback]();
	}
}
var jsapi=new jsloader();
