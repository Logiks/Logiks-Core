function lgksAlert(msg,title,callback) {
	return bootbox.alert(msg, callback);
}
function lgksPrompt(msg,title,callback) {
	return bootbox.prompt(msg, callback);
}
function lgksConfirm(msg,title,callback) {
	return bootbox.confirm(msg, callback);
}

function lgksOverlay(msg, title, paramsXtra) {
	params={
		className: "overlayBox",
		size: "large",
	};
	params=$.extend(params,paramsXtra);
	lgksMsg(msg, title, params);
}
function lgksOverlayDiv(divID,title, paramsXtra) {
	if(title==null) title=$(divID).attr('title');
	msg=$(divID).html();
	
	params={
		className: "overlayBox",
		size: "large",
	};
	params=$.extend(params,paramsXtra);
	lgksMsg(msg, title, params);
}

function lgksOverlayFrame(url, title, paramsXtra) {
	msg="<iframe width=100% height=100% frameborder=0 style='border:0px;' class='overlayFrame' src='"+url+"'></iframe>";
	params={
		className: "overlayBox",
		size: "large",
	};
	params=$.extend(params,paramsXtra);
	lgksMsg(msg, title, params);
}

function lgksOverlayURL(url, title, callBackFunc, paramsXtra) {
	processAJAXQuery(url,function(txt) {
		params={
			className: "overlayBox",
			size: "large",
		};
		params=$.extend(params,paramsXtra);
		lgksMsg(txt, title, params);
		callBackFunc();
	},"TEXT");
}

function lgksMsg(msg,title,paramsXtra) {
	params={
	  /**
	   * @required String|Element
	   */
	  message: "I am a custom dialog",

	  /**
	   * @optional String|Element
	   * adds a header to the dialog and places this text in an h4
	   */
	  title: "Custom title",

	  /**
	   * @optional Function
	   * allows the user to dismisss the dialog by hitting ESC, which
	   * will invoke this function
	   */
	  onEscape: function() {},

	  /**
	   * @optional Boolean
	   * @default: true
	   * whether the dialog should be shown immediately
	   */
	  show: true,

	  /**
	   * @optional Boolean
	   * @default: true
	   * whether the dialog should be have a backdrop or not
	   */
	  backdrop: true,

	  /**
	   * @optional Boolean
	   * @default: true
	   * show a close button
	   */
	  closeButton: true,

	  /**
	   * @optional Boolean
	   * @default: true
	   * animate the dialog in and out (not supported in < IE 10)
	   */
	  animate: true,

	  /**
	   * @optional String
	   * @default: null
	   * an additional class to apply to the dialog wrapper
	   */
	  className: "my-modal",

	  /**
	   * @optional Object
	   * @default: {}
	   * any buttons shown in the dialog's footer
	   */
	  buttons: {
	  }
	};
	params.title=title;
	params.message=msg;

	params=$.extend(params,paramsXtra);

	bootbox.dialog(params);
}