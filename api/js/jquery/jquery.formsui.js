// jQuery Basic Form UI Plugin
//
// Version 1.01
//
// Bismay Kumar Mohapatra
// Visit http://openlogiks.org for more information
if(jQuery) (function($){
	$.extend($.fn, {
		loadFormUI: function() {
				var frmid="#"+$(this).attr("id");
				$(frmid+" .required").parents("td").append("<div style='float:right;' class='field_required'></div>");
				
				$(frmid+" .datefield").each(function() {
					if(typeof window.$.Zebra_DatePicker == "function") {
						$(this).css("width","88%");
						$(this).Zebra_DatePicker({
								offset:[-350,230],
								format:'d/m/Y'
							});
					} else {
						if(typeof window.$.timepicker == "object") {
							$(this).datepicker({
								changeMonth: true,
								changeYear: true,
								//maxDate:'+20y',//+5m +1w
								//minDate:'-2y',//-1m -1w
								yearRange: '1950:2100',
								showButtonPanel: true,
							});
						} else {
							$(this).datepicker({
								changeMonth: true,
								changeYear: true,
								//maxDate:'+20y',//+5m +1w
								//minDate:'-2y',//-1m -1w
								yearRange: '1950:2100',
								showButtonPanel: false,
							});
						}
					}
				});
				if(typeof window.$.timepicker == "object") {
						$(frmid+" .datetimefield").each(function() {
							$(this).datetimepicker({
									timeFormat:'h:mTT',
									separator:' ',
									ampm: true,
								});
						});
					
					$(frmid+" .timefield").timepicker({
								timeFormat:'h:mTT',
								separator:'@',
								ampm: true,
							});
					//$(frmid+" .datetimefield, "+frmid+" .datefield, "+frmid+" .timefield").attr("readonly","readonly");
				}
				if(typeof $.uniform=="object") {
					//$(frmid+"button").uniform();
					$(frmid+" select, "+frmid+" input:file").uniform();
					//$(frmid+"input, textarea, select, button").uniform();
				}
				
				$(frmid+" button:not(.nostyle)").button();				
				$(frmid+" .progressbar").each(function() {
						x=0;
						if($(this).attr("value")!=null) x=parseInt($(this).attr("value"));
						$(this).progressbar({
							value:x,
						});
					});
				
				$(frmid+" .slider").each(function() {
						min1=0;
						max1=100;
						val=0;
						forWhom="";
						
						if($(this).attr("min")!=null) min1=parseInt($(this).attr("min"));
						if($(this).attr("max")!=null) max1=parseInt($(this).attr("max"));
						if($(this).attr("value")!=null) val=parseInt($(this).attr("value"));
						
						$(this).slider({
								min:min1,
								max:max1,
								value:val,
								orientation:"horizontal",
								range: "min",
								animate: true,
								slide: function( event, ui ) {
									if($(this).attr("for")!=null) {
										$($(this).attr("for")).val(ui.value);
										$($(this).attr("for")).html(ui.value);
									}
								}
							});
					});
				//if(typeof $.ui.rateit=="function") {
				$(frmid+" .rateit").each(function() { 
						cnt=5;
						val=0;
						forWhom="";
						iconOn1="";
						iconOff1="";
						
						if($(this).attr("count")!=null) cnt=parseInt($(this).attr("count"));
						if($(this).attr("value")!=null) val=parseInt($(this).attr("value"));
						if($(this).attr("for")!=null) forWhom=$(this).attr("for");
						if($(this).attr("iconOn")!=null) iconOn1=$(this).attr("iconOn");
						if($(this).attr("iconOff")!=null) iconOff1=$(this).attr("iconOff");
						
						$(this).rateit({
								count:cnt,
								value:val,
								animate:true,
								iconOn:iconOn1,
								iconOff:iconOff1,
							}, function(val,maxCnt) {
									if(forWhom.length>0) {
										$(forWhom).val(val);
										$(forWhom).html(val);
									}
								});
					});
				if(typeof $.ui.tagit=="function") {
					$(frmid+' .tagfield').each(function() {
							sf=true;
							as=false;
							if($(this).attr("singleField")!=null) sf=$(this).attr("singleField");
							if($(this).attr("allowSpaces")!=null) as=$(this).attr("allowSpaces");
							$(this).removeClass("tagfield");
							$(this).tagit({
								singleField:sf,
								allowSpaces:as,
							});
						});
				}
			},
			generateForm: function(fields) {
				var s="";
				$.each(fields, function(key, value) {
					if(value.type==null) value.type="text";
					if(value.name==null) value.name=key;
					if(value.title==null) value.title=key;
					s+="<div style='width:150px;float:left;margin-left:20px;'>"+value.title+"</div>";
					if(value.type=='text') {
						s+="<input "+genTags(key, value, 'textfield')+"type=text style='width:350px;float:left;border:1px solid #aaa;' />";
					} else if(value.type=='password') {
						s+="<input "+genTags(key, value,'password')+"type=text style='width:150px;float:left;border:1px solid #aaa;' />";
					} else if(value.type=='date') {
						s+="<input "+genTags(key, value,'datefield')+"type=text style='width:150px;float:left;border:1px solid #aaa;' />";
					} else if(value.type=='email') {
						s+="<input "+genTags(key, value, 'emailfield')+"type=text style='width:350px;float:left;border:1px solid #aaa;' />";
					} else if(value.type=='phone') {
						s+="<input "+genTags(key, value, 'phonefield')+"type=text style='width:350px;float:left;border:1px solid #aaa;' />";
					} else if(value.type=='url') {
						s+="<input "+genTags(key, value, 'urlfield')+"type=text style='width:350px;float:left;border:1px solid #aaa;' />";
					} else if(value.type=='file') {
						s+="<input "+genTags(key, value, 'filefield')+"type=file style='width:350px;float:left;border:1px solid #aaa;' />";
					} else if(value.type=='tags') {
						s+="<input "+genTags(key, value, 'tagfield')+"type=text style='width:350px;float:left;border:1px solid #aaa;' />";
					} else if(value.type=='rate') {
						s+="<input "+genTags(key, value, 'text')+"type=text style='width:35px;float:left;border:1px solid #aaa;text-align:center' />";
						s+="<div class=rateit for='#"+key+"' ";
						if(value.rate!=undefined) {
							if(value.rate.count!=undefined) s+="count='"+value.rate.count+"' "; else s+="count='5' ";
							if(value.rate.iconOn!=undefined) s+="iconOn='"+value.rate.iconOn+"' ";
							if(value.rate.iconOff!=undefined) s+="iconOff='"+value.rate.iconOff+"' ";
							if(value.rate.value!=undefined) s+="value='"+value.value+"' "; else s+="value='0'";
						} else {
							s+="count='5' ";
							s+="value='0'";
						}						
						s+="style='margin-left:20px;height:25px;float:left;'></div>";						
					} else if(value.type=='list') {
						s+="<select "+genTags(key, value,'select')+"style='width:360px;float:left;border:1px solid #aaa;'>";
						$.each(value.list, function(k, v) {
							if(value.list.length>0) {
								s+="<option value='"+v+"'>"+v+"</option>";
							} else {
								s+="<option value='"+k+"'>"+v+"</option>";
							}
						});
						s+="</select>";
					} else if(value.type=='checkbox') {
						s+="<input "+genTags(key, value, 'checkbox')+"type=checkbox style='width:25px;float:left;border:1px solid #aaa;' />";
					} else if(value.type=='radio') {
						$.each(value.list, function(k, v) {
							if(value.list.length>0) {
								value.value=v;
								s+="<input "+genTags(key, value, 'radio')+"type=radio style='width:25px;float:left;border:1px solid #aaa;'/>";
							} else {
								value.value=k;
								s+="<input "+genTags(key, value, 'radio')+"type=radio style='width:25px;float:left;border:1px solid #aaa;'/>";
							}
							s+="<span style='float:left'>"+v+"</span>";
						});
					} else if(value.type=='slider') {
						s+="<input "+genTags(key, value, 'text')+"type=text style='width:35px;float:left;border:1px solid #aaa;text-align:center' />";
						s+="<div class=slider min='"+value.slider.minimum+"' max='"+value.slider.maximum+"' for='#"+key+"' value='"+value.value+"'";
						s+="style='margin-left:20px;margin-top:10px;width:280px;float:left;border:1px solid #aaa;'></div>";
					}
					s+="<br/><br/>";
				});
				
				function genTags(key, value, clas) {
					var r="";
					r+=" id="+key+" ";
					if(value.name!=null) r+="name='"+value.name+"' ";
					if(value.value!=null) r+="value='"+value.value+"' ";
					if(value.tips!=null) r+="tips='"+value.tips+"' ";
					if(value.clas!=null) r+="class='"+clas+" " + value.clas+"' "; else  r+="class='"+ clas+"' ";
					return r;
				}
				$(this).append(s);
			}
		});
})(jQuery);
