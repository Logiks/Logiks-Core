function initPageUI(ui) {
	$("select:not(.nostyle):not(multiple)",ui).addClass("ui-state-default ui-corner-all");

	$("button:not(.nostyle)",ui).button();
	$(".tabs",ui).tabs();
	$(".accordion",ui).accordion();
	$(".buttonset",ui).buttonset();

	$(".noselection").disableSelection();

	$(".datefield",ui).each(function() {
		//$(this).attr("id",Math.ceil(Math.random()*100000000));
		$(this).datepicker({
				separator:' ',
				changeMonth:true,
				changeYear:true,
				showButtonPanel:false,
				yearRange:"1950:2100",
				dateFormat:"d/m/yy",
			});
	});
	if(typeof $.fn.ghosttext=="function") {
		$(".ghosttext",ui).each(function() {
			$(this).ghosttext();
		});
	}

	if($(".datatable tbody").length>0) {
		$(".datatable tbody").delegate("input[type=checkbox][name=rowselect]", "change", function() {
					if($(this).is(":checked")) {
						$(this).parent().parent().addClass("highlight");
					} else {
						$(this).parent().parent().removeClass("highlight");
					}
			});
	}
}
function invokePopup(ppdiv,onrun,w,h) {
	if(w==null) w=500;//400
	if(h==null) h=300;//500
	$(ppdiv+":ui-dialog").dialog("destroy");
	if(onrun==null) {
		$(ppdiv).dialog({
			width:w,
			height:h,
			modal:true,
			closeOnEscape:false,
			resizable:false,
			show:'slide',
			buttons: {
				Close:function() {
					$(this).dialog("close");
				},
			},
			open: function(event, ui) { $(".ui-dialog-titlebar-close").hide(); },
			close:function(event, ui) {
			},
		});
	} else {
		$(ppdiv).dialog({
			width:w,
			height:h,
			modal:true,
			closeOnEscape:false,
			resizable:false,
			show:'slide',
			buttons: {
				Run:function() {
					if(onrun()) $(this).dialog("close");
				},
				Close:function() {
					$(this).dialog("close");
				},
			},
			open: function(event, ui) { $(".ui-dialog-titlebar-close").hide(); },
			close:function(event, ui) {
			},
		});
	}
}
function printError(div,title,msg) {
	html='<div class="ui-state-error ui-corner-all" style="padding: 0 .7em;width:400px;margin:auto;"> ';
	html+='<p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span> ';
	html+='<strong>'+title+'</strong><p style="padding-left:15px;margin-top:0;">'+msg+'</p></p>';
	html+='</div>';
	$(div).html(html);
}
function printWarning(div,title,msg) {
	html='<div class="ui-state-error ui-corner-all" style="padding: 0 .7em;width:400px;margin:auto;"> ';
	html+='<p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span> ';
	html+='<strong>'+title+'</strong><p style="padding-left:15px;margin-top:0;">'+msg+'</p></p>';
	html+='</div>';
	$(div).html(html);
}
function hashCode(s){
  return s.split("").reduce(function(a,b){a=((a<<5)-a)+b.charCodeAt(0);return a&a},0);              
}