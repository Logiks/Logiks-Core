//All Popup Window Based Functions
function showPopupURL(url,title, params) {
	if(typeof iBox != "undefined") {
		if(params==null) {
			var params = {};
			params.width = '600';
			params.height = '300';
		}
		return iBox.showURL(url,title,params);
	} else {
		jqPopupURL(url, title);
	}
	return null;
}
function showPopupData(data, title, params) {
	if(title==null) {
		title="Message";
	}
	if(typeof iBox != "undefined") {
		if(params==null) {
			var params = {};
			params.width = '600';
			params.height = '300';
		}
		return iBox.show(data,title,params);
	} else if(typeof $.colorbox=="function") {
		if(params==null) {
			var params = {};
			params.width = '600';
			params.height = '300';
			params.html = data;
		}
		$.colorbox(params);
	} else {
		jqPopupData(data, title);
	}
	return null;
}
function showPopupDiv(divID, title, params) {
	if(title==null) {
		title=$(divID).attr("title");
	}
	if(typeof iBox != "undefined") {
		if(params==null) {
			var params = {};
			params.width = '600';
			params.height = '300';
		}
		return iBox.show($(divID).html(),title,params);
	} else {
		jqPopupDiv(divID,"Message");
	}
	return null;
}
function jqPopupURL(url, title, func, modalX, w, h, anim) {
	var resizable=true;
	if(title==null) {
		title="Message";
	}
	if(modalX==null) {
		modalX=true;
	}
	if(w==null) {
		w=600;
	} else {
		resizable=false;
	}
	if(h==null) {
		h=300;
	} else {
		resizable=false;
	}
	if(anim==null) {
		anim='slide';
	}
	var dialog = $('<div style="display:none" title="'+title+'"></div>').appendTo('body');
	if(func==null) {
		params={
			width:w,
			height:h,
			modal:modalX,
			stack:true,
			show:anim,
			hide:anim,
			resizable:resizable,
			draggable:true,
			dialogClass:'alert',
			closeOnEscape:true,
			close: function(event, ui) {
				dialog.remove();
			}
		};
	} else {
		params={
			width:w,
			height:h,
			modal:modalX,
			stack:true,
			show:anim,
			hide:anim,
			resizable:resizable,
			draggable:true,
			dialogClass:'alert',
			closeOnEscape:false,
			buttons: {
				Ok: function() {
					if(func!=null) {
						txt="OK";
						if(typeof(func)=='function') func(txt);
						else window[func](txt);
					}
					$(this).dialog("close");
					dialog.remove();
				},
				Cancel: function() {
					if(func!=null) {
						txt="CANCEL";
						if(typeof(func)=='function') func(txt);
						else window[func](txt);
					}
					$(this).dialog("close");
					dialog.remove();
				}
			},
			// add a close listener to prevent adding multiple divs to the document
			close: function(event, ui) {
				dialog.remove();
			}
		};
	}
	return dialog.load(
		url,
		{}, // omit this param object to issue a GET request instead a POST request, otherwise you may provide post parameters within the object
		function (responseText, textStatus, XMLHttpRequest) {
			dialog.dialog(params);
		}
	);
}
function jqPopupData(data, title, func, modalX, w, h, anim) {
	var resizable=true;
	if(title==null) {
		title="Message";
	}
	if(modalX==null) {
		modalX=true;
	}
	if(w==null) {
		w=600;
	} else {
		resizable=false;
	}
	if(h==null) {
		h=300;
	} else {
		resizable=false;
	}
	if(anim==null) {
		anim='slide';
	}
	var dialog = $('<div style="display:none" title="'+title+'">'+data+'</div>').appendTo('body');
	if(func==null) {
		params={
			width:w,
			height:h,
			modal:modalX,
			stack:true,
			show:anim,
			hide:anim,
			resizable:resizable,
			draggable:true,
			dialogClass:'alert',
			closeOnEscape:true,
			buttons: {
				Close: function() {
					$(this).dialog( "close" );
				}
			},
			close: function(event, ui) {
				// remove div with all data and events
				dialog.remove();
			}
		};
	} else {
		params={
			width:w,
			height:h,
			modal:modalX,
			stack:true,
			show:anim,
			hide:anim,
			resizable:resizable,
			draggable:true,
			dialogClass:'alert',
			closeOnEscape: true,
			buttons: {
				Ok: function() {
					if(func!=null) {
						txt="OK";
						if(typeof(func)=='function') func(txt);
						else window[func](txt);
					}
					$(this).dialog( "close" );
				},
				Cancel: function() {
					if(func!=null) {
						txt="CANCEL";
						if(typeof(func)=='function') func(txt);
						else window[func](txt);
					}
					$(this).dialog( "close" );
				}
			},
			// add a close listener to prevent adding multiple divs to the document
			close: function(event, ui) {
				// remove div with all data and events
				dialog.remove();
			}
		};
	}

	return dialog.dialog(params);
}
function jqPopupDiv(divID,func, modalX, w, h, anim) {
	var resizable=true;
	if(modalX==null) {
		modalX=true;
	}
	if(w==null) {
		w=600;
	} else {
		resizable=false;
	}
	if(h==null) {
		h=300;
	} else {
		resizable=false;
	}
	if(anim==null) {
		anim='slide';
	}
	if(func==null) {
		params={
			width:w,
			height:h,
			modal:modalX,
			stack:true,
			show:anim,
			hide:anim,
			resizable:resizable,
			draggable:false,
			closeOnEscape:true,
			dialogClass:'alert',
			buttons: {
				Close: function() {
					$(this).dialog("close");
				}
			},
			close: function(event, ui) {
				//event.preventDefault();
			}
		};
	} else {
		params={
			width:w,
			height:h,
			modal:modalX,
			stack:true,
			show:anim,
			hide:anim,
			resizable:resizable,
			draggable:true,
			closeOnEscape:false,
			dialogClass:'alert',
			buttons: {
				Close: function() {
					if(func!=null) {
						if(typeof(func)=='function') func("OK");
						else window[func]("OK");
					}
					$(this).dialog( "close" );
				},
			},
			close: function(event, ui) {
				//event.preventDefault();
			}
		};
	}
	return $(divID).dialog(params);
}
function osxPopupURL(url, title, func, w, anim) {
	if(title==null) {
		title="Message";
	}
	if(w==null) {
		w=600;
	}
	if(anim==null) {
		anim='blind';
	}
	var dialog = $('<div style="display:none" title="'+title+'"></div>').appendTo('body');
	if(func==null) {
		params={
			width:w,
			height:'auto',
			position:'top',
			modal:true,
			stack:true,
			show:anim,
			hide:anim,
			resizable:false,
			draggable:false,
			closeOnEscape:true,
			dialogClass:'osx',
			close: function(event, ui) {
				//event.preventDefault();
				dialog.remove();
			}
		};
	} else {
		params={
			width:w,
			height:'auto',
			position:'top',
			modal:true,
			stack:true,
			show:anim,
			hide:anim,
			resizable:false,
			draggable:false,
			closeOnEscape:true,
			dialogClass:'osx',
			buttons: {
				Ok: function() {
					if(func!=null) {
						txt="OK";
						if(typeof(func)=='function') func(txt);
						else window[func](txt);
					}
					$(this).dialog( "close" );
				},
				Cancel: function() {
					if(func!=null) {
						txt="CANCEL";
						if(typeof(func)=='function') func(txt);
						else window[func](txt);
					}
					$(this).dialog( "close" );
				}
			},
			close: function(event, ui) {
				//event.preventDefault();
				dialog.remove();
			}
		};
	}
	// load remote content
	return dialog.load(
		url,
		{}, // omit this param object to issue a GET request instead a POST request, otherwise you may provide post parameters within the object
		function (responseText, textStatus, XMLHttpRequest) {
			dialog.dialog(params);
		}
	);
}
function osxPopupData(data, title, func, w, anim) {
	if(title==null) {
		title="Message";
	}
	if(w==null) {
		w=600;
	}
	if(anim==null) {
		anim='blind';
	}
	var dialog = $('<div style="display:none" title="'+title+'">'+data+'</div>').appendTo('body');
	if(func==null) {
		params={
			width:w,
			height:'auto',
			position:'top',
			modal:true,
			stack:true,
			show:anim,
			hide:anim,
			resizable:false,
			draggable:false,
			closeOnEscape:true,
			dialogClass:'osx',
			close: function(event, ui) {
				//event.preventDefault();
			}
		};
	} else {
		params={
			width:w,
			height:'auto',
			position:'top',
			modal:true,
			stack:true,
			show:anim,
			hide:anim,
			resizable:false,
			draggable:false,
			closeOnEscape:true,
			dialogClass:'osx',
			buttons: {
				Ok: function() {
					if(func!=null) {
						txt="OK";
						if(typeof(func)=='function') func(txt);
						else window[func](txt);
					}
					$(this).dialog( "close" );
				},
				Cancel: function() {
					if(func!=null) {
						txt="CANCEL";
						if(typeof(func)=='function') func(txt);
						else window[func](txt);
					}
					$(this).dialog( "close" );
				}
			},
			close: function(event, ui) {
				//event.preventDefault();
			}
		};
	}
	return dialog.dialog(params);
}

