/*
*	jQuery Debugger
*
*	Copyright (c) 2008 Petr Staníček (pixy@pixy.cz)
*	January 2009
*
*	usage:

	var debug = new jQuery.debug( options ); // see var Prefs for options properties
	debug.dump(x);

example:

	var debug = new jQuery.debug({ posTo: {x:'right',y:'bottom'}, height:'150px',width:'150px' });
	var myObj = { 'arr' : [1,2,3], cnt : 3, elm : document.createElement('DIV') };
	debug.dump(myObj);	

*/


jQuery.extend({ debug : function(options) {

	// Properties

	var Prefs = jQuery.extend( {}, {

		excludeFunctions : true,

		listDOM : null,			// null | 'all' | 'props-only' | 'fn-only' | [ 'prop1', .. , 'propN' ]

		continuous : true,
		itemDivider : '<br>',
		maxParseDepth : 3,
		spacer : '&nbsp;&nbsp;',

		parent : 'body',				// jQuery selector like 'body' or '#main-box'

		posTo : { x:'left', y:'top' },	// x : left|right, y : top|bottom
		x : '0px',
		y : '0px',
		zIndex : 10000,
		overflow : 'auto',
		width : '150px',
		height : '300px',
		whiteSpace : 'nowrap',
		font : '11px/1.1 monospace',
		padding : '5px',
		background : '#333',
		color : '#6c3',
		labelColor : '#786'
		
		}, options );

	
	// Methods

	this.dump = function(x,label) {
		var s = '';
		if (label) s += fLbl(label);
		if (x!=undefined) s += formatObj(x);
		this.out(s);
		}

	this.out = function(s) {
		if (!this.Box) this.createBox();
		if (Prefs.continuous) jQuery(this.Box).prepend(s+Prefs.itemDivider);
		else jQuery(this.Box).html(s);
		}

	this.createBox = function() {
		this.Box = document.createElement('DIV');
		jQuery(Prefs.parent).prepend(this.Box);
		jQuery(this.Box).css({
			'position':'fixed',
			'z-index' : Prefs.zIndex,
			'overflow' : Prefs.overflow,
			'white-space' : Prefs.whiteSpace,
			'width':Prefs.width,
			'height':Prefs.height,
			'font':Prefs.font,
			'padding':Prefs.padding,
			'background':Prefs.background,
			'color':Prefs.color
			}).css(Prefs.posTo.x,Prefs.x).css(Prefs.posTo.y,Prefs.y);
		}

	// Private

	function formatObj(obj) {
		return parse(obj,1);
		}


	function fArr(arr,lQuo,rQuo,depth) {
		var p = '', br = '<br>';
		for (var i=0;i<depth;i++) p += Prefs.spacer;
		return lQuo + br + p + arr.join(',' + br + p) + br + p + rQuo;
		}
	function fLbl(s) {
		return '<span style="color:'+Prefs.labelColor+'">'+s+ ':</span> '
		}
	function parse(obj,depth,propList) {
		if (obj===null) return '<i>null</i>';
		else if (jQuery.isFunction(obj)) return '<i>Function</i>';
		else switch (typeof obj) {
			case undefined: return '<i>undefined</i>'; break;
			case 'number':
			case 'boolean':
				return obj.toString(); break;
			case 'string':
				return '"' + obj.replace(/"/g,'&quot;') + '"'; break;
			case 'function':
			case 'object':
				var s, oType = 0;
				if (obj.ownerDocument!=undefined) {
					s = '<i>DOM_object</i>';
					oType = 1;
					}
				else if (obj['jquery']!=undefined) {
					s = '<i>jQuery object</i>';
					oType = 2;
					}
				if (oType>0 && Prefs.listDOM && depth<Prefs.maxParseDepth) {
					var i,l, k, arr = [];
					if (typeof Prefs.listDOM == 'object' && Prefs.listDOM.concat!=undefined) {
						for (i=0,l=Prefs.listDOM.length;i<l;i++) {
							k = Prefs.listDOM[i];
							arr.push( fLbl(k) + parse(obj[k],depth+1) );
							}
						}
					else {
						var isFn, what = 0;
						if (Prefs.listDOM=='props-only') what = 1;
						if (Prefs.listDOM=='fn-only') what = 2;
						for (k in obj) {
							isFn = jQuery.isFunction(obj[k]);
							if (what==0 || (what==1 && !isFn) || (what==2 && isFn)) arr.push( fLbl(k) + parse(obj[k],depth+1) );
							}
						}
					s += ': ' + fArr(arr,'{','}',depth);
					}
				if (oType>0) return s;
				if (depth>=Prefs.maxParseDepth) return '{...}';
				var k, arr = [];
				for (k in obj) if (!Prefs.excludeFn || !jQuery.isFunction(obj[k])) arr.push( fLbl(k) + parse(obj[k],depth+1) );
				return fArr(arr,'{','}',depth);
				break;
			default:
				return '<i>undefined</i>'; break;
			}
		}
	
	
	}

});