function osxPopupDiv(divID,func, w, anim) {
	if(w==null) {
		w=600;
	}
	if(anim==null) {
		anim='blind';
	}
	if(func==null) {
		params={
			width:w,
			height:'auto',
			position:'top',
			modal:true,
			stack:true,
			show:anim,
			hide:anim,
			resizable:false,
			draggable:false,
			closeOnEscape:true,
			dialogClass:'osx',
			close: function(event, ui) {
				//event.preventDefault();
			}
		};
	} else {
		params={
			width:w,
			height:'auto',
			position:'top',
			modal:true,
			stack:true,
			show:anim,
			hide:anim,
			resizable:false,
			draggable:false,
			closeOnEscape:true,
			dialogClass:'osx',
			buttons: {
				Ok: function() {
					if(func!=null) {
						txt="OK";
						if(typeof(func)=='function') func(txt);
						else window[func](txt);
					}
					$(this).dialog( "close" );
				},
				Cancel: function() {
					if(func!=null) {
						txt="CANCEL";
						if(typeof(func)=='function') func(txt);
						else window[func](txt);
					}
					$(this).dialog( "close" );
				}
			},
			close: function(event, ui) {
				//event.preventDefault();
			}
		};
	}
	return $(divID).dialog(params);
}


//LGKS Dialog Functions
function lgksOverlay(msg, title, func) {
	if(title==null) {
		title="Message";
	}
	return jqPopupData(msg,title,function(txt) {
			if(func!=null) {
				if(typeof(func)=='function') func(txt);
				else window[func](txt);
			}
		}, true,$(window).width()-50,$(window).height()-100,"fade");
}
function lgksOverlayURL(url, title, func, w, h) {
	if(w==null) w=$(window).width()-50;
	if(h==null) h=$(window).height()-100;

	if(title==null) {
		title="Message";
	}
	if(func==null) {
		return jqPopupURL(url,title,null, true, w, h,"slide");
	} else {
		return jqPopupURL(url,title,function(txt) {
			if(func!=null) {
				if(typeof(func)=='function') func(txt);
				else window[func](txt);
			}
		}, true,$(window).width()-50,$(window).height()-100,"fade");
	}
}
function lgksOverlayDiv(divID, func, w, h) {
	if(w==null) w=$(window).width()-50;
	if(h==null) h=$(window).height()-100;

	return jqPopupDiv(divID,function(txt) {
			if(func!=null) {
				if(typeof(func)=='function') func(txt);
				else window[func](txt);
			}
		}, true, w, h,"fade");
}
function lgksOverlayFrame(url, title, func, w, h) {
	if(w==null) w=$(window).width()-50;
	if(h==null) h=$(window).height()-100;

	if(title==null) {
		title="Message";
	}
	data="<iframe src='"+url+"' width=100% height=100% frameborder=0 style='margin:auto;'></iframe>";
	var dialog = $('<div class=ui-corner-all style="display:none;overflow:hidden;padding:0px;" title="'+title+'">'+data+'</div>').appendTo('body');

	if(func==null) {
		params={
			width:w,
			height:h,
			modal:true,
			stack:true,
			show:'fade',
			hide:"blind",
			resizable:false,
			draggable:false,
			dialogClass:'alert',
			closeOnEscape:true,
			close: function(event, ui) {
				dialog.remove();
			}
		};
	} else {
		params={
			width:w,
			height:h,
			modal:true,
			stack:true,
			show:'fade',
			hide:"blind",
			resizable:false,
			draggable:false,
			dialogClass:'alert',
			closeOnEscape:true,
			buttons: {
				Select:function() {
					if(func!=null) {
						if(typeof(func)=='function') func();
						else window[func]();
					}
					$(this).dialog( "close" );
				},
				Close: function() {
					$(this).dialog( "close" );
				}
			},
			close: function(event, ui) {
				dialog.remove();
			}
		};
	}
	return dialog.dialog(params);
}
function lgksPrompt(msg, title, fields, func) {
	oriField=fields;
	if(fields==null) {
		fields={
				"input":{
					"title":"",
					"type":"text",
				}
			};
	}
	if(typeof fields=="string") {
		sr=fields;
		fields={
				"input":{
					"title":"",
					"type":"text",
					"value":sr,
				}
			};
	}
	if(title==null) {
		title="Message";
	}
	$("#spd123").detach();
	s="<div id='spd123' style='width:100%;display:none;' title='"+title+"' > ";
	s+=msg+"<br/><br/>";
	s+="</div>";
	$(s).appendTo('body');
	if((typeof $.fn.generateForm)!="function") {
		sr="";
		if(typeof oriField=="string") sr=oriField;
		fields={
				"input":{
					"title":"",
					"type":"text",
					"value":sr,
				}
			};
		$("#spd123").css("width","200px");
		s1="<input id=input name=input value='"+sr+"' type=text style='width:98%;border:1px solid #777;'>";
		$("#spd123").append(s1);
	} else {
		$("#spd123").generateForm(fields);
		$("#spd123").loadFormUI();
	}
	return jqPopupDiv("#spd123",function(txt) {
			if(txt=="OK") {
				if(func!=null) {
					out={};
					$.each(fields, function(key, value) {
						if(value.type=="checkbox") {
							out[key]=$("#spd123 #" + key).is(":checked");
						} else if(value.type=="radio") {
							out[key]=$("#spd123 #" + key+":checked").val();
						} else {
							out[key]=$("#spd123 #" + key).val();
						}
					});
					if(out.input!=null && out.input.length>=0) {
						if(typeof(func)=='function') func(out.input);
						else window[func](out.input);
					} else {
						if(typeof(func)=='function') func(out);
						else window[func](out);
					}
				}
			}
			$("#spd123").detach();
		}, true,'auto','auto');
}
function lgksConfirm(msg, title, okFunc, cancelFunc) {
	if(title==null) {
		title="Message";
	}
	return jqPopupData(msg,title,function(txt) {
			if(txt=="OK") {
				if(okFunc!=null) {
					if(typeof(okFunc)=='function') okFunc();
					else window[okFunc]();
				}
			} else {
				if(cancelFunc!=null) {
					if(typeof(cancelFunc)=='function') cancelFunc();
					else window[cancelFunc]();
				}
			}
		}, true,'auto','auto');
}
function lgksAlert(msg, title) {
	if(title==null) {
		title="Message";
	}
	return jqPopupData(msg,title,null, true,'auto','auto');
}
function lgksPopup(divID,buttons, paramConfigs,type,title) {
	if(type==null) {
		type='div';
	}
	if(title==null) title="Message";
	if((typeof buttons)=="function") {
		func=buttons;
		buttonParams={
				Close: function() {
					if(func!=null) {
						if(typeof(func)=='function') func("OK");
						else window[func]("OK");
					}
					$(this).dialog( "close" );
				},
			};
	} else if((typeof buttons)=="object") {
		buttonParams=buttons;
	} else {
		buttonParams={};
	}

	params={
			width:'400',
			height:'210',
			modal:true,
			stack:true,
			show:'blind',
			hide:'blind',
			resizable:true,
			draggable:true,
			closeOnEscape:false,
			dialogClass:'alert',
			buttons: buttonParams,
			close: function(event, ui) {
				//event.preventDefault();
			}
		};
	if(paramConfigs != null) {
		$.each(paramConfigs, function(key,val) {
			params[key]=val;
		});
	}
	if(type=="div") {
		return $(divID).dialog(params);
	} else if(type=="data") {
		var dialog = $('<div style="display:none" title="'+title+'">'+divID+'</div>').appendTo('body');
		params['close']=function(event, ui) {
				dialog.remove();
			};
		return dialog.dialog(params);
	} else if(type=="url") {
		var dialog = $('<div style="display:none" title="'+title+'"></div>').appendTo('body');
		return dialog.load(
				divID,
				{}, // omit this param object to issue a GET request instead a POST request, otherwise you may provide post parameters within the object
				function (responseText, textStatus, XMLHttpRequest) {
					dialog.dialog(params);
				}
			);
	}
	return null;
}
